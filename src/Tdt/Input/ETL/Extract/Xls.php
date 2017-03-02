<?php

namespace Tdt\Input\ETL\Extract;

use Tdt\Core\DataControllers\XLSController;

class Xls extends AExtractor
{
    protected $rows;

    protected function open()
    {
        $extension = XLSController::getFileExtension($this->extractor->uri);

        if (empty($this->extractor->start_row)) {
            $this->extractor->start_row = 0;
        }

        $reader = XLSController::loadExcel($this->extractor->uri, XLSController::getFileExtension($this->extractor->uri), $this->extractor->sheet);

        $this->rows = $this->readDataFromXls($reader, $this->extractor->start_row);
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext()
    {
        return ! empty($this->rows);
    }

    /**
     * Gives us the next chunk to process through our emlp
     * @return a chunk in a php array
     */
    public function pop()
    {
        $row = array_shift($this->rows);

        if (! empty($row)) {
            return $row;
        }

        return [];
    }

    private function readDataFromXls($reader)
    {
        $worksheet = $reader->getSheetByName($this->extractor->sheet);

        $row_objects = [];

        if (empty($worksheet)) {
            throw new \Exception(500, "The worksheet $this->extractor->sheet could not be found in the Excel file located on $this->extractor->uri.");
        }

        $headers = [];

        // The amount of rows added to the result
        $total_rows = 0;

        // Iterate all the rows of the Excell sheet
        foreach ($worksheet->getRowIterator() as $row) {
            $row_index = $row->getRowIndex();

            // If our offset is ok, start parsing the data from the excell sheet
            if ($row_index > $this->extractor->start_row) {
                $cell_iterator = $row->getCellIterator();
                $cell_iterator->setIterateOnlyExistingCells(false);

                $rowobject = [];

                // Iterate each cell in the row, create an array of the values with the name of the column
                // Indices start from 1 in the Excel API
                $data = array();

                foreach ($cell_iterator as $cell) {
                    $data[$cell->columnIndexFromString($cell->getColumn()) - 1] = $cell->getCalculatedValue();
                }

                if (! empty($headers)) {
                    $indexRow = [];
                    $mapped_rowobject = [];

                    foreach ($headers as $index => $header_value) {
                        $mapped_rowobject[$header_value] = $data[$index];
                    }

                    array_push($row_objects, $mapped_rowobject);
                } else {
                    $headers = $data;
                }
            }
        }

        \Log::info($row_objects);

        $reader->disconnectWorksheets();

        return $row_objects;
    }

    public function close()
    {
        //
    }
}
