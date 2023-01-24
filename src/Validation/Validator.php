<?php

namespace Wester\ChunkUpload\Validation;

use Exception;
use Wester\ChunkUpload\Validation\Exceptions\ValidationException;
use Wester\ChunkUpload\Exceptions\MainException;
use Wester\ChunkUpload\Language\Language;

class Validator extends ExceptionHandler
{
    /**
     * The data.
     * 
     * @var array
     */
    protected $data = [];

    /**
     * The parameters.
     * 
     * @var array
     */
    protected $parameters = [];

    /**
     * The current parameter.
     * 
     * @var string
     */
    public $currentParameter = [];

    /**
     * The current rules.
     * 
     * @var array
     */
    public $currentRules = [];

    /**
     * The current data type.
     * 
     * @var string
     */
    public $currentDataType = 'string';


    /**
     * Create a new instance.
     * 
     * @param  array  $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validate parameters.
     * 
     * @param  array  $parameters
     * @return \Wester\ChunkUpload\Validator
     */
    public function validate(array $parameters)
    {
        $messages = [];
        $this->setParameters($parameters);

        foreach ($parameters as $parameter => $rules) {
            $this->setCurrentParameter($parameter)
                ->setCurrentRules($rules)
                ->setCurrentDataType($parameter);

            // Filter rules
            $rules = array_filter($rules, function ($item) {
                return $item !== '!';
            });

            foreach ($rules as $rule) {
                $values = $this->slice($rule);
                $class = $this->createClassName($values);

                $args = [$class, $parameter];

                if (isset($values[1])) {
                    $args[] = $values[1];
                }

                if (! $this->callRule(...$args)) {
                    try {
                        $this->throw($class);
                    } catch (ValidationException $e) {
                        if ($this->isThrowable())
                            throw new MainException("The data is invalid.");

                        $messages[$parameter][] = $this->getValidationMessage($values);
                    }
                }
            } 
        }

        if (count($messages) > 0) {
            throw new ValidationException("The data is invalid.", $messages);
        }

        return $this;
    }

    /**
     * Set parameters.
     * 
     * @param  array  $parameters
     * @return \Wester\ChunkUpload\Validation\Validator
     */
    private function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Set current paramter.
     * 
     * @param  string  $parameter
     * @return \Wester\ChunkUpload\Validation\Validator
     */
    private function setCurrentParameter(string $parameter)
    {
        $this->currentParameter = $parameter;

        return $this;
    }

    /**
     * Set current rules.
     * 
     * @param  array  $rules
     * @return \Wester\ChunkUpload\Validation\Validator
     */
    private function setCurrentRules(array $rules)
    {
        $this->currentRules = $rules;

        return $this;
    }

    /**
     * Set current data type.
     * 
     * @param  string  $parameter
     * @return \Wester\ChunkUpload\Validation\Validator
     */
    private function setCurrentDataType(string $parameter)
    {
        $this->currentDataType = $this->getDataType($parameter);

        return $this;
    }

    /**
     * Determine whether the parameter can throw non-validation exceptions.
     * 
     * @return bool
     */
    private function isThrowable(): bool
    {
        return in_array('!', $this->currentRules);
    }

    /**
     * Get the validation message.
     * 
     * @param  array  $rule
     * @return string
     */
    private function getValidationMessage(array $rule): string
    { 
        if (isset($rule[1]) && $this->currentDataType === 'file') {
            $value = (int) $rule[1] / 1024;
        } else {
            $value = $rule[1];
        }
        
        $data = [
            'attribute' => $this->currentParameter,
            'value' => $value
        ];

        if ($rule[0] !== 'extension' && isset($rule[1])) {
            $key = "{$rule[0]}.{$this->currentDataType}";
        } else if ($rule[0] === 'extension') {
            $key = 'mimes';
        } else {
            $key = $rule[0];
        }
        
        return Language::expression($key, $data);
    }

    /**
     * Call the rule.
     * 
     * @param  string  $method
     * @param  string  $parameter
     * @param  mixed  $data
     * @return bool
     */
    private function callRule(string $method, string $parameter, $data = null)
    {
        $method = ucfirst($method);
        $rule = "\\Wester\\ChunkUpload\\Validation\\Rules\\{$method}";
    
        return $rule::set($this, $parameter, $this->getAttribute($parameter), $data)->isValid();
    }

    /**
     * Convert the parameters.
     * 
     * @return array
     */
    public function convert()
    {
        foreach($this->data as $key => $value) {
            $this->data[$key] = $this->toDataType($key, $this->data[$key]);
        }

        return $this->data;
    }

    /**
     * Convert data type.
     * 
     * @param  string  $name
     * @param  mixed  $value
     * @return mixed
     */
    private function toDataType(string $name, $value)
    {
        switch ($this->getDataType($name)) {
            case 'string':
                return (string) $value;

            case 'numeric':
                return (int) $value;

            case 'file':
                return (int) $value;
        }

        return $value;
    }

    /**
     * Get data type.
     * 
     * @param  string  $name
     * @return string
     */
    private function getDataType($name)
    {
        $types = ['string', 'numeric', 'file'];
    
        foreach ($this->parameters[$name] as $rule) {
            if (in_array($rule, $types))
                return $rule;
        }

        return 'string';
    }

    /**
     * Get attribute.
     * 
     * @param  null|string  $name
     * @return mixed
     */
    protected function getAttribute(string $name = null)
    {
        if (! $name) {
            return $this->getAttribute($this->currentParameter);
        }

        return $this->data[$name] ?? null;
    }

    /**
     * Check if the header exists.
     * 
     * @param  string  $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Create a class name for the rule.
     * 
     * @param  array  $array
     * @return string
     */
    protected function createClassName(array $array): string
    {
        return lcfirst($array[0]) . 'Rule';
    }

    /**
     * Slice the string.
     * 
     * @param  string  $string
     */
    private function slice($string)
    {
        return explode(':', $string);
    }
}
