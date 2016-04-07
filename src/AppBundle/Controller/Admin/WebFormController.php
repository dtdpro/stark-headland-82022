<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AdminController;
use AppBundle\Entity\PostCategory;
use AppBundle\Form\PostBlogParamsType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\WebForm;

/**
 * Controller used to manage froms in the backend.
 *
 * @Route("/admin/webform")
 * @Security("has_role('ROLE_ADMIN')")
*/
class WebFormController extends AdminController
{
    /**
     * Lists all form entities.
     *
     * @Route("/", name="admin_webform_index", defaults={"page" = 1})
     * @Route("/page/{page}", name="admin_webform_index_paginated", requirements={"page" : "\d+"})
     * @Method({"GET", "POST"})
     */
    public function indexAction($page, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $catrepo = $entityManager->getRepository('AppBundle:PostCategory');

        // Query
        $repository = $this->getDoctrine()->getRepository('AppBundle:WebForm');
        $queryBuilder = $repository->createQueryBuilder('f');
        $queryBuilder->orderBy('f.title', 'asc');

        $query = $queryBuilder->getQuery();
        $paginator = $this->get('knp_paginator');
        $webforms = $paginator->paginate($query, $page, 25);
        $webforms->setUsedRoute('admin_webform_index_paginated');

        $root = $catrepo->findOneBy(array('slug'=>'form'));
        $catlist = $catrepo->getNodesHierarchyQuery($root)->getResult();

        return $this->render('admin/webform/index.html.twig', array('webforms' => $webforms,'catlist'=>$catlist));
    }

    /**
     * Bulk Action Processor
     *
     * @Route("/bulkact", name="admin_webform_bulk", defaults={"page" = 1})
     * @Method({"POST"})
     */
    public function bulkAction(Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form(s) can only be changed in bulk by admins.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $cids = $request->request->get('cid');
        $setStatus = $request->request->get('setStatus');
        $setCategory = $request->request->get('setCategory');
        $wfrepo = $this->getDoctrine()->getRepository('AppBundle:WebForm');
        $catrepo = $this->getDoctrine()->getRepository('AppBundle:PostCategory');

        foreach ($cids as $wf) {
            $webform = $wfrepo->find($wf);

            if ($setStatus != '-99') {
                $webform->setStatus( $setStatus );
            }

            if ($setCategory != '-99') {
                $cat = $catrepo->find($setCategory);
                $webform->setCategory($cat);
            }

            $em = $this->getDoctrine()->getManager();

            $em->persist($webform);
            $em->flush();
        }
        $this->addFlash('notice', 'Web Form(s) status changed.');
        return $this->redirectToRoute('admin_webform_index');
    }

    /**
     * Creates a new Web Form entity.
     *
     * @Route("/new", name="admin_webform_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_EDITOR')) {
            $this->addFlash('error', 'Web Form(s) can only be changed in bulk by admins.');
            return $this->redirectToRoute('admin_post_index');
        }

        $webform = new WebForm();
        $webform->setUser($this->getUser());

        // See http://symfony.com/doc/current/book/forms.html#submitting-forms-with-multiple-buttons
        $form = $this->createForm('AppBundle\Form\WebFormType', $webform)
                     ->add('saveAndClose', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('attr'=>array('class'=>'btn btn-primary btn-block')))
                     ->add('saveAndCreateNew', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('attr'=>array('class'=>'btn btn-default btn-block')));

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {
            $webform->setSlug($this->get('slugger')->slugify($webform->getTitle()));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($webform);
            $entityManager->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See http://symfony.com/doc/current/book/controller.html#flash-messages
            $this->addFlash('success', 'Web Form created successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_webform_new');
            }

            return $this->redirectToRoute('admin_webform_index');
        }

        return $this->render('admin/webform/new.html.twig', array(
            'webform' => $webform,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing web from entity.
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="admin_webform_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(WebForm $webform, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Forms can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }
        $entityManager = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\WebFormType', $webform)
                         ->add('saveAndClose', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('attr'=>array('class'=>'btn btn-primary btn-block')))
                         ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType',array('label'=>'action.save','attr'=>array('class'=>'btn btn-default btn-block')));

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $webform->setSlug($this->get('slugger')->slugify($webform->getTitle()));
            $webform->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'post.updated_successfully');

            if ($editForm->get('saveAndClose')->isClicked()) {
                return $this->redirectToRoute('admin_webform_index');
            }

            return $this->redirectToRoute('admin_webform_edit', array('id' => $webform->getId()));
        }

        return $this->render('admin/webform/edit.html.twig', array(
            'webform'        => $webform,
            'edit_form'   => $editForm->createView(),
        ));
    }


}
