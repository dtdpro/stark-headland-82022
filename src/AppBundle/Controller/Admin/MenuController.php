<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\MenuItem;

/**
 * Controller used to manage menus in the backend.
 *
 * @Route("/admin/menu")
 * @Security("has_role('ROLE_ADMIN')")
 */
class MenuController extends AdminController
{
    /**
     * Lists all root MenuItem entities.
     *
     * @Route("/", name="admin_menu_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Menus can only be viewed by administrators.');
            return $this->redirectToRoute('admin_index');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $menus = $entityManager->getRepository('AppBundle:MenuItem')->findBy(array('parent' => null));

        return $this->render('admin/menu/index.html.twig', array('menus' => $menus));
    }

    /**
     * Creates a new Menu.
     *
     * @Route("/new", name="admin_menu_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_SUPER_ADMIN')) {
            $this->addFlash('error', 'Menu(s) can only be changed by admins.');
            return $this->redirectToRoute('admin_menu_index');
        }

        $menu = new MenuItem();

        // See http://symfony.com/doc/current/book/forms.html#submitting-forms-with-multiple-buttons
        $form = $this->createForm('AppBundle\Form\MenuType', $menu)
                     ->add('saveAndClose', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('attr'=>array('class'=>'btn btn-primary btn-block')))
                     ->add('saveAndCreateNew', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('attr'=>array('class'=>'btn btn-default btn-block')));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($menu);
            $entityManager->flush();

            $this->addFlash('success', 'Menu Created Successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_menu_new');
            }

            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/new.html.twig', array(
            'menu' => $menu,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing menuy.
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="admin_menu_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(MenuItem $menu, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_SUPER_ADMIN')) {
            $this->addFlash('error', 'Menus can only be edited by admins.');
            return $this->redirectToRoute('admin_menu_index');
        }
        $entityManager = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\MenuType', $menu)
                         ->add('saveAndClose', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('attr'=>array('class'=>'btn btn-primary btn-block')))
                         ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('label'=>'action.save','attr'=>array('class'=>'btn btn-default btn-block')));

       $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $post->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Menu Updated Successfully');

            if ($editForm->get('saveAndClose')->isClicked()) {
                return $this->redirectToRoute('admin_menu_index');
            }

            return $this->redirectToRoute('admin_menu_edit', array('id' => $menu->getId()));
        }

        return $this->render('admin/menu/edit.html.twig', array(
            'menu'        => $menu,
            'edit_form'   => $editForm->createView(),
        ));
    }

}
