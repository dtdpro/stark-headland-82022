<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;
use AppBundle\Form\Type\DateTimePickerType;
use AppBundle\Form\Type\CheckSwitchType;


/**
 * Defines the form used to create and manipulate web forms.
 */
class WebFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // For the full reference of options defined by each form field type
        // see http://symfony.com/doc/current/reference/forms/types.html

        // By default, form fields include the 'required' attribute, which enables
        // the client-side form validation. This means that you can't test the
        // server-side validation errors from the browser. To temporarily disable
        // this validation, set the 'required' attribute to 'false':
        //
        //     $builder->add('title', null, array('required' => false, ...));

        $builder
            ->add('title', null, array(
                'attr' => array('autofocus' => true),
                'label' => 'label.title',
            ))
            ->add('category', null, array(
                'label' => 'label.category',
                'query_builder' => function(NestedTreeRepository $er) {
                    $root = $er->findOneBy(array('slug'=>'form'));
                    return $er->getNodesHierarchyQueryBuilder($root);
                },
                'required' => true,
            ))
            ->add('status', ChoiceType::class,array(
                'choices'  => array(
                    'Trashed' => '-1',
                    'Unpublished' => '0',
                    'Published' => '1',
                    'Archived' => '2',
                    'Review' => '3',
                ),
                'label' => 'label.status',
                'choices_as_values' => true,
            ))
            ->add('summary', TextareaType::class, array('label' => 'label.summary'))
            ->add('content_before_top', null, array(
                'attr' => array('rows' => 20),
                'label' => 'Before Submission, Above',
                'required' => false
            ))
            ->add('content_before_bottom', null, array(
                'attr' => array('rows' => 20),
                'label' => 'Before Submission, Bottom',
                'required' => false
            ))
            ->add('content_results_top', null, array(
                'attr' => array('rows' => 20),
                'label' => 'After Submission, Above',
                'required' => false
            ))
            ->add('content_results_bottom', null, array(
                'attr' => array('rows' => 20),
                'label' => 'After Submission, Bottom',
                'required' => false
            ))
            ->add('user', null, array('label' => 'label.author'))
            ->add('publishedAt', DateTimePickerType::class, array(
                'label' => 'label.published_at',
            ))
            ->add('allow_unreg', CheckSwitchType::class, array(
                'label' => 'Allow Anonymous Submissions',
            ))
            ->add('allow_multiple', CheckSwitchType::class, array(
                'label' => 'Allow Multiple Submissions',
            ))
            ->add('form_open', DateTimePickerType::class, array(
                'label' => 'Open',
                'required' => false
            ))
            ->add('form_close', DateTimePickerType::class, array(
                'label' => 'Close',
                'required' => false
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\WebForm',
        ));
    }
}
