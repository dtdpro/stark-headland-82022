<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Form\Type\CheckSwitchType;

/**
 * Defines the form used to create and manipulate webform questions.
 */
class WebFormQuestionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content')
            ->add('type', ChoiceType::class,array(
                'choices'  => array(
                    'Single, Multiple Chioce' => 'radio',
                    'Multiple, Multiple Chioce' => 'multicbox',
                    'Drop Down' => 'dropdown',
                    'Checkbox' => 'cbox',
                    'Text Field' => 'textfield',
                    'Text/Comment Box' => 'textbox',
                    'Email Address' => 'email',
                ),
                'label' => 'Type',
                'choices_as_values' => true,
            ))
            ->add('is_required', CheckSwitchType::class, array(
                'label' => 'Required',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\WebFormQuestion',
        ));
    }
}
