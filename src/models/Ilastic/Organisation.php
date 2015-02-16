<?php namespace Ilastic;

use Jenssegers\Mongodb\Model as Eloquent;

class Organisation extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'organisations';

    protected $guarded = ['_id'];
}
