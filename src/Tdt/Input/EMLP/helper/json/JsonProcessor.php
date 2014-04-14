<?php

namespace Tdt\Input\EMLP\Helper\Json;

class JsonProcessor implements \tdt\json\JSONChunkProcessor
{

    private $obj;
    private $new = false;

    public function process($chunk)
    {

        //set the flag: a new object is loaded
        $this->new = true;
        $this->obj = $this->flatten(json_decode($chunk, true));
    }

    private function flatten($ar)
    {
        $new = array();

        if (!empty($ar)) {
            foreach ($ar as $k => $v) {
                if (is_array($v)) {

                    if (is_int($k)) {
                        $k++;
                    }

                    $prefix = $k;
                    $flat = $this->flatten($v);

                    foreach ($flat as $fkey => $fval) {
                        $new[$prefix . "_" . $fkey] = $fval;
                    }
                } else {

                    if (is_int($k)) {
                        $k++;
                    }

                    $new[$k] = $v;
                }
            }
            return $new;
        }
        return $ar;
    }

    public function hasNew()
    {
        return $this->new;
    }

    public function pop()
    {
        if ($this->new) {
            $this->new = false;
            return $this->obj;
        } else {
            return null;
        }
    }
}
