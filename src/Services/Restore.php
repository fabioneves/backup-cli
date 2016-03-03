<?php
namespace BackupCli\Services;

use BackupCli\Config;
use BackupCli\Providers\BackupDatabase;

class Restore extends Service
{

    // Restores a database backup.
    public static function database($database, $storage, $backup_file, $compression, $parts)
    {
        // Execute the restore task.
        try {
            $database_provider = new BackupDatabase;
            $database_provider->restore($database, $storage, $backup_file, $compression, $parts);
        } catch (\Exception $e) {
            return parent::consoleFormattedError($e->getMessage());
        }
die('Service restore');
// If we get till here, it means there were no exceptions, so the restore process was successful.
        return "\n  Database restored with success!

  Filesystem: <fg=green>{$arguments['filesystem']}</>
  Backup file: <fg=green>{$arguments['filesystem_path']}</>
  Database: <fg=green>{$arguments['database']}</>\n";
    }

    // Restore files backup to a destination path.
    public static function files($arguments = [])
    {
        // Check if we have a 'filesystem', 'filesystem_path' and a 'destination_path'.
        if ((!empty($arguments['filesystem'])) && (!empty($arguments['filesystem_path'])) && (!empty($arguments['destination_directory']))) {

            // Check if the filesystem config exists.
            if (!Config::checkKey($arguments['filesystem'], 'filesystem')) {
                return "<error>The source filesystem config '{$arguments['filesystem']}' was not found.</error>";
            }

            // Files backup.
            try {
                $files = new FileBackupProvider(Config::getFile('filesystem'));
                $files->restore($arguments['filesystem'], $arguments['filesystem_path'], $arguments['destination_directory']);
            } catch (\Exception $e) {
                return "<error>{$e->getMessage()}</error>";
            }

            // If we get till here, it means there were no exceptions, so the backup process was successful.
            return "\n  Files restored with success!

  Filesystem: <fg=green>{$arguments['filesystem']}</>
  Backup file: <fg=green>{$arguments['filesystem_path']}</>
  Extracted to: <fg=green>{$arguments['destination_directory']}</>\n";
        }
    }


}