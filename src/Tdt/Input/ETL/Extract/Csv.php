<?php

namespace Tdt\Input\ETL\Extract;

// Flexibility measure for MS-DOS, Mac line endings in a file.
ini_set('auto_detect_line_endings', true);

class Csv extends AExtractor
{
    use Encoding;

    private $handle;
    private $row_index;
    private $header;

    protected function open()
    {
        $this->uri = $this->extractor->uri;
        $this->encoding = $this->extractor->encoding;
        $this->is_uri_tmp_file = false;

        // Keep track at which row the Csv handler is
        $this->row_index = 0;

        // Open a filehandle for the uri
        // TODO: this can probably be removed
        $ssl_options = array(
                            'ssl' => array(
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                ),
                            );

        // If the file is an only file, copy its contents to a temporary file first
        if (substr($this->uri, 0, 4) == 'http') {
            $tmp_file = sys_get_temp_dir() . '/' . uniqid() . '.csv';

            $this->downloadFile($this->uri, $tmp_file);

            $this->uri = $tmp_file;
            $this->is_uri_tmp_file = true;
        }

        $this->handle = fopen($this->uri, 'r', false, stream_context_create($ssl_options));

        if (! $this->handle) {
            $this->log("Could not open the file with location $this->uri.");
            die;
        }

        $this->log("Opened the CSV file located at $this->uri");

        if ($this->extractor->has_header_row && ($data = fgetcsv($this->handle, 0, $this->extractor->delimiter)) !== false) {
            $csvIndex = 0;

            foreach ($data as &$el) {
                if ($this->encoding != 'UTF-8') {
                    $el = $this->convertToUtf8($el, $this->encoding);
                }

                $this->header[$csvIndex] = $this->fixUtf8($el);
                $csvIndex++;
            }

            $this->row_index++;
        }
    }

    /**
     * Download a remote file to a local file
     *
     * @param  string $url
     * @param  string $tmp_file
     * @return void
     */
    private function downloadFile($url, $tmp_file)
    {
        $fp = fopen($tmp_file, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);

        $data = curl_exec($ch);

        curl_close($ch);
        fclose($fp);
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext()
    {
        return ! feof($this->handle);
    }

    /**
     * Gives us the next chunk to process through our emlp
     * @return a chunk in a php array
     */
    public function pop()
    {
        $row = array();

        if (($data = fgetcsv($this->handle, 0, $this->extractor->delimiter)) !== false) {
            $csvIndex = 0;

            foreach ($data as $el) {
                if ($this->encoding != 'UTF-8') {
                    $el = $this->convertToUtf8($el, $this->encoding);
                }

                if ($this->extractor->has_header_row) {
                    $row[$this->header[$csvIndex]] = $this->fixUtf8($el);
                } else {
                    $row[$csvIndex] = $this->fixUtf8($el);
                }

                $csvIndex++;
            }
        }

        $this->log("Extracted data from row $this->row_index");
        $this->row_index++;

        return $row;
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close()
    {
        fclose($this->handle);

        if ($this->is_uri_tmp_file) {
            unlink($this->uri);
        }
    }
}
