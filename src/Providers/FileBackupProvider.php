<?php
namespace BackupCli\Providers;

use BackupManager\Config\Config;
use BackupManager\Filesystems;
use BackupManager\Procedures\Procedure;
use BackupManager\Procedures\Sequence;
use BackupManager\Tasks\Storage\DeleteFile;
use BackupManager\Tasks\Storage\TransferFile;
use Symfony\Component\Process\Process;

class FileBackupProvider extends Procedure
{

    private $local_filesystem;

    public function __construct(array $filesystem_config)
    {
        // Filesystems.
        $this->filesystems = new Filesystems\FilesystemProvider(new Config($filesystem_config));
        $this->filesystems->add(new Filesystems\LocalFilesystem);
        $this->filesystems->add(new Filesystems\Awss3Filesystem);
        $this->filesystems->add(new Filesystems\DropboxFilesystem);

        // Set local filesystem.
        $this->local_filesystem = $this->filesystems->get('local');
    }

    public function backup($backup_path, $targets, $exclude)
    {
        // Init sequence.
        $sequence = new Sequence;

        // Working file.
        $working_file = $this->getWorkingFile('local').'.tar.gz';

        // Create local files backup.
        $this->tar($backup_path, $working_file);

        // Upload the archive to the targets.
        foreach ($targets as $target) {
            $sequence->add(new TransferFile(
              $this->local_filesystem,
              basename($working_file),
              $this->filesystems->get($target->destinationFilesystem()),
              $target->destinationPath()
            ));
        }

        // Cleanup the local archive.
        $sequence->add(new DeleteFile($this->local_filesystem, basename($working_file)));

        // Execute the tasks.
        $sequence->execute();
    }

    public function restore($filesystem, $filesystem_path, $destination_dir)
    {
        // Working file.
        $working_file = $this->getWorkingFile('local', 'restore_'.uniqid().'_'.basename($filesystem_path));
        // var_dump($working_file, $filesystem, $filesystem_path, $destination_path);

        // Download or retrieve the archived backup file.
        $transfer_file = new TransferFile(
          $this->filesystems->get($filesystem), $filesystem_path,
          $this->local_filesystem, basename($working_file)
        );
        $transfer_file->execute();

        // Extract file(s) to the destination directory.
        $this->untar($working_file, $destination_dir);

        // Cleanup the local copy.
        $del_file = new DeleteFile($this->local_filesystem, basename($working_file));
        $del_file->execute();
    }

    // Compresses a directory.
    private function tar($backup_path, $backup_file, $exclude = null)
    {
        // Exclude string.
        $exclude_argument = null;
        if ($exclude) {
            $exclude_argument = '--exclude='.str_replace(',', ' --exclude=', $exclude);
        }

        // Excludes.
        $excludes = '--exclude=.DS_Store --exclude=.git';

        // Tar command string.
        $tar_command = "tar $exclude_argument $excludes -cvzf $backup_file -C $backup_path .";
        $process = new Process($tar_command);
        $process->setTimeout(0);
        $process->run();

        // If the tar fails, throw a new exception.
        if (!$process->isSuccessful()) {
            throw new \Exception('Failed to create backup archive: '.$process->getErrorOutput());
        }

        return true;
    }

    // Extracts a tar.gz file contents to a directory.
    private function untar($backup_file, $directory)
    {
        // Tar command string.
        $tar_command = "mkdir -p $directory && tar -xf $backup_file -C $directory";
        $process = new Process($tar_command);
        $process->setTimeout(0);
        $process->run();

        // If the tar fails, throw a new exception.
        if (!$process->isSuccessful()) {
            throw new \Exception('Failed to extract backup: '.$process->getErrorOutput());
        }

        return true;
    }

}