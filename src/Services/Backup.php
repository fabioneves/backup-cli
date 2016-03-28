<?php
namespace BackupCli\Services;

use BackupCli\Config;
use BackupCli\Providers\BackupDatabase;
use BackupCli\Providers\BackupFile;
use BackupCli\Providers\FileBackup;

class Backup extends Service
{

    public static function database($database, $storage, $compression, $storage_directory)
    {
        try {
            $database_provider = new BackupDatabase;
            $backup = $database_provider->backup($database, $storage, $compression, $storage_directory);
        } catch (\Exception $e) {
            return parent::consoleFormattedError($e->getMessage());
        }

        // Get storage config.
        $storage_config = Config::getStorage($backup['storage']);

        // If we get till here, it means there were no exceptions, so the backup process was successful.
        return "  Backup <fg=green>successfully</> saved on <fg=blue>{$backup['storage']}</> (<fg=yellow>{$storage_config['root']}</>) storage
  Backup path: <fg=yellow>{$backup['directory']}/{$backup['file']}</>\n";
    }

    public static function files($directory, $storage, $storage_directory, $compression, $exclude)
    {
        try {
            $file_provider = new BackupFile;
            $backup = $file_provider->backup($directory, $storage, $storage_directory, $compression, $exclude);
        } catch (\Exception $e) {
            return parent::consoleFormattedError($e->getMessage());
        }

        // Get storage config.
        $storage_config = Config::getStorage($backup['storage']);

        // If we get till here, it means there were no exceptions, so the backup process was successful.
        return "  Backup <fg=green>successfully</> saved on <fg=blue>{$backup['storage']}</> (<fg=yellow>{$storage_config['root']}</>) storage
  Backup path: <fg=yellow>{$backup['directory']}/{$backup['file']}</>\n";
    }

}