<?php

namespace Evozon\TranslatrBundle\Translation\Catalogue;

use Symfony\Component\Translation\Catalogue\AbstractOperation;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * Class MergeOperation
 *
 * @package   Evozon\TranslatrBundle\Translation\Catalogue
 * @author    Balazs Csaba <csaba.balazs@evozon.com>
 * @copyright 2016 Evozon (https://www.evozon.com)
 */
class MergeOperation extends AbstractOperation
{
    /**
     * @var null|string
     */
    private $locale;

    /**
     * @var array
     */
    private $notices = [];

    /**
     * @param MessageCatalogueInterface $source
     * @param MessageCatalogueInterface $target
     * @param mixed                     $locale
     *
     * @throws \LogicException
     */
    public function __construct(MessageCatalogueInterface $source, MessageCatalogueInterface $target, $locale = null)
    {
        parent::__construct($source, $target);

        $this->locale = $locale;
    }

    /**
     * Process domain catalogues
     *  source: OneSky catalogue
     *  target: local catalogue
     *
     * @param string $domain
     */
    protected function processDomain($domain)
    {
        $this->messages[$domain] = [
            'all' => [],
            'new' => [],
            'obsolete' => [],
        ];

        $this->write(sprintf('Processing %s.%s catalogue', $domain, $this->locale));
        $this->writeSeparator();

        // add all messages from the target catalogue first
        foreach ($this->target->all($domain) as $id => $message) {
            $this->messages[$domain]['all'][$id] = $message;
            $this->result->add([$id => $message], $domain);
            if (null !== $keyMetadata = $this->target->getMetadata($id, $domain)) {
                $this->result->setMetadata($id, $keyMetadata, $domain);
            }
        }

        // update messages in the target catalogue which are changed
        foreach ($this->source->all($domain) as $id => $message) {
            if ($this->target->has($id, $domain) &&
                $this->target->get($id, $domain) !== $this->source->get($id, $domain)
            ) {
                if ($id === $this->source->get($id, $domain)) {
                    $this->addNotice(
                        sprintf('[OneSky %s.%s] Message id equals string: "%s". Skipping.', $domain, $this->locale, $id)
                    );
                    continue;
                }
                $this->messages[$domain]['all'][$id] = $message;
                $this->messages[$domain]['new'][$id] = $message;
                $this->result->add([$id => $message], $domain);
                if (null !== $keyMetadata = $this->target->getMetadata($id, $domain)) {
                    $this->result->setMetadata($id, $keyMetadata, $domain);
                }
            }
        }

        // double check translations for untranslated strings
        foreach ($this->target->all($domain) as $id => $message) {
            if ($id === $this->target->get($id, $domain)) {
                $this->addNotice(sprintf('[%s.%s] WARNING! Untranslated string "%s".', $domain, $this->locale, $id));
                continue;
            }
        }

        if ($this->hasNotices()) {
            $this->writeNotices();
        } else {
            $this->write('All good.');
        }

        $this->writeSeparator();
    }

    /**
     * @param string $notice
     *
     * @return $this
     */
    private function addNotice($notice)
    {
        $this->notices[] = $notice;

        return $this;
    }

    /**
     * @return bool
     */
    private function hasNotices()
    {
        return (count($this->notices) > 0);
    }

    /**
     * @return $this
     */
    private function writeNotices()
    {
        foreach ($this->notices as $notice) {
            $this->write($notice);
        }
        $this->notices = [];

        return $this;
    }

    /**
     * @return $this
     */
    private function writeSeparator()
    {
        $this->write('--------------------------------------------');

        return $this;
    }

    /**
     * @param string $string
     */
    private function write($string)
    {
        echo $string.PHP_EOL;
    }
}
