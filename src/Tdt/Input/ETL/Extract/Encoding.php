<?php namespace Tdt\Input\ETL\Extract;

trait Encoding
{
    /**
     * Convert a value from a given encoding to Utf8
     *
     * @param string $value    The value that needs to be converted to UTF-8
     * @param string $encoding The encoding the value currently is in
     *
     * @return string
     */
    public function convertToUtf8($value, $encoding)
    {
        if (!empty($encoding)) {
            return mb_convert_encoding($value, 'UTF-8', $encoding);
        }

        return $value;
    }

    /**
     * Clean a UTF-8 string. Sometimes the encoding might be UTF-8
     * but contains contaminated characters e.g. when the encoding
     * is not know of a file, there's no way to address that.
     *
     *
     * @param string $value
     *
     * @return string
     */
    public function fixUtf8($value)
    {
        return \ForceUTF8\Encoding::fixUTF8($value);
    }
}
