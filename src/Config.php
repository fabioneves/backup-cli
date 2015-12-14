<?php

namespace app;

use Symfony\Component\Yaml\Yaml;

class Config
{

    // Check configuration paths.
    static public function check()
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
    static public function path()
    {
        return (\Phar::running(false)) ? dirname(\Phar::running(false)).'/config/' : __DIR__.'/../config/';
    }

    // Check if a key exists in a config file.
    static public function checkKey($key = null, $file = null)
    {
        if ((Config::check()) && (!empty($key)) && (!empty($file))) {
            $config = Config::getFile($file);
            if (!empty($config[$key])) {
                return true;
            }
        }

        return false;
    }

    // Get all config from a specified file.
    static public function getFile($file = null)
    {
        $config_path = (\Phar::running(false)) ? dirname(\Phar::running(false).'/config/') : __DIR__.'/../config/';
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
}