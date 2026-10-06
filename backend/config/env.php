<?php

/*
 * Deployment settings.
 *
 * Values are read from an ini file outside the web root (so secrets are never
 * servable), then from the process environment, then from the defaults passed
 * by the caller. The defaults reproduce the local XAMPP setup, so development
 * keeps working with no config file present.
 *
 * Override the file location with the NRS_CONFIG environment variable.
 */

function nrs_env($key, $default = null)
{
    static $values = null;

    if ($values === null) {
        $values = [];
        // Apache SetEnv reaches getenv() under mod_php and $_SERVER under
        // php-fpm, so check both.
        $file = getenv("NRS_CONFIG");
        if (($file === false || $file === "") && isset($_SERVER["NRS_CONFIG"])) {
            $file = $_SERVER["NRS_CONFIG"];
        }
        if ($file === false || $file === "") {
            $file = "/etc/nrs/nrs.ini";
        }
        if (is_readable($file)) {
            $parsed = parse_ini_file($file, false, INI_SCANNER_RAW);
            if (is_array($parsed)) {
                $values = $parsed;
            }
        }
    }

    if (array_key_exists($key, $values) && $values[$key] !== "") {
        return $values[$key];
    }

    $fromEnv = getenv($key);
    if ($fromEnv !== false && $fromEnv !== "") {
        return $fromEnv;
    }

    return $default;
}
