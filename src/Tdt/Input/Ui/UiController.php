<?php

/**
 * @copyright (C) 2011, 2014 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Michiel Vancoillie <michiel@okfn.be>
 */
namespace Tdt\Input\Ui;

use Tdt\Core\Auth\Auth;
use Tdt\Input\Controllers\InputController;

class UiController extends \Controller
{

    /**
     * Request handeling
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
     * Job list
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
     * Delete a job
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
