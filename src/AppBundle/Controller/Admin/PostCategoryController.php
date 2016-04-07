<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\PostCategory;
use AppBundle\Entity\PostCategoryList;
use AppBundle\Form\Admin\PostCategoryListType;

/**
 * Controller used to manage categories in the backend.
 *
 * @Route("/admin/category")
 * @Security("has_role('ROLE_ADMIN')")
 */
class PostCategoryController extends AdminController
{
    /**
     * Lists all categories.
     *
     * @Route("/{catroot}", name="admin_category_index", defaults={"page" = 1})
     * @Route("/{catroot}/page/{page}", name="admin_category_index_paginated", requirements={"page" : "\d+"})
     * @Method({"GET", "POST"})
     */
    public function indexAction($catroot, $page, Request $request)
    {
        // Filters
        if (!$filterlist = $this->get('session')->get('admin.catlist')) {
            $filterlist = new PostCategoryList();
        }
        $filterform = $this->createForm(PostCategoryListType::class, $filterlist);
        $filterform->handleRequest($request);
        $this->get('session')->set('admin.catist',$filterlist);

        // Query
        $entityManager = $this->getDoctrine()->getManager();
        $catrepo = $entityManager->getRepository('AppBundle:PostCategory');

        $root = $catrepo->findOneBy(array('slug'=>$catroot));

        $queryBuilder = $catrepo->getNodesHierarchyQueryBuilder($root);

        if ($filterlist->getSearchName()) {
            $queryBuilder->andWhere('node.name like :title')->setParameter('title', '%'.$filterlist->getSearchName().'%');
        }

        $query=$queryBuilder->getQuery();

        $paginator = $this->get('knp_paginator');
        $cats = $paginator->paginate($query, $page, $filterlist->getNumListItems());
        $cats->setUsedRoute('admin_category_index_paginated');

        return $this->render('admin/postcategory/index.html.twig', array('catroot'=> $catroot,'cats' => $cats,'filterform'=>$filterform->createView()));
    }

    /**
     * Creates a new Category entity.
     *
     * @Route("/{catroot}/new", name="admin_category_new")
     * @Method({"GET", "POST"})
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     */
    public function newAction($catroot,Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Categories can only be shown to admins.');
            return $this->redirectToRoute('admin_category_index', array('catroot'=> $catroot));
        }

        $cat = new PostCategory();

        // See http://symfony.com/doc/current/book/forms.html#submitting-forms-with-multiple-buttons
        $form = $this->createForm('AppBundle\Form\PostCategoryType', $cat, array('catroot'=>$catroot))
                     ->add('saveAndClose', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('attr'=>array('class'=>'btn btn-primary btn-block')))
                     ->add('saveAndCreateNew', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('attr'=>array('class'=>'btn btn-default btn-block')));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cat);
            $entityManager->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See http://symfony.com/doc/current/book/controller.html#flash-messages
            $this->addFlash('success', 'post.created_successfully');

            $catrepo = $entityManager->getRepository('AppBundle:PostCategory');
            $catrepo->reorder($cat,'name','ASC');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_category_new', array('catroot'=> $catroot));
            }

            return $this->redirectToRoute('admin_category_index', array('catroot'=> $catroot));
        }

        return $this->render('admin/postcategory/new.html.twig', array(
            'cat' => $cat,
            'catroot' => $catroot,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{catroot}/{id}", requirements={"id" = "\d+"}, name="admin_category_show")
     * @Method("GET")
     */
    public function showAction($catroot,PostCategory $cat)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Categories can only be shown to admins.');
            return $this->redirectToRoute('admin_category_index', array('catroot'=> $catroot));
        }

        return $this->render('admin/postcategory/show.html.twig', array(
            'cat'        => $cat,
            'catroot'        => $catroot,
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{catroot}/{id}/edit", requirements={"id" = "\d+"}, name="admin_category_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction($catroot, PostCategory $cat, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Categories can only be edited by administrators.');
            return $this->redirectToRoute('admin_category_index', array('catroot'=> $catroot));
        }
        $entityManager = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\PostCategoryType', $cat, array('catroot'=>$catroot))
                         ->add('saveAndClose', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('attr'=>array('class'=>'btn btn-primary btn-block')))
                         ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('label'=>'action.save','attr'=>array('class'=>'btn btn-default btn-block')));

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $cat->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $catrepo = $entityManager->getRepository('AppBundle:PostCategory');
            $catrepo->reorder($cat->getRoot(),'name','ASC');

            $this->addFlash('success', 'Category updated successfully.');

            if ($editForm->get('saveAndClose')->isClicked()) {
                return $this->redirectToRoute('admin_category_index', array('catroot'=> $catroot));
            }

            return $this->redirectToRoute('admin_category_edit', array('id' => $cat->getId(),'catroot'=> $catroot));
        }

        return $this->render('admin/postcategory/edit.html.twig', array(
            'cat'        => $cat,
            'catroot'        => $catroot,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{catroot}/{id}", name="admin_category_delete")
     * @Method("DELETE")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     */
    public function deleteAction($catroot,Request $request, PostCategory $cat)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Posts can only be deleted by administrators.');
            return $this->redirectToRoute('admin_category_index');
        }

        $form = $this->createDeleteForm($cat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($cat);
            $entityManager->flush();

            $this->addFlash('success', 'Category deleted successfully.');
        }

        return $this->redirectToRoute('admin_caterogy_index',array('catroot'=> $catroot));
    }
}
