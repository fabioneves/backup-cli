<?php
namespace app\Commands;

use app\Update\ManifestStrategy;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdate extends Command
{

    protected function configure()
    {
        $this->setName('self-update')
          ->setDescription('Check and update this tool if there is a new version.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater(null, false);
        $updater->setStrategyObject(
          new ManifestStrategy(
            $this->getApplication()->getVersion(),
            true,
            'http://fabioneves.github.io/backup-cli/manifest.json'
          )
        );

        try {
            $result = $updater->update();
            $result ? exit('Updated!') : exit('No update needed!');
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return;
        }
    }


}