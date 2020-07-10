<?php

namespace ArcaSolutions\ListingBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ListingTemplate
 *
 * @ORM\Table(name="ListingTemplate")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ListingBundle\Repository\ListingtemplateRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ListingTemplate
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="layout_id", type="integer", nullable=false, options={"default"="0"} )
     */
    private $layoutId = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="entered", type="datetime", nullable=false)
     */
    private $entered;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=false, options={"default"="enabled"})
     */
    private $status = 'enabled';

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $price = 0.00;

    /**
     * @var string
     *
     * @ORM\Column(name="cat_id", type="string", length=255, nullable=true)
     */
    private $catId;

    /**
     * @var string
     *
     * @ORM\Column(name="editable", type="string", length=1, nullable=false, options={"default"="y"})
     */
    private $editable = 'y';

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\Listing", mappedBy="template")
     */
    private $listings;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTemplateField", mappedBy="template", fetch="EAGER")
     * @ORM\JoinColumn(name="id", referencedColumnName="listingtemplate_id")
     */
    private $fields;

    /**
     * Gets triggered on update and insert
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updatedTimestamps()
    {
        $this->updated = new \DateTime();

        if ($this->getEntered() == null) {
            $this->entered = new \DateTime();
        }
    }

    /**
     * Get entered
     *
     * @return \DateTime
     */
    public function getEntered()
    {
        return $this->entered;
    }

    /**
     * Set entered
     *
     * @param \DateTime $entered
     * @return ListingTemplate
     */
    public function setEntered($entered)
    {
        $this->entered = $entered;

        return $this;
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
     * Get layoutId
     *
     * @return integer
     */
    public function getLayoutId()
    {
        return $this->layoutId;
    }

    /**
     * Set layoutId
     *
     * @param integer $layoutId
     * @return ListingTemplate
     */
    public function setLayoutId($layoutId)
    {
        $this->layoutId = $layoutId;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ListingTemplate
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return ListingTemplate
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return ListingTemplate
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return ListingTemplate
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get catId
     *
     * @return string
     */
    public function getCatId()
    {
        return $this->catId;
    }

    /**
     * Set catId
     *
     * @param string $catId
     * @return ListingTemplate
     */
    public function setCatId($catId)
    {
        $this->catId = $catId;

        return $this;
    }

    /**
     * Get editable
     *
     * @return string
     */
    public function getEditable()
    {
        return $this->editable;
    }

    /**
     * Set editable
     *
     * @param string $editable
     * @return ListingTemplate
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * @param mixed $listings
     */
    public function setListings($listings)
    {
        $this->listings = $listings;
    }

    /**
     * @return ListingTemplateField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param ListingTemplateField[] $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }
}
