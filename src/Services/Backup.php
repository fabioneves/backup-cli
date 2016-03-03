<?php
namespace BackupCli\Services;

use BackupCli\Config;
use BackupCli\Providers\BackupDatabase;
use BackupCli\Providers\FileBackup;

class Backup extends Service
{

    // Backup database(s).
    public static function database($database, $storage, $compression, $storage_directory)
    {
        try {
            $database_provider = new BackupDatabase;
            $backup_path = $database_provider->backup($database, $storage, $compression, $storage_directory);
        } catch (\Exception $e) {
            return parent::consoleFormattedError($e->getMessage());
        }

        // If we get till here, it means there were no exceptions, so the backup process was successful.
        return "  Backup <fg=green>successfully</> saved on <fg=yellow>{$backup_path}.gz</> using <fg=blue>{$storage}</> filesystem.\n";
    }

    // Backup files.
    public static function files($arguments = [])
    {
        // Check if we have a 'backup_directory', 'target' and a 'target_directory'.
        if ((!empty($arguments['backup_directory'])) && (!empty($arguments['target'])) && (!empty($arguments['target_directory']))) {

            // Exclude optional argument.
            $exclude = empty($arguments['exclude']) ? null : $arguments['exclude'];

            // Files backup.
            try {
                $files = new FileBackupProvider(Config::getFile('filesystem'));
                $target_path = $files->backup($arguments['backup_directory'], $arguments['target'], $exclude);
            } catch (\Exception $e) {
                return "<error>{$e->getMessage()}</error>";
            }

            // If we get till here, it means there were no exceptions, so the backup process was successful.
            return "  Backup <fg=green>successfully</> saved on <fg=yellow>{$target_path}</> using <fg=blue>{$arguments['target']}</> filesystem.\n";
        }
    }

}