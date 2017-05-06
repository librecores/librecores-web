<?php
namespace Librecores\ProjectRepoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayName', TextType::class, array('label' => 'Display name', 'required' => false))
            ->add('tagline', TextType::class, array('label' => 'Tagline', 'required' => false))
            ->add('descriptionTextAutoUpdate', ChoiceType::class, array(
                'choices' => array(
                    'Extract the project description out of the README file in the source code.' => true,
                    'Enter the project description here.' => false
                ),
                'choices_as_values' => true,
                'label' => 'Project Description',
                'expanded' => true,
                'multiple' => false))
            ->add('descriptionText', PurifiedTextareaType::class, array('label' => false, 'required' => false))
            ->add('projectUrl', UrlType::class, array('label' => 'Project URL', 'required' => false))
            ->add('issueTracker', UrlType::class, array('label' => 'Issue/Bug Tracker URL', 'required' => false))
            ->add('sourceRepo', SourceRepoType::class)
            ->add('licenseName', TextType::class, array('label' => 'License Name (such as GPL or MIT)', 'required' => false))
            ->add('licenseTextAutoUpdate', ChoiceType::class, array(
                'choices' => array(
                    'Extract the full license text out of the LICENSE file in the source code.' => true,
                    'Enter the license text here.' => false,
                ),
                'choices_as_values' => true,
                'label' => 'Full License Text',
                'expanded' => true,
                'multiple' => false))
            ->add('licenseText', PurifiedTextareaType::class, array('label' => false, 'required' => false))
            ->add('save', SubmitType::class, array('label' => 'Update Project', 'attr' => ['class' => 'btn-primary']))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Librecores\ProjectRepoBundle\Entity\Project',
        ));
    }
}
