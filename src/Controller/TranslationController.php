<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;

/**
 * Class TranslationController
 * @package App\Controller
 */
class TranslationController extends AbstractController
{
    /**
     * @Route("/")
     *
     * @param Request $request
     * @param ParameterBagInterface $params
     * @param TranslationReaderInterface $reader
     *
     * @return Response
     */
    public function translations(Request $request, ParameterBagInterface $params, TranslationReaderInterface $reader)
    {
        $translationsPath = $params->get('translator.default_path');
        $translationsDomains = $this->getTranslationDomains($translationsPath);
        $defaultDomain = 'messages';
        $selectedDomain = $request->query->get('domain', $defaultDomain);

        $defaultLocale = $request->getDefaultLocale();
        $availableLocales = $params->get('supported_locales');

        $selectedLocale = $request->query->get('locale', $availableLocales[0]);

        $currentCatalog = new MessageCatalogue($defaultLocale);
        $reader->read($translationsPath, $currentCatalog);

        $catalogs = [];
        foreach ($availableLocales as $locale) {
            $catalog = new MessageCatalogue($locale);
            $reader->read($translationsPath, $catalog);

            $catalogs[$locale] = $catalog->all($selectedDomain);
        }

        return $this->render('translation/translations.html.twig', [
            'messages' => $currentCatalog->all($selectedDomain),
            'domains' => $translationsDomains,
            'selectedDomain' => $selectedDomain,
            'defaultLocale' => $defaultLocale,
            'selectedLocale' => $selectedLocale,
            'locales' => $availableLocales,
            'catalogs' => $catalogs,
        ]);
    }

    /**
     * @Route("/submit")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function submitTranslations(Request $request)
    {
        $translations = $request->request->all();

        foreach ($translations as $translation) {
            list($key, $locale, $domain) = \explode('-', $translation);

        }

        return new JsonResponse('Science bitch');
    }

    /**
     * @param $translationsPath
     * @return array
     */
    protected function getTranslationDomains($translationsPath)
    {
        $finder = new Finder();
        $files = $finder->files()->in($translationsPath);

        $domains = [];

        if (!$files->hasResults()) {
            return $domains;
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $domains[] = \explode('.', $file->getFilename())[0];
        }

        return \array_unique($domains);
    }
}