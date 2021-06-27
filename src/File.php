<?php

namespace Wester\ChunkUpload;

use Wester\ChunkUpload\Exceptions\FileEmptyException;
use Wester\ChunkUpload\Exceptions\FileNotSingleException;
use Wester\ChunkUpload\Exceptions\FileErrorException;

class File
{
    /**
     * The name.
     * 
     * @var array
     */
    public $name;

    /**
     * The size.
     * 
     * @var int
     */
    public $size;

    /**
     * The type.
     * 
     * @var string
     */
    public $type;

    /**
     * The error.
     * 
     * @var integer
     */
    public $error;

    /**
     * The temp name.
     * 
     * @var integer
     */
    public $tmp_name;

    /**
     * Create a new instance.
     * 
     * @param  string  $name
     */
    public function __construct($name)
    {
        $this->setFile($name);
    }

    /**
     * Set file.
     * 
     * @param  string  $name
     * @return void
     */
    protected function setFile(string $name)
    {
        if ($this->isEmpty($name))
            throw new FileEmptyException("There's no file.");

        if (! $this->isSingle($name))
            throw new FileNotSingleException("There are multiple files.");

        if (! $this->isValid($name))
            throw new FileErrorException("There are some errors.");

        $this->setAttributes($name);
    }

    /**
     * Set attributes.
     * 
     * @param  string  $name
     * @return void
     */
    private function setAttributes(string $name)
    {
        $attributes = ['name', 'size', 'type', 'error', 'tmp_name'];
        
        foreach ($attributes as $attribute) {
            $this->{$attribute} = $this->getFile($name)->{$attribute};
        }
    }

    /**
     * Get the file.
     * 
     * @param  string  $name
     * @return object
     */
    protected function getFile(string $name)
    {
        return json_decode(
            json_encode($_FILES[$name]), false
        );
    }

    /**
     * Check if there's no file.
     * 
     * @param  string  $name
     * @return bool
     */
    protected function isEmpty(string $name): bool
    {
        return ! isset(
            $_FILES[$name]
        ) || empty(
            $_FILES[$name]['name']
        );
    }

    /**
     * Check if the user has uploaded a single file.
     * 
     * @param  string  $name
     * @return bool
     */
    protected function isSingle(string $name): bool
    {
        return ! is_array(
            $_FILES[$name]['name']
        );
    }

    /**
     * Check if the file has any errors.
     * 
     * @param  string  $name
     * @return bool
     */
    protected function isValid(string $name): bool
    {
        return $_FILES[$name]['error'] === 0;
    }
}
