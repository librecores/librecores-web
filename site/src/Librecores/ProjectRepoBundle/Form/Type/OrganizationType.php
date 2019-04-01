<?php

namespace Librecores\ProjectRepoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => 'Name (Short)', 'required' => true))
            ->add('displayName', TextType::class, array('label' => 'Display Name (Long)', 'required' => true))
            ->add('description', TextType::class, array('label' => 'Description', 'required' => true))
            ->add('save', SubmitType::class, array('label' => 'Update Organization'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Librecores\ProjectRepoBundle\Entity\Organization',
                'label' => 'Organization Details',
            ]
        );
    }
}
