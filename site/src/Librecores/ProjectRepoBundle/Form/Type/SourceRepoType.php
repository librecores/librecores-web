<?php
namespace Librecores\ProjectRepoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;

/**
 * Form to edit the details of a source code repository.
 *
 * @author Philipp Wagner <mail@philipp-wagner.com>
 *
 */
class SourceRepoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                'choices' => array(
                    'Git' => SourceRepo::REPO_TYPE_GIT,
                    'Subversion (SVN)' => SourceRepo::REPO_TYPE_SVN,
                ),
                'choices_as_values' => true,
                'label' => 'Repository Type',
                'expanded' => false,
                'multiple' => false))
            ->add('url', 'url', array('label' => 'URL', 'required' => true))
        ;
    }

    public function getName()
    {
        return 'source_repo';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Librecores\ProjectRepoBundle\Entity\SourceRepo',
        ));
    }
}
