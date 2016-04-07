<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class CheckSwitchType extends AbstractType
{
    public function getParent()
    {
        return CheckboxType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'checkswitch';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $emptyData = function (FormInterface $form, $viewData) {
            return $viewData;
        };
        $resolver->setDefaults(array(
            'value' => '1',
            'empty_data' => $emptyData,
            'compound' => false,
            'required' => false,
            'on_text' => 'Yes',
            'off_text' => 'No',
            'on_color' => 'success',
            'off_color' => 'danger',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['on_text'] = $options['on_text'];
        $view->vars['off_text'] = $options['off_text'];
        $view->vars['on_color'] = $options['on_color'];
        $view->vars['off_color'] = $options['off_color'];
    }

}
