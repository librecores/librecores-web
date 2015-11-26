<?php
namespace Librecores\ProjectRepoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // XXX: Set expanded=true below as soon as symfony bug #14712 is fixed
        //      Also restore JS code in project_settings.html.twig
        $builder
            ->add('descriptionTextAutoUpdate', 'choice', array(
                'choices' => array(
                    'Extract the project description out of the README file in the source code.' => true,
                    'Enter the project description here.' => false
                ),
                'choices_as_values' => true,
                'label' => 'Project Description',
                'expanded' => false, /* XXX see above */
                'multiple' => false))
            ->add('descriptionText', 'textarea', array('label' => false, 'required' => false))
            ->add('projectUrl', 'url', array('label' => 'Project URL', 'required' => false))
            ->add('issueTracker', 'url', array('label' => 'Issue/Bug Tracker URL', 'required' => false))
            ->add('sourceRepo', new SourceRepoType())
            ->add('licenseName', 'text', array('label' => 'License Name (such as GPL or MIT)', 'required' => false))
            ->add('licenseTextAutoUpdate', 'choice', array(
                'choices' => array(
                    'Extract the full license text out of the LICENSE file in the source code.' => true,
                    'Enter the license text here.' => false,
                ),
                'choices_as_values' => true,
                'label' => 'Full License Text',
                'expanded' => false, /* XXX see above */
                'multiple' => false))
            ->add('licenseText', 'textarea', array('label' => false, 'required' => false))
            ->add('save', 'submit', array('label' => 'Update Project'))
        ;
    }

    public function getName()
    {
        return 'project';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Librecores\ProjectRepoBundle\Entity\Project',
        ));
    }
}
