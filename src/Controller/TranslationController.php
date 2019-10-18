<?php

namespace App\Controller;

use App\Form\TranslationsFiltersType;
use App\Translation\TranslationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TranslationController
 * @package App\Controller
 */
class TranslationController extends AbstractController
{
    /**
     * @var TranslationManager
     */
    private $translationManager;

    /**
     * TranslationController constructor.
     *
     * @param TranslationManager $translationManager
     */
    public function __construct(TranslationManager $translationManager)
    {
        $this->translationManager = $translationManager;
    }

    /**
     * @Route("/")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function list(Request $request)
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

        $defaultCatalog = $this->translationManager->getHydratedCatalog($defaultLocale);
        $selectedCatalog = $this->translationManager->getHydratedCatalog($selectedLocale);

        return $this->render('translation/translations.html.twig', [
            'form' => $form->createView(),
            'messages' => $defaultCatalog->all($selectedDomain),
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
     *
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        $translation = $request->request->get('translation');
        $domain = $request->request->get('domain');
        $locale = $request->request->get('locale');
        $key = $request->request->get('key');

        if (!$translation || !$domain || !$locale || !$key) {
            return JsonResponse::create(['success' => false]);
        }

        $catalog = $this->translationManager->getHydratedCatalog($locale);
        $this->translationManager->write($catalog, $key, $translation, $domain);

        return JsonResponse::create(['success' => true]);
    }
}