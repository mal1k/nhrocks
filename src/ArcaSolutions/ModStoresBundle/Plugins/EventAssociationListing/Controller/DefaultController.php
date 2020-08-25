<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Controller;

use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Entity\EventAssociated;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function formListingEventAction(Request $request)
    {
        $listing = $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->find($request->query->get('id'));

        return $this->render('EventAssociationListingBundle::form-sitemgr-listing.html.twig', [
            'listing' => $listing,
        ]);
    }

    public function saveListingAction(Request $request)
    {
        if (!$this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SM_LOGGEDIN') &&
            !$this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SESS_ACCOUNT_ID')) {

            $this->redirect('/'.$this->getParameter('alias_sitemgr_module').'/login.php');
        }

        $events = $request->request->get('event_attached', []);
        $listingId = $request->request->get('listing_id', null);
        $accountId = $request->request->get('account_id', null);

        if (!empty($listingId)) {

            $doctrine = $this->container->get('doctrine');
            $manager = $doctrine->getManager();

            $associations = $doctrine->getRepository('EventAssociationListingBundle:EventAssociated')->findBy([
                'listingId' => $listingId,
            ]);

            foreach ($associations as $association) {
                if (($key = array_search($association->getEvent()->getId(), $events)) !== false) {
                    unset($events[$key]);
                } else {
                    $manager->remove($association);
                }
            }

            foreach ($events as $eventId) {
                $event = $doctrine->getRepository('EventBundle:Event')->find($eventId);
                $eventAssociation = $doctrine->getRepository('EventAssociationListingBundle:EventAssociated')->findOneBy([
                    'event' => $eventId,
                ]);

                if (empty($eventAssociation)) {
                    $eventAssociation = new EventAssociated();
                    $eventAssociation->setEvent($event);
                }

                if (!empty($accountId)) {
                    $event->setAccountId($accountId);
                    $manager->persist($event);
                }

                $eventAssociation->setListing($listingId);
                $manager->persist($eventAssociation);
            }

            $manager->flush();
        }

        $redirectUrl = '/'.$this->getParameter('alias_sitemgr_module').'/content/listing/index.php?message=1';

        return new RedirectResponse($redirectUrl);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function listListingAction(Request $request)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $response = ['error' => 'Not Found'];

        if (!$this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SM_LOGGEDIN') &&
            !$this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SESS_ACCOUNT_ID')) {
            return new JsonResponse($response);
        }

        $queryParams = [];

        $accountId = (int)$request->query->get('accountId', 0);

        $listingLevels = ListingLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));

        foreach ($listingLevels as $listingLevel)
        {
            if ($listingLevel->eventsCount > 0)
            {
                $eventLevels[] = $listingLevel->level;
            }
        }

        if(empty($eventLevels)){
            echo json_encode([]);
            exit;
        }

        $eventLevels = implode(',', $eventLevels);

        $where = sprintf(' level IN (%s) ', $eventLevels);

        if ((int)$accountId > 0) {
            // with account
            $where .= ' AND account_id = '.$accountId;
        } else {
            $where .= ' AND (account_id = 0 OR account_id IS NULL) ';
        }

        if(!empty($_GET['query'])){
            $where .= " AND Listing.`title` LIKE '%".$_GET['query']."%' ";
        }

        $query = "
            SELECT id, title
            FROM Listing
            WHERE {$where}
            ORDER BY title
            LIMIT 1000;
        ";

        $statement = $connection->prepare($query);

        foreach ($queryParams as $key => $value) {
            $statement->bindValue($key, $value);
        }

        $statement->execute();
        $result = $statement->fetchAll();

        if (empty($result)) {
            return new JsonResponse($response);
        }

        $response = [];
        foreach ($result as $listing) {
            $response[] = [
                'title' => $listing['title'],
                'id'    => $listing['id'],
            ];
        }

        return new JsonResponse($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function listEventAction(Request $request)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $response = ['error' => 'Not Found'];

        if (!$this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SM_LOGGEDIN') &&
            !$this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SESS_ACCOUNT_ID')) {
            return new JsonResponse($response);
        }

        $queryParams = [];

        $accountId = (int)$request->query->get('accountId', 0);

        $listingLevels = ListingLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));

        foreach ($listingLevels as $listingLevel)
        {
            if ($listingLevel->eventsCount > 0)
            {
                $eventLevels[] = $listingLevel->level;
            }
        }

        if(empty($eventLevels)){
            echo json_encode([]);
            exit;
        }

        $eventLevels = implode(',', $eventLevels);

        $where = sprintf(' level IN (%s) ', $eventLevels);

        if ((int)$accountId > 0) {
            // with account
            $where .= ' AND (account_id = '.$accountId. ' OR (account_id = 0 OR account_id IS NULL))';
        } else {
            $where .= ' AND (account_id = 0 OR account_id IS NULL) ';
        }

        if(!empty($_GET['query'])){
            $where .= " AND Listing.`title` LIKE '%".$_GET['query']."%' ";
        }

        $query = "
            SELECT id, title
            FROM Event
            WHERE {$where}
            ORDER BY title
            LIMIT 1000
        ";

        $statement = $connection->prepare($query);

        foreach ($queryParams as $key => $value) {
            $statement->bindValue($key, $value);
        }

        $statement->execute();
        $result = $statement->fetchAll();

        if (empty($result)) {
            return new JsonResponse($response);
        }

        $response = ['data' => []];
        foreach ($result as $event) {
            $response['data'][] = [
                'label' => $event['title'],
                'id'    => $event['id'],
            ];
        }

        return new JsonResponse($response);
    }
}
