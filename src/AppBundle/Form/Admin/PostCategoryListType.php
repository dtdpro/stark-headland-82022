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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Defines the form used to create and manipulate blog posts.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PostCategoryListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('searchName', TextType::class,array(
                'label' => 'Search Name',
                'required' => false,
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
