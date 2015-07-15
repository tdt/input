<?php

use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the JSON extractor
 *
 * NOTE: The JSON extractor extracts elements from the first JSON array it encounters in the JSON document.
 */
class XmlTest extends PHPUnit_Framework_TestCase
{
    private $test_extraction_cases = array(
        'events' => array(
            'file' => 'xml/events.xml',
            'arraylevel' => 2,
            'count' => 2,
            'encoding' => 'UTF-8',
        ),
    );

    private $test_emlp_cases = array(
         array(
            'extract' => array(
                'file' => 'xml/events.xml',
                'arraylevel' => 2,
                'verify' => 'xml/serialized_xml',
                'encoding' => 'UTF-8',
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

            // Extractor Xml model - mock
            Mockery::mock('Eloquent');

            $extract_model = Mockery::mock('Extract\Xml');

            // Mock the command object
            $command = $this->getMockedCommand();

            $extract_model->uri = __DIR__ . '/../data/' . $config['file'];
            $extract_model->arraylevel = $config['arraylevel'];
            $extract_model->encoding = $config['encoding'];

            $xml_extractor = new \Tdt\Input\ETL\Extract\Xml($extract_model, $command);

            $obj_count = 0;

            while ($xml_extractor->hasNext()) {
                $obj =$xml_extractor->pop();

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
     * Test the em (lp) sequence
     */
    public function testETL()
    {
        foreach ($this->test_emlp_cases as $config) {
            Mockery::mock('Eloquent');
            $extract_model = Mockery::mock('Extract\Xml');

            $command = $this->getMockedCommand();

            $extract_model->uri = __DIR__ . '/../data/' . $config['extract']['file'];
            $extract_model->arraylevel = $config['extract']['arraylevel'];
            $extract_model->encoding = $config['extract']['encoding'];

            $xml_extractor = new \Tdt\Input\ETL\Extract\Xml($extract_model, $command);

            // We only extract one item, and check for conversion correctness

            $this->assertTrue($xml_extractor->hasNext());

            if ($xml_extractor->hasNext()) {
                $chunk = $xml_extractor->pop();

                // Serialize the object to make comparing object easy
                $encoded_obj = serialize($chunk);

                // Fetch the correct object to which the test must be verified
                $verified_obj = file_get_contents(__DIR__ . '/../data/' . $config['extract']['verify']);

                $this->assertEquals($verified_obj, $encoded_obj);
            }
        }
    }
}
