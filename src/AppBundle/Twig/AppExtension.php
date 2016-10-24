<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Twig;

use AppBundle\Utils\Markdown;
use Symfony\Component\Intl\Intl;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * This Twig extension adds a new 'md2html' filter to easily transform Markdown
 * contents into HTML contents inside Twig templates.
 * See http://symfony.com/doc/current/cookbook/templating/twig_extension.html
 *
 * In addition to creating the Twig extension class, before using it you must also
 * register it as a service. See app/config/services.yml file for details.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Julien ITARD <julienitard@gmail.com>
 */
class AppExtension extends \Twig_Extension
{
    /**
     * @var Markdown
     */
    private $parser;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TokenStorage
     */
    private $security_token;

    /**
     * @var AuthorizationChecker
     */
    private $auth_checker;

    /**
     * @var array
     */
    private $locales;

    public function __construct(Markdown $parser, $locales, EntityManager $em, TokenStorage $token, AuthorizationChecker $authcheck)
    {
        $this->parser = $parser;
        $this->locales = $locales;
        $this->em = $em;
        $this->security_token = $token->getToken();
        $this->auth_checker = $authcheck;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('md2html', array($this, 'markdownToHtml'), array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('locales', array($this, 'getLocales')),
            new \Twig_SimpleFunction('phpver', array($this, 'phpVer')),
            new \Twig_SimpleFunction('menu_render', array($this, 'menuRender'), array('is_safe' => array('html'),'needs_environment' => true)),
        );
    }

    /**
     * Transforms the given Markdown content into HTML content.
     *
     *  @param string $content
     *
     * @return string
     */
    public function markdownToHtml($content)
    {
        return $this->parser->toHtml($content);
    }

    /**
     * Takes the list of codes of the locales (languages) enabled in the
     * application and returns an array with the name of each locale written
     * in its own language (e.g. English, Français, Español, etc.)
     *
     * @return array
     */
    public function getLocales()
    {
        $localeCodes = explode('|', $this->locales);

        $locales = array();
        foreach ($localeCodes as $localeCode) {
            $locales[] = array('code' => $localeCode, 'name' => Intl::getLocaleBundle()->getLocaleName($localeCode, $localeCode));
        }

        return $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        // the name of the Twig extension must be unique in the application. Consider
        // using 'app.extension' if you only have one Twig extension in your application.
        return 'app.extension';
    }

    /**
     * Returns the PHP Version
     *
     * @return array
     */
    public function phpVer()
    {
        return phpversion();
    }

    public function menuRender(\Twig_Environment $twig, $menu, array $menu_options = array())
    {
        $default_options = array('menu_layout'=>'navline');
        $options = array_merge($default_options,$menu_options);
        $repo = $this->em->getRepository('AppBundle:MenuItem');
        $menu = $repo->getMenu($menu,$this->auth_checker);
        if ($options['menu_layout'] == 'sidebar') {
            $options['menu_class'] = 'nav nav-pills nav-stacked';
        } else if ($options['menu_layout'] == 'navbar') {
            $options['menu_class'] = 'nav navbar-nav';
        } else if ($options['menu_layout'] == 'navbar-right') {
            $options['menu_class'] = 'nav navbar-nav navbar-right';
        } else if ($options['menu_layout'] == 'footnav') {
            $options['menu_class'] = 'nav-footer';
        } else {
            $options['menu_class'] = 'nav nav-pills';
        }
        return $twig->render('AppBundle::menu.html.twig',array('menuitems'=>$menu,'options'=>$options));
    }
}
