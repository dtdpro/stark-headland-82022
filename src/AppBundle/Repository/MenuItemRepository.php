<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Sortable\Entity\Repository\SortableRepository;
use AppBundle\Entity\MenuItem;

class MenuItemRepository extends SortableRepository
{
	/**
	 * Constructor.
	 *
	 * @param EntityManager $em    The EntityManager to use.
	 * @param ClassMetadata $class The class descriptor.
	 */
	public function __construct($em, ClassMetadata $class)
	{
		parent::__construct($em, $class);
	}

	public function getMenu($name,$authchecker)
	{

		$menu = $this->findOneBy(array('name'=>$name));
		$menuitems = $menu->getChildren();

		foreach ($menuitems as $k=>&$m) {
			$can_view=false;
			if ($m->getDisplayAnon() && $authchecker->isGranted('IS_AUTHENTICATED_FULLY')) {
				// No View
			} else {
				if ( $m->getRoles() ) {
					foreach ( $m->getRoles() as $r ) {
						if ( $authchecker->isGranted( $r ) ) {
							if ($m->getDisplay()) $can_view = true;
						}
					}
				} else {
					if ($m->getDisplay()) $can_view = true;
				}
			}
			if (!$can_view) unset($menuitems[$k]);
		}

		return $menuitems;
	}

}