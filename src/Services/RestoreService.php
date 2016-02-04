<?php
namespace BackupCli\Services;

use BackupCli\Config;
use BackupCli\Providers\FileBackupProvider;

class RestoreService
{

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
            return "<info>Files restore job was successfully completed.</info>";
        }
    }

    // Restores a database backup.
    public static function db($arguments = [])
    {
        // Check if we have a 'filesystem', 'filesystem_path' and a 'database'.
        if ((!empty($arguments['filesystem'])) && (!empty($arguments['filesystem_path'])) && (!empty($arguments['database']))) {

            // Check if the filesystem config exists.
            if (!Config::checkKey($arguments['filesystem'], 'filesystem')) {
                return "<error>The source filesystem config '{$arguments['filesystem']}' was not found.</error>";
            }

            // Check if the database connection config exists.
            if (!Config::checkKey($arguments['database'], 'db')) {
                return "<error>The database connection '{$arguments['database']}' was not found.</error>";
            }

            // Execute the restore task.
            try {
                ManagerService::restoreDatabase($arguments['filesystem'], $arguments['filesystem_path'], $arguments['database']);
            } catch (\Exception $e) {
                return "<error>{$e->getMessage()}</error>";
            }

            // If we get till here, it means there were no exceptions, so the restore process was successful.
            return "<info>Restored database backup to db config '{$arguments['database']}' with success!</info>";
        }
    }

}