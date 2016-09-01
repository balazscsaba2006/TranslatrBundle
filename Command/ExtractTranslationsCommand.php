<?php

namespace Evozon\TranslatrBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExtractTranslationsCommand
 *
 * @package   Evozon\TranslatrBundle\Command
 * @author    Balazs Csaba <csaba.balazs@evozon.com>
 * @author    Calin Bolea <calin.bolea@evozon.com>
 * @copyright 2016 Evozon (https://www.evozon.com)
 */
class ExtractTranslationsCommand extends AbstractCommand
{
    /**
     * @var array
     */
    private $supportedConfigs = [
        'routes',
        'app',
        'bundles_messages',
        'bundles_rest',
    ];

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('evozon:translatr:extract')
            ->setDescription('Extract translations from the application')
            ->addOption(
                'configs',
                null,
                InputOption::VALUE_OPTIONAL,
                'Configure what will be extracted. Support arrays as comma separated values. Defaults to all available configurations.',
                null
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Extracting translations from the application</info>");

        $extractConfigs = $this->supportedConfigs;
        if (null !== $input->getOption('configs')) {
            $configs = array_map('trim', explode(',', $input->getOption('configs')));

            $unsupported = [];
            foreach ($configs as $config) {
                if (!in_array($config, $this->supportedConfigs)) {
                    $unsupported[] = $config;
                }
            }

            if ($unsupported) {
                $output->writeln(
                    sprintf(
                        '<error>Unsupported configs given: %sAvailable configs: %s</error>',
                        implode(',', $unsupported).PHP_EOL,
                        implode(',', $this->supportedConfigs)
                    )
                );

                return;
            }

            $extractConfigs = $configs;
        }

        $locales = $this->getContainer()->getParameter('available_locales', []);
        $locales = array_map(
            function ($locale) {
                return strtolower(substr($locale, 0, 2));
            },
            $locales
        );

        foreach ($extractConfigs as $config) {
            $this->runCommand($output, 'translation:extract', ['locales' => $locales, '--config' => $config]);
        }

        $output->writeln("<info>Translations successfully extracted</info>");
    }
}
