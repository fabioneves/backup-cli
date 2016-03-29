<?php namespace BackupCli\Compressors;

class SevenZipNull extends SevenZip
{

    public function handles($type)
    {
        return strtolower($type) == '7zip-null';
    }

    public function getCompressCommandLine($inputPath, $optionalPath = null, $delete = false)
    {
        $optionalPath = empty($optionalPath) ? $inputPath : $optionalPath;
        $delete_switch = empty($delete) ? null : '-sdel';
        return '7za a ' . $delete_switch . ' -mx0 -v1g ' . escapeshellarg($inputPath) . '.7z ' . $optionalPath;
    }
}
