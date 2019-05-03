<?php

namespace App\Form\Type;

use App\Form\Model\SearchQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type: the search query box
 */
class SearchQueryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', SearchType::class, array('required' => false))
            ->add('type', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => SearchQuery::class,
                'csrf_protection' => false,
                'method' => 'GET',
            )
        );
    }

    /**
     * Get the form name
     *
     * This avoids enclosing the form field names in search_query[NAME],
     * in order to have just "q" as query parameter in this form.
     *
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::getName()
     */
    public function getBlockPrefix()
    {
        return null;
    }
}
