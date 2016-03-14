<?php
// src/AppBundle/Form/RegistrationType.php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('first_name');
		$builder->add('last_name');
	}

	public function getParent()
	{
		return 'fos_user_profile';
	}

	public function getName()
	{
		return 'app_user_profile';
	}
}