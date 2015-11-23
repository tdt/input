<?php

/**
 * @copyright (C) 2011, 2014 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Michiel Vancoillie <michiel@okfn.be>
 * @author Jan Vansteenlandt <jan@okfn.be>
 */
namespace Tdt\Input\Ui;

use Tdt\Core\Auth\Auth;
use Tdt\Input\Controllers\InputController;

class UiController extends \Controller
{

    /**
     * Request handling
     *
     * TODO: Change everything to work with $id
     */
    public function handle($uri)
    {
        switch ($uri) {
            case 'jobs':
                // Set permission
                Auth::requirePermissions('tdt.input.view');

                // Get list of jobs
                return $this->listJobs();
                break;

            case 'jobs/add':
                // Set permission
                Auth::requirePermissions('tdt.input.create');

                // Create new job
                return $this->addJob();
                break;
            case (preg_match('/^jobs\/edit/i', $uri) ? true : false):
                // Set permission
                Auth::requirePermissions('tdt.input.edit');
                // Edit a job
                return $this->editJob($uri);
                break;

            case (preg_match('/^jobs\/delete/i', $uri) ? true : false):
                // Set permission
                Auth::requirePermissions('tdt.input.delete');
                // Delete a job
                return $this->deleteJob($uri);
                break;
        }

        return false;
    }

    /**
     * Define menu items
     */
    public function menu()
    {
        return array(
            array(
                'title' => 'Jobs',
                'slug' => 'jobs',
                'permission' => 'tdt.input.view',
                'icon' => 'fa-wrench',
                'priority' => 50
                ),
        );
    }

    /**
     * Return a list of jobs
     *
     * @return \View
     */
    private function listJobs()
    {
        // Get list of jobs
        $jobs = \Job::all();

        return \View::make('input::ui.jobs.list')
                    ->with('title', 'Jobs management | The Datatank')
                    ->with('jobs', $jobs);
    }

    /**
     * Add a job
     *
     * @return \View
     */
    private function addJob()
    {
        $discovery = \App::make('Tdt\Core\Definitions\DiscoveryController');
        $discovery = json_decode($discovery->get()->getcontent());

        // Get the configuration for the etl configuration
        $etl_config = $discovery->resources->input->methods->put->body;

        $job_config = [];

        // Job specific settings
        $job_config['schedule'] = explode('|', $etl_config->schedule->list);

        $configuration = [];

        $etl_parts = ['extract', 'load'];

        // Fill in list parameters in the provided configurations
        foreach ($etl_parts as $etl_part) {
            $etl_part_config = $etl_config->$etl_part;
            unset($etl_config->$etl_part);

            foreach ($etl_part_config->parameters as $params) {
                $el_config = [];

                foreach ($params as $type => $config) {
                    $processed_param = [];

                    foreach ($config->parameters as $name => $parameter) {
                        if ($parameter->type == 'list') {
                            if (strpos($parameter->list, '|')) {
                                $parameter->list = explode('|', $parameter->list);
                            } else {
                                $list = json_decode($this->getDocument($parameter->list));

                                if (!empty($parameter->list_option)) {
                                    $filtered_list = [];

                                    foreach ($list as $object) {
                                        $object = (array) $object;
                                        $filtered_list[] = $object[$parameter->list_option];
                                    }

                                    $parameter->list = $filtered_list;
                                } else {
                                    $parameter->list = $list;
                                }
                            }
                        }

                        $processed_param[$name] = $parameter;
                    }

                    $el_config[$type] = $processed_param;
                }

                $configuration[$etl_part] = $el_config;
            }
        }

        return \View::make('input::ui.jobs.add')
                    ->with('title', 'New job | The Datatank')
                    ->with('configuration', $configuration)
                    ->with('job', $job_config);
    }

    /**
     * Edit a job
     *
     * @param string $uri The uri of the job
     *
     * @return \View
     */
    private function editJob($uri)
    {
        $pieces = explode('/', $uri);

        $id = array_pop($pieces);

        if (is_numeric($id)) {
            $job = \Job::where('id', $id)->with('extractor', 'loader')->first();

            if (empty($job)) {
                return \Redirect::to('api/admin/jobs');
            }
        }

        $discovery = \App::make('Tdt\Core\Definitions\DiscoveryController');
        $discovery = json_decode($discovery->get()->getcontent());

        // Get the configuration for the selected ETL
        $input_extract_spec = $discovery->resources->input->methods->put->body->extract->parameters->type;
        $input_load_spec = $discovery->resources->input->methods->put->body->load->parameters->type;

        $extract_type = strtolower($job->extractor->type);
        $load_type = strtolower($job->loader->type);

        $extract_parameters = $input_extract_spec->$extract_type->parameters;
        $load_parameters = $input_load_spec->$load_type->parameters;

        // Fill in list parameters in the provided configurations
        foreach ($extract_parameters as $parameter) {
            if ($parameter->type == 'list') {
                if (strpos($parameter->list, '|')) {
                    $parameter->list = explode('|', $parameter->list);
                } else {
                    $list = json_decode($this->getDocument($parameter->list));

                    if (!empty($parameter->list_option)) {
                        $filtered_list = [];

                        foreach ($list as $object) {
                            $object = (array) $object;
                            $filtered_list[] = $object[$parameter->list_option];
                        }

                        $parameter->list = $filtered_list;
                    } else {
                        $parameter->list = $list;
                    }
                }
            }
        }

        // Fill in list parameters in the provided configurations
        foreach ($load_parameters as $parameter) {
            if ($parameter->type == 'list') {
                if (strpos($parameter->list, '|')) {
                    $parameter->list = explode('|', $parameter->list);
                } else {
                    $list = json_decode($this->getDocument($parameter->list));

                    if (!empty($parameter->list_option)) {
                        $filtered_list = [];

                        foreach ($list as $object) {
                            $object = (array) $object;
                            $filtered_list[] = $object[$parameter->list_option];
                        }

                        $parameter->list = $filtered_list;
                    } else {
                        $parameter->list = $list;
                    }
                }
            }
        }

        $job_config = [];

        // Job specific settings
        $job_config['schedule'] = explode('|', $discovery->resources->input->methods->put->body->schedule->list);

        return \View::make('input::ui.jobs.edit')
                    ->with('title', 'Edit a job | The Datatank')
                    ->with('extract_parameters', $extract_parameters)
                    ->with('load_parameters', $load_parameters)
                    ->with('job', $job)
                    ->with('job_config', $job_config);
    }

    /**
     * Delete a job
     *
     * @param string $uri The unique identifier URI of the job
     *
     * @return mixed
     */
    private function deleteJob($uri)
    {
        // Get the id
        $id = str_replace('jobs/delete/', '', $uri);

        if (is_numeric($id)) {
            $job = \Job::find($id);
            if (!empty($job)) {
                $job->delete();
            }

            return \Redirect::to('api/admin/jobs');
        } else {
            return false;
        }
    }

    private function getDocument($uri)
    {
        // Create a CURL client
        $cURL = new \Buzz\Client\Curl();
        $cURL->setVerifyPeer(false);
        $cURL->setTimeout(30);

        // Get discovery document
        $browser = new \Buzz\Browser($cURL);
        $response = $browser->get(\URL::to($uri));

        // Document content
        return $response->getContent();
    }
}
