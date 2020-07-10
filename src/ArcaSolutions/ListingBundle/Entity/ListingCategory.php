<?php

namespace ArcaSolutions\ListingBundle\Entity;

use ArcaSolutions\ImportBundle\Entity\ImportLog;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Listingcategory
 *
 * @ORM\Table(name="ListingCategory", indexes={@ORM\Index(name="category_id", columns={"category_id"}), @ORM\Index(name="title1", columns={"title"}), @ORM\Index(name="friendly_url1", columns={"friendly_url"}), @ORM\Index(name="keywords", columns={"keywords", "title"})})
 * @ORM\Entity(repositoryClass="ArcaSolutions\ListingBundle\Repository\ListingCategoryRepository")
 */
class ListingCategory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"listingDetail", "API", "dealDetail"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Serializer\Groups({"listingDetail", "API", "dealDetail"})
     */
    private $title;

    /**
     * @var integer
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    private $categoryId;

    /**
     * @var integer
     *
     * @ORM\Column(name="image_id", type="integer", nullable=true)
     */
    private $imageId;

    /**
     * @var integer
     *
     * @ORM\Column(name="icon_id", type="integer", nullable=true)
     */
    private $iconId;

    /**
     * @var string
     *
     * @ORM\Column(name="featured", type="string", length=1, nullable=false)
     */
    private $featured = 'n';

    /**
     * @var string
     *
     * @ORM\Column(name="summary_description", type="string", length=255, nullable=true)
     */
    private $summaryDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_description", type="string", length=255, nullable=true)
     */
    private $seoDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="page_title", type="string", length=255, nullable=true)
     */
    private $pageTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="friendly_url", type="string", length=255, nullable=false)
     * @Serializer\Groups({"listingDetail", "API", "dealDetail"})
     */
    private $friendlyUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="string", length=255, nullable=true)
     */
    private $keywords;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_keywords", type="string", length=255, nullable=true)
     */
    private $seoKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="enabled", type="string", length=1, nullable=false)
     */
    private $enabled;

    /**
     * @var ListingCategory
     *
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingCategory", mappedBy="parent")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    private $children = [];

    /**
     * @var ListingCategory
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingCategory", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\Listing", mappedBy="categories")
     */
    private $listings;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\ImageBundle\Entity\Image", fetch="EAGER")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $image;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\ImageBundle\Entity\Image", fetch="EAGER")
     * @ORM\JoinColumn(name="icon_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $icon;

    /**
     * @var ImportLog
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ImportBundle\Entity\ImportLog", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, name="import_id")
     */
    private $import;

    /**
     * @var string
     * @Serializer\Groups({"API", "dealDetail"})
     * @Serializer\SerializedName("image_url")
     * @Serializer\Type("string")
     */
    private $imagePath;

    /**
     * Constructor
     *
     * ArrayCollection
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->listings = new ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return ListingCategory
     */
    public function setTitle($title)
    {
        $this->title = $title;

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
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     * @return ListingCategory
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * @param mixed $listings
     * @return ListingCategory
     */
    public function setListings($listings)
    {
        $this->listings = $listings;

        return $this;
    }

    /**
     * Set imageId
     *
     * @param integer $imageId
     * @return ListingCategory
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;

        return $this;
    }

    /**
     * Get imageId
     *
     * @return integer
     */
    public function getImageId()
    {
        return $this->imageId;
    }

    /**
     * Set iconId
     *
     * @param integer $iconId
     * @return ListingCategory
     */
    public function setIconId($iconId)
    {
        $this->iconId = $iconId;

        return $this;
    }

    /**
     * Get iconId
     *
     * @return integer
     */
    public function getIconId()
    {
        return $this->iconId;
    }

    /**
     * Set featured
     *
     * @param string $featured
     * @return ListingCategory
     */
    public function setFeatured($featured)
    {
        $this->featured = $featured;

        return $this;
    }

    /**
     * Get featured
     *
     * @return string
     */
    public function getFeatured()
    {
        return $this->featured;
    }

    /**
     * Set summaryDescription
     *
     * @param string $summaryDescription
     * @return ListingCategory
     */
    public function setSummaryDescription($summaryDescription)
    {
        $this->summaryDescription = $summaryDescription;

        return $this;
    }

    /**
     * Get summaryDescription
     *
     * @return string
     */
    public function getSummaryDescription()
    {
        return $this->summaryDescription;
    }

    /**
     * Set seoDescription
     *
     * @param string $seoDescription
     * @return ListingCategory
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    /**
     * Get seoDescription
     *
     * @return string
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * Set pageTitle
     *
     * @param string $pageTitle
     * @return ListingCategory
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    /**
     * Get pageTitle
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Set friendlyUrl
     *
     * @param string $friendlyUrl
     * @return ListingCategory
     */
    public function setFriendlyUrl($friendlyUrl)
    {
        $this->friendlyUrl = $friendlyUrl;

        return $this;
    }

    /**
     * Get friendlyUrl
     *
     * @return string
     */
    public function getFriendlyUrl()
    {
        return $this->friendlyUrl;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     * @return ListingCategory
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set seoKeywords
     *
     * @param string $seoKeywords
     * @return ListingCategory
     */
    public function setSeoKeywords($seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;

        return $this;
    }

    /**
     * Get seoKeywords
     *
     * @return string
     */
    public function getSeoKeywords()
    {
        return $this->seoKeywords;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return ListingCategory
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set enabled
     *
     * @param string $enabled
     * @return ListingCategory
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return string
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set countSub
     *
     * @param integer $countSub
     * @return ListingCategory
     */
    public function setCountSub($countSub)
    {
        $this->countSub = $countSub;

        return $this;
    }

    /**
     * Get countSub
     *
     * @return integer
     */
    public function getCountSub()
    {
        return $this->countSub;
    }

    /**
     * Set parentId
     *
     * @param ListingCategory $parent
     * @return ListingCategory
     */
    public function setParent(ListingCategory $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get Parent
     *
     * @return ListingCategory
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get Children
     *
     * @return ListingCategory
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get an array of all parent ids
     *
     * @param ListingCategory $category
     * @return array
     */
    public function getParentIds($category = null)
    {
        $category = $category ?: $this;

        if ($parent = $category->getParent()) {
            return array_merge([$parent->getId()], $this->getParentIds($parent));
        }

        return [];
    }

    /**
     * @param ListingCategory $children
     * @return ListingCategory
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * Add children
     *
     * @param \ArcaSolutions\ListingBundle\Entity\ListingCategory $children
     * @return ListingCategory
     */
    public function addChild(\ArcaSolutions\ListingBundle\Entity\ListingCategory $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \ArcaSolutions\ListingBundle\Entity\ListingCategory $children
     */
    public function removeChild(\ArcaSolutions\ListingBundle\Entity\ListingCategory $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param string $imagePath
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Get all children categories related
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("children")
     * @Serializer\Groups({"API_WITH_CHILDREN"})
     *
     * @return array
     */
    public function getChildCategories()
    {
        $categories_array = [];

        /* @var $child ListingCategory */
        foreach ($this->getChildren() as $child)
        {
            if ($child->getEnabled() == 'n')
                continue;

            $categories_array[] = $child;
        }

        return $categories_array;
    }

    /**
     * @return ImportLog
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @param ImportLog $import
     */
    public function setImport(ImportLog $import)
    {
        $this->import = $import;
    }
}
