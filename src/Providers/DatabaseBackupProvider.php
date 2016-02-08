<?php

namespace BackupCli\Providers;

use BackupCli\Config;
use BackupCli\Filesystems\OVHFilesystem;
use BackupManager\Compressors;
use BackupManager\Config\Config as BackupManagerConfig;
use BackupManager\Databases;
use BackupManager\Filesystems;
use BackupManager\Manager;

class DatabaseBackupProvider
{

    public static function bootstrap()
    {
        // Filesystems.
        $target_config = Config::getFile('filesystem');
        $filesystems = new Filesystems\FilesystemProvider(new BackupManagerConfig($target_config));
        $filesystems->add(new Filesystems\LocalFilesystem);
        $filesystems->add(new Filesystems\Awss3Filesystem);
        $filesystems->add(new Filesystems\DropboxFilesystem);
        $filesystems->add(new OVHFilesystem);

        // Databases.
        $db_config = Config::getFile('db');
        $databases = new Databases\DatabaseProvider(new BackupManagerConfig($db_config));
        $databases->add(new Databases\MysqlDatabase);
        $databases->add(new Databases\PostgresqlDatabase);

        // Compressors
        $compressors = new Compressors\CompressorProvider;
        $compressors->add(new Compressors\GzipCompressor);
        $compressors->add(new Compressors\NullCompressor);

        return new Manager($filesystems, $databases, $compressors);
    }

    public static function backupDatabase($source, array $targets, $compression = 'gzip')
    {
        $manager = self::bootstrap();

        return $manager->makeBackup()->run($source, $targets, $compression);
    }

    public static function restoreDatabase($filesystem, $path, $target_db, $compression = 'gzip')
    {
        $manager = self::bootstrap();

        return $manager->makeRestore()->run($filesystem, $path, $target_db, $compression);
    }
}