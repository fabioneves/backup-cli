<?php namespace BackupCli\Tasks;

use BackupManager\Compressors\Compressor;
use BackupManager\ShellProcessing\ShellProcessor;
use BackupManager\Tasks\Compression\DecompressFile;

class DecompressFileAlt extends DecompressFile
{

    private $sourcePath;
    private $optionalPath;
    private $shellProcessor;
    private $compressor;

    public function __construct(Compressor $compressor, $sourcePath, ShellProcessor $shellProcessor, $optionalPath)
    {
        $this->sourcePath = $sourcePath;
        $this->optionalPath = $optionalPath;
        $this->shellProcessor = $shellProcessor;
        $this->compressor = $compressor;
    }

    public function execute()
    {
        return $this->shellProcessor->process($this->compressor->getDecompressCommandLine(
          $this->sourcePath,
          $this->optionalPath
        ));
    }
}
