<?php

namespace Main\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Click
 *
 * @ORM\Table(name="click")
 * @ORM\Entity(repositoryClass="Main\SiteBundle\Entity\ClickRepository")
 */
class Click
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="referrer", type="string", length=255)
     */
    private $referrer;

    /**
     * @ORM\ManyToOne(targetEntity="Main\SiteBundle\Entity\Link", inversedBy="click")
     * @ORM\JoinColumn(nullable=false)
     */
    private $link;

    /**
     * @var datetime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


    public function __construct()
    {
        $this->date = new \Datetime();
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
     * Set country
     *
     * @param string $country
     * @return Click
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set referrer
     *
     * @param string $referrer
     * @return Click
     */
    public function setReferrer($referrer)
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * Get referrer
     *
     * @return string
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * Set link
     *
     * @param \Main\SiteBundle\Entity\Link $link
     * @return Click
     */
    public function setLink(\Main\SiteBundle\Entity\Link $link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return \Main\SiteBundle\Entity\Link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Click
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
