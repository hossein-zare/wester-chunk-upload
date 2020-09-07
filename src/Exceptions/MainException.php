<?php

namespace Wester\ChunkUpload\Exceptions;

use Wester\ChunkUpload\Response;

class MainException extends \Exception
{
    /**
     * Create a new instance of the response class.
     * 
     * @param  null|int  $status
     * @return \Wester\ChunkUpload\Response
     */
    public function response($status = null)
    {
        return (new Response())->status($status);
    }
}
