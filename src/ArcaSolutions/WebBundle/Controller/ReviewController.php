<?php

namespace ArcaSolutions\WebBundle\Controller;

use ArcaSolutions\CoreBundle\Form\Type\CaptchaType;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\WebBundle\Form\Type\ReviewsType;
use ArcaSolutions\WebBundle\Services\TimelineHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ReviewController
 *
 * @package ArcaSolutions\WebBundle\Controller
 */
class ReviewController extends Controller
{
    /**
     * Rate a review: adds like or dislike
     *
     * @param $type
     * @param $id
     *
     * @return Response
     */
    public function rateAction($type, $id)
    {
        /* gets review */
        $review = $this->get('doctrine')->getRepository('WebBundle:Review')->find($id);

        /* if it was not found returns an error */
        if (!$review) {
            $response = new Response();
            $response->setStatusCode(503);

            return $response->send();
        }

        /* gets IP and keep it ready to use */
        $userIP = '||'.$this->get('request')->getClientIp().'||';

        /* Like type */
        if ('like' == $type) {
            /* verify if user already voted using its IP */
            if (false !== strpos($review->getLikeIps(), $userIP)) {
                /* returns message */
                return JsonResponse::create([
                    'status'  => 0,
                    'message' => $this->get('translator')->trans('Already voted'),
                ]);
            }

            if (false !== strpos($review->getDislikeIps(), $userIP)) {
                /* workaround to not leave comma in column */
                $ip_removed = str_replace(','.$userIP, '', $review->getDislikeIps());
                $ip_removed = str_replace($userIP, '', $ip_removed);
                $review->setDislikeIps($ip_removed);

                /* decrease dislike */
                $review->setDislike($review->getDislike() - 1);
            }

            /* saves quantity of likes */
            $review->setLike($review->getLike() + 1);
            /* adding IP */
            /* saves user IP following edirectory pattern */
            $ip = $userIP;
            /* concatenates with olders IPs */
            if ($review->getLikeIps()) {
                $ip = $review->getLikeIps().','.$ip;
            }
            /* sets IP */
            $review->setLikeIps($ip);
        }

        if ('dislike' == $type) {
            /* verify if user already voted using its IP */
            if (false !== strpos($review->getDislikeIps(), $userIP)) {
                /* returns message */
                return JsonResponse::create([
                    'status'  => 0,
                    'message' => $this->get('translator')->trans('Already voted'),
                ]);
            }

            if (false !== strpos($review->getLikeIps(), $userIP)) {
                /* workaround to not leave comma in column */
                $ip_removed = str_replace(','.$userIP, '', $review->getLikeIps());
                $ip_removed = str_replace($userIP, '', $ip_removed);
                $review->setLikeIps($ip_removed);

                /* decrease like */
                $review->setLike($review->getLike() - 1);
            }

            /* saves quantity of dislikes */
            $review->setDislike($review->getDislike() + 1);
            /* adding IP */
            /* following edir pattern */
            $ip = $userIP;
            /* concatenates with olders IPs */
            if ($review->getDislikeIps()) {
                $ip = $review->getDislikeIps().','.$ip;
            }
            /* sets IP */
            $review->setDislikeIps($ip);
        }

        /* prepares save */
        $this->get('doctrine')->getManager()->persist($review);
        /* executes save */
        $this->get('doctrine')->getManager()->flush();

        /* Adds to sitemanager's timeline */
        $this->get('timelinehandler')->add(
            $review->getId(),
            TimelineHandler::ITEMTYPE_REVIEW,
            TimelineHandler::ACTION_NEW
        );

        return JsonResponse::create([
            'status'  => 1,
            'message' => $this->get('translator')->trans('Voted'),
            'like'    => $review->getLike(),
            'dislike' => $review->getDislike(),
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param bool $ajax
     * @return Response
     * @throws \Exception
     */
    public function addReviewAction(Request $request, $id = '', $ajax = false)
    {

        $reviewHandler = $this->get('review.handler');

        $userId = $request->getSession()->get('SESS_ACCOUNT_ID');

        $forceLogin = $reviewHandler->forceLogin();

        if ($userId === null && $forceLogin) {
            return new JsonResponse([
                'status'  => 'login',
            ]);
        }

        if ($ajax) {
            $response = [];

            $item = $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->find($id);

            $listingLevel = ListingLevelFeatures::normalizeLevel($item->getLevelObj(), $this->container->get('doctrine'));

            if ($listingLevel->hasCoverImage && $item->getCoverImage()) {
                if(!empty($item->getCoverImage()->getUnsplash())) {
                    $response['item']['coverImage'] = $item->getCoverImage()->getUnsplash();
                } else {
                    $response['item']['coverImage'] = $this->container->get('templating.helper.assets')
                        ->getUrl($this->container->get('imagehandler')->getPath($item->getCoverImage()), 'domain_images');
                }
            }

            if ($listingLevel->hasLogoImage && $item->getLogoImage()) {
                $imagine_filter = $this->container->get('liip_imagine.cache.manager');
                $imagePath = $this->container->get('templating.helper.assets')
                    ->getUrl($this->container->get('imagehandler')->getPath($item->getLogoImage()), 'domain_images');
                $response['item']['logoImage'] = $imagine_filter->getBrowserPath($imagePath, 'logo_icon_3');
            }

            $item->getTitle() and $response['item']['title'] = $item->getTitle();
            $item->getId() and $response['item']['actionUrl'] = $this->container->get('router')->generate('web_add_review', ['id' => $item->getId()]);

            return JsonResponse::create($response);
        }

        $response = [
            'status'  => false
        ];

        $review = null;
        $memberAccount = null;

        if ($userId && $id) {
            $memberAccount = $this->getDoctrine()->getRepository('WebBundle:Accountprofilecontact')->find($userId);

            $review = $this->getDoctrine()->getRepository('WebBundle:Review')->findOneBy([
                'memberId' => $userId,
                'itemType' => 'listing',
                'itemId'   => $id,
            ]);

            if ($review) {
                $response['content'] = $this->get('translator')->trans('You already reviewed this.');
            }
        }

        $form = $this->createForm(ReviewsType::class, null, ['member' => $memberAccount ? true : false]);

        /* Adds a captcha if not exist user logged */
        if (!$userId) {

            if ($this->container->get('settings')->getDomainSetting('google_recaptcha_status') === 'on') {
                $options = [];
            } else {
                $options = [
                    'reload' => true,
                    'as_url' => true,
                ];
            }

            $form->add('reviewCaptcha', CaptchaType::class, $options);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()) {
                try {
                    if (!$id) {
                        return new JsonResponse($response);
                    }

                    $reviewHandler->save('listing', $id, $memberAccount, $form->getData());

                    $response = [
                        'status'  => true,
                        'content' => $reviewHandler->successMessage(),
                    ];
                } catch (\Exception $e) {
                    $logger = $this->get('logger');
                    $logger->addError('Add new Review: '.$e->getMessage());
                    !$review and $response['content'] = $this->get('translator')->trans('An error occurred, try again');
                }
            } else {
                $response = [
                    'status' => false,
                    'error'  => $this->get('translator')->trans(/** @Ignore */  $form->getErrors(true)->current()->getMessage(), [], 'administrator')
                ];
            }

            return new JsonResponse($response);
        }

        return new JsonResponse($response);
    }
}
