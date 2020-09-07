<?php

namespace Wester\ChunkUpload\Validation\Rules;

use Wester\ChunkUpload\Validation\Validator;

class NumericRule
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
     * @return void
     */
    public function __construct(Validator $validator, string $name, $value)
    {
        $this->validator = $validator;
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Validate the rule.
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return ! $this->validator->exists($this->name) || is_numeric($this->value);
    }

    /**
     * Set arguments and create an instance.
     * 
     * @param  array  $args
     * @return \Wester\ChunkUpload\Validation\Rules\NumericRule
     */
    public static function set(...$args)
    {
        return new self(...$args);
    }
}
