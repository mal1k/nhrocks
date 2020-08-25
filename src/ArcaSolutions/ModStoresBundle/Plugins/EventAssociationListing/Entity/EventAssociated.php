<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventAssociated
 *
 * @ORM\Table(name="EventAssociated")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class EventAssociated
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
     * @ORM\Column(name="listing_id", type="integer", nullable=true)
     */
    private $listingId;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\EventBundle\Entity\Event", fetch="EAGER")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return EventAssociated
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getListingId()
    {
        return $this->listingId;
    }

    /**
     * @param mixed $listingId
     * @return EventAssociated
     */
    public function setListing($listingId)
    {
        $this->listingId = $listingId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     * @return EventAssociated
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }
}