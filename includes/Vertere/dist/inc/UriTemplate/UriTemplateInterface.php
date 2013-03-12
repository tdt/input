<?php

/*
Copyright (c) 2011 Michael Dowling, https://github.com/mtdowling <mtdowling@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
*/

namespace Guzzle\Parser\UriTemplate;

/**
 * Expands URI templates using an array of variables
 *
 * @link http://tools.ietf.org/html/rfc6570
 */
interface UriTemplateInterface
{
    /**
     * Expand the URI template using the supplied variables
     *
     * @param string $template  URI Template to expand
     * @param array  $variables Variables to use with the expansion
     *
     * @return string Returns the expanded template
     */
    public function expand($template, array $variables);
}
