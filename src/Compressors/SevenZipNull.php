<?php namespace BackupCli\Compressors;

class SevenZipNull extends SevenZip
{

    public function handles($type)
    {
        return strtolower($type) == '7zip-null';
    }

    public function getCompressCommandLine($inputPath)
    {
        return '7za a -sdel -mx0 -v3g ' . escapeshellarg($inputPath) . '.7z ' . escapeshellarg($inputPath);
    }
}
