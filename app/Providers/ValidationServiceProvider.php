<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;


class ValidationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        validator::extend('strict_string', function ($attribute, $value) {
            return is_string($value);
        }, 'The :attribute  must be string');
        Validator::extend('strict_int', function ($attribute, $value) {
            return is_int($value);
        }, 'The :attribute  must be a integer.');
        Validator::extend('strict_bool', function ($attribute, $value) {
            return is_bool($value);
        }, 'The :attribute  must be a boolean.');
        Validator::extend('strict_float', function ($attribute, $value) {
            return is_float($value);
        }, 'The :attribute  must be a float.');
        Validator::extend('strict_number', function ($attribute, $value) {
            return is_numeric($value);
        }, 'The :attribute must be a valid number.');
        Validator::extend('array_of_strings', function ($attribute, $value) {
            if (!is_array($value)) return false;
            foreach ($value as $v) {
                if (!is_string($v)) return false;
            }
            return true;
        }, 'The :attribute  must be an array of strings.');
        Validator::extend('array_of_ints', function ($attribute, $value) {
            if (!is_array($value)) return false;
            foreach ($value as $v) {
                if (!is_int($v)) return false;
            }
            return true;
        }, 'The :attribute  must be an array of integers.');
        Validator::extend('object_schema', function ($attribute, $value, $parameters) {
            if (!is_array($value)) return false; // must be JSON object
            foreach ($parameters as $param) {
                [$key, $type] = explode(':', $param);

                if (!array_key_exists($key, $value)) {
                    return false; // missing key
                }
                $val = $value[$key];
                switch ($type) {
                    case 'string':
                        if (!is_string($val)) return false;
                        break;
                    case 'int':
                        if (!is_int($val)) return false;
                        break;
                    case 'bool':
                        if (!is_bool($val)) return false;
                        break;
                    case 'array':
                        if (!is_array($val)) return false;
                        break;
                    default:
                        return false;
                }
            }

            return true;
        }, 'The :attribute  must match the required schema.');
        Validator::extend('array_of_objects_with_schema', function ($attribute, $value, $parameters) {
            if (!is_array($value)) return false;
            $schema = [];
            foreach ($parameters as $param) {
                [$key, $type] = array_map('trim', explode(':', $param));
                $schema[$key] = $type;
            }
            foreach ($value as $index => $obj) {
                if (!is_array($obj)) return false; // must be object

                foreach ($schema as $key => $type) {
                    if (!array_key_exists($key, $obj)) {
                        app()->instance(
                            'schema_field',
                            "{$attribute}.{$index}.{$key}"
                        );
                        return false;
                    }
                    $val = $obj[$key];
                    switch ($type) {
                        case 'string':
                            if (!is_string($val)) {
                                app()->instance(
                                    'schema_field',
                                    "{$attribute}.{$index}.{$key} must be string"
                                );
                                return false;
                            };
                            break;
                        case 'int':
                            if (!is_int($val)) {
                                app()->instance(
                                    'schema_field',
                                    "{$attribute}.{$index}.{$key} must be integer"
                                );
                                return false;
                            };
                            break;
                        case 'bool':
                            if (!is_bool($val)) {
                                app()->instance(
                                    'schema_field',
                                    "{$attribute}.{$index}.{$key} must be boolean"
                                );
                                return false;
                            };
                            break;
                        case 'array':
                            if (!is_array($val)) {
                                app()->instance(
                                    'schema_field',
                                    "{$attribute}.{$index}.{$key} must be array"
                                );
                                return false;
                            };
                            break;
                        case 'double':
                            if (!is_numeric($val)) {
                                app()->instance(
                                    'schema_field',
                                    "{$attribute}.{$index}.{$key} must be double"
                                );
                                return false;
                            };
                            break;
                        case 'float':
                            if (!is_float($val)) {
                                app()->instance(
                                    'schema_field',
                                    "{$attribute}.{$index}.{$key} must be float"
                                );
                                return false;
                            };
                            break;
                        case 'decimal':
                            if (!preg_match('/^\d+(\.\d{1,15})?$/', (string)$val)) {
                                app()->instance(
                                    'schema_field',
                                    "{$attribute}.{$index}.{$key} must be decimal"
                                );
                                return false;
                            };
                            break;
                        default:
                            return false;
                    }
                }
            }
            return true;
        }, 'The :schema_field is required');
        Validator::replacer(
            'array_of_objects_with_schema',
            function ($message) {
                return str_replace(
                    ':schema_field',
                    app()->bound('schema_field')
                        ? app('schema_field')
                        : '',
                    $message
                );
            }
        );
    }
}
