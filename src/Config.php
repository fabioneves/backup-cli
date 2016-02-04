<?php

namespace BackupCli;

use Symfony\Component\Yaml\Yaml;

class Config
{

    // Check configuration paths.
    private static function validate()
    {
        // Config path.
        $config_path = Config::path();

        // Check if the 'db' and 'filesystem' config files exist.
        if ((!file_exists($config_path.'db.yml')) || (!file_exists($config_path.'filesystem.yml'))) {
            return false;
        }

        return true;
    }

    // Get configuration path.
    public static function path()
    {
        return (\Phar::running(false)) ? dirname(\Phar::running(false)).'/config/' : __DIR__.'/../config/';
    }

    // Check if a key exists in a config file.
    public static function checkKey($key = null, $file = null)
    {
        if ((Config::validate()) && (!empty($key)) && (!empty($file))) {
            $config = Config::getFile($file);
            if (!empty($config[$key])) {
                return true;
            }
        }

        return false;
    }

    // Get all config from a specified file.
    public static function getFile($file = null)
    {
        // Validate configuration.
        if (Config::validate() && (!empty($file))) {
            $config_path = Config::path();
            $config = [];
            if ((!empty($file)) && (file_exists($config_path.$file.'.yml'))) {
                try {
                    $config = Yaml::parse(file_get_contents($config_path.$file.'.yml'));
                } catch (ParseException $e) {
                    printf("Unable to parse the YAML string: %s", $e->getMessage());
                }
            }

            return $config;
        }

        return false;
    }
}