<?php
namespace ArcaSolutions\WebBundle\Controller;

use ArcaSolutions\ClassifiedBundle\Entity\Internal\ClassifiedLevelFeatures;
use ArcaSolutions\CoreBundle\Form\Type\CaptchaType;
use ArcaSolutions\EventBundle\Entity\Internal\EventLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\WebBundle\Form\Type\SendMailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SendMailController extends Controller
{
    /**
     * Send email to the item owner (Listing, Classified, Event)
     *
     * @param Request $request
     * @param string $id
     * @param string $module
     *
     * @param bool $ajax
     * @return \Symfony\Component\HttpFoundation\Response|static
     * @throws \Exception
     */
    public function indexAction(Request $request, $id = '', $module = '', $ajax = false)
    {
        $translator = $this->get('translator');

        /* Default error response for this action */
        $response = [
            'status' => false,
            'title' => $translator->trans('Error'),
            'content' => $translator->trans('The item you are trying to contact does not exist.')
        ];

        if (is_numeric($id) && $id > 0) {
            $doctrine = $this->get('doctrine');

            switch ($module) {
                case 'listing':
                    $item = $doctrine->getRepository('ListingBundle:Listing')->find($id);
                    $itemLevel = ListingLevelFeatures::normalizeLevel($item->getLevelObj(), $this->container->get('doctrine'));
                    break;
                case 'event':
                    $item = $doctrine->getRepository('EventBundle:Event')->find($id);
                    $itemLevel = EventLevelFeatures::normalizeLevel($item->getLevelObj(), $this->container->get('doctrine'));
                    break;
                case 'classified':
                    $item = $doctrine->getRepository('ClassifiedBundle:Classified')->find($id);
                    $itemLevel = ClassifiedLevelFeatures::normalizeLevel($item->getLevelObj(), $this->container->get('doctrine'));
                    break;
                default:
                    $item = null;
                    $itemLevel = null;
                    break;
            }

            /* ModStores Hooks */
            HookFire("sendmail_controller_enhancedlead", [
                "item"   => &$item,
                "module" => &$module,
                "id"     => &$id,
            ]);

            if ($item) {
                if ($ajax) {
                    $response = [];

                    if ($itemLevel->hasCoverImage && $item->getCoverImage()) {
                        if(!empty($item->getCoverImage()->getUnsplash())) {
                            $response['item']['coverImage'] = $item->getCoverImage()->getUnsplash();
                        } else {
                            $response['item']['coverImage'] = $this->container->get('templating.helper.assets')
                                ->getUrl($this->container->get('imagehandler')->getPath($item->getCoverImage()), 'domain_images');
                        }
                    }

                    if ($item instanceof Listing && $itemLevel->hasLogoImage && $item->getLogoImage()) {
                        $imagine_filter = $this->container->get('liip_imagine.cache.manager');
                        $imagePath = $this->container->get('templating.helper.assets')
                            ->getUrl($this->container->get('imagehandler')->getPath($item->getLogoImage()), 'domain_images');
                        $response['item']['logoImage'] = $imagine_filter->getBrowserPath($imagePath, 'logo_icon_3');
                    }

                    $item->getTitle() and $response['item']['title'] = $item->getTitle();
                    $item->getId() and $response['item']['actionUrl'] = $this->container->get('router')->generate($module . '_sendmail', ['id' => $item->getId()]);

                    return JsonResponse::create($response);
                }

                $userId = $request->getSession()->get('SESS_ACCOUNT_ID');
                $memberAccount = null;

                if($userId) {
                    $memberAccount = $this->getDoctrine()->getRepository('WebBundle:Accountprofilecontact')->find($userId);
                }

                $form = $this->createForm(new SendMailType(), null, ['member' => $memberAccount]);

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

                    $form->add('sendEmailCaptcha', CaptchaType::class, $options);
                }

                $form->handleRequest($request);

                if ($form->isSubmitted()) {
                    if($form->isValid()) {

                        /* ModStores Hooks */
                        if (!HookFire("sendmail_enhancedlead", [
                            "item"       => &$item,
                            "form"       => &$form,
                            "module"     => &$module,
                            "id"         => &$id,
                            "response"   => &$response
                        ])) {
                            /* creating response default */
                            $response = [
                                'status'  => false,
                                'title'   => $translator->trans('Message'),
                                'content' => $translator->trans('We can not send your email. Try again, please.'),
                            ];

                            $send = $this->get('sendmail.module')->send($item, $form);
                            $send and $response = [
                                'status'  => true,
                                'title'   => $translator->trans('Message'),
                                'content' => $translator->trans('Your e-mail has been sent. Thank you.'),
                            ];

                        }
                    } else {
                        $response = [
                            'status' => false,
                            'error'  => $this->get('translator')->trans(/** @Ignore */  $form->getErrors(true)->current()->getMessage(), [], 'administrator')
                        ];
                    }
                }
            }
        }

        return JsonResponse::create($response);
    }
}
