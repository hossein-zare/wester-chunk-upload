<?php

namespace Wester\ChunkUpload\Validation\Rules;

use Wester\ChunkUpload\Validation\Validator;

class SizeRule
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
        if ($this->validator->exists($this->name)) {
            switch ($this->validator->currentDataType) {
                case 'numeric':
                    return (int) $this->value === (int) $this->data;

                case 'file':
                    return (int) $this->value === (int) $this->data;

                case 'string':
                    return strlen($this->value) === (int) $this->data;
            }

            return false;
        }

        return true;
    }

    /**
     * Set arguments and create an instance.
     * 
     * @param  array  $args
     * @return \Wester\ChunkUpload\Validation\Rules\SizeRule
     */
    public static function set(...$args)
    {
        return new self(...$args);
    }
}
