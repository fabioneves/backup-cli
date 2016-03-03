<?php namespace BackupCli\Compressors;

class SevenZipUltra extends SevenZip
{

    public function handles($type)
    {
        return strtolower($type) == '7zip-ultra';
    }

    public function getCompressCommandLine($inputPath)
    {
        return '7za a -sdel -mx9 -v3g ' . escapeshellarg($inputPath) . '.7z ' . escapeshellarg($inputPath);
    }
}
