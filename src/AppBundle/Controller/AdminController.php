<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminController extends Controller
{
	/**
	 * @Route("/admin", name="admin_index")
	 */
	public function homeAction()
	{
		return $this->render('admin/index.html.twig');
	}
	public function hasRole($role) {
		$auth_checker = $this->get( 'security.authorization_checker' );
		return $auth_checker->isGranted( $role );
	}


}