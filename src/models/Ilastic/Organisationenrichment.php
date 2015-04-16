<?php namespace Ilastic;

use Jenssegers\Mongodb\Model as Eloquent;

class Organisationenrichment extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'organisation_enrichment';

    protected $guarded = ['_id'];
}
