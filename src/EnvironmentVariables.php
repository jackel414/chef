<?php

trait EnvironmentVariables
{
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function env($key, $default = null)
    {
    	$environment_variables = $this->getEnvironmentVariables();

        $value = $environment_variables[$key] ?: false;

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        return $value;
    }

    /**
     * Get the contents of env file.
     *
     * @return array
     */
    public function getEnvironmentVariables()
    {
        $env_file = __DIR__ . '/.env';
        $env_default_file = __DIR__ . '/.env.default';

        return parse_ini_file($env_file) ?: parse_ini_file($env_default_file);
    }
}