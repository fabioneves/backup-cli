<?php namespace BackupCli\Tasks;

use BackupManager\Compressors\Compressor;
use BackupManager\ShellProcessing\ShellProcessor;
use BackupManager\Tasks\Compression\CompressFile;

class CompressFileAlt extends CompressFile
{

    private $sourcePath;
    private $exclude;
    private $delete;
    private $optionalPath;
    private $shellProcessor;
    private $compressor;

    public function __construct(Compressor $compressor, $sourcePath, ShellProcessor $shellProcessor, $delete, $optionalPath, $exclude)
    {
        $this->compressor = $compressor;
        $this->delete = $delete;
        $this->exclude = $exclude;
        $this->sourcePath = $sourcePath;
        $this->optionalPath = $optionalPath;
        $this->shellProcessor = $shellProcessor;
    }

    public function execute()
    {
        return $this->shellProcessor->process($this->compressor->getCompressCommandLine(
          $this->sourcePath,
          $this->optionalPath,
          $this->delete,
          $this->exclude
        ));
    }
}
