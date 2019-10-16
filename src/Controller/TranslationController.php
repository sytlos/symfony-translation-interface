<?php

namespace App\Controller;

use App\Form\TranslationsFiltersType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
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
        $defaultLocale = $request->getDefaultLocale();

        $form = $this->createForm(TranslationsFiltersType::class, [
            'domain' => 'messages',
            'locale' => $defaultLocale,
        ], [
            'defaultLocale' => $defaultLocale,
            'method' => Request::METHOD_GET,
        ]);

        $form->handleRequest($request);

        $selectedDomain = $form->get('domain')->getData();
        $selectedLocale = $form->get('locale')->getData();

        $translationsPath = $params->get('translator.default_path');

        $currentCatalog = new MessageCatalogue($defaultLocale);
        $reader->read($translationsPath, $currentCatalog);

        $selectedCatalog = new MessageCatalogue($selectedLocale);
        $reader->read($translationsPath, $selectedCatalog);

        return $this->render('translation/translations.html.twig', [
            'form' => $form->createView(),
            'messages' => $currentCatalog->all($selectedDomain),
            'selectedDomain' => $selectedDomain,
            'defaultLocale' => $defaultLocale,
            'selectedLocale' => $selectedLocale,
            'translatedMessages' => $selectedCatalog->all($selectedDomain),
        ]);
    }

    /**
     * @Route("/submit")
     *
     * @param Request $request
     * @param ParameterBagInterface $params
     *
     * @return JsonResponse
     */
    public function submitTranslations(Request $request, ParameterBagInterface $params)
    {
        $translationsPath = $params->get('translator.default_path');
        $translations = $request->request->all();

        $filesystem = new Filesystem();

        foreach ($translations as $infos => $translation) {
            list($key, $locale, $domain) = \explode('-', $infos);

            $filepath = \sprintf('%s/%s.%s.yml', $translationsPath, $domain, $locale);
            if (!$filesystem->exists($filepath)) {
                $filesystem->touch($filepath);
            }

            $catalog = new MessageCatalogue($locale);
            $catalog->add($key, $domain);
        }

        return new JsonResponse(['success' => true]);
    }
}