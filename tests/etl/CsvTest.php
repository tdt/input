<?php

use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the CSV extractor and emlp sequence
 *
 */
class CsvTest extends \Orchestra\Testbench\TestCase
{
    private $test_extraction_cases = array(
        'regios' => array(
            'file' => 'csv/regios.csv',
            'count' => 5,
            'delimiter' => ',',
            'has_header_row' => 1,
            'start_row' => 0,
            'encoding' => 'UTF-8',
        ),
    );

    private $test_emlp_cases = array(
         array(
            'extract' => array(
                'file' => 'csv/regios.csv',
                'verify' => 'csv/serialized_regios',
                'delimiter' => ',',
                'has_header_row' => 1,
                'start_row' => 0,
                'encoding' => 'UTF-8',
            ),
            'map' => array(
                'file' => 'regios.ttl',
                'base_uri' => 'http://foo.mock/',
                'triples_amount' => 6,
            ),
            ),
    );

    private function getMockedCommand()
    {
        $command = Mockery::mock('Illuminate\Console\Command');
        $command->shouldReceive('info');
        $command->shouldReceive('error');
        $command->shouldReceive('line');

        return $command;
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * Tests the extraction by counting the objects that the extractor returns
     */
    public function testExtraction()
    {
        foreach ($this->test_extraction_cases as $test_case_name => $config) {
            // Extractor Csv model - mock
            Mockery::mock('Eloquent');

            $extract_model = new stdClass;

            // Mock the command object
            $command = $this->getMockedCommand();

            $extract_model->uri = __DIR__ . '/../data/' . $config['file'];
            $extract_model->has_header_row = $config['has_header_row'];
            $extract_model->delimiter = $config['delimiter'];
            $extract_model->start_row = $config['start_row'];
            $extract_model->encoding = $config['encoding'];

            $csv_extractor = new \Tdt\Input\ETL\Extract\Csv($extract_model, $command);

            $obj_count = 0;

            while ($csv_extractor->hasNext()) {
                $obj =$csv_extractor->pop();

                // The pop() can return a NULL value for it streams data and creates objects.
                // Hence the hasNext() indicates that is may possibly contain a following object.
                if (!empty($obj)) {
                    $obj_count++;
                }
            }

            $this->assertEquals($config['count'], $obj_count);
        }
    }

    /**
     * Test the etl sequence
     */
    public function testETL()
    {
        foreach ($this->test_emlp_cases as $config) {
            $extract_model = new stdClass;

            $command = $this->getMockedCommand();

            $extract_model->uri = __DIR__ . '/../data/' . $config['extract']['file'];
            $extract_model->has_header_row = $config['extract']['has_header_row'];
            $extract_model->delimiter = $config['extract']['delimiter'];
            $extract_model->start_row = $config['extract']['start_row'];
            $extract_model->encoding = $config['extract']['encoding'];

            $csv_extractor = new \Tdt\Input\ETL\Extract\Csv($extract_model, $command);

            // We only extract one item, and check for conversion correctness
            $this->assertTrue($csv_extractor->hasNext());

            if ($csv_extractor->hasNext()) {
                $chunk = $csv_extractor->pop();

                // Serialize the object to make comparing object easy
                $encoded_obj = serialize($chunk);

                // Fetch the correct object to which the test must be verified
                $verified_obj = file_get_contents(__DIR__ . '/../data/' . $config['extract']['verify']);

                $this->assertEquals($verified_obj, $encoded_obj);

                // Map the object from the json extraction and compare the amount of
                // triples as a parameter of test

                $map_model = Mockery::mock('Map\Rdf');

                $map_model->mapfile = __DIR__ . '/../map/' . $config['map']['file'];
                $map_model->base_uri = $config['map']['base_uri'];
            }
        }
    }
}
