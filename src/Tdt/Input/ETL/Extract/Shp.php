<?php

namespace Tdt\Input\ETL\Extract;

/**
 * This class handles a SHP file, using the ShapeFile.inc.php file as its library.
 * This library reads a SHP file through file handlers, thus making its workflow read the
 * data through a stream.
 *
 * @copyright (C) 2011 by iRail vzw/asbl
 * @author Jan Vansteenlandt jan@okfn.be
 * @license AGPLv3
 */

use muka\ShapeReader\ShapeReader;
use proj4php\Proj;
use proj4php\Proj4php;
use proj4php\Point;

class SHP extends AExtractor
{
    use Encoding;

    private $read_record;
    private $shape_file_wrapper;
    private $EPSG = "";

    private $RECORD_TYPES = [
        0 => "Null Shape",
        1 => "Point",
        3 => "PolyLine",
        5 => "Polygon",
        8 => "MultiPoint",
        11 => "PointZ",
        13 => "PolyLineZ",
        15 => "PolygonZ",
        18 => "MultiPointZ"
    ];

    protected function open()
    {
        if (isset($this->extractor["epsg"])) {
            $this->EPSG = $this->extractor["epsg"];
        }

        $this->encoding = $this->extractor["encoding"];
        $uri = $this->extractor['uri'];

        if (!is_dir("tmp")) {
            mkdir("tmp");
        }

        try {
            $options = array('noparts' => false);
            $isUrl = (substr($uri, 0, 4) == "http");

            if ($isUrl) {
                $tmpFile = uniqid();
                file_put_contents("tmp/" . $tmpFile . ".shp", file_get_contents(substr($uri, 0, strlen($uri) - 4) . ".shp"));
                file_put_contents("tmp/" . $tmpFile . ".dbf", file_get_contents(substr($uri, 0, strlen($uri) - 4) . ".dbf"));
                file_put_contents("tmp/" . $tmpFile . ".shx", file_get_contents(substr($uri, 0, strlen($uri) - 4) . ".shx"));

                $this->shape_file_wrapper = new ShapeReader("tmp/" . $tmpFile . ".shp", $options);
            } else {
                $this->shape_file_wrapper = new ShapeReader($uri, $options);
            }
        } catch (\Exception $ex) {
            throw new \Exception("Something went wrong during the configuration of the SHP Loader: $ex->getMessage()");
        }
    }

    public function hasNext()
    {
        if (($record = $this->shape_file_wrapper->getNext()) != false) {
            $rowobject =array();

            $dbf_data = $record->getDbfData();

            foreach ($dbf_data as $property => $value) {
                if ($this->encoding != 'UTF-8') {
                    $property = $this->convertToUtf8($property, $this->encoding);
                    $value = $this->convertToUtf8($value, $this->encoding);
                }

                $property = $this->fixUtf8($property);
                $value = $this->fixUtf8($value);

                $property = strtolower($property);
                $rowobject[$property] = trim($value);
            }

            $shp_data = $record->getShpData();
            $shape_type = $this->RECORD_TYPES[$record->getTypeCode()];

            // Prepare the projection class
            $this->proj4 = new Proj4php();

            $this->projSrc = new Proj('EPSG:'. $this->EPSG, $this->proj4);
            $this->projDest = new Proj('EPSG:4326', $this->proj4);

            $geometry = [];

            switch (strtolower($shape_type)) {
                case 'point':
                    $geometry = $this->parsePoint($shp_data);
                    break;
                case 'polyline':
                    $geometry = $this->parsePolyline($shp_data);
                    break;
                case 'polygon':
                    $geometry = $this->parsePolygon($shp_data);
                    break;
                case 'multipoint':
                    $geometry = $this->parseMultipoint($shp_data);
                    break;
                case 'pointz':
                    $geometry = $this->parsePointZ($shp_data);
                    break;
                case 'polylinez':
                    $geometry = $this->parsePolylineZ($shp_data);
                    break;
                case 'polygonz':
                    $geometry = $this->parsePolygonZ($shp_data);
                    break;
                case 'multipointz':
                    $geometry = $this->parseMultipointZ($shp_data);
                    break;
            }

            $rowobject['location'] = $geometry;

            $this->read_record = $rowobject;

            return true;
        } else {
            return false;
        }
    }

    private function parsePoint($shp_data)
    {
        $x = $shp_data['x'];
        $y = $shp_data['y'];

        if ($this->EPSG != "") {
            $pointSrc = new Point($x, $y);
            $pointDest = $this->proj4->transform($this->projSrc, $this->projDest, $pointSrc);
            $x = $pointDest->x;
            $y = $pointDest->y;
        }

        return [
            'coordinates' => [$x, $y],
            'type' => 'point'
        ];
    }

