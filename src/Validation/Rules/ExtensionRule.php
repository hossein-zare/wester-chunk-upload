<?php

namespace Wester\ChunkUpload\Validation\Rules;

use Wester\ChunkUpload\Validation\Validator;

class ExtensionRule
{
    /**
     * The validator.
     * 
     * @var \Wester\ChunkUpload\Validation\Validator
     */
    private $validator;

    /**
     * The name.
     * 
     * @var string
     */
    private $name;

    /**
     * The value.
     * 
     * @var mixed
     */
    private $value;

    /**
     * Create a new instance.
     * 
     * @param  \Wester\ChunkUpload\Validation\Validator  $validator
     * @param  string  $name
     * @param  mixed  $value
     * @param  string  $data
     * @return void
     */
    public function __construct(Validator $validator, string $name, $value, string $data)
    {
        $this->validator = $validator;
        $this->name = $name;
        $this->value = $value;
        $this->data = $data;
    }

    /**
     * Validate the rule.
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->validator->exists($this->name) && is_string($this->value)) {
            $extensions = explode(',', $this->data);
            $extension = trim(
                pathinfo($this->value, PATHINFO_EXTENSION)
            );

            return ! empty($extension) && in_array(strtolower($extension), $extensions);
        }

        return true;
    }

    /**
     * Set arguments and create an instance.
     * 
     * @param  array  $args
     * @return \Wester\ChunkUpload\Validation\Rules\ExtensionRule
     */
    public static function set(...$args)
    {
        return new self(...$args);
    }
}
