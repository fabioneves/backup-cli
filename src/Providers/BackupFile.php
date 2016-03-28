<?php
namespace BackupCli\Providers;

use BackupCli\Tasks\CompressFileAlt;
use BackupManager\Compressors;
use BackupManager\Tasks;

class BackupFile extends Backup
{

    public function backup($directory, $storage, $storage_directory, $compression, $exclude)
    {
        // Set defaults
        $storage = empty($storage) ? 'local' : $storage;
        $compression = empty($compression) ? '7zip' : $compression;

        // Create backup.
        $backup_file = $this->createBackup($directory, $compression, $exclude);

        // Storage directory.
        $storage_directory = $this->getStorageDirectory($storage_directory);

        // Upload the backup file(s) to the storage.
        $this->uploadBackup($backup_file, $storage, $storage_directory);

        // Return information about the backup.
        return [
          'file'      => is_array($backup_file) ? basename($backup_file[0]) . ' (parts: ' . count($backup_file) . ')' : basename($backup_file),
          'storage'   => $storage,
          'directory' => $storage_directory,
        ];
    }

    public function restore($storage, $backup_file, $destination, $compression, $parts)
    {
        // Download backup file(s).
        $working_file = $this->downloadBackup($backup_file, $storage, $parts);

        // Decompress the archived backup.
        $working_file = $this->decompressBackup($working_file, $compression, $destination);

        return TRUE;

    }

    // Create backup.
    private function createBackup($directory, $compression, $exclude)
    {
        // Working file.
        $working_file = $this->getWorkingFile('local', 'files_backup_' . date('d-m-Y') . '_' . uniqid());

        // Check if the backup directory exists.
        if (!file_exists($directory)) {
            throw new \Exception("Backup directory doesn't exist.");
        }

        // Compression check.
        if ($compression == 'gzip') {
            throw new \Exception("gzip doesn't support compressing an entire directory, please use 7zip.");
        }

        // Compress directory.
        $compressor = $this->compressors->get($compression);
        $compress = new CompressFileAlt($compressor, $working_file, $this->shellProcessor, false, $directory . '/*', $exclude);
        $compress->execute();

        // Return compressed file.
        return $compressor->getCompressedPath($working_file);
    }

    private function getStorageDirectory($directory)
    {
        return empty($directory) ? 'files' : $directory;
    }
}