<?php

namespace Tdt\Input\ETL\Extract;

class XML extends AExtractor
{
    use Encoding;

    private $next;
    private $reader;
    private $index = 0;

    protected function open()
    {
        $uri = $this->extractor->uri;
        $this->encoding = $this->extractor->encoding;

        $this->reader = new \XMLReader();

        if (!$this->reader->open($uri)) {
            $this->log("The uri ($uri) could not be opened. Make sure it is retrievable by the server.");
        }

        $arraylevel = $this->extractor->arraylevel;

        if (!$this->reader->next()) {
            $this->log("Could not get next element from the XML document, make sure you provided the correct arraylevel.");
        }

        for ($i = 1; $i < $arraylevel; $i++) {
            $this->reader->read();
        }

        $this->next = $this->reader->expand();
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext()
    {
        if (!empty($this->next)) {
            return true;
        } else {
            if ($this->reader->next()) {
                $this->next = $this->reader->expand();
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Gives us the next chunk to process through our ETML
     * @return a chunk in a php array
     */
    public function pop()
    {
        if ($this->hasNext()) {
            $document = array();
            $this->makeFlat($document, $this->next);

            unset($this->next);
            $this->index= 0;

            $utf8_document = [];

            if ($this->encoding != 'UTF-8') {
                foreach ($document as $key => $value) {

                }
            }

            return $document;
        } else {
            return null;
        }

    }

    private function parseAttributes(&$document, &$xmlobject, $name)
    {
        if (!empty($xmlobject->attributes)) {
            foreach ($xmlobject->attributes as $key => $value) {
                $document[ $this->index ] = $value->value;
                $document[$name . "_attr_" . $key] = $value->value;
                $this->index++;
            }
        }
    }

    private function makeFlat(&$document, &$xmlobject, $parentname = "", $index = null)
    {
        // Prefix for row names
        if ($parentname == "") {
            $prefix = "";
            $name = $xmlobject->nodeName;
        } else {
            $prefix = $parentname;
            $name =  "_" . $xmlobject->nodeName;

            if (!empty($index)) {
                $index--;
                $name = $name . "[" . $index . "]";
            }
        }

        // Parse the attributes
        $this->parseAttributes($document, $xmlobject, $prefix . $name);

        if (sizeof($xmlobject->childNodes) == 0) {
            //store the value of the element in the document array under its prefix name
            $document[ $prefix ] = $xmlobject->nodeValue;

            //count the number of keys we have.
            $this->index++;
        } else {
            //then the children
            $frequency = array(); //an array of how many times a certain key occurred
            $current_index = array();
            // You have to fill in the frequency table first, there's no way of knowing otherwise how many elements of the same name
            // are after it.
            foreach ($xmlobject->childNodes as $child) {
                if (empty($frequency[$child->nodeName])) {
                    $frequency[$child->nodeName] = 0;
                    $current_index[$child->nodeName] = 1;
                }
                $frequency[$child->nodeName]++;
            }

            foreach ($xmlobject->childNodes as $child) {
                // If the child's name did not occur yet, add both [0] and without the 0 for backward compatibility
                if (isset($frequency[$child->nodeName])) { // This shouldn't be checked, just for safety measures.
                    if ($frequency[$child->nodeName] > 1) {
                        if ($current_index[$child->nodeName] == 1) {
                            $this->makeFlat($document, $child, $prefix . $name, null);
                        }

                        $this->makeFlat($document, $child, $prefix . $name, $current_index[$child->nodeName]);
                        $current_index[$child->nodeName]++;
                    } else {
                        $this->makeFlat($document, $child, $prefix . $name, null);
                    }

                }
            }
        }
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close()
    {
        $this->reader->close();
    }
}
