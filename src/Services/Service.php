<?php namespace BackupCli\Services;

class Service
{

    protected static function consoleFormattedError($message)
    {
        return "\n  <fg=red>!!!! ERROR !!!!</>\n  <fg=yellow>$message</>\n";
    }
}