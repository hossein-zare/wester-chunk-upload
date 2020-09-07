<?php

namespace Wester\ChunkUpload\Validation;

class ExceptionHandler
{
    /**
     * Throw a rule exception.
     * 
     * @return void
     * 
     * @throws \Exception
     */
    protected function throw(string $rule)
    {
        $rule = ucfirst($rule);
        $exception = "\\Wester\\ChunkUpload\\Validation\\Rules\\Exceptions\\{$rule}Exception";

        throw new $exception("The data is invalid.");
    }
}
