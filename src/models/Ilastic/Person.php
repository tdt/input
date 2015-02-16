<?php namespace Ilastic;

use Jenssegers\Mongodb\Model as Eloquent;

class Person extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'persons';

    protected $guarded = ['_id'];
}
