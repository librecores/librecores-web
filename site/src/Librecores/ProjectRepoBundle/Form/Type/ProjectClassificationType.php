<?php
namespace Librecores\ProjectRepoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Form to add the classification categories details of a project
 *
 * Categories contains the classifier string of the project
 * that can be uset to represent classification system
 *
 * @author Sandip Kumar Bhuyan <sandipbhuyan@gmail.com>
 */

class ProjectClassificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('classification',TextType::class, array('label' => 'Add project classifications', 'required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Librecores\ProjectRepoBundle\Entity\ProjectClassification',
        ));
    }
}
