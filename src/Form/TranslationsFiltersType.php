<?php

namespace App\Form;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;

class TranslationsFiltersType extends AbstractType
{
    /**
     * @var TranslationReaderInterface
     */
    private $reader;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * TranslationsFiltersType constructor.
     * @param TranslationReaderInterface $reader
     * @param ParameterBagInterface $params
     */
    public function __construct(TranslationReaderInterface $reader, ParameterBagInterface $params)
    {
        $this->reader = $reader;
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $catalog = new MessageCatalogue($options['defaultLocale']);
        $this->reader->read($this->params->get('translator.default_path'), $catalog);

        $builder
            ->add('domain', ChoiceType::class, [
                'label' => 'Translation domain',
                'choices' => $catalog->getDomains(),
                'choice_label' => function ($choice, $key, $value) {
                    return $value;
                }
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'Translation locale',
                'choices' => $this->params->get('supported_locales'),
                'choice_label' => function ($choice, $key, $value) {
                    return $value;
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'defaultLocale' => null,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return null;
    }
}