<?php namespace BackupCli\Providers;

use BackupManager\Compressors;
use BackupManager\Databases;
use BackupManager\Filesystems;
use BackupManager\Tasks;

class BackupDatabase extends Backup
{

    public function backup($database, $storage, $compression, $storage_directory)
    {
        // Set defaults
        $storage = empty($storage) ? 'local' : $storage;
        $compression = empty($compression) ? 'gzip' : $compression;

        // Dump the database.
        $working_file = $this->dumpDatabase($database);

        // Compress backup.
        $backup_file = $this->compressDatabaseDump($working_file, $compression);

        // Storage directory.
        $storage_directory = $this->getStorageDirectory($database, $storage_directory);

        // Upload the backup file(s) to the storage.
        $this->uploadBackup($backup_file, $storage, $storage_directory);

        // Return information about the backup.
        return [
          'file'      => is_array($backup_file) ? basename($backup_file[0]) . ' (parts: ' . count($backup_file) . ')' : basename($backup_file),
          'storage'   => $storage,
          'directory' => $storage_directory,
        ];
    }

    public function restore($database, $storage, $backup_file, $compression, $parts)
    {
        // Download backup file(s).
        $working_file = $this->downloadBackup($backup_file, $storage, $parts);

        // Decompress the archived backup.
        $working_file = $this->decompressBackup($working_file, $compression);

        // Restore the database.
        $this->restoreDatabase($database, $working_file);

        // Cleanup the local files.
        $this->deleteFile(basename($working_file));
    }

    private function getFilename($database)
    {
        return $database . '_' . date('d-m-Y') . '_' . uniqid() . '.sql';
    }

    private function getStorageDirectory($database, $directory)
    {
        return empty($directory) ? 'databases/' . $database : $directory;
    }

    private function restoreDatabase($database, $backup_file)
    {
        try {
            $restore = new Tasks\Database\RestoreDatabase(
              $this->databases->get($database),
              $backup_file,
              $this->shellProcessor
            );
            $restore->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function compressDatabaseDump($working_file, $compression)
    {
        // Check if the file exists.
        if (!file_exists($working_file)) {
            throw new \Exception("Failed to compress database dump, file ($working_file) doesn't exist.");
        }

        try {

            // Compress dump.
            $compressor = $this->compressors->get($compression);
            $compress = new Tasks\Compression\CompressFile($compressor, $working_file, $this->shellProcessor);
            $compress->execute();

            // Delete database dump.
            $this->deleteFile(basename($working_file));

            // Return compressed file.
            return $compressor->getCompressedPath($working_file);

        } catch (\Exception $e) {
            $this->deleteFile(basename($working_file));
            throw new \Exception($e->getMessage());
        }
    }

    private function dumpDatabase($database)
    {
        // Backup filename.
        $filename = $this->getFilename($database);
        $working_file = $this->getWorkingFile('local', $filename);

        // Dump database.
        $dump = new Tasks\Database\DumpDatabase(
          $this->databases->get($database),
          $working_file,
          $this->shellProcessor
        );
        $dump->execute();

        return $working_file;
    }
}