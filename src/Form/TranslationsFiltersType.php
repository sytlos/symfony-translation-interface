<?php

namespace App\Form;

use App\Translation\TranslationManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class TranslationsFiltersType extends AbstractType
{
    /**
     * @var TranslationManager
     */
    private $translationManager;

    /**
     * @var array
     */
    private $supportedLocales;

    /**
     * @param TranslationManager $translationManager
     * @param array $supportedLocales
     */
    public function __construct(TranslationManager $translationManager, array $supportedLocales)
    {
        $this->translationManager = $translationManager;
        $this->supportedLocales = $supportedLocales;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $catalog = $this->translationManager->getHydratedCatalog($options['defaultLocale']);

        $builder
            ->add('domain', ChoiceType::class, [
                'label' => 'Translation domain',
                'choices' => $catalog->getDomains(),
                'constraints' => new Choice(['choices' => $catalog->getDomains()]),
                'choice_label' => function ($choice, $key, $value) {
                    return $value;
                }
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'Translation locale',
                'choices' => $this->supportedLocales,
                'constraints' => new Choice(['choices' => $this->supportedLocales]),
                'choice_label' => function ($choice, $key, $value) {
                    return $value;
                }
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'defaultLocale' => null,
            ])
        ;
    }

    /**
     * @return string|null
     */
    public function getBlockPrefix()
    {
        return null;
    }
}