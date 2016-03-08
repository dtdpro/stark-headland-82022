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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Post;
use AppBundle\Entity\PostList;
use AppBundle\Form\Admin\PostListType;
use AppBundle\Form\Admin\PostBulkType;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Route("/admin/post")
 * @Security("has_role('ROLE_ADMIN')")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class BlogController extends Controller
{
    /**
     * Lists all Post entities.
     *
     * This controller responds to two different routes with the same URL:
     *   * 'admin_post_index' is the route with a name that follows the same
     *     structure as the rest of the controllers of this class.
     *   * 'admin_index' is a nice shortcut to the backend homepage. This allows
     *     to create simpler links in the templates. Moreover, in the future we
     *     could move this annotation to any other controller while maintaining
     *     the route name and therefore, without breaking any existing link.
     *
     * @Route("/", name="admin_index", defaults={"page" = 1})
     * @Route("/", name="admin_post_index", defaults={"page" = 1})
     * @Route("/page/{page}", name="admin_post_index_paginated", requirements={"page" : "\d+"})
     * @Method({"GET", "POST"})
     */
    public function indexAction($page, Request $request)
    {
        // Filters
        if (!$postlist = $this->get('session')->get('admin.postlist')) {
            $postlist = new PostList();
        }
        $postform = $this->createForm(PostListType::class, $postlist);
        $postform->handleRequest($request);
        $this->get('session')->set('admin.postlist',$postlist);

        // Query
        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $queryBuilder = $repository->createQueryBuilder('p');
        $queryBuilder ->orderBy($postlist->getOrderBy(), $postlist->getOrderDir());

        if ($postlist->getStatus() != -99) {
            $queryBuilder->andWhere('p.status = :status')->setParameter('status', $postlist->getStatus());
        } else {
            $queryBuilder->andWhere('p.status >= :status')->setParameter('status', 0);
        }

        if ($postlist->getSearchTitle()) {
            $queryBuilder->andWhere('p.title  like :title')->setParameter('title', '%'.$postlist->getSearchTitle().'%');
        }

        $query = $queryBuilder->getQuery();
        $paginator = $this->get('knp_paginator');
        $posts = $paginator->paginate($query, $page, $postlist->getNumListItems());
        $posts->setUsedRoute('admin_post_index_paginated');

        return $this->render('admin/blog/index.html.twig', array('posts' => $posts,'postform'=>$postform->createView()));
    }

    /**
     * Bulk Action Processor
     *
     * This controller responds to two different routes with the same URL:
     *   * 'admin_post_index' is the route with a name that follows the same
     *     structure as the rest of the controllers of this class.
     *   * 'admin_index' is a nice shortcut to the backend homepage. This allows
     *     to create simpler links in the templates. Moreover, in the future we
     *     could move this annotation to any other controller while maintaining
     *     the route name and therefore, without breaking any existing link.
     *
     * @Route("/bulkact", name="admin_post_bulk", defaults={"page" = 1})
     * @Method({"POST"})
     */
    public function bulkAction(Request $request)
    {
        if (null === $this->getUser() || !$this->getUser()->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Post(s) can only be changed in bulk by admins.');
            return $this->redirectToRoute('admin_post_index');
        }

        $cids = $request->request->get('cid');
        $setStatus = $request->request->get('setStatus');
        $repo = $this->getDoctrine()->getRepository('AppBundle:Post');

        foreach ($cids as $p) {
            $post = $repo->find($p);
            $post->setStatus($setStatus);

            $em = $this->getDoctrine()->getManager();

            $em->persist($post);
            $em->flush();
        }
        $this->addFlash('notice', 'Post(s) status changed.');
        return $this->redirectToRoute('admin_post_index');
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/new", name="admin_post_new")
     * @Method({"GET", "POST"})
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     */
    public function newAction(Request $request)
    {
        $post = new Post();
        $post->setUser($this->getUser());

        // See http://symfony.com/doc/current/book/forms.html#submitting-forms-with-multiple-buttons
        $form = $this->createForm('AppBundle\Form\PostType', $post)
            ->add('saveAndCreateNew', 'Symfony\Component\Form\Extension\Core\Type\SubmitType');

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug($this->get('slugger')->slugify($post->getTitle()));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See http://symfony.com/doc/current/book/controller.html#flash-messages
            $this->addFlash('success', 'post.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_post_new');
            }

            return $this->redirectToRoute('admin_post_index');
        }

        return $this->render('admin/blog/new.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="admin_post_show")
     * @Method("GET")
     */
    public function showAction(Post $post)
    {
        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
        if (null === $this->getUser() || !$post->isAuthor($this->getUser())) {
            throw $this->createAccessDeniedException('Posts can only be shown to their authors.');
        }

        $deleteForm = $this->createDeleteForm($post);

        return $this->render('admin/blog/show.html.twig', array(
            'post'        => $post,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="admin_post_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Post $post, Request $request)
    {
        if (null === $this->getUser() || !$this->getUser()->hasRole('ROLE_ADMIN') || (!$post->isAuthor($this->getUser()) && !$this->getUser()->hasRole('ROLE_EDITOR'))) {
            $this->addFlash('error', 'Posts can only be edited by their authors.');
            return $this->redirectToRoute('admin_post_index');
        }
        $entityManager = $this->getDoctrine()->getManager();

        $editForm = $this->createForm('AppBundle\Form\PostType', $post);
        $deleteForm = $this->createDeleteForm($post);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $post->setSlug($this->get('slugger')->slugify($post->getTitle()));
            $post->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'post.updated_successfully');

            return $this->redirectToRoute('admin_post_edit', array('id' => $post->getId()));
        }

        return $this->render('admin/blog/edit.html.twig', array(
            'post'        => $post,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}", name="admin_post_delete")
     * @Method("DELETE")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     */
    public function deleteAction(Request $request, Post $post)
    {
        if (null === $this->getUser() || !$this->getUser()->hasRole('ROLE_ADMIN') || (!$post->isAuthor($this->getUser()) && !$this->getUser()->hasRole('ROLE_EDITOR'))) {
            $this->addFlash('error', 'Posts can only be deleted by their authors or an admin.');
            return $this->redirectToRoute('admin_post_index');
        }

        $form = $this->createDeleteForm($post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($post);
            $entityManager->flush();

            $this->addFlash('success', 'post.deleted_successfully');
        }

        return $this->redirectToRoute('admin_post_index');
    }

    /**
     * Creates a form to delete a Post entity by id.
     *
     * This is necessary because browsers don't support HTTP methods different
     * from GET and POST. Since the controller that removes the blog posts expects
     * a DELETE method, the trick is to create a simple form that *fakes* the
     * HTTP DELETE method.
     * See http://symfony.com/doc/current/cookbook/routing/method_parameters.html.
     *
     * @param Post $post The post object
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Post $post)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_post_delete', array('id' => $post->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
