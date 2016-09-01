<?php

namespace Evozon\TranslatrBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DownloadCommand
 *
 * @package   Evozon\TranslatrBundle\Command
 * @author    Balazs Csaba <csaba.balazs@evozon.com>
 * @copyright 2016 Evozon (https://www.evozon.com)
 */
class DownloadCommand extends AbstractCommand
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('evozon:translatr:download')
            ->setDescription('Download translations from OneSky')
            ->setDefinition([new InputOption('clear-cache', null, InputOption::VALUE_NONE, 'Clear the cache after dump')]);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Downloading translations from OneSky</info>");

        $this->getContainer()
            ->get('evozon_translatr_downloader')
            ->download();

        $output->writeln("<info>Translations successfully updated from OneSky</info>");

        if ($input->getOption('clear-cache')) {
            $output->writeln("<info>Clearing cache after dumping translations</info>");
            $this->clearCache($output);
        }
    }

    /**
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function clearCache(OutputInterface $output)
    {
        $this->runCommand($output, 'cache:clear');
    }
}
