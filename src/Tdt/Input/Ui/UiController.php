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
     * Request handeling
     *
     * TODO: Change everything to work with $id
     */
    public static function handle($uri)
    {
        switch ($uri) {
            case 'jobs':
                // Set permission
                Auth::requirePermissions('tdt.input.view');

                // Get list of jobs
                return self::listJobs();
                break;

            case 'jobs/add':
                // Set permission
                Auth::requirePermissions('tdt.input.create');

                // Create new job
                return self::addJob();
                break;
            case (preg_match('/^jobs\/edit/i', $uri) ? true : false):
                // Set permission
                Auth::requirePermissions('tdt.input.edit');
                // Edit a job
                return self::editJob($uri);
                break;

            case (preg_match('/^jobs\/delete/i', $uri) ? true : false):
                // Set permission
                Auth::requirePermissions('tdt.input.delete');
                // Delete a job
                return self::deleteJob($uri);
                break;
        }

        return false;
    }

    /**
     * Define menu items
     */
    public static function menu()
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
    private static function listJobs()
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
    private static function addJob()
    {
        $discovery = \App::make('Tdt\Core\Definitions\DiscoveryController');
        $discovery = json_decode($discovery->get()->getcontent());

        // Get spec for media types
        $input_spec = $discovery->resources->input->methods->put->body;

        return \View::make('input::ui.jobs.add')
                    ->with('title', 'New job | The Datatank')
                    ->with('input_spec', $input_spec);
    }

    /**
     * Edit a job
     *
     * @param string $uri The uri of the job
     *
     * @return \View
     */
    private static function editJob($uri)
    {
        $pieces = explode('/', $uri);

        $id = array_pop($pieces);

        if (is_numeric($id)) {
            $job = \Job::find($id)->with('extractor', 'loader')->first();

            if (empty($job)) {
                return \Redirect::to('api/admin/jobs');
            }
        }

        $discovery = \App::make('Tdt\Core\Definitions\DiscoveryController');
        $discovery = json_decode($discovery->get()->getcontent());

        // Get spec for media types
        $input_extract_spec = $discovery->resources->input->methods->put->body->extract->parameters->type;
        $input_load_spec = $discovery->resources->input->methods->put->body->load->parameters->type;

        $extract_type = strtolower($job->extractor->type);
        $load_type = strtolower($job->loader->type);

        $extract_parameters = $input_extract_spec->$extract_type->parameters;
        $load_parameters = $input_load_spec->$load_type->parameters;

        return \View::make('input::ui.jobs.edit')
                    ->with('title', 'Edit a job | The Datatank')
                    ->with('extract_parameters', $extract_parameters)
                    ->with('load_parameters', $load_parameters)
                    ->with('job', $job);
    }

    /**
     * Delete a job
     *
     * @return mixed
     */
    private static function deleteJob($uri)
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
}
