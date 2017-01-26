<?php

/*
 * This file is part of the KtwDatabaseMenuBundle package.
 *
 * (c) Kevin T. Weber <https://github.com/kevintweber/KtwDatabaseMenuBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MenuItemRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="symfony_demo_menu_items")
 */
class MenuItem
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * Name of this menu item (used for id by parent menu)
	 *
	 * @ORM\Column(type="string")
	 */
	protected $name = null;

	/**
	 * Name of this menu item (used for id by parent menu)
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $icon = null;

	/**
	 * Label to output, name is used by default
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $label = null;

	/**
	 * Route name
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $route = null;

	/**
	 * Route Praameters
	 *
	 * @ORM\Column(type="array")
	 */
	protected $params = array();

	/**
	 * Route Roles
	 *
	 * @ORM\Column(type="array")
	 */
	protected $roles = array();

	/**
	 * Whether the item is displayed
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $display = true;

	/**
	 * Whether the children of the item are displayed
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $displayChildren = true;

	/**
	 * Whether the item is displayed to Anon users only
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $displayAnon = false;

	/**
	 * Child items
	 *
	 * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="parent", cascade={"all"})
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $children;

	/**
	 * Parent item
	 *
	 * @ORM\ManyToOne(targetEntity="MenuItem", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
	 */
	protected $parent = null;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $updated;

	/**
	 * @Gedmo\Sortable(groups={"parent"})
	 * @ORM\Column(type="integer")
	 */
	private $position;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->children = new ArrayCollection();

		$this->created = new \DateTime();
		$this->updated = new \DateTime();
	}

	/**
	 * Getter for 'id'.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Getter for 'created'.
	 *
	 * @return mixed The value of 'created'.
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * Getter for 'updated'.
	 *
	 * @return mixed The value of 'updated'.
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * @ORM\PreUpdate
	 */
	public function preUpdate()
	{
		$this->updated = new \DateTime();
	}

	/*
	 * Note: Since Knp\Menu uses an array for children, while we use an
	 * ArrayCollection, we must adapt all references to children.
	 */

	/**
	 * {@inheritDoc}
	 */
	public function addChild($child, array $options = array())
	{
		if (!($child instanceof ItemInterface)) {
			$child = $this->factory->createItem($child, $options);
		} elseif (null !== $child->getParent()) {
			throw new \InvalidArgumentException('Cannot add menu item as child, it already belongs to another menu (e.g. has a parent).');
		}

		$child->setParent($this);

		$this->children->set($child->getName(), $child);

		return $child;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChild($name)
	{
		return $this->children->get($name);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChildren()
	{
		return $this->children->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setChildren(array $children)
	{
		$this->children = new ArrayCollection($children);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeChild($name)
	{
		$name = $name instanceof ItemInterface ? $name->getName() : $name;

		$child = $this->getChild($name);
		if ($child !== null) {
			$child->setParent(null);
			$this->children->remove($name);
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFirstChild()
	{
		return $this->children->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastChild()
	{
		return $this->children->last();
	}

	/**
	 * Synonymous with 'isDisplayed'.
	 *
	 * @return boolean
	 */
	public function getDisplay()
	{
		return $this->display;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reorderChildren($order)
	{
		if (count($order) != $this->count()) {
			throw new \InvalidArgumentException('Cannot reorder children, order does not contain all children.');
		}

		$newChildren = array();

		foreach ($order as $name) {
			if (!$this->children->containsKey($name)) {
				throw new \InvalidArgumentException('Cannot find children named ' . $name);
			}

			$child = $this->getChild($name);
			$newChildren[$name] = $child;
		}

		$this->setChildren($newChildren);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function copy()
	{
		$newMenu = clone $this;
		$newMenu->children = new ArrayCollection();
		$newMenu->setParent(null);
		foreach ($this->getChildren() as $child) {
			$newMenu->addChild($child->copy());
		}

		return $newMenu;
	}

	public function __toString()
	{
		return $this->getName();
	}

	/**
	 * @return mixed
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * @param mixed $position
	 */
	public function setPosition($position) {
		$this->position = $position;
	}

	/**
	 * @return mixed
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @param mixed $params
	 */
	public function setParams($params) {
		$this->params = $params;
	}

	/**
	 * @return mixed
	 */
	public function getRoute() {
		return $this->route;
	}

	/**
	 * @param mixed $route
	 */
	public function setRoute($route) {
		$this->route = $route;
	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * @param mixed $name
	 */
	public function setIcon($name) {
		$this->icon = $name;
	}

	/**
	 * @return mixed
	 */
	public function getDisplayAnon() {
		return $this->displayAnon;
	}

	/**
	 * @param mixed $displayAnon
	 */
	public function setDisplayAnon( $displayAnon ) {
		$this->displayAnon = $displayAnon;
	}

	/**
	 * @return mixed
	 */
	public function getRoles() {
		return $this->roles;
	}

	/**
	 * @param mixed $roles
	 */
	public function setRoles( $roles ) {
		$this->roles = $roles;
	}


	public function setParent(MenuItem $parent = null)
	{
		$this->parent = $parent;
	}

	public function getParent()
	{
		return $this->parent;
	}
}