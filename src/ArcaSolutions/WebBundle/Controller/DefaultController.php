<?php

namespace ArcaSolutions\WebBundle\Controller;

use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use ArcaSolutions\WebBundle\Entity\Quicklist;
use ArcaSolutions\WebBundle\Services\TimelineHandler;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class DefaultController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $searchEngine = $this->get('search.engine');
        $JSHandler = $this->get("javascripthandler");

        $JSHandler->addTwigParameter('geolocationCookieName', $searchEngine->getGeoLocationCookieName());

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::HOME_PAGE);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * Newsletter action
     *
     * Used to save news visitors
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function newsletterAction(Request $request)
    {
        // getting POST data
        $data = [
            'name'  => $request->get('name', ''),
            'email' => $request->get('email', ''),
        ];

        /* sets validators */
        $validator = Validation::createValidator();
        $constraint = new Assert\Collection([
            'email' => [
                new Assert\Email(),
                new Assert\NotBlank(),
            ],
            'name'  => [
                new Assert\NotBlank(),
            ],
        ]);
        $validation = $validator->validate($data, $constraint);

        if (count($validation) == 0) {

            // calling service
            $subscription = $this->get('subscription.mailer.service');
            $subscription->setAction('addSubscriber');
            $subscription->setSubscriberName($data['name']);
            $subscription->setSubscriberEmail($data['email']);
            $subscription->setSubscriberType('visitor');
            $subscription->sendSubscription();

            /* Creates sitemanager timeline entry */
            $this->container->get("timelinehandler")->add(
                0,
                TimelineHandler::ITEMTYPE_NEWSLETTER,
                TimelineHandler::ACTION_NEW
            );

            return JsonResponse::create([
                'success' => true,
                'message' => $this->get('translator')->trans('Check your e-mail to complete your subscription.'),
            ]);
        }

        $error = [];
        $error['success'] = false;
        for ($i = 0; $i < count($validation); $i++) {
            // getting field name
            preg_match('/[a-zA-Z]+/', $validation->get($i)->getPropertyPath(), $key);
            $key = current($key);

            // creating array of errors
            $error['errors'][] = [
                'field'   => $key,
                'message' => /** @Ignore */
                    $this->get('translator')->trans($validation->get($i)->getMessage(), [], 'validators'),
            ];
        }

        return JsonResponse::create($error);
    }

    /**
     * FAQ page
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function faqAction(Request $request)
    {
        $keyword = $request->query->get('keyword', '');

        if (empty($keyword)) {
            $faq = $this->get('doctrine')->getRepository('WebBundle:Faq')->findByFrontend('y');
        } else {
            $faq = $this->get('doctrine')->getRepository('WebBundle:Faq')->searchKeyword($keyword);
        }

        $this->get('widget.service')->setModule('');

        $twig = $this->container->get("twig");

        $twig->addGlobal('questions', $faq);
        $twig->addGlobal('keyword', $keyword);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::FAQ_PAGE);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function termsAction()
    {
        $this->get('widget.service')->setModule('');

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::TERMS_OF_SERVICE_PAGE);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function privacyAction()
    {
        $this->get('widget.service')->setModule('');

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::PRIVACY_POLICY_PAGE);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * Bookmark action, saves a item in a bookmark's list
     * Used in ajax
     *
     * @param Request $request
     * @param int $id
     * @param string $module This is being validated in the routing rules
     *
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     * @throws \Twig_Error
     */
    public function bookmarkAction(Request $request, $id, $module = '')
    {
        /* gets user Id using profile credentials */
        $userId = $request->getSession()->get('SESS_ACCOUNT_ID');

        if ($userId === null) {
            return new JsonResponse([
                'status'  => 'login',
            ]);
        }

        $item = new \Quicklist();

        try {
            $item->setAccountId($userId)
                ->setItemId($id)
                ->setItemType($module);
            $item->Add();
            $status = 'pinned!';
        } catch (\Exception $e) {
            $item->Delete();
            $status = 'unpinned!';
        }

        /*

        $item = $this->get('doctrine')->getRepository('WebBundle:Quicklist')->findOneBy([
            'accountId' => $userId,
            'itemId'    => $id,
            'itemType'  => $module,
        ]);

        try {
            $em = $this->get('doctrine')->getManager();

            if ($item === null) {
                $item = new Quicklist();
                $item->setAccountId($userId)
                    ->setItemId($id)
                    ->setItemType($module);

                $em->persist($item);
                $status = 'pinned';
            } else {

                $em->remove($item);

                $status = 'unpinned';
            }

            $em->flush();
        } catch (\Exception $e) {
            $status = 'fail';
        }

        */
        
        return JsonResponse::create([
            'status' => $status,
        ]);
    }
}
