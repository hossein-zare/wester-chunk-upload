<?php

namespace Wester\ChunkUpload;

use Wester\ChunkUpload\Validation\Validator;
use CaseConverter\CaseString;

class Header
{
    /**
     * The headers.
     * 
     * @var array
     */
    public $headers = [];

    /**
     * Create a new instance.
     * 
     * @param  array  $keys
     * @return void
     */
    public function __construct(array $keys = null)
    {
        $this->setHeaders();
        $this->only($keys);
    }

    /**
     * Set all headers.
     * 
     * @return void
     */
    protected function setHeaders()
    {
        $this->headers = array_change_key_case(getallheaders(), CASE_LOWER);
    }

    /**
     * Preserve specified headers.
     * 
     * @param  null|array  $keys
     * @return void
     */
    public function only($keys)
    {
        if ($keys) {
            $this->filter(function (string $key) use ($keys) {
                return in_array($key, $keys);
            });
        }
    }

    /**
     * Check if the header exists.
     * 
     * @param  string  $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * Filter the headers.
     * 
     * @param  callable  $callback
     * @return array
     */
    public function filter(callable $callback)
    {
        $this->headers = array_filter($this->headers, $callback, ARRAY_FILTER_USE_KEY);

        return $this->headers;
    }

    /**
     * Validate the headers.
     * 
     * @param  array  $headers
     * @return void
     */
    public function validate($headers)
    {
        $validator = new Validator($this->headers);
        $this->headers = $validator->validate($headers)->convert();
    }

    /**
     * Get a header value.
     * 
     * @param  string  $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->headers[
            'x-' . StringHelper::camelToKebab($name)
        ] ?? null;
    }
}
