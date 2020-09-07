<?php

namespace Wester\ChunkUpload\Validation\Exceptions;

class ValidationException extends \Exception
{
    /**
     * The errors.
     * 
     * @var array
     */
    private $errors = [];

    /**
     * Create a new instance.
     * 
     * @param  string  $message
     * @param  array  $errors
     * @return void
     */
    public function __construct(string $message, array $errors = [])
    {
        $this->errors = $errors;
        parent::__construct($message);
    }

    /**
     * Get the validation errors.
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
