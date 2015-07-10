<?php

namespace Extract;

class Type extends \Eloquent
{
    protected $appends = array('type');

    public function getTypeAttribute()
    {
        return str_replace('EXTRACT\\', '', strtoupper(get_called_class()));
    }
}
