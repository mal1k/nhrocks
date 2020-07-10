<?php

namespace ArcaSolutions\WebBundle\Entity;

use ArcaSolutions\WysiwygBundle\Entity\Page;
use Doctrine\ORM\Mapping as ORM;

/**
 * SettingNavigation
 *
 * @ORM\Table(name="Setting_Navigation", indexes={@ORM\Index(name="label", columns={"label"})})
 * @ORM\Entity(repositoryClass="ArcaSolutions\WebBundle\Repository\SettingNavigationRepository")
 */
class SettingNavigation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="`order`", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="area", type="string", length=20, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $area;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=100, nullable=false)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * @var boolean
     *
     * @ORM\Column(name="custom", type="boolean", length=1, nullable=false)
     */
    private $custom;

    /**
     * @var boolean
     *
     * @ORM\Column(name="page_id", type="integer", nullable=true)
     */
    private $pageId;

    /**
     * @var Page
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\WysiwygBundle\Entity\Page", inversedBy="navigation")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     */
    private $page;

    /**
     * @var string
     */
    private $pageUrl;

    /**
     * Set order
     *
     * @param integer $order
     * @return SettingNavigation
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set area
     *
     * @param string $area
     * @return SettingNavigation
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return SettingNavigation
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return SettingNavigation
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set custom
     *
     * @param string $custom
     * @return SettingNavigation
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;

        return $this;
    }

    /**
     * Get custom
     *
     * @return string
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @return bool
     */
    public function isPageId()
    {
        return $this->pageId;
    }

    /**
     * @param bool $pageId
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return string
     */
    public function getPageUrl()
    {
        return $this->pageUrl;
    }

    /**
     * @param string $pageUrl
     */
    public function setPageUrl($pageUrl)
    {
        $this->pageUrl = $pageUrl;
    }
}
