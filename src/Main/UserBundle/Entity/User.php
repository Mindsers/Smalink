<?php
namespace Main\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     *
     * @ORM\OneToMany(targetEntity="Main\SiteBundle\Entity\Link", mappedBy="author", orphanRemoval=true)
     */
    private $link;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->link = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add link
     *
     * @param \Main\SiteBundle\Entity\Link $link
     * @return User
     */
    public function addLink(\Main\SiteBundle\Entity\Link $link)
    {
        $this->link[] = $link;
        $link->setUser($this);

        return $this;
    }

    /**
     * Remove link
     *
     * @param \Main\SiteBundle\Entity\Link $link
     */
    public function removeLink(\Main\SiteBundle\Entity\Link $link)
    {
        $this->link->removeElement($link);
        $link->setUser(null);
    }

    /**
     * Get link
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLink()
    {
        return $this->link;
    }
}
