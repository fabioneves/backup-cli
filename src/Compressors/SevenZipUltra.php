<?php namespace BackupCli\Compressors;

class SevenZipUltra extends SevenZip
{

    public function handles($type)
    {
        return strtolower($type) == '7zip-ultra';
    }

    public function getCompressCommandLine($inputPath, $optionalPath = null, $delete = false)
    {
        $optionalPath = empty($optionalPath) ? $inputPath : $optionalPath;
        $delete_switch = empty($delete) ? null : '-sdel';
        return '7za a ' . $delete_switch . ' -mx9 -v512m ' . escapeshellarg($inputPath) . '.7z ' . $optionalPath;
    }
}
