<?php

namespace Wester\ChunkUpload;

class Response
{
    /**
     * Set http resonse code.
     * 
     * @param  int  status
     * @return \Wester\ChunkUpload\Response
     */
    public function status($status = null)
    {
        if ($status !== null)
            http_response_code($status);

        return $this;
    }

    /**
     * Abort the connection with http status code.
     * 
     * @param  null|int  status
     * @return void
     */
    public function abort($status = null)
    {
        $this->status($status);

        die();
    }

    /**
     * Json response.
     * 
     * @param  mixed  $data
     * @return void
     */
    public function json($data)
    {
        header('Content-Type: application/json');

        die(json_encode($data));
    }

    /**
     * Create a new instance statically.
     * 
     * @param  string  $method
     * @param  array  $args
     * @return \Wester\ChunkUpload\Response
     */
    public static function __callStatic($method, $args)
    {
        return new self(...$args);
    }
}
