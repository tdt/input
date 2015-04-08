<?php namespace Ilastic;

use Jenssegers\Mongodb\Model as Eloquent;

class Publicationenrichment extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'publication_enrichment';

    protected $guarded = ['_id'];
}
