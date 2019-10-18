<?php

namespace App\Translation;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;
use Symfony\Component\Translation\Writer\TranslationWriterInterface;

class TranslationManager
{
    /**
     * @var TranslationReaderInterface
     */
    private $reader;

    /**
     * @var TranslationWriterInterface
     */
    private $writer;

    /**
     * @var string
     */
    private $translationPath;

    /**
     * TranslationManager constructor.
     *
     * @param TranslationReaderInterface $reader
     * @param TranslationWriterInterface $writer
     * @param string $translationPath
     */
    public function __construct(TranslationReaderInterface $reader, TranslationWriterInterface $writer, string $translationPath)
    {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->translationPath = $translationPath;
    }

    /**
     * @param string $domain
     * @param string $locale
     */
    protected function createFile(string $domain, string $locale)
    {
        $filesystem = new Filesystem();

        $filepath = \sprintf('%s/%s.%s.yml', $this->translationPath, $domain, $locale);
        if (!$filesystem->exists($filepath)) {
            $filesystem->touch($filepath);
        }
    }

    /**
     * @param string $locale
     *
     * @return MessageCatalogue
     */
    public function getHydratedCatalog(string $locale)
    {
        $catalog = new MessageCatalogue($locale);
        $this->reader->read($this->translationPath, $catalog);

        return $catalog;
    }

    /**
     * @param MessageCatalogue $catalog
     * @param string $key
     * @param string $translation
     * @param string $domain
     */
    public function write(MessageCatalogue $catalog, string $key, string $translation, string $domain)
    {
        $this->createFile($domain, $catalog->getLocale());

        $catalog->add([$key => $translation], $domain);

        $this->writer->write($catalog, 'yml', [
            'path' => $this->translationPath,
            'as_tree' => true,
        ]);
    }
}