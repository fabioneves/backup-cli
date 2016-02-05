<?php
namespace BackupCli\Services;

use BackupCli\Config;
use BackupCli\Providers\FileBackupProvider;
use BackupManager\Filesystems\Destination;

class BackupService
{

    // Backup files.
    public static function files($arguments = [])
    {
        // Check if we have a 'backup_path', 'target' and a 'target_path'.
        if ((!empty($arguments['backup_path'])) && (!empty($arguments['target'])) && (!empty($arguments['target_path']))) {

            // Exclude optional argument.
            $exclude = empty($arguments['exclude']) ? null : $arguments['exclude'];

            // Build destinations.
            $target_path = 'files/'.$arguments['target_path'].'/files_backup_'.date('d-m-Y').'_'.uniqid().'.tar.gz';
            $input_targets = explode(',', $arguments['target']);
            foreach ($input_targets as $target) {
                // Check if the filesystem config exists.
                if (!Config::checkKey($target, 'filesystem')) {
                    return "<error>The target '$target' config was not found.</error>";
                }
                // Add this filesystem to the targets array.
                $targets[] = new Destination($target, $target_path);
            }

            // Files backup.
            try {
                $files = new FileBackupProvider(Config::getFile('filesystem'));
                $files->backup($arguments['backup_path'], $targets, $exclude);
            } catch (\Exception $e) {
                return "<error>{$e->getMessage()}</error>";
            }

            // If we get till here, it means there were no exceptions, so the backup process was successful.
            return "  Backup <fg=green>successfully</> saved on <fg=yellow>{$target_path}</> using <fg=blue>{$arguments['target']}</> filesystem!\n";
        }
    }

    // Backup database(s).
    public static function db($arguments = [])
    {
        // Check if we have a 'database' and a 'target'.
        if ((!empty($arguments['database'])) && (!empty($arguments['target']))) {

            // Check if the db connection config exists.
            if (!Config::checkKey($arguments['database'], 'db')) {
                return "<error>The database connection config '{$arguments['database']}' was not found.</error>";
            }

            // Build destinations.
            $target_path = 'databases/'.$arguments['database'].'/'.$arguments['database'].'_'.date('d-m-Y').'_'.uniqid().'.sql';
            $input_targets = explode(',', $arguments['target']);
            foreach ($input_targets as $target) {
                // Check if the filesystem config exists.
                if (!Config::checkKey($target, 'filesystem')) {
                    return "<error>The target '$target' config was not found.</error>";
                }
                // Add this filesystem to the targets array.
                $targets[] = new Destination($target, $target_path);
            }

            // Execute the backup task.
            try {
                ManagerService::backupDatabase($arguments['database'], $targets);
            } catch (\Exception $e) {
                return "<error>{$e->getMessage()}</error>";
            }

            // If we get till here, it means there were no exceptions, so the backup process was successful.
            return "  Backup <fg=green>successfully</> saved on <fg=yellow>{$target_path}.gz</> using <fg=blue>{$arguments['target']}</> filesystem!\n";

        }
    }

}