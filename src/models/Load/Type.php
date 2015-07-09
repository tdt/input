<?php

namespace Load;

class Type extends \Eloquent
{
    protected $appends = array('type');

    public function getTypeAttribute()
    {
        return str_replace('LOAD\\', '', strtoupper(get_called_class()));
    }
}
