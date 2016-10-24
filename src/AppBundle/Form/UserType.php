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
use AppBundle\Form\Type\CheckSwitchType;

/**
 * Defines the form used to create and manipulate users.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class UserType extends AbstractType
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
            ->add('username', null, array(
                'attr' => array('autofocus' => true),
                'label' => 'Username',
            ))
            ->add('first_name', null, array(
                'attr' => array('autofocus' => true),
                'label' => 'First Name',
            ))
            ->add('last_name', null, array(
                'attr' => array('autofocus' => true),
                'label' => 'Last Name',
            ))
            ->add('email', null, array('label' => 'Email'))
            ->add('plainPassword', 'repeated', array('type' => 'password','required'=>false,'first_options' => array('label' => 'Password'),'second_options' => array('label' => 'Confirm Password'),'invalid_message' => 'Passwords do not match',))
            ->add('roles', 'choice', array('choices'=>$this->refactorRoles($options['roles']), 'expanded'=>true, 'multiple'=>true, 'label' => 'Roles'))
            ->add('enabled', CheckSwitchType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
            'roles' => null
        ));
    }

    private function refactorRoles($originRoles)
    {
        $roles = array();
        $rolesAdded = array();

        // Add herited roles
        foreach ($originRoles as $roleParent => $rolesHerit) {
            $tmpRoles = array_values($rolesHerit);
            $rolesAdded = array_merge($rolesAdded, $tmpRoles);
            $roles[$roleParent] = array_combine($tmpRoles, $tmpRoles);
        }
        // Add missing superparent roles
        $rolesParent = array_keys($originRoles);
        foreach ($rolesParent as $roleParent) {
            if (!in_array($roleParent, $rolesAdded)) {
                $roles['-----'][$roleParent] = $roleParent;
            }
        }

        return $roles;
    }
}
