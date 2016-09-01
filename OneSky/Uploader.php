<?php

namespace Evozon\TranslatrBundle\OneSky;

/**
 * Class Uploader
 *
 * @package   Evozon\TranslatrBundle\OneSky
 * @author    Balazs Csaba <csaba.balazs@evozon.com>
 * @copyright 2016 Evozon (https://www.evozon.com)
 */
class Uploader extends AbstractService
{
    /**
     * @var bool
     */
    protected $isKeepingAllStrings = false;

    /**
     * @return $this
     */
    public function upload()
    {
        $locales = $this->getAllLocales();
        foreach ($locales as $locale) {
            foreach ($this->mappings as $mapping) {
                $this->client->files('upload', [
                    'project_id'             => $this->project,
                    'file'                   => $mapping->getOutputFilename(null, $locale),
                    'file_format'            => 'GNU_PO',
                    'locale'                 => $locale,
                    'is_keeping_all_strings' => $this->isKeepingAllStrings,
                ]);
            }
        }

        return $this;
    }
}
