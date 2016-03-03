<?php namespace BackupCli\Compressors;

use BackupManager\Compressors\Compressor;
use Symfony\Component\Finder\Finder;

class SevenZip extends Compressor
{

    public function handles($type)
    {
        return strtolower($type) == '7zip';
    }

    public function getCompressCommandLine($inputPath)
    {
        return '7za a -sdel -mx5 -v3g ' . escapeshellarg($inputPath) . '.7z ' . escapeshellarg($inputPath);
    }

    public function getDecompressCommandLine($outputPath)
    {
        return '7za -y x ' . escapeshellarg($outputPath) . ' -o' . escapeshellarg(dirname($outputPath));
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
}