    private function parsePointZ($shp_data)
    {
        $x = $shp_data['x'];
        $y = $shp_data['y'];
        $z = $shp_data['z'];

        if ($this->EPSG != "" && $this->EPSG != 4326) {
            $pointSrc = new Point($x, $y, $z);
            $pointDest = $this->proj4->transform($this->projSrc, $this->projDest, $pointSrc);
            $x = $pointDest->x;
            $y = $pointDest->y;
            $z = $pointDest->z;
        }

        return [
            'coordinates' => [$x, $y, $z],
            'type' => 'point'
        ];
    }

    private function parsePolyline($shp_data)
    {
        $parts = [];
        $points = [];

        foreach ($shp_data['parts'] as $part) {
            foreach ($part['points'] as $point) {
                $x = $point['x'];
                $y = $point['y'];

                if ($this->EPSG != "" || true) {
                    $pointSrc = new Point($x, $y);

                    $pointDest = $this->proj4->transform($this->projSrc, $this->projDest, $pointSrc);
                    $x = $pointDest->x;
                    $y = $pointDest->y;
                }

                $points[] = [$x, $y];
            }
            array_push($parts, $points);
        }

        return [
            'coordinates' => $parts,
            'type' => 'multilinestring'
        ];
    }

    private function parsePolylineZ($shp_data)
    {
        $parts = [];
        $points = [];

        foreach ($shp_data['parts'] as $part) {
            foreach ($part['points'] as $point) {
                $x = $point['x'];
                $y = $point['y'];
                $z = $point['z'];

                if ($this->EPSG != "" && $this->EPSG != 4326) {
                    $pointSrc = new Point($x, $y, $z);

                    $pointDest = $this->proj4->transform($this->projSrc, $this->projDest, $pointSrc);
                    $x = $pointDest->x;
                    $y = $pointDest->y;
                    $z = $pointDest->z;
                }

                $points[] = [$x, $y, $z];
            }
            array_push($parts, $points);
        }

        return [
            'coordinates' => $parts,
            'type' => 'multilinestring'
        ];
    }

    private function parsePolygon($shp_data)
    {
        $parts = [];
        $points = [];

        foreach ($shp_data['parts'] as $part) {
            foreach ($part['points'] as $point) {
                $x = $point['x'];
                $y = $point['y'];

                if ($this->EPSG != "" || true) {
                    $pointSrc = new Point($x, $y);

                    $pointDest = $this->proj4->transform($this->projSrc, $this->projDest, $pointSrc);
                    $x = $pointDest->x;
                    $y = $pointDest->y;
                }

                $points[] = [$x, $y];
            }
            array_push($parts, $points);
        }

        return [
            'coordinates' => $parts,
            'type' => 'polygon'
        ];
    }

    private function parsePolygonZ($shp_data)
    {
        $parts = [];
        $points = [];

        foreach ($shp_data['parts'] as $part) {
            foreach ($part['points'] as $point) {
                $x = $point['x'];
                $y = $point['y'];
                $z = $point['z'];

                if ($this->EPSG != "" && $this->EPSG != 4326) {
                    $pointSrc = new Point($x, $y, $z);

                    $pointDest = $this->proj4->transform($this->projSrc, $this->projDest, $pointSrc);
                    $x = $pointDest->x;
                    $y = $pointDest->y;
                    $z = $pointDest->z;
                }

                $points[] = [$x, $y, $z];
            }
            array_push($parts, $points);
        }

        return [
            'coordinates' => $parts,
            'type' => 'polygon'
        ];
    }

    private function parseMultipoint($shp_data)
    {
        $points = [];

        foreach ($shp_data['points'] as $point) {
            $x = $point['x'];
            $y = $point['y'];

            if ($this->EPSG != "" || true) {
                $pointSrc = new Point($x, $y);

                $pointDest = $this->proj4->transform($this->projSrc, $this->projDest, $pointSrc);
                $x = $pointDest->x;
                $y = $pointDest->y;
            }

            $points[] = [$x, $y];
        }

        return [
            'coordinates' => $points,
            'type' => 'multipoint'
        ];
    }

    private function parseMultipointZ($shp_data)
    {
        $points = [];

        foreach ($shp_data['points'] as $point) {
            $x = $point['x'];
            $y = $point['y'];
            $z = $point['z'];

            if ($this->EPSG != "" && $this->EPSG != 4326) {
                $pointSrc = new Point($x, $y, $z);

                $pointDest = $this->proj4->transform($this->projSrc, $this->projDest, $pointSrc);
                $x = $pointDest->x;
                $y = $pointDest->y;
                $z = $pointDest->z;
            }

            $points[] = [$x, $y, $z];
        }

        return [
            'coordinates' => $points,
            'type' => 'multipoint'
        ];
    }

    public function pop()
    {
        return $this->read_record;
    }

    protected function close()
    {
    }
}
