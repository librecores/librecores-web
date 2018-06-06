<?php
namespace Librecores\ProjectRepoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Form to edit the user profile settings
 */
class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => 'Full name', 'required' => false))
            ->add('save', SubmitType::class, array('label' => 'Update profile settings'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Librecores\ProjectRepoBundle\Entity\User',
            'label'      => 'User Profile Settings',
        ]);
    }
}
