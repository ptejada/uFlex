<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/2/2016
 * Time: 8:57 PM
 */

namespace ptejada\uFlex\Service\Validation;


use ptejada\uFlex\Classes\Collection;

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
                    'min' => 3,
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
        if ($valueLength < $rule->min) {
            throw new ValidationException(
                ValidationException::ERROR_MINIMUM, $fieldName,
                "The value '{$fieldValue}' is shorter than {$rule->min} characters."
            );
        }

        if ($valueLength > $rule->max) {
            throw new ValidationException(
                ValidationException::ERROR_MAXIMUM, $fieldName,
                "The value '{$fieldValue}' is longer than {$rule->max} characters."
            );
        }

        if (!preg_match($rule->pattern, $value)) {
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
                    "The value '{$fieldValue}' does not match the value for '{$fieldToMatch}'."
                );
            }
        }
    }

    /**
     * Validate all the fields
     *
     * @throws ValidationException
     */
    public function validateAll( $data = null )
    {

        foreach ($this->data as $fieldName => $fieldValue) {
            $this->validate($fieldName, $fieldValue);
        }
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
                throw new \Exception("Validation rules for '{$fieldName}' missing options: " . implode(', ', $diff));
            }
        } else {
            throw new \Exception("Missing validation rules for '{$fieldName}'.");
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
     * Set the data to validate
     * @param Collection $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
