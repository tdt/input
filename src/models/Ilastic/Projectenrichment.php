<?php namespace Ilastic;

use Jenssegers\Mongodb\Model as Eloquent;

class Projectenrichment extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'project_enrichment';

    protected $guarded = ['_id'];
}
