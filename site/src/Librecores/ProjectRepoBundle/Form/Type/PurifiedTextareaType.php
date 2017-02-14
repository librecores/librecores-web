<?php
namespace Librecores\ProjectRepoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Textarea form control type which purifies (sanitizes) its contents
 *
 * This type extends the textarea type and passes the data through HTML Purifier
 * to sanitize it, making it safe from XSS and other vulerabilities.
 */
class PurifiedTextareaType extends AbstractType
{
    private $purifierTransformer;

    public function __construct(DataTransformerInterface $purifierTransformer)
    {
        $this->purifierTransformer = $purifierTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->purifierTransformer);
    }

    public function getParent()
    {
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => false,
        ));
    }

    public function getName()
    {
        return 'purified_textarea';
    }
}

