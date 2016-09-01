<?php

namespace Evozon\TranslatrBundle\OneSky;

use Symfony\Component\Translation\Loader\PoFileLoader;
use Evozon\TranslatrBundle\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\Dumper\PoFileDumper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class Downloader
 *
 * @package   Evozon\TranslatrBundle\OneSky
 * @author    Balazs Csaba <csaba.balazs@evozon.com>
 * @copyright 2016 Evozon (https://www.evozon.com)
 */
class Downloader extends AbstractService
{
    /**
     * @return $this
     */
    public function download()
    {
        $sources = $this->getAllSources();
        $locales = $this->getAllLocales();

        foreach ($sources as $source) {
            foreach ($locales as $locale) {
                $this->dump($source, $locale);
            }
        }

        return $this;
    }

    /**
     * @param string $source
     * @param string $locale
     *
     * @return $this
     */
    private function dump($source, $locale)
    {
        $content = null;
        foreach ($this->mappings as $mapping) {
            if (!$mapping->useLocale($locale) || !$mapping->useSource($source)) {
                continue;
            }

            if ($content === null) {
                $content = $this->fetch($source, $locale);
            }

            $this->write(
                $mapping->getOutputFilename($source, $locale),
                $this->cleanupContent($content)
            );


            $this->merge($mapping, $source, $locale);
        }

        return $this;
    }

    /**
     * @param Mapping $mapping
     * @param string  $source
     * @param string  $locale
     */
    private function merge(Mapping $mapping, $source, $locale)
    {
        $oneSkyFile = $mapping->getOutputFilename($source, $locale);
        $localFile = $mapping->getOriginalOutputFilename($source, $locale);
        $domain = $mapping->getOutputFileDomain($source, $locale);

        $poFileLoader = new PoFileLoader();
        $oneSkyCatalogue = $poFileLoader->load($oneSkyFile, $locale, $domain);
        $localCatalogue = $poFileLoader->load($localFile, $locale, $domain);

        // delete temporary file downloaded from OneSky
        $fs = new Filesystem();
        $fs->remove($oneSkyFile);

        $mergeOperation = new MergeOperation($oneSkyCatalogue, $localCatalogue, $locale);
        $poFileDumper = new PoFileDumper();
        $poFileDumper->setBackup(false);
        $poFileDumper->dump($mergeOperation->getResult(), ['path' => dirname($localFile)]);
    }

    /**
     * Remove empty newlines from the content
     *
     * @param $content
     *
     * @return mixed
     */
    private function cleanupContent($content)
    {
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $content);
    }

    /**
     * @param string $source
     * @param string $locale
     *
     * @return mixed
     */
    private function fetch($source, $locale)
    {
        $content = $this->client->translations(
            'export',
            [
                'project_id'       => $this->project,
                'locale'           => $locale,
                'source_file_name' => $source,
            ]
        );

        return $content;
    }

    /**
     * @param $file
     * @param $content
     *
     * @return $this
     */
    private function write($file, $content)
    {
        $fs = new Filesystem();
        $fs->dumpFile($file, $content);

        return $this;
    }
}
