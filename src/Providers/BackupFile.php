<?php
namespace BackupCli\Providers;

use BackupManager\Procedures\Sequence;
use BackupManager\Tasks\Storage\DeleteFile;
use BackupManager\Tasks\Storage\TransferFile;
use Symfony\Component\Process\Process;

class BackupFile extends Backup
{

    public function backup($backup_path, $targets, $exclude, $compression = '7zip')
    {
        // Init.
        $sequence = new Sequence;
        $local_filesystem = $this->filesystems->get('local');

        // Build destinations.
        $target_path = 'files/'.$arguments['target_directory'].'/files_backup_'.date('d-m-Y').'_'.uniqid().'.tar.gz';
        $input_targets = explode(',', $arguments['target']);
        foreach ($input_targets as $target) {
            // Check if the filesystem config exists.
            if (!Config::checkKey($target, 'filesystem')) {
                return "<error>The target '$target' config was not found.</error>";
            }
            // Add this filesystem to the targets array.
            $targets[] = new Destination($target, $target_path);
        }

        // Working file.
        $working_file = $this->getWorkingFile('local');

        // Create local files backup.
        $working_file = $this->createBackupFile($compression, $backup_path, $working_file, $exclude);

        // Upload the archive to the targets.
        foreach ($targets as $target) {
            $sequence->add(new TransferFile(
              $local_filesystem,
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

    // Create backup file.
    private function createBackupFile($compression, $backup_path, $backup_file, $exclude)
    {
        switch ($compression) {
            case '7zip':
                $backup_file = $this->sZipCreate($backup_path, $backup_file, $exclude);
            default:
                $backup_file = $this->tar($backup_path, $backup_file, $exclude);
                break;
        }

        return $backup_file;
    }

    // Compresses a directory with 7zip.
    private function sZipCreate($backup_path, $backup_file, $exclude = null)
    {
        // Backup filename.
        $backup_file = $backup_file.'.7z';

        // Create file archive with volumes of 4G.
        $command = "7za a -v4g $backup_file $backup_path";
        $process = $this->run($command);

        // If the tar fails, throw a new exception.
        if (!$process->isSuccessful()) {
            throw new \Exception('Failed to create backup archive: '.$process->getErrorOutput());
        }

        return $backup_file;
    }

    // Compresses a directory with tar.
    private function tar($backup_path, $backup_file, $exclude = null)
    {
        // Backup filename.
        $backup_file = $backup_file.'.tar.gz';

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

        return $backup_file;
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

    private function run($command)
    {
        $process = new Process($command);
        $process->setTimeout(0);
        $process->run();

        return $process;
    }
}