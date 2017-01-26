<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\MenuItem;
use AppBundle\Entity\PostCategory;
use AppBundle\Entity\User;
use AppBundle\Entity\Post;
use AppBundle\Entity\Comment;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the sample data to load in the database when running the unit and
 * functional tests. Execute this command to load the data:
 *
 *   $ php app/console doctrine:fixtures:load
 *
 * See http://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class LoadFixtures implements FixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $adminUser = $this->loadUsers($manager);
        $this->loadPosts($manager,$adminUser);
	    $this->loadForms($manager);
	    $this->loadMenu($manager);
    }

    private function loadUsers(ObjectManager $manager)
    {
        $passwordEncoder = $this->container->get('security.password_encoder');

        $johnUser = new User();
        $johnUser->setUsername('john_user');
        $johnUser->setFirstName('John');
        $johnUser->setLastName('User');
        $johnUser->setEmail('john_user@dtdpro.com');
        $johnUser->setEnabled(true);
        $encodedPassword = $passwordEncoder->encodePassword($johnUser, 'admin');
        $johnUser->setPassword($encodedPassword);
        $manager->persist($johnUser);

        $annaAdmin = new User();
        $annaAdmin->setUsername('admin');
        $annaAdmin->setFirstName("Admin");
        $annaAdmin->setLastName("User");
        $annaAdmin->setEmail('admin@dtdpro.com');
	    $annaAdmin->setEnabled(true);
        $annaAdmin->setRoles(array('ROLE_ADMIN'));
        $encodedPassword = $passwordEncoder->encodePassword($annaAdmin, 'admin');
        $annaAdmin->setPassword($encodedPassword);
        $manager->persist($annaAdmin);

        $manager->flush();

        return $annaAdmin;
    }

    private function loadPosts(ObjectManager $manager, $adminUser)
    {
		// Categories
    	$rootCategory = new PostCategory();
    	$rootCategory->setName('Blog');
	    $rootCategory->setSlug('blog');
	    $manager->persist($rootCategory);
	    $manager->flush();

	    $baseCategory = new PostCategory();
	    $baseCategory->setName('Uncategorized');
	    $baseCategory->setSlug('uncategorized');
	    $baseCategory->setParent($rootCategory);
	    $manager->persist($baseCategory);
	    $manager->flush();

	    // Posts
    	foreach (range(1, 30) as $i) {

            $newdate = new \DateTime('now - '.$i.'days');
            $post = new Post();

            $post->setTitle($this->getRandomPostTitle());
            $post->setSummary($this->getRandomPostSummary());
            $post->setSlug($this->container->get('slugger')->slugify($post->getTitle()));
            $post->setContent($this->getPostContent());
            $post->setUser($adminUser);
            $post->setPublishedAt($newdate);
            $post->setUpdatedAt($newdate);
            $post->setStatus(1);
            $post->setCategory($baseCategory);

            foreach (range(1, 5) as $j) {
                $comment = new Comment();

                $comment->setUser($adminUser);
                $comment->setPublishedAt(new \DateTime('now + '.($i + $j).'seconds'));
                $comment->setContent($this->getRandomCommentContent());
                $comment->setPost($post);

                $manager->persist($comment);
                $post->addComment($comment);
            }

            $manager->persist($post);
        }

        $manager->flush();
    }

	private function loadForms(ObjectManager $manager)
	{

		// Categories
		$rootCategory = new PostCategory();
		$rootCategory->setName('Form');
		$rootCategory->setSlug('form');
		$manager->persist($rootCategory);
		$manager->flush();

		$baseCategory = new PostCategory();
		$baseCategory->setName('Uncategorized');
		$baseCategory->setSlug('uncategorized');
		$baseCategory->setParent($rootCategory);
		$manager->persist($baseCategory);

		$manager->flush();
	}

	private function loadMenu(ObjectManager $manager)
	{

		// Main Menu
		$mainMenu = new MenuItem();
		$mainMenu->setName("Main Menu");
		$manager->persist($mainMenu);
		$manager->flush();

		$menuItem = new MenuItem(); $menuItem->setName("Home"); $menuItem->setRoute("blog_index"); $menuItem->setIcon('fa-home'); $menuItem->setParent($mainMenu); $manager->persist($menuItem); $manager->flush();
		$menuItem = new MenuItem(); $menuItem->setName("Webforms"); $menuItem->setRoute("webform_index"); $menuItem->setIcon('fa-clipboard'); $menuItem->setParent($mainMenu); $manager->persist($menuItem); $manager->flush();

		$watsonItem = new MenuItem(); $watsonItem->setName("Watson"); $watsonItem->setRoute("watson_index"); $watsonItem->setIcon('fa-laptop'); $watsonItem->setRoles(array('ROLE_USER')); $watsonItem->setParent($mainMenu); $manager->persist($watsonItem); $manager->flush();
		$menuItem = new MenuItem(); $menuItem->setName("Watson Playground"); $menuItem->setRoute("watson_index"); $menuItem->setIcon('fa-laptop'); $menuItem->setRoles(array('ROLE_USER'));  $menuItem->setParent($watsonItem); $manager->persist($menuItem); $manager->flush();
		$menuItem = new MenuItem(); $menuItem->setName("Text to Speech"); $menuItem->setRoute("text_to_speech"); $menuItem->setIcon('fa-volume-up'); $menuItem->setRoles(array('ROLE_USER'));  $menuItem->setParent($watsonItem); $manager->persist($menuItem); $manager->flush();

		$menuItem = new MenuItem(); $menuItem->setName("Admin"); $menuItem->setRoute("admin_index"); $menuItem->setIcon('fa-lock'); $menuItem->setRoles(array('ROLE_ADMIN')); $menuItem->setParent($mainMenu); $manager->persist($menuItem); $manager->flush();
		$menuItem = new MenuItem(); $menuItem->setName("Profile"); $menuItem->setRoute("fos_user_profile_show"); $menuItem->setIcon('fa-user'); $menuItem->setRoles(array('ROLE_USER')); $menuItem->setParent($mainMenu); $manager->persist($menuItem); $manager->flush();
		$menuItem = new MenuItem(); $menuItem->setName("Logout"); $menuItem->setRoute("fos_user_security_logout"); $menuItem->setIcon('fa-sign-out'); $menuItem->setRoles(array('ROLE_USER')); $menuItem->setParent($mainMenu); $manager->persist($menuItem); $manager->flush();
		$menuItem = new MenuItem(); $menuItem->setName("Register"); $menuItem->setRoute("fos_user_registration_register"); $menuItem->setIcon('fa-user'); $menuItem->setDisplayAnon(true); $menuItem->setParent($mainMenu); $manager->persist($menuItem); $manager->flush();
		$menuItem = new MenuItem(); $menuItem->setName("Login"); $menuItem->setRoute("fos_user_security_login"); $menuItem->setIcon('fa-sign-in'); $menuItem->setDisplayAnon(true); $menuItem->setParent($mainMenu); $manager->persist($menuItem); $manager->flush();


		// Side Menu
		$sideMenu = new MenuItem();
		$sideMenu->setName("Side Menu");
		$manager->persist($sideMenu);
		$manager->flush();

		$menuItem = new MenuItem(); $menuItem->setName("Home"); $menuItem->setRoute("blog_index"); $menuItem->setIcon('fa-home'); $menuItem->setParent($sideMenu); $manager->persist($menuItem); $manager->flush();

		// Footer Menu
		$footMenu = new MenuItem();
		$footMenu->setName("Footer Menu");
		$manager->persist($footMenu);
		$manager->flush();

		$menuItem = new MenuItem(); $menuItem->setName("Admin"); $menuItem->setRoute("admin_index"); $menuItem->setIcon('fa-lock'); $menuItem->setRoles(array('ROLE_ADMIN')); $menuItem->setParent($footMenu); $manager->persist($menuItem); $manager->flush();
		$menuItem = new MenuItem(); $menuItem->setName("Logout"); $menuItem->setRoute("fos_user_security_logout"); $menuItem->setIcon('fa-sign-out'); $menuItem->setRoles(array('ROLE_USER')); $menuItem->setParent($footMenu); $manager->persist($menuItem); $manager->flush();
		$menuItem = new MenuItem(); $menuItem->setName("Login"); $menuItem->setRoute("fos_user_security_login"); $menuItem->setIcon('fa-sign-in'); $menuItem->setDisplayAnon(true); $menuItem->setParent($footMenu); $manager->persist($menuItem); $manager->flush();

		$manager->flush();
	}

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function getPostContent()
    {
        return <<<MARKDOWN
Lorem ipsum dolor sit amet consectetur adipisicing elit, sed do eiusmod tempor
incididunt ut labore et **dolore magna aliqua**: Duis aute irure dolor in
reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
deserunt mollit anim id est laborum.

  * Ut enim ad minim veniam
  * Quis nostrud exercitation *ullamco laboris*
  * Nisi ut aliquip ex ea commodo consequat

Praesent id fermentum lorem. Ut est lorem, fringilla at accumsan nec, euismod at
nunc. Aenean mattis sollicitudin mattis. Nullam pulvinar vestibulum bibendum.
Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos
himenaeos. Fusce nulla purus, gravida ac interdum ut, blandit eget ex. Duis a
luctus dolor.

Integer auctor massa maximus nulla scelerisque accumsan. *Aliquam ac malesuada*
ex. Pellentesque tortor magna, vulputate eu vulputate ut, venenatis ac lectus.
Praesent ut lacinia sem. Mauris a lectus eget felis mollis feugiat. Quisque
efficitur, mi ut semper pulvinar, urna urna blandit massa, eget tincidunt augue
nulla vitae est.

Ut posuere aliquet tincidunt. Aliquam erat volutpat. **Class aptent taciti**
sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Morbi
arcu orci, gravida eget aliquam eu, suscipit et ante. Morbi vulputate metus vel
ipsum finibus, ut dapibus massa feugiat. Vestibulum vel lobortis libero. Sed
tincidunt tellus et viverra scelerisque. Pellentesque tincidunt cursus felis.
Sed in egestas erat.

Aliquam pulvinar interdum massa, vel ullamcorper ante consectetur eu. Vestibulum
lacinia ac enim vel placerat. Integer pulvinar magna nec dui malesuada, nec
congue nisl dictum. Donec mollis nisl tortor, at congue erat consequat a. Nam
tempus elit porta, blandit elit vel, viverra lorem. Sed sit amet tellus
tincidunt, faucibus nisl in, aliquet libero.
MARKDOWN;
    }

    private function getPhrases()
    {
        return array(
            'Lorem ipsum dolor sit amet consectetur adipiscing elit',
            'Pellentesque vitae velit ex',
            'Mauris dapibus risus quis suscipit vulputate',
            'Eros diam egestas libero eu vulputate risus',
            'In hac habitasse platea dictumst',
            'Morbi tempus commodo mattis',
            'Ut suscipit posuere justo at vulputate',
            'Ut eleifend mauris et risus ultrices egestas',
            'Aliquam sodales odio id eleifend tristique',
            'Urna nisl sollicitudin id varius orci quam id turpis',
            'Nulla porta lobortis ligula vel egestas',
            'Curabitur aliquam euismod dolor non ornare',
            'Sed varius a risus eget aliquam',
            'Nunc viverra elit ac laoreet suscipit',
            'Pellentesque et sapien pulvinar consectetur',
        );
    }

    private function getRandomPostTitle()
    {
        $titles = $this->getPhrases();

        return $titles[array_rand($titles)];
    }

    private function getRandomPostSummary($maxLength = 255)
    {
        $phrases = $this->getPhrases();

        $numPhrases = rand(6, 12);
        shuffle($phrases);

        return substr(implode(' ', array_slice($phrases, 0, $numPhrases-1)), 0, $maxLength);
    }

    private function getRandomCommentContent()
    {
        $phrases = $this->getPhrases();

        $numPhrases = rand(2, 15);
        shuffle($phrases);

        return implode(' ', array_slice($phrases, 0, $numPhrases-1));
    }
}
