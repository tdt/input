<?php

use Symfony\Component\HttpFoundation\Request;


/**
 * Tests the JSON extractor
 *
 * NOTE: The JSON extractor extracts elements from the first JSON array it encounters in the JSON document.
 */
class JsonExtract extends \Orchestra\Testbench\TestCase{

    private $test_cases = array(
        'routes' => array(
            'file' => 'json/routes.json',
            'count' => 195,
        ),
    );


    /**
     * Tests the extraction by counting the objects that the extractor returns
     */
    public function test_extraction(){

        foreach($this->test_cases as $test_case_name => $config){

            // Extractor Json model - mock
            $extract_model = Mockery::mock('tdt\input\emlp\extract\JsonExtract');
            $extract_model->uri = __DIR__ . '/data/' . $config['file'];

            $json_extractor = new \tdt\input\emlp\extract\Json($extract_model);

            $obj_count = 0;
            while($json_extractor->hasNext()){

                $obj =$json_extractor->pop();

                // The pop() can return a NULL value for it streams data and creates objects.
                // Hence the hasNext() indicates that is may possibly contain a following object.
                if(!empty($obj)){
                    $obj_count++;
                }

            }

            $this->assertEquals($config['count'], $obj_count);
            Mockery::close();
        }

    }
}