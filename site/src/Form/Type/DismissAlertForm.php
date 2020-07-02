<?php


namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class DismissAlertForm extends AbstractType
{
    const ALERT_LICENSE = 'license';
    const ALERT_ISSUE_TRACKER = 'issue_tracker';
    const ALERT_HOME_PAGE = 'homepage';
    const ALERT_README = 'readme';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('alert', HiddenType::class);
    }
}
