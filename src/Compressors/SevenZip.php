<?php namespace BackupCli\Compressors;

use BackupManager\Compressors\Compressor;
use Symfony\Component\Finder\Finder;

class SevenZip extends Compressor
{

    public function handles($type)
    {
        return strtolower($type) == '7zip';
    }

    public function getCompressCommandLine($inputPath, $optionalPath = null, $delete = false, $exclude = null)
    {
        $optionalPath = empty($optionalPath) ? $inputPath : $optionalPath;
        $delete_switch = empty($delete) ? null : '-sdel';
        $exclude = $this->excludeArguments($exclude);
        return '7za a ' . $delete_switch . ' -mx5 -v3g ' . $exclude . escapeshellarg($inputPath) . '.7z ' . escapeshellarg($optionalPath);
    }

    public function getDecompressCommandLine($outputPath, $optionalPath = null)
    {
        $optionalPath = empty($optionalPath) ? dirname($outputPath) : $optionalPath;
        return '7za -y x ' . escapeshellarg($outputPath) . ' -o' . escapeshellarg($optionalPath);
    }

    public function getCompressedPath($inputPath)
    {
        $files = [];
        $finder = new Finder();
        $finder->name(basename($inputPath) . '.*')->in(dirname($inputPath));
        foreach ($finder as $file) {
            $files[] = $file->getRealpath();
        }

        return $files;
    }

    public function getDecompressedPath($inputPath)
    {
        return preg_replace('/.7z.001$/', '', $inputPath);
    }

    public function findParts($inputPath)
    {
        $files = [];
        $finder = new Finder();
        $finder->name(str_replace('.7z.001', '', basename($inputPath)) . '.7z.*')
          ->in(dirname($inputPath));
        foreach ($finder as $file) {
            $files[] = $file->getRealpath();
        }

        return $files;
    }

    private function excludeArguments($exclude)
    {
        $exclude_argument = null;
        if (!empty($exclude)) {
            $excludes = explode(',', $exclude);
            foreach ($excludes as $exclude) {
                $exclude_argument .= '-xr!' . $exclude . ' ';
            }
        }
        return $exclude_argument;
    }

}
