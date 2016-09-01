<?php

namespace Evozon\TranslatrBundle\OneSky;

/**
 * Class Mapping
 *
 * @package   Evozon\TranslatrBundle\OneSky
 * @author    Balazs Csaba <csaba.balazs@evozon.com>
 * @copyright 2016 Evozon (https://www.evozon.com)
 */
class Mapping
{
    /** @var array */
    private $sources = [];

    /** @var array */
    private $locales = [];

    /** @var string */
    private $output;

    /** @var mixed */
    private $postfix;

    /**
     * @param array  $sources
     * @param array  $locales
     * @param string $output
     * @param mixed  $postfix
     */
    public function __construct(array $sources, array $locales, $output, $postfix = null)
    {
        $this->sources = $sources;
        $this->locales = $locales;
        $this->output = $output;
        $this->postfix = (string) $postfix;
    }

    /**
     * @param string $source
     *
     * @return bool
     */
    public function useSource($source)
    {
        return empty($this->sources) || in_array($source, $this->sources);
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function useLocale($locale)
    {
        return empty($this->locales) || in_array($locale, $this->locales);
    }

    /**
     * @param string $source
     * @param string $locale
     *
     * @return string
     */
    public function getOutputFilename($source, $locale)
    {
        return $this->getOriginalOutputFilename($source, $locale).$this->postfix;
    }

    /**
     * @param string $source
     * @param string $locale
     *
     * @return string
     */
    public function getOriginalOutputFilename($source, $locale)
    {
        return strtr($this->output, [
            '[dirname]'   => pathinfo($source, PATHINFO_DIRNAME),
            '[filename]'  => pathinfo($source, PATHINFO_FILENAME),
            '[locale]'    => $locale,
            '[extension]' => pathinfo($source, PATHINFO_EXTENSION),
            '[ext]'       => pathinfo($source, PATHINFO_EXTENSION),
        ]);
    }

    /**
     * @param string $source
     * @param string $locale
     *
     * @return string
     */
    public function getOutputFileDomain($source, $locale)
    {
        //replace last occurrence of locale in source
        $source = preg_replace(
            '/('.implode('|', $this->locales).')(?!.*('.implode('|', $this->locales).'))/i',
            $locale,
            $source
        );

        //get position of last occurrence of locale in source
        $pos = strrpos($source, $locale);

        //if locale is on the first position, default to messages domain
        if ($pos === 0) {
            return 'messages';
        }

        //return domain name w/o locale. eg: source routes.en.po return routes
        return substr($source, 0, (($pos - 1) < 0) ? 0 : ($pos - 1));
    }
}
