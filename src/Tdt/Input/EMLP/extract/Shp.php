<?php

/**
 * This class handles a SHP file, using the ShapeFile.inc.php file as its library.
 * This library reads a SHP file through file handlers, thus making its workflow read the
 * data through a stream.
 *
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 */

namespace Tdt\Input\EMLP\Extract;

include_once(__DIR__ . "/../../../../lib/ShapeFile.inc.php");
include_once(__DIR__ . "/../../../../lib/proj4php/proj4php.php");

class SHP extends AExtractor
{

    private $read_record; // the ShapeFile.inc has a function getNext() which basically is the combination of hasNext() and pop()
    // therefore we're going to wrap getNext() into hasNext() and set read_record, which we return in pop().
    private $shape_file_wrapper; // represents the library, containing the file handler and several help functions.
    private $EPSG = "";

    protected function open($uri)
    {
        set_time_limit(1337); // reading records can take a long while, set the time limit to a high number.
        if (isset($this->config["EPSG"])) {
            $this->EPSG = $this->config["EPSG"];
        }

        /**
         *  Put the files into a temp directory if it's not been done already.
         * TODO clear the tmp via the uniqid() return value after extraction.
         */
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

                $this->shape_file_wrapper = new \ShapeFile("tmp/" . $tmpFile . ".shp", $options); // along this file the class will use file.shx and file.dbf
            } else {
                $this->shape_file_wrapper = new \ShapeFile($uri, $options); // along this file the class will use file.shx and file.dbf
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
         * Since our records are hierarchical we will deliver a flattened object of the record since the ETML expects this
         *
         */

        if (($record = $this->shape_file_wrapper->getNext()) != false) {
            // read meta data

            $rowobject =array();

            $dbf_data = $record->getDbfData();

            foreach ($dbf_data as $property => $value) {
                $property = strtolower($property);
                $rowobject[$property] = trim($value);
            }

            $shp_data = $record->getShpData();
            if (isset($shp_data['parts']) || $shp_data['x']) {
                // read shape data

                if ($this->EPSG != "") {
                    $proj4 = new \Proj4php();
                    $projSrc = new \Proj4phpProj('EPSG:'. $this->EPSG, $proj4);
                    $projDest = new \Proj4phpProj('EPSG:4326', $proj4);
                }

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

                    if ($EPSG != "") {
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
            // filehandlers are handled and properly closed in the shp library.
    }
}
