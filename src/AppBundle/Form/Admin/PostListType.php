<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Defines the form used to create and manipulate blog posts.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PostListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('searchTitle', TextType::class,array(
                'label' => 'Search Title',
                'required' => false,
            ))
            ->add('status', ChoiceType::class,array(
                'choices'  => array(
                    '--Any Status--' => '-99',
                    'Trashed' => '-1',
                    'Unpublished' => '0',
                    'Published' => '1',
                    'Archived' => '2',
                    'Review' => '3',
                ),
                'label' => 'label.status',
                'choices_as_values' => true,
            ))
            ->add('orderBy', ChoiceType::class,array(
                'choices'  => array(
                    'Published At' => 'p.publishedAt',
                    'Updated At' => 'p.publishedAt',
                    'Title' => 'p.title'
                ),
                'label' => 'Order By',
                'choices_as_values' => true,
            ))
            ->add('orderDir', ChoiceType::class,array(
                'choices'  => array(
                    'Ascending' => 'ASC',
                    'Descending' => 'DESC',
                ),
                'label' => 'Order Direction',
                'choices_as_values' => true,
            ))
            ->add('numListItems', ChoiceType::class,array(
                'choices'  => array(
                    '10' => '10',
                    '25' => '25',
                    '50' => '50',
                    '100' => '100',
                ),
                'label' => '# List Items',
                'choices_as_values' => true,
            ))
            ->add('submit', SubmitType::class, array('label' => 'Submit'))
        ;
    }
}
