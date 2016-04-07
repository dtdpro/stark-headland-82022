<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AdminController;
use AppBundle\Form\PostBlogParamsType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\WebForm;
use AppBundle\Entity\WebFormQuestion;
use AppBundle\Entity\WebFormQuestionOption;

/**
 * Controller used to manage comments in the backend.
 *
 * @Route("/admin/webformquestionoption")
 * @Security("has_role('ROLE_ADMIN')")
 */
class WebFormQuestionOptionController extends AdminController
{

    /**
     * Creates a new web form question entity.
     *
     * @Route("/{id}/new", name="admin_webform_question_option_new")
     * @Method({"GET", "POST"})
     */
    public function newPostQuestionOptionAction(WebFormQuestion $question, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question Option(s) can only be added by admins.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $form = $this->createForm('AppBundle\Form\WebFormQuestionOptionType');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $option = $form->getData();
            $option->setQuestion($question);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($option);
            $entityManager->flush();

            return new Response('true');
        }

        return $this->render('admin/webform/option_form_new.html.twig', array(
            'question' => $question,
            'form' => $form->createView(),
        ));
    }

    /**
     * Set comment status to trashed
     *
     * @Route("/{id}/load", requirements={"id" = "\d+"}, name="admin_webform_question_option_load")
     * @Method({"GET", "POST"})
     */
    public function loadQuestionOption(WebFormQuestionOption $option, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question Option(s) can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $entityManager = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\WebFormQuestionOptionType', $option);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Question Option Saved');
        }

        return $this->render('admin/webform/option_form_load.html.twig', array(
            'option'        => $option,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
     * AJAX Load of post comments
     *
     * @Route("/{id}/webform_question_options", requirements={"id" = "\d+"}, name="admin_question_options_byquestion")
     * @Method({"GET", "POST"})
     */
    public function loadWebFormQuestionOptions(WebFormQuestion $question, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web From Question Options can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }
        return $this->render('admin/webform/options.html.twig', array(
            'question' => $question,
        ));

    }

    /**
     * Bulk Action Processor
     *
     * @Route("/webform_bulkact", name="admin_webform_question_options_bulk", defaults={"page" = 1})
     * @Method({"POST"})
     */
    public function webformBulkAction(Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s) can only be changed in bulk by admins.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $oids = $request->request->get('oid');
        $task = $request->request->get('task');

        $orepo = $this->getDoctrine()->getRepository('AppBundle:WebFormQuestionOption');

        if ($task == 'saveorder') {
            $pos = 0;
            foreach ($oids as $o) {
                $option = $orepo->find($o);

                $option->setPosition($pos);

                $em = $this->getDoctrine()->getManager();

                $em->persist($option);
                $em->flush();

                $pos++;
            }
        }

        return new Response('true');
    }

    /**
     * Set comment status to unpublished
     *
     * @Route("/{id}/unpublish", requirements={"id" = "\d+"}, name="admin_webform_question_option_unpublish")
     * @Method({"GET", "POST"})
     */
    public function unPublishQuestionOption(WebFormQuestionOption $option, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s) can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $option->setStatus(0);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($option);
        $entityManager->flush();

        return new Response('true');
    }

    /**
     * Set comment status to published
     *
     * @Route("/{id}/publish", requirements={"id" = "\d+"}, name="admin_webform_question_option_publish")
     * @Method({"GET", "POST"})
     */
    public function publishQuestionOption(WebFormQuestionOption $option, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s)  can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $option->setStatus(1);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($option);
        $entityManager->flush();

        return new Response('true');
    }

    /**
     * Set comment status to trashed
     *
     * @Route("/{id}/trash", requirements={"id" = "\d+"}, name="admin_webform_question_option_trash")
     * @Method({"GET", "POST"})
     */
    public function trashQuestionOption(WebFormQuestionOption $option, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s)  can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $option->setStatus(-1);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($option);
        $entityManager->flush();

        return new Response('true');
    }

    /**
     * Move the question up in the order
     *
     * @Route("/{id}/moveup", requirements={"id" = "\d+"}, name="admin_webform_question_option_up")
     * @Method({"GET", "POST"})
     */
    public function moveUpQuestionOption(WebFormQuestionOption $option, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question Option(s)  can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $option->setPosition($option->getPosition()-1);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($option);
        $entityManager->flush();

        return new Response('true');
    }

    /**
     * Move the question down in the order
     *
     * @Route("/{id}/movedown", requirements={"id" = "\d+"}, name="admin_webform_question_option_down")
     * @Method({"GET", "POST"})
     */
    public function moveDownQuestionOption(WebFormQuestionOption $option, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question Option(s)  can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $option->setPosition($option->getPosition()+1);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($option);
        $entityManager->flush();

        return new Response('true');
    }
}
