<?php


namespace Librecores\ProjectRepoBundle\Form\Type;

use Librecores\ProjectRepoBundle\Form\DataTransformer\UserToEmailTransformer;
use Librecores\ProjectRepoBundle\Form\Model\ResendConfirmationEmailRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResendConfirmationEmailRequestType extends AbstractType
{

    private $transformer;

    public function __construct(UserToEmailTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'user',
            EmailType::class,
            [
                'label' => 'Email',
                'invalid_message' => 'User not found',
                'constraints' => [ new NotBlank() ],
            ]
        );

        $builder->get('user')
            ->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ResendConfirmationEmailRequest::class,
            ]
        );
    }
}
