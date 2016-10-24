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

/**
 * Controller used to manage comments in the backend.
 *
 * @Route("/admin/webformquestion")
 * @Security("has_role('ROLE_ADMIN')")
 */
class WebFormQuestionController extends AdminController
{

    /**
     * Creates a new web form question entity.
     *
     * @Route("/newquestion/{id}", name="admin_webform_question_new")
     * @Method({"GET", "POST"})
     */
    public function newPostQuestionAction(WebForm $webform, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s) can only be added by admins.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $form = $this->createForm('AppBundle\Form\WebFormQuestionType');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData();
            $question->setForm($webform);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($question);
            $entityManager->flush();

            return new Response('true');
        }

        return $this->render('admin/webform/question_form_new.html.twig', array(
            'webform' => $webform,
            'form' => $form->createView(),
        ));
    }

    /**
     * Set comment status to trashed
     *
     * @Route("/{id}/load", requirements={"id" = "\d+"}, name="admin_webform_question_load")
     * @Method({"GET", "POST"})
     */
    public function loadWebFormQuestion(WebFormQuestion $question, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s) can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $entityManager = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\WebFormQuestionType', $question);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Question Saved');
        }

        return $this->render('admin/webform/question_form_load.html.twig', array(
            'question'        => $question,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
     * AJAX Load of post comments
     *
     * @Route("/{id}/webform_questions", requirements={"id" = "\d+"}, name="admin_questions_bywebform")
     * @Method({"GET", "POST"})
     */
    public function loadWebFormQuestions(WebForm $webform, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Posts can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }
        return $this->render('admin/webform/questions.html.twig', array(
            'webform' => $webform,
        ));

    }

    /**
     * Bulk Action Processor
     *
     * @Route("/webform_bulkact", name="admin_webform_questions_bulk", defaults={"page" = 1})
     * @Method({"POST"})
     */
    public function webformBulkAction(Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s) can only be changed in bulk by admins.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $qids = $request->request->get('qid');
        $task = $request->request->get('task');

        $qrepo = $this->getDoctrine()->getRepository('AppBundle:WebFormQuestion');

        if ($task == 'saveorder') {
            $pos = 0;
            foreach ($qids as $q) {
                $question = $qrepo->find($q);

                $question->setPosition($pos);

                $em = $this->getDoctrine()->getManager();

                $em->persist($question);
                $em->flush();

                $pos++;
            }
        }

        return new Response('true');
    }

    /**
     * Set comment status to unpublished
     *
     * @Route("/{id}/unpublish", requirements={"id" = "\d+"}, name="admin_webform_question_unpublish")
     * @Method({"GET", "POST"})
     */
    public function unPublishQuestion(WebFormQuestion $question, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s) can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $question->setStatus(0);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($question);
        $entityManager->flush();

        return new Response('true');
    }

    /**
     * Set comment status to published
     *
     * @Route("/{id}/publish", requirements={"id" = "\d+"}, name="admin_webform_question_publish")
     * @Method({"GET", "POST"})
     */
    public function publishQuestion(WebFormQuestion $question, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s)  can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $question->setStatus(1);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($question);
        $entityManager->flush();

        return new Response('true');
    }

    /**
     * Set comment status to trashed
     *
     * @Route("/{id}/trash", requirements={"id" = "\d+"}, name="admin_webform_question_trash")
     * @Method({"GET", "POST"})
     */
    public function trashQuestion(WebFormQuestion $question, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s)  can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $question->setStatus(-1);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($question);
        $entityManager->flush();

        return new Response('true');
    }

    /**
     * Move the question up in the order
     *
     * @Route("/{id}/moveup", requirements={"id" = "\d+"}, name="admin_webform_question_up")
     * @Method({"GET", "POST"})
     */
    public function moveUpQuestion(WebFormQuestion $question, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s)  can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $question->setPosition($question->getPosition()-1);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($question);
        $entityManager->flush();

        return new Response('true');
    }

    /**
     * Move the question down in the order
     *
     * @Route("/{id}/movedown", requirements={"id" = "\d+"}, name="admin_webform_question_down")
     * @Method({"GET", "POST"})
     */
    public function moveDownQuestion(WebFormQuestion $question, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Web Form Question(s)  can only be edited by their authors.');
            return $this->redirectToRoute('admin_webform_index');
        }

        $question->setPosition($question->getPosition()+1);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($question);
        $entityManager->flush();

        return new Response('true');
    }
}
