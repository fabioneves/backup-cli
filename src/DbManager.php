<?php

namespace app;

use BackupManager\Compressors;
use BackupManager\Config\Config as BackupManagerConfig;
use BackupManager\Databases;
use BackupManager\Filesystems;
use BackupManager\Manager;

class DbManager
{

    static public function get()
    {
        // File systems.
        $target_config = Config::getFile('filesystem');
        $filesystems = new Filesystems\FilesystemProvider(new BackupManagerConfig($target_config));
        $filesystems->add(new Filesystems\Awss3Filesystem);
        $filesystems->add(new Filesystems\LocalFilesystem);

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
}