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
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;


/**
 * Defines the form used to create and manipulate blog posts.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PostCategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                'attr' => array('autofocus' => true),
                'label' => 'label.title',
            ))
            ->add('parent', null, array(
                'label' => 'Parent Category',
                'query_builder' => function(NestedTreeRepository $er) {
                    return $er->getNodesHierarchyQueryBuilder();
                },
                'required' => false,
            ))
            ->add('createdAt', 'AppBundle\Form\Type\DateTimePickerType', array(
                'label' => 'label.published_at',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PostCategory',
        ));
    }
}
