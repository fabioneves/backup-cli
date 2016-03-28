<?php
namespace BackupCli\Services;

use BackupCli\Providers\BackupDatabase;
use BackupCli\Providers\BackupFile;

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

        // If we get till here, it means there were no exceptions, so the restore process was successful.
        return "  Restore process completed <fg=green>successfully</>!\n";
    }

    // Restore files backup to a destination path.
    public static function files($storage, $backup_file, $destination, $compression, $parts)
    {

        // Files backup.
        try {
            $file_provider = new BackupFile;
            $file_provider->restore($storage, $backup_file, $destination, $compression, $parts);
        } catch (\Exception $e) {
            return parent::consoleFormattedError($e->getMessage());
        }

        // If we get till here, it means there were no exceptions, so the restore process was successful.
        return "  Restore process completed <fg=green>successfully</>!\n";
    }


}