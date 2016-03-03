<?php namespace BackupCli\Providers;

use BackupCli\Compressors\SevenZip;
use BackupCli\Compressors\SevenZipNull;
use BackupCli\Compressors\SevenZipUltra;
use BackupCli\Config;
use BackupCli\Filesystems\OVH;
use BackupManager\Compressors;
use BackupManager\Config\Config as BackupManagerConfig;
use BackupManager\Databases;
use BackupManager\Filesystems;
use BackupManager\Procedures\Procedure;
use BackupManager\ShellProcessing\ShellProcessor;
use BackupManager\Tasks;
use Symfony\Component\Process\Process;

class Backup extends Procedure
{

    public function __construct()
    {
        // Filesystems
        $storage_config = Config::getFile('storage');
        $this->filesystems = new Filesystems\FilesystemProvider(new BackupManagerConfig($storage_config));
        $this->filesystems->add(new Filesystems\LocalFilesystem);
        $this->filesystems->add(new Filesystems\Awss3Filesystem);
        $this->filesystems->add(new Filesystems\DropboxFilesystem);
        $this->filesystems->add(new OVH);

        // Databases
        $db_config = Config::getFile('database');
        $this->databases = new Databases\DatabaseProvider(new BackupManagerConfig($db_config));
        $this->databases->add(new Databases\MysqlDatabase);
        $this->databases->add(new Databases\PostgresqlDatabase);

        // Compressors
        $this->compressors = new Compressors\CompressorProvider;
        $this->compressors->add(new Compressors\GzipCompressor);
        $this->compressors->add(new Compressors\NullCompressor);
        $this->compressors->add(new SevenZip);
        $this->compressors->add(new SevenZipNull);
        $this->compressors->add(new SevenZipUltra);

        // Shell processor.
        $this->shellProcessor = $this->getShellProcessor();
    }

    private function getShellProcessor()
    {
        return new ShellProcessor(new Process('', null, null, null, null));
    }

    protected function decompressBackup($backup_file, $compression, $parts = 1)
    {
        // Compressor.
        $compressor = $this->compressors->get($compression);

        try {
            // Unpack files.
            $unpack = new Tasks\Compression\DecompressFile(
              $compressor,
              $backup_file,
              $this->shellProcessor
            );
            $unpack->execute();

            // Decompressed backup.
            $working_file = $compressor->getDecompressedPath($backup_file);

        } catch (\Exception $e) {
            // Default message.
            $message = $e->getMessage();
            // Warn about missing parts option.
            if (strpos($e->getMessage(), 'Unexpected end of archive') !== false) {
                $message = 'Failed to decompress backup, perhaps you forgot to specify how many parts is the backup? Use -p option.';
                // Delete files if 7zip.
                $this->delete7zipFiles($backup_file, $compression, $compressor);
            }
            $this->deleteFile(basename($backup_file));
            throw new \Exception($message);
        }

        // Delete files after extraction if 7zip compression is used.
        $this->delete7zipFiles($backup_file, $compression, $compressor);

        // Return backup decompressed path/file.
        return empty($working_file) ? null : $working_file;
    }

    protected function deleteFile($file)
    {
        try {
            $delete = new Tasks\Storage\DeleteFile($this->filesystems->get('local'), $file);
            $delete->execute();
        } catch (\Exception $e) {
        }
    }

    protected function deleteFiles($files)
    {
        foreach ($files as $file) {
            $this->deleteFile(basename($file));
        }
    }

    protected function delete7zipFiles($backup_file, $compression, $compressor)
    {
        if (strpos($compression, '7zip') !== false) {
            $this->deleteFiles($compressor->findParts($backup_file));
        }
    }

    protected function downloadBackup($file, $storage, $parts = null)
    {
        // Get extra file names to download if this is a multipart backup.
        $backup_files = [$file];
        if (!empty($parts)) {
            $backup_files = [];
            $pattern = '/(.+\.)([0-9]{3})/i';
            for ($i = 001; $i <= $parts; $i++) {
                $part_number = str_pad($i, 3, '0', STR_PAD_LEFT);
                $backup_files[] = preg_replace($pattern, '${1}' . $part_number, $file);
            }
        }

        // Download files.
        try {
            foreach ($backup_files as $backup_file) {
                $transfer = new Tasks\Storage\TransferFile(
                  $this->filesystems->get($storage),
                  $backup_file,
                  $this->filesystems->get('local'),
                  basename($backup_file)
                );
                $transfer->execute();
            }
            return $this->getWorkingFile('local', basename($backup_files[0]));
        } catch (\Exception $e) {
            $message = $e->getMessage();
            // If file already exists, don't delete it.
            if (strpos($e->getMessage(), 'File already exists at path') === false) {
                if (!empty($backup_files)) {
                    $this->deleteFiles($backup_files);
                }
            }
            // Failed to download.
            if (strpos($e->getMessage(), 'File not found at path:') !== false) {
                $message = "Failed to download file, please verify the path or 7zip parts number.\n  File (<fg=green>$storage</>): <fg=white>$backup_file</>";
            }
            throw new \Exception($message);
        }
    }

    protected function uploadBackup($files, $storage, $storage_directory)
    {
        try {
            // Backup file(s).
            $files = is_array($files) ? $files : [$files];
            // Local and other storage systems.
            $local_filesystem = $this->filesystems->get('local');
            $storages = explode(',', $storage);
            // Loop through all the storage systems.
            foreach ($storages as $storage) {
                // Loop through all the backup files.
                foreach ($files as $file) {
                    // Filesystem destination.
                    $filesystem = new Filesystems\Destination($storage, $storage_directory . '/' . basename($file));
                    // Transfer file.
                    $transfer_file = new Tasks\Storage\TransferFile(
                      $local_filesystem,
                      basename($file),
                      $this->filesystems->get($filesystem->destinationFilesystem()),
                      $filesystem->destinationPath()
                    );
                    $transfer_file->execute();
                }
            }
        } catch (\Exception $e) {
            // Delete all the compressed files.
            if (!empty($files)) {
                $this->deleteFiles($file);
            }
            // Show message.
            throw new \Exception($e->getMessage());
        }

        // Delete files.
        $this->deleteFiles($files);
    }

}