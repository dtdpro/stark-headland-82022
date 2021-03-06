<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;
use AppBundle\Form\Type\DateTimePickerType;


/**
 * Defines the form used to create and manipulate blog posts.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PostType extends AbstractType
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
                    $root = $er->findOneBy(array('slug'=>'blog'));
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
            ->add('content', null, array(
                'attr' => array('rows' => 20),
                'label' => 'label.content',
            ))
            ->add('user', null, array('label' => 'label.author'))
            ->add('publishedAt', DateTimePickerType::class, array(
                'label' => 'label.published_at',
            ))
            ->add('post_image_file', VichImageType::class, array('label'=>"label.postimage",'download_link'=>false,'required'=>false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Post',
        ));
    }
}
