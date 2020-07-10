<?php


namespace ArcaSolutions\ApiBundle\Controller;

use ArcaSolutions\ApiBundle\Documents\GeneralDocument;
use ArcaSolutions\ApiBundle\Documents\ModuleLevelDocument;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\ListingItemDetail;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class DefaultV3Controller extends DefaultV2Controller
{
    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get listing  detail",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Return the listing detail",
     *       Codes::HTTP_NOT_FOUND = "Listing not found",
     *     },
     *     output={
     *       "class"="\ArcaSolutions\ListingBundle\Entity\Listing",
     *       "groups"={"listingDetail"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     *     parameters={
     *       {"name" = "account_id", "dataType" = "integer", "required" = false, "description" = "Account id of user", "format" = "\d+"},
     *     },
     *     requirements={
     *       {"name" = "listing", "dataType" = "integer", "description" = "Listing id", "requirement" = "\d+"},
     *     }
     * )
     *
     * @param Listing $listing
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     * @View(serializerGroups={"listingDetail", "listingDetailV3"})
     * @ParamConverter("listing", class="ListingBundle:Listing")
     */
    public function getListingAction(Listing $listing)
    {
        $return = parent::getListingAction($listing);

        $generalDocument = new GeneralDocument($this->container);
        $listingItemDetail = new ListingItemDetail($this->container, $listing);
        $moduleLevel = new ModuleLevelDocument('listing');

        $listing->setLogoImageUrl($generalDocument->getImagePath($listing->getLogoImage()));

        $listing = $moduleLevel->applyModuleLevel($listing, $listingItemDetail->getLevel());

        $newReturn = [
            'data' => $listing
        ];

        return array_merge($return, $newReturn);
    }


}
