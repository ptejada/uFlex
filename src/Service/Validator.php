<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/2/2016
 * Time: 8:57 PM
 */

namespace ptejada\uFlex\Service;

use ptejada\uFlex\Classes\Collection;
use ptejada\uFlex\Classes\Helper;
use ptejada\uFlex\Exception\InternalException;
use ptejada\uFlex\Exception\ValidationException;

class Validator
{
    /** @var Collection The validation rules */
    protected $schema;
    /** @var Collection The data to be validated */
    protected $data;

    public function __construct()
    {
        $this->data   = new Collection();
        $this->schema = new Collection(
            array(
                'Username' => array(
                    'min' => 4,
                    'max' => 15,
                    'pattern' => '/^([a-zA-Z0-9_])+$/',
                ),
                'Password' => array(
                    'min' => 3,
                    'max' => 15,
                    'pattern' => '',
                ),
                'Email'    => array(
                    'min' => 4,
                    'max' => 45,
                    'pattern' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,63})$/i',
                ),
            )
        );
    }

    /**
     * Validate a single field
     *
     * @param string $fieldName The unique name of the field
     * @param mixed  $value     An optional field to use internal of the internal field
     *
     * @throws ValidationException If there a validation error with the input
     */
    public function validate($fieldName, $value = null)
    {
        $fieldValue = $this->getFieldValue($fieldName, $value);
        $rule       = $this->getFieldRules($fieldName);

        $valueLength = strlen($fieldValue);
        if ($rule->min && $valueLength < $rule->min) {
            throw new ValidationException(
                ValidationException::ERROR_MINIMUM, $fieldName,
                "The value '{$fieldValue}' is shorter than {$rule->min} characters."
            );
        }

        if ($rule->max && $valueLength > $rule->max) {
            throw new ValidationException(
                ValidationException::ERROR_MAXIMUM, $fieldName,
                "The value '{$fieldValue}' is longer than {$rule->max} characters."
            );
        }

        if ($rule->pattern && !preg_match($rule->pattern, $value)) {
            throw new ValidationException(
                ValidationException::ERROR_PATTERN, $fieldName,
                "The value '{$fieldValue}' did not matched the expected pattern."
            );
        }

        $fieldToMatch = $rule->match;
        if ($fieldToMatch) {
            if ($fieldValue != $this->getFieldValue($fieldToMatch)) {
                throw new ValidationException(
                    ValidationException::ERROR_MISMATCH, $fieldName,
                    "The value '{$fieldValue}' does not match the value from '{$fieldToMatch}'."
                );
            }
        }
    }

    /**
     * Validate all the fields
     *
     * @param array|Collection $data data to validate
     *
     * @throws ValidationException
     */
    public function validateAll($data)
    {
        $this->data = Helper::getCollection($data);
        
        foreach ($data as $fieldName => $fieldValue) {
            $this->validate($fieldName, $fieldValue);
        }
        
        $this->data = new Collection();
    }

    /**
     * Get the value from the internal data if value provided is null
     *
     * @param string      $fieldName The name of the field
     * @param null|string $value     Optional value to be used
     *
     * @return string
     */
    protected function getFieldValue($fieldName, $value = null)
    {
        if (is_null($value)) {
            return (string)$this->data->get($fieldName);
        } else {
            return (string)$value;
        }
    }

    /**
     * Get the validation rules for a field
     *
     * @param string $fieldName The field name
     *
     * @return Collection The validation rules
     * @throws \Exception
     */
    public function getFieldRules($fieldName)
    {
        $rules = $this->schema->get($fieldName);
        if ($rules && $rules instanceof Collection) {
            $expectedKeys = array('pattern', 'min', 'max');
            $actualKeys   = array_keys($rules->toArray());
            $diff         = array_diff($expectedKeys, $actualKeys);

            if (empty($diff)) {
                // The field rules are complete
                return $rules;
            } else {
                // The field rules is missing some options
                throw new InternalException("Validation rules for '{$fieldName}' missing options: " . implode(', ', $diff));
            }
        } else {
            throw new InternalException("Missing validation rules for '{$fieldName}'.");
        }
    }

    /**
     * Set the schema
     * @param Collection $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }
    
    /**
     * Add new set of rules for a field
     *
     * @param string $fieldName
     * @param array  $rules List of validation rules. Supported array keys [min, max, pattern, match]
     *
     * @throws \Exception If a set rules already exists for the given field
     */
    public function addRule($fieldName, array $rules){
        if ($this->schema->get($fieldName)) {
            throw new InternalException("Validation rules already exist for field '{$fieldName}'");
        }

        $finalRules = array_merge(
            array(
                'min'     => '',
                'max'     => '',
                'pattern' => '',
            ), $rules
        );

        $this->schema->set($fieldName, $finalRules);
    }

    /**
     * Add multiple field rules definition
     * 
     * @param array $fieldRules Multi dimensional array of fields and their rules
     *
     * @throws \Exception If a set rules already exists for the given field
     */
    public function addRules(array $fieldRules)
    {
        foreach ($fieldRules as $fieldName => $rules){
            $this->addRule($fieldName, $rules);
        }
    }
}
