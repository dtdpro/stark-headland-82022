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
use AppBundle\Entity\User;

/**
 * Controller used to manage users in the backend.
 *
 * @Route("/admin/user")
 * @Security("has_role('ROLE_ADMIN')")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class UserController extends AdminController
{
    /**
     * Lists all User entities.
     *
     * This controller responds to two different routes with the same URL:
     *   * 'admin_user_index' is the route with a name that follows the same
     *     structure as the rest of the controllers of this class.
     *   * 'admin_index' is a nice shortcut to the backend homepage. This allows
     *     to create simpler links in the templates. Moreover, in the future we
     *     could move this annotation to any other controller while maintaining
     *     the route name and therefore, without breaking any existing link.
     *
     * @Route("/", name="admin_user_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Users can only be viewed by administrators.');
            return $this->redirectToRoute('admin_index');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $users = $entityManager->getRepository('AppBundle:User')->findAll();

        return $this->render('admin/user/index.html.twig', array('users' => $users));
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/new", name="admin_user_new")
     * @Method({"GET", "POST"})
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     */
    public function newAction(Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Users can only be created by administrators.');
            return $this->redirectToRoute('admin_user_index');
        }

        $user = new User();

        // See http://symfony.com/doc/current/book/forms.html#submitting-forms-with-multiple-buttons
        $form = $this->createForm('AppBundle\Form\UserType', $user)
            ->add('saveAndCreateNew', 'Symfony\Component\Form\Extension\Core\Type\SubmitType');

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {


            $userManager = $this->get('fos_user.user_manager');
            $userManager->updatePassword($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See http://symfony.com/doc/current/book/controller.html#flash-messages
            $this->addFlash('success', 'User Created Successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_user_new');
            }

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="admin_user_show")
     * @Method("GET")
     */
    public function showAction(User $user)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Users can only be viewed by administrators.');
            return $this->redirectToRoute('admin_user_index');
        }

        $deleteForm = $this->createDeleteForm($user);

        return $this->render('admin/user/show.html.twig', array(
            'user'        => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="admin_user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(User $user, Request $request)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Users can only be edited by administrators.');
            return $this->redirectToRoute('admin_user_index');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $userManager = $this->get('fos_user.user_manager');

        $editForm = $this->createForm('AppBundle\Form\UserType', $user);
        $deleteForm = $this->createDeleteForm($user);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $userManager->updatePassword($user);

            $entityManager->flush();

            $this->addFlash('success', 'User Successfully Updated');

            return $this->redirectToRoute('admin_user_edit', array('id' => $user->getId()));
        }

        return $this->render('admin/user/edit.html.twig', array(
            'user'        => $user,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}", name="admin_user_delete")
     * @Method("DELETE")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     */
    public function deleteAction(Request $request, User $user)
    {
        if (null === $this->getUser() || !$this->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Users can only be deleted by administrators.');
            return $this->redirectToRoute('admin_user_index');
        }

        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'user.deleted_successfully');
        }

        return $this->redirectToRoute('admin_user_index');
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
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
