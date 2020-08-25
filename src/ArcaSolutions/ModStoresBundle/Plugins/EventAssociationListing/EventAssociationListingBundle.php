<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing;

use ArcaSolutions\ListingBundle\ListingItemDetail;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Entity\ListingLevelFieldEvents;
use DateTime;
use Elastica\Result;
use PDO;

class EventAssociationListingBundle extends Bundle
{
    /**
     * Boots the Bundle.
     */
    public function boot()
    {

        if ($this->isSitemgr()) {

            /*
             * Register sitemgr only bundle hooks
             */
            Hooks::Register('formpricing_after_add_fields', function (&$params = null) {
                return $this->getFormPricingAfterAddFields($params);
            });
            Hooks::Register('paymentgateway_after_save_levels', function (&$params = null) {
                return $this->getPaymentGatewayAfterSaveLevels($params);
            });
            Hooks::Register('formlevels_render_fields', function (&$params = null) {
                return $this->getFormLevelsRenderFields($params);
            });
            Hooks::Register('sitemgrlistingtabs_after_render_tabs', function (&$params = null) {
                return $this->getSitemgrListingTabsAfterRenderTabs($params);
            });
            Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                return $this->getModulesFooterAfterRenderJs($params);
            });
            Hooks::Register('eventcode_after_save', function (&$params = null) {
                return $this->getEventCodeAfterSave($params);
            });
            Hooks::Register('classevent_before_delete', function (&$params = null) {
                return $this->getClassEventBeforeDelete($params);
            });
            Hooks::Register('classlisting_before_delete', function (&$params = null) {
                return $this->getClassListingBeforeDelete($params);
            });
            Hooks::Register('sitemgrheader_after_render_metatags', function (&$params = null) {
                return $this->getSitemgrHeaderAfterRenderMetatags($params);
            });
            Hooks::Register('formevent_after_render_renewaldate', function (&$params = null) {
                return $this->getFormEventAfterRenderRenewalDate($params);
            });
            Hooks::Register('listinglevelfeature_before_return', function (&$params = null) {
                return $this->getListingLevelFeatureBeforeReturn($params);
            });

        } else {

            /*
            * Register front only bundle hooks
            */
            Hooks::Register('listingdetail_after_render_event', function (&$params = null) {
                return $this->getListingDetailAfterRenderEvent($params);
            });
            Hooks::Register('listingdetail_after_render_eventtab', function (&$params = null) {
                return $this->getListingDetailAfterRenderEventTab($params);
            });
            Hooks::Register('detailextension_overwrite_activetab', function (&$params = null) {
                return $this->getDetailExtensionOverwriteActiveTab($params);
            });
            Hooks::Register('listinglevel_construct', function (&$params = null) {
                return $this->getListingLevelConstruct($params);
            });
            Hooks::Register('listinglevelfeature_before_return', function (&$params = null) {
                return $this->getListingLevelFeatureBeforeReturn($params);
            });
            Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                return $this->getModulesFooterAfterRenderJs($params);
            });
            Hooks::Register('listing_before_add_globalvars', function (&$params = null) {
                return $this->getListingBeforeAddGlobalVars($params);
            });
            Hooks::Register('eventcode_after_save', function (&$params = null) {
                return $this->getEventCodeAfterSave($params);
            });
            Hooks::Register('classevent_before_delete', function (&$params = null) {
                return $this->getClassEventBeforeDelete($params);
            });
            Hooks::Register('classlisting_before_delete', function (&$params = null) {
                return $this->getClassListingBeforeDelete($params);
            });
            Hooks::Register('eventdetail_after_render_contact', function (&$params = null) {
                return $this->getEventDetailAfterRenderContact($params);
            });
            Hooks::Register('formevent_after_render_renewaldate', function (&$params = null) {
                return $this->getFormEventAfterRenderRenewalDate($params);
            });
            Hooks::Register('event_after_validate_itemdetail', function (&$params = null) {
                return $this->getEventAfterValidateItemDetail($params);
            });
            Hooks::Register('blocksextension_overwrite_recurringdata', function (&$params = null) {
                return $this->getBlockExtensionOverwriteRecurringData($params);
            });
        }
    }

    private function getFormPricingAfterAddFields(&$params = null)
    {
        if ($params['type'] == 'listing') {

            $translation = $this->container->get('translator');

            $params['levelOptions'][] = [
                'name'  => 'events',
                'type'  => 'numeric',
                'title' => $translation->trans('Event Association'),
                'tip'   => $translation->trans('Number of Events the listing owner is able to associate'),
                'min'   => 0,
            ];
        }
    }

    private function getPaymentGatewayAfterSaveLevels(&$params = null)
    {
        if ($params['type'] == 'listing' && $params['levelOptionData']['events']) {

            $doctrine = $this->container->get('doctrine');
            $manager = $this->container->get('doctrine')->getManager();

            foreach ($params['levelOptionData']['events'] as $level => $field) {

                $listingLevel = $doctrine->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
                    'level' => $level,
                ]);

                if ($listingLevel) {
                    $listingLevel->setField($field);
                    $manager->persist($listingLevel);
                } else {
                    $listingLevel = new ListingLevelFieldEvents();
                    $listingLevel->setLevel($level);
                    $listingLevel->setField($field);
                    $manager->persist($listingLevel);
                }
            }

            $manager->flush();
        }
    }

    private function getFormLevelsRenderFields(&$params = null)
    {
        if (is_a($params['levelObj'], 'ListingLevel') && $params['option']['name'] == 'events') {

            $params['levelObj']->events = [];

            $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findBy([],
                ['level' => 'DESC']);

            if ($resultLevel) {
                foreach ($resultLevel as $levelfield) {
                    $params['levelObj']->events[] = $levelfield->getField();
                }
            }
        }
    }

    private function getSitemgrListingTabsAfterRenderTabs(&$params = null)
    {
        $translation = $this->container->get('translator');

        $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
            'level' => $params['listing']->getNumber('level'),
        ]);

        if (!empty($resultLevel) && EVENT_FEATURE == 'on' && CUSTOM_EVENT_FEATURE == 'on' && $resultLevel->getField() > 0) {
            printf('<li %s><a href="%s/event.php?id=%d" role="tab">%s</a></li>',
                $params['activeTab']['event'],
                $params['url_redirect'],
                $params['id'],
                ucfirst($translation->trans('Event'))
            );
        }
    }

    private function getModulesFooterAfterRenderJs(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], 'content/'.EVENT_FEATURE_FOLDER.'/') !== false ||
            string_strpos($_SERVER['PHP_SELF'], 'sponsors/'.EVENT_FEATURE_FOLDER.'/') !== false) {

            $request = $this->container->get('request_stack')->getCurrentRequest();
            $request !== null and $attached_listing = $request->get('listing_id', 0);

            if (empty($attached_listing) && !empty($params['id'])) {

                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $statement = $connection->prepare('SELECT listing_id FROM EventAssociated WHERE event_id = :event_id LIMIT 1');
                $statement->bindValue('event_id', $params['id']);
                $statement->execute();

                $attached_listing = $statement->fetch()['listing_id'];

            }

            echo $this->container->get('templating')->render('EventAssociationListingBundle::js/event_form_association.html.twig',
                [
                    'members'          => $params['members'],
                    'attached_listing' => $attached_listing,
                    'id'               => $params['id']
                ]);

        }

        if (string_strpos($_SERVER['PHP_SELF'], 'content/'.LISTING_FEATURE_FOLDER.'/event') !== false) {

            $listing = null;
            $attached_event = [];

            if (isset($params['id'])) {

                $doctrine = $this->container->get('doctrine');
                $manager = $doctrine->getManager();
                $connection = $manager->getConnection();

                $listing = $doctrine->getRepository('ListingBundle:Listing')->find($params['id']);

                $statement = $connection->prepare('SELECT event_id FROM EventAssociated WHERE listing_id = :listing_id');
                $statement->bindValue('listing_id', $params['id']);
                $statement->execute();

                $attached_event = $statement->fetchAll(PDO::FETCH_COLUMN);

                $associationLevel = $doctrine->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy(['level' => $listing->getLevel()]);
            }

            echo $this->container->get('templating')->render('EventAssociationListingBundle::js/listing_form_association.html.twig',
                [
                    'members'        => $params['members'],
                    'listing'        => $listing,
                    'level'          => $associationLevel,
                    'attached_event' => str_replace('"', '\"', json_encode($attached_event)),
                ]);

        }
    }

    private function getEventCodeAfterSave(&$params = null)
    {
        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        $listing_attached = (isset($_POST['listing_id']) && !empty($_POST['listing_id'])) ? $_POST['listing_id'] : null;

        $statement = $connection->prepare('SELECT * FROM EventAssociated WHERE event_id = :id');
        $statement->bindValue('id', $params['event']->getNumber('id'));
        $statement->execute();

        $results = $statement->fetch();

        if ($results) {
            $statement = $connection->prepare('UPDATE EventAssociated SET listing_id = :listingAttached WHERE event_id = :id');
        } else {
            $statement = $connection->prepare('INSERT INTO EventAssociated (event_id, listing_id) VALUES (:id, :listingAttached)');
        }

        $statement->bindValue('id', $params['event']->getNumber('id'));
        $statement->bindValue('listingAttached', $listing_attached);
        $statement->execute();
    }

    private function getClassEventBeforeDelete(&$params = null)
    {
        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        $statement = $connection->prepare('DELETE FROM EventAssociated WHERE event_id = :id');
        $statement->bindValue('id', $params['that']->id);
        $statement->execute();
    }

    private function getClassListingBeforeDelete(&$params = null)
    {
        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        $statement = $connection->prepare('DELETE FROM EventAssociated WHERE listing_id = :id');
        $statement->bindValue('id', $params['that']->id);
        $statement->execute();
    }

    private function getSitemgrHeaderAfterRenderMetatags(&$params = null)
    {
        echo '<style>
            #listingSelectBox .selectize-input{
                max-height: 34px;
            }
        </style>';
    }

    private function getFormEventAfterRenderRenewalDate(&$params = null)
    {
        if(empty($params['id'])) {
            return;
        }

        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        $statement = $connection->prepare('SELECT listing_id FROM EventAssociated WHERE event_id = :event_id LIMIT 1');
        $statement->bindValue('event_id', $params['id']);
        $statement->execute();

        if(($listing_id = $statement->fetch()['listing_id']) && empty($listing_id)) {
            $listing_id = 0;
        }

        echo $this->container->get('templating')->render('EventAssociationListingBundle::form-sitemgr-event.html.twig', [
            'listing_id' => $listing_id
        ]);
    }

    private function getListingDetailAfterRenderEvent(&$params = null)
    {
        echo $this->container->get('templating')->render('EventAssociationListingBundle::eventassoc-listingdetail.html.twig');
    }

    private function getListingDetailAfterRenderEventTab(&$params = null)
    {
        echo $this->container->get('templating')->render('EventAssociationListingBundle::eventassoctab-listingdetail.html.twig', [
            'activeTab' => $params['activeTab']
        ]);
    }

    private function getDetailExtensionOverwriteActiveTab(&$params = null)
    {
        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        $statement = $connection->prepare('SELECT id FROM EventAssociated WHERE listing_id = :listing_id LIMIT 1');
        $statement->bindValue('listing_id', $params['listing']->getId());
        $statement->execute();

        $associationId = $statement->fetch(PDO::FETCH_COLUMN);

        $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
            'level' => $params['listing']->getLevel(),
        ]);

        !empty($resultLevel) and $num_events_allowed = $resultLevel->getField();

        if(!empty($associationId) && !empty($num_events_allowed)) {
            $params['contentCount']++;
            $params['activeTab'] = $params['activeTab'] < 6 ? 6 : $params['activeTab'];
        }
    }

    private function getListingLevelConstruct(&$params = null)
    {
        $params['that']->eventsCount = 0;
    }

    private function getListingLevelFeatureBeforeReturn(&$params = null)
    {
        $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
            'level' => $params['level']->getValue(),
        ]);

        if(!empty($resultLevel)) {
            $params['listingLevel']->eventsCount = $resultLevel->getField();
        } else {
            $params['listingLevel']->eventsCount = 0;
        }
    }

    private function getListingBeforeAddGlobalVars(&$params = null)
    {
        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        $events = null;

        $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
            'level' => $params['item']->getLevel(),
        ]);

        !empty($resultLevel) and $num_events_allowed = $resultLevel->getField();

        if (!empty($num_events_allowed)) {
            $dateNow = new DateTime('now');
            $statement = $connection->prepare("SELECT id FROM Event WHERE status = :status AND id IN (SELECT event_id FROM EventAssociated WHERE listing_id = :listing_id) AND (((until_date >= :now1 OR DATE_FORMAT(until_date, '%Y-%m-%d') = :emptyDate) AND recurring = :yes1) OR (end_date >= :now1 AND recurring = :no1)) ORDER BY id LIMIT :limit");
            $statement->bindValue('status', 'A');
            $statement->bindValue('listing_id', $params['item']->getId());
            $statement->bindValue('now1', $dateNow->format('Y-m-d'));
            $statement->bindValue('now2', $dateNow->format('Y-m-d'));
            $statement->bindValue('emptyDate', '0000-00-00');
            $statement->bindValue('yes1', 'Y');
            $statement->bindValue('no1', 'N');
            $statement->bindValue('limit', (int)$num_events_allowed, PDO::PARAM_INT);
            $statement->execute();

            $resEvents = $statement->fetchAll();

            foreach ($resEvents as $event) {
                $tmpEvent = $this->container->get('doctrine')->getRepository('EventBundle:Event')->find($event['id']);
                $tmpEvent->setRecurring(['enabled' => $tmpEvent->getRecurring() == 'Y']);
                $tmpEvent->date = [
                    'start' => $tmpEvent->getStartDate(),
                    'end'   => $tmpEvent->getEndDate(),
                ];
                $tmpEvent->event = $tmpEvent;
                $events[] = $tmpEvent;
            }

            $this->container->get('twig')->addGlobal('eventsAssoc', $events);
        }
    }

    private function getEventDetailAfterRenderContact(&$params = null)
    {
        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        $statement = $connection->prepare('SELECT listing_id FROM EventAssociated WHERE event_id = :event_id LIMIT 1');
        $statement->bindValue('event_id', $params['item']->getId());
        $statement->execute();

        $listingId = $statement->fetch(PDO::FETCH_COLUMN);

        if (!empty($listingId)) {

            $listing = $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->findOneBy([
                'id'     => $listingId,
                'status' => 'A',
            ]);

            if ($listing) {

                $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
                    'level' => $listing->getLevel(),
                ]);

                if (!empty($resultLevel) && $resultLevel->getField()) {

                    $listingItemDetail = new ListingItemDetail($this->container, $listing);
                    $level = $listingItemDetail->getLevel();

                    $locations = $this->container->get('location.service')->getLocations($listing);
                    $locations_ids = [];
                    $locations_rows = [];
                    foreach (array_filter($locations) as $levelLocation => $location) {
                        $key = substr($levelLocation, 0, 2).':'.$location->getId();
                        $locations_ids[] = $key;
                        $locations_rows[$key] = $location;
                    }

                    echo $this->container->get('templating')->render('EventAssociationListingBundle::eventassoc-eventdetail.html.twig',
                        [
                            'listing'       => $listing,
                            'level'         => $level,
                            'locationsIDs'  => $locations_ids,
                            'locationsObjs' => $locations_rows,
                        ]);

                }
            }
        }
    }

    private function getEventAfterValidateItemDetail(&$params = null)
    {
        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        $statement = $connection->prepare('SELECT listing_id FROM EventAssociated WHERE event_id = :event_id LIMIT 1');
        $statement->bindValue('event_id', $params['item']->getId());
        $statement->execute();

        $attached_listing = $statement->fetch()['listing_id'];

        if (!empty($attached_listing)) {
            $listing = $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->find($attached_listing);
            $listingItemDetail = new ListingItemDetail($this->container, $listing);

            $reviewTotal = $this->container->get('doctrine')->getRepository('WebBundle:Review')->getReviewsPaginated($attached_listing, 1);

            !empty($reviewTotal) and $this->container->get('twig')->addGlobal('listingReviewsTotal', $reviewTotal['total']);
            $listingItemDetail !== null and $this->container->get('twig')->addGlobal('listingLevel', $listingItemDetail->getLevel());
        }
    }

    /**
     * @param null $params
     * @throws \Exception
     */
    private function getBlockExtensionOverwriteRecurringData(&$params = null)
    {
        if($params['item'] instanceof Result) {
            $params['_return'] = false;
        } else {
            if (!empty($params['item']->getStartDate()) && $params['item']->getRecurring()['enabled']) {
                $dateStart = $this->container->get('event.recurring.service')->getNextOccurrence(
                    $params['item']->getStartDate(),
                    $params['item']->getUntilDate(),
                    str_replace('RRULE:', '',
                        $this->container->get('event.recurring.service')->getRRule_rfc2445($params['item']))
                );
            } else {
                $dateStart = $params['item']->getStartDate();
            }

            $params['data']['weekDay'] = $dateStart->format('w') + 1;
            $params['data']['month'] = $dateStart->format('m');
            $params['data']['day'] = $dateStart->format('d');
        }
    }
}