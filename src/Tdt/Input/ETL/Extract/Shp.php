<?php

namespace Tdt\Input\ETL\Extract;

/**
 * This class handles a SHP file, using the ShapeFile.inc.php file as its library.
 * This library reads a SHP file through file handlers, thus making its workflow read the
 * data through a stream.
 *
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 */

use muka\ShapeReader\ShapeReader;

class SHP extends AExtractor
{
    private $read_record;
    private $shape_file_wrapper;
    private $EPSG = "";

    protected function open()
    {
        if (isset($this->extractor["epsg"])) {
            $this->EPSG = $this->extractor["epsg"];
        }

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
        } catch (Exception $ex) {
            throw new \Exception("Something went wrong during the configuration of the SHP Loader: $ex->getMessage()");
        }
    }

    public function hasNext()
    {
        /**
         * We have to return our information in non-hierarchical manner!
         * This brings some complications with handling shp files ofourcse
         * This class will be used as a normal reader would be used namely
         * while(hasNext()){ $data = pop()}
         *
         * Since our records are hierarchical we will deliver a flattened object
         * of the record since the ETML expects this
         *
         */

        if (($record = $this->shape_file_wrapper->getNext()) != false) {
            $rowobject =array();

            $dbf_data = $record->getDbfData();

            foreach ($dbf_data as $property => $value) {
                $property = strtolower($property);
                $rowobject[$property] = trim($value);
            }

            $shp_data = $record->getShpData();

            if (isset($shp_data['parts']) || $shp_data['x']) {
                $proj4 = new \Proj4php();

                $projSrc = new \Proj4phpProj('EPSG:'. $this->EPSG, $proj4);
                $projDest = new \Proj4phpProj('EPSG:4326', $proj4);

                if (isset($shp_data['parts'])) {
                    $parts = array();

                    foreach ($shp_data['parts'] as $part) {
                        $points = array();

                        foreach ($part['points'] as $point) {
                            $x = $point['x'];
                            $y = $point['y'];

                            if ($this->EPSG != "" || true) {
                                $pointSrc = new \proj4phpPoint($x, $y);

                                $pointDest = $proj4->transform($projSrc, $projDest, $pointSrc);
                                $x = $pointDest->x;
                                $y = $pointDest->y;
                            }

                            $points[] = $x.','.$y;
                        }
                        array_push($parts, implode(" ", $points));
                    }

                    $rowobject["coords"] = implode(';', $parts);
                }

                if (isset($shp_data['x'])) {
                    $x = $shp_data['x'];
                    $y = $shp_data['y'];

                    if ($this->EPSG != "") {
                        $pointSrc = new \proj4phpPoint($x, $y);
                        $pointDest = $proj4->transform($projSrc, $projDest, $pointSrc);
                        $x = $pointDest->x;
                        $y = $pointDest->y;
                    }

                    $rowobject["long"] = $x;
                    $rowobject["lat"] = $y;
                }
            }
            $this->read_record = $rowobject;

            return true;
        } else {
            return false;
        }
    }

    public function pop()
    {
        return $this->read_record;
    }

    protected function close()
    {
    }
}
