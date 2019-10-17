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
use Symfony\Component\Translation\Writer\TranslationWriterInterface;

/**
 * Class TranslationController
 * @package App\Controller
 */
class TranslationController extends AbstractController
{
    /**
     * @var TranslationReaderInterface
     */
    private $reader;

    public function __construct(TranslationReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @Route("/")
     *
     * @param Request $request
     * @param ParameterBagInterface $params
     *
     * @return Response
     */
    public function translations(Request $request, ParameterBagInterface $params)
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
        $this->reader->read($translationsPath, $currentCatalog);

        $selectedCatalog = new MessageCatalogue($selectedLocale);
        $this->reader->read($translationsPath, $selectedCatalog);

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
     * @param TranslationWriterInterface $writer
     *
     * @return JsonResponse
     */
    public function submitTranslations(Request $request, ParameterBagInterface $params, TranslationWriterInterface $writer)
    {
        $translationsPath = $params->get('translator.default_path');

        $translation = $request->request->get('translation');
        $domain = $request->request->get('domain');
        $locale = $request->request->get('locale');
        $key = $request->request->get('key');

        $data = [];

        if (!$translation || !$domain || !$locale || !$key) {
            $data['success'] = false;
        } else {
            $filesystem = new Filesystem();

            $filepath = \sprintf('%s/%s.%s.yml', $translationsPath, $domain, $locale);
            if (!$filesystem->exists($filepath)) {
                $filesystem->touch($filepath);
            }

            $catalog = new MessageCatalogue($locale);
            $this->reader->read($translationsPath, $catalog);
            $catalog->add([$key => $translation], $domain);

            $writer->write($catalog, 'yml', [
                'path' => $translationsPath,
                'as_tree' => true,
            ]);

            $data['success'] = true;
        }

        return new JsonResponse($data);
    }
}