<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @Route("/blog")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class BlogController extends Controller
{
    /**
     * @Route("/", name="blog_index", defaults={"page" = 1})
     * @Route("/page/{page}", name="blog_index_paginated", requirements={"page" : "\d+"})
     * @Cache(smaxage="10")
     */
    public function indexAction($page)
    {
        $user = $this->getUser();
        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $queryBuilder = $repository->createQueryBuilder('p');
        $queryBuilder ->orderBy('p.publishedAt', 'DESC');

        if (!$user) {
            $queryBuilder->andWhere('p.status = :status')->setParameter('status', '1');
            $queryBuilder->andWhere('p.publishedAt <= :now')->setParameter('now', new \DateTime());
        } else if ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')) {
            $queryBuilder->andWhere('p.status >= :status')->setParameter('status', '0');
        } else if ($user->hasRole('ROLE_EDITOR')) {
            $queryBuilder->andWhere('p.status >= :status')->setParameter('status', '1');
        } else {
            $queryBuilder->andWhere('p.status = :status')->setParameter('status', '1');
            $queryBuilder->andWhere('p.publishedAt <= :now')->setParameter('now', new \DateTime());
        }

        $query = $queryBuilder->getQuery();

        $paginator = $this->get('knp_paginator');
        $posts = $paginator->paginate($query, $page, Post::NUM_ITEMS);
        $posts->setUsedRoute('blog_index_paginated');

        return $this->render('blog/index.html.twig', array('posts' => $posts));
    }

    /**
     * @Route("/category/{category}", name="blog_index_category", defaults={"page" = 1})
     * @Route("/category/{category}/{page}", name="blog_index_category_paginated", requirements={"page" : "\d+"})
     * @Cache(smaxage="10")
     */
    public function categoryAction($category,$page)
    {
        $user = $this->getUser();

        $catrepo = $this->getDoctrine()->getRepository('AppBundle:PostCategory');
        $cat = $catrepo->findOneBy(array('slug'=>$category));
        $cat_children = $catrepo->getChildren($cat);
        $cat_children[] = $cat;

        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $queryBuilder = $repository->createQueryBuilder('p');
        $queryBuilder ->orderBy('p.publishedAt', 'DESC');

        if (!$user) {
            $queryBuilder->andWhere('p.status = :status')->setParameter('status', '1');
            $queryBuilder->andWhere('p.publishedAt <= :now')->setParameter('now', new \DateTime());
        } else if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder->andWhere('p.status >= :status')->setParameter('status', '0');
        } else if ($user->hasRole('ROLE_EDITOR')) {
            $queryBuilder->andWhere('p.status >= :status')->setParameter('status', '1');
        } else {
            $queryBuilder->andWhere('p.status = :status')->setParameter('status', '1');
            $queryBuilder->andWhere('p.publishedAt <= :now')->setParameter('now', new \DateTime());
        }
        $queryBuilder->andWhere('p.category  IN (:cats)')->setParameter('cats', $cat);
        $queryBuilder->orWhere('p.category  IN (:cats)')->setParameter('cats', $cat_children);

        $query = $queryBuilder->getQuery();

        $paginator = $this->get('knp_paginator');
        $posts = $paginator->paginate($query, $page, Post::NUM_ITEMS);
        $posts->setUsedRoute('blog_index_category_paginated');

        return $this->render('blog/catIndex.html.twig', array('posts' => $posts,'cat'=>$cat));
    }

    /**
     * @Route("/posts/{slug}", name="blog_post")
     *
     * NOTE: The $post controller argument is automatically injected by Symfony
     * after performing a database query looking for a Post with the 'slug'
     * value given in the route.
     * See http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
     */
    public function postShowAction(Post $post)
    {
        return $this->render('blog/post_show.html.twig', array('post' => $post));
    }

    /**
     * @Route("/comment/{postSlug}/new", name = "comment_new")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @Method("POST")
     * @ParamConverter("post", options={"mapping": {"postSlug": "slug"}})
     *
     * NOTE: The ParamConverter mapping is required because the route parameter
     * (postSlug) doesn't match any of the Doctrine entity properties (slug).
     * See http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html#doctrine-converter
     */
    public function commentNewAction(Request $request, Post $post)
    {
        $form = $this->createForm('AppBundle\Form\CommentType');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Comment $comment */
            $comment = $form->getData();
            $comment->setUser($this->getUser());
            $comment->setPost($post);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('blog_post', array('slug' => $post->getSlug()));
        }

        return $this->render('blog/comment_form_error.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
        ));
    }

    /**
     * This controller is called directly via the render() function in the
     * blog/post_show.html.twig template. That's why it's not needed to define
     * a route name for it.
     *
     * The "id" of the Post is passed in and then turned into a Post object
     * automatically by the ParamConverter.
     *
     * @param Post $post
     *
     * @return Response
     */
    public function commentFormAction(Post $post)
    {
        $form = $this->createForm('AppBundle\Form\CommentType');

        return $this->render('blog/_comment_form.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
        ));
    }
}
