<?php

namespace ArcaSolutions\ImportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ImportLog
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 *
 * @package ArcaSolutions\ImportBundle\Entity
 *
 * @ORM\Table(name="ImportLog")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ImportBundle\Repository\ImportLogRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ImportLog
{

    /**
     * Import on queue to be processed
     *
     * @since 11.3.00
     */
    const STATUS_PENDING = "pending";

    /**
     * Import in progress
     *
     * @since 11.3.00
     */
    const STATUS_RUNNING = "running";

    /**
     * Import aborted by user
     *
     * @since 11.3.00
     */
    const STATUS_ABORTED = "aborted";

    /**
     * Import data successfully persisted on the database and wait to be syncronized within ElasticSearch
     *
     * @since 11.3.00
     */
    const STATUS_DONE = "done";

    /**
     * Import undone by user. Imported items will be deleted, except for categories and locations
     *
     * @since 11.3.00
     */
    const STATUS_WAITROLLBACK = "waitrollback";

    /**
     * Import undone by user already completed. Imported items have been deleted.
     *
     * @since 11.3.00
     */
    const STATUS_UNDONE = "undone";

    /**
     * Import being syncronized within ElasticSearch
     *
     * @since 11.3.00
     */
    const STATUS_SYNC = "sync";

    /**
     * Import process finished
     *
     * @since 11.3.00
     */
    const STATUS_COMPLETED = "completed";

    /**
     * Import process error
     *
     * @since 11.3.00
     */
    const STATUS_ERROR = "error";

    /**
     * @since 11.3.00
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @since 11.3.00
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255, nullable=false)
     */
    private $filename;

    /**
     * @since 11.3.00
     * @var string
     *
     * @ORM\Column(name="content_type", type="string", length=20, nullable=false)
     */
    private $contentType;

    /**
     * @since 11.3.00
     * @var bool
     *
     * @ORM\Column(name="has_header", type="boolean", nullable=false)
     */
    private $hasHeader;

    /**
     * @since 11.3.00
     * @var string
     *
     * @ORM\Column(name="delimiter", type="string", nullable=true)
     */
    private $delimiter;

    /**
     * @since 11.3.00
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=50, nullable=false)
     */
    private $module;

    /**
     * @since 11.3.00
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @since 11.3.00
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=false)
     */
    private $updatedAt;

    /**
     * @since 11.3.00
     * @var string
     *
     * @ORM\Column(name="level", type="string", nullable=true)
     */
    private $levelForItemsNotSpecified;

    /**
     * @since 11.3.00
     * @var int
     *
     * @ORM\Column(name="account_id", type="integer", nullable=true)
     */
    private $accountIdForAllItems;

    /**
     * @since 11.3.00
     * @var bool
     *
     * @ORM\Column(name = "should_update_friendly_url", type="boolean", nullable=true)
     */
    private $updateFriendlyUrl;

    /**
     * @since 11.3.00
     * @var bool
     *
     * @ORM\Column(name = "should_update_existing_data", type="boolean", nullable=true)
     */
    private $updateExistingData;

    /**
     * @since 11.3.00
     * @var bool
     *
     * @ORM\Column(name = "is_new_categories_featured", type="boolean", nullable=true)
     */
    private $newCategoriesFeatured;

    /**
     * @since 11.3.00
     * @var bool
     *
     * @ORM\Column(name = "is_imported_items_active", type="boolean", nullable=true)
     */
    private $importedItemsActive;

    /**
     * @since 11.3.00
     * @var integer
     *
     * @ORM\Column(name = "error_lines", type="integer", nullable=false, options={"default": 0})
     */
    private $errorLines = 0;

    /**
     * @since 11.3.00
     * @var integer
     *
     * @ORM\Column(name="total_lines", type="integer", nullable=false, options={"default": 0})
     */
    private $totalLines = 0;

    /**
     * @since 11.3.00
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    private $status;

    /**
     * @var array
     *
     * @ORM\Column(name="errors", type="json_array", nullable=true)
     */
    private $errors;

    /**
     * Gets triggered on update and insert
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updatedTimestamps()
    {
        $this->updatedAt = new \DateTime();

        if ($this->getCreatedAt() == null) {
            $this->createdAt = new \DateTime();
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param string $module
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasHeader()
    {
        return $this->hasHeader;
    }

    /**
     * @param bool $hasHeader
     * @return $this
     */
    public function setHasHeader($hasHeader)
    {
        $this->hasHeader = $hasHeader;

        return $this;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     * @return $this
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * @param string $levelForItemsNotSpecified
     * @return $this
     */
    public function setLevelForItemsNotSpecified($levelForItemsNotSpecified)
    {
        $this->levelForItemsNotSpecified = $levelForItemsNotSpecified;

        return $this;
    }

    /**
     * @return string
     */
    public function getLevelForItemsNotSpecified()
    {
        return $this->levelForItemsNotSpecified;
    }


    /**
     * @param bool $importedItemsActive
     * @return $this
     */
    public function setImportedItemsActive($importedItemsActive)
    {
        $this->importedItemsActive = $importedItemsActive;

        return $this;
    }

    /**
     * @return bool
     */
    public function isImportedItemsActive()
    {
        return $this->importedItemsActive;
    }

    /**
     * @param bool $newCategoriesFeatured
     * @return $this
     */
    public function setNewCategoriesFeatured($newCategoriesFeatured)
    {
        $this->newCategoriesFeatured = $newCategoriesFeatured;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isNewCategoriesAsFeatured()
    {
        return $this->newCategoriesFeatured;
    }

    /**
     * @param bool $updateExistingData
     * @return $this
     */
    public function setUpdateExistingData($updateExistingData)
    {
        $this->updateExistingData = $updateExistingData;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isUpdateExistingData()
    {
        return $this->updateExistingData;
    }

    /**
     * @return int
     */
    public function getAccountIdForAllItems()
    {
        return $this->accountIdForAllItems;
    }

    /**
     * @param int $accountIdForAllItems
     * @return $this
     */
    public function setAccountIdForAllItems($accountIdForAllItems)
    {
        $this->accountIdForAllItems = $accountIdForAllItems;

        return $this;
    }

    /**
     * @param bool $updateFriendlyUrl
     * @return $this
     */
    public function setUpdateFriendlyUrl($updateFriendlyUrl)
    {
        $this->updateFriendlyUrl = $updateFriendlyUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isUpdateFriendlyUrl()
    {
        return $this->updateFriendlyUrl;
    }

    /**
     * @return int
     */
    public function getErrorLines()
    {
        return $this->errorLines;
    }

    /**
     * @param int $errorLines
     * @return $this
     */
    public function setErrorLines($errorLines)
    {
        $this->errorLines = $errorLines;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalLines()
    {
        return $this->totalLines;
    }

    /**
     * @param int $totalLines
     * @return $this
     */
    public function setTotalLines($totalLines)
    {
        $this->totalLines = $totalLines;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

}
