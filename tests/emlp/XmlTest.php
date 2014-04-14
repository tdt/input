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
        ),
    );

    private $test_emlp_cases = array(
         array(
            'extract' => array(
                'file' => 'xml/events.xml',
                'arraylevel' => 2,
                'verify' => 'xml/serialized_xml',
            ),
            'map' => array(
                'file' => 'events.ttl',
                'base_uri' => 'http://foo.mock/',
                'triples_amount' => 110,
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

            $extract_model = Mockery::mock('extract\Xml');

            // Mock the command object
            $command = $this->getMockedCommand();

            $extract_model->uri = __DIR__ . '/../data/' . $config['file'];
            $extract_model->arraylevel = $config['arraylevel'];

            $xml_extractor = new \Tdt\Input\EMLP\Extract\Xml($extract_model, $command);

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
    public function testEMLP()
    {
        foreach ($this->test_emlp_cases as $config) {

            Mockery::mock('Eloquent');
            $extract_model = Mockery::mock('extract\Xml');

            $command = $this->getMockedCommand();

            $extract_model->uri = __DIR__ . '/../data/' . $config['extract']['file'];
            $extract_model->arraylevel = $config['extract']['arraylevel'];

            $xml_extractor = new \Tdt\Input\EMLP\Extract\Xml($extract_model, $command);

            // We only extract one item, and check for conversion correctness

            $this->assertTrue($xml_extractor->hasNext());

            if ($xml_extractor->hasNext()) {

                $chunk = $xml_extractor->pop();

                // Serialize the object to make comparing object easy
                $encoded_obj = serialize($chunk);

                // Fetch the correct object to which the test must be verified
                $verified_obj = file_get_contents(__DIR__ . '/../data/' . $config['extract']['verify']);

                $this->assertEquals($verified_obj, $encoded_obj);

                // Map the object from the json extraction and compare the amount of
                // triples as a parameter of test

                $map_model = Mockery::mock('map\Rdf');

                $map_model->mapfile = __DIR__ . '/../map/' . $config['map']['file'];
                $map_model->base_uri = $config['map']['base_uri'];

                $mapper = new \Tdt\Input\EMLP\Map\Rdf($map_model, $command);
                $mapper->init();

                $graph = $mapper->execute($chunk);

                $this->assertEquals($config['map']['triples_amount'], $graph->countTriples());
            }
        }
    }
}
