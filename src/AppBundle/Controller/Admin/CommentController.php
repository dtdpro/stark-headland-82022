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
use AppBundle\Entity\Post;
use AppBundle\Entity\Comment;
use AppBundle\Form\Admin\PostBulkType;

/**
 * Controller used to manage comments in the backend.
 *
 * @Route("/admin/comments")
 * @Security("has_role('ROLE_ADMIN')")
 */
class CommentController extends AdminController
{

    /**
     * Creates a new Post entity.
     *
     * @Route("/newpost/{id}", name="admin_comment_post_new")
     * @Method({"GET", "POST"})
     */
    public function newPostCommentAction(Post $post, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Post(s) can only be changed in bulk by admins.');
            return $this->redirectToRoute('admin_post_index');
        }

        $form = $this->createForm('AppBundle\Form\CommentType');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setUser($this->getUser());
            $comment->setPost($post);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return new Response('true');
        }

        return $this->render('admin/blog/comment_form_new.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
        ));
    }

    /**
     * Set comment status to trashed
     *
     * @Route("/{id}/load", requirements={"id" = "\d+"}, name="admin_comment_load")
     * @Method({"GET", "POST"})
     */
    public function loadComment(Comment $comment, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Posts can only be edited by their authors.');
            return $this->redirectToRoute('admin_post_index');
        }

        $entityManager = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\CommentType', $comment);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Comment Saved');
        }

        return $this->render('admin/blog/comment_form_load.html.twig', array(
            'comment'        => $comment,
            'edit_form'   => $editForm->createView(),
        ));
    }
    /**
     * AJAX Load of post comments
     *
     * @Route("/{id}/post_comments", requirements={"id" = "\d+"}, name="admin_comments_bypost")
     * @Method({"GET", "POST"})
     */
    public function loadPostComments(Post $post, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Posts can only be edited by their authors.');
            return $this->redirectToRoute('admin_post_index');
        }
        return $this->render('admin/blog/comments.html.twig', array(
            'post' => $post,
        ));

    }

    /**
     * Set comment status to unpublished
     *
     * @Route("/{id}/unpublish", requirements={"id" = "\d+"}, name="admin_comment_unpublish")
     * @Method({"GET", "POST"})
     */
    public function unPublishComment(Comment $comment, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Posts can only be edited by their authors.');
            return $this->redirectToRoute('admin_post_index');
        }

        $comment->setStatus(0);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($comment);
        $entityManager->flush();

        return new Response('true');
    }

    /**
     * Set comment status to published
     *
     * @Route("/{id}/publish", requirements={"id" = "\d+"}, name="admin_comment_publish")
     * @Method({"GET", "POST"})
     */
    public function publishComment(Comment $comment, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Posts can only be edited by their authors.');
            return $this->redirectToRoute('admin_post_index');
        }

        $comment->setStatus(1);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($comment);
        $entityManager->flush();

        return new Response('true');
    }

    /**
     * Set comment status to trashed
     *
     * @Route("/{id}/trash", requirements={"id" = "\d+"}, name="admin_comment_trash")
     * @Method({"GET", "POST"})
     */
    public function trashComment(Comment $comment, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Posts can only be edited by their authors.');
            return $this->redirectToRoute('admin_post_index');
        }

        $comment->setStatus(-1);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($comment);
        $entityManager->flush();

        return new Response('true');
    }
}
