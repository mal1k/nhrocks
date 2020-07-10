<?php

namespace ArcaSolutions\WebBundle\Controller;

use ArcaSolutions\CoreBundle\Form\Type\CaptchaType;
use ArcaSolutions\WebBundle\Form\Type\EnquireType;
use ArcaSolutions\WebBundle\Services\LeadHandler;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\MapHelperBuilder;
use Ivory\GoogleMap\Overlay\Icon;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Overlay\Marker;
use Ivory\GoogleMap\Base\Coordinate;

class ContactusController extends Controller
{
    public function indexAction(Request $request)
    {
        /* Creates a new form */
        $form = $this->createForm(EnquireType::class);

        /* Adds a captcha if not exist user logged */
        if (null === $request->getSession()->get('SESS_ACCOUNT_ID')) {

            if ($this->container->get('settings')->getDomainSetting('google_recaptcha_status') === 'on') {
                $options = [];
            } else {
                $options = [
                    'reload' => true,
                    'as_url' => true,
                ];
            }

            $form->add('captcha', CaptchaType::class, $options);
        }

        /* Translator Instance */
        $translator = $this->get('translator');

        $customForm = $this->get('web.json_form_builder');
        $customForm->generate($form, 'save.json', 'contact');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Send Mail for Admins */
            $name = $form->get('firstname')->getData().' '.$form->get('lastname')->getData();
            $subject = '['.$this->get('multi_domain.information')->getTitle().'] '.$translator->trans('New Inquire');
            $message = $customForm->serialize($form);

            try {
                /* Gets Contact Us emails from sitemgr */
                $sendTo = explode(',', $this->getDoctrine()
                    ->getRepository('WebBundle:Setting')
                    ->getSetting('sitemgr_contactus_email'));

                $this->get('core.mailer')->newMail()
                    ->setSubject($subject.' - '.$form->get('subject')->getData())
                    ->setTo($sendTo, null, true)
                    ->setReplyTo($form->get('email')->getData(), $name)
                    ->setBody($this->renderView('::mailer/contactus.html.twig', [
                        'firstname' => $form->get('firstname')->getData(),
                        'lastname'  => $form->get('lastname')->getData(),
                        'email'     => $form->get('email')->getData(),
                        'phone'     => $form->get('phone')->getData(),
                        'message'   => $form->get('message')->getData(),
                        'fields'    => $customForm->getFieldsWithValues($form),
                    ]), 'text/html')
                    ->send();

                /* Creates a lead */
                $this->get("leadhandler")->add(
                    LeadHandler::ITEMTYPE_GENERAL,
                    0,
                    $form->get('firstname')->getData(),
                    $form->get('lastname')->getData(),
                    $form->get('email')->getData(),
                    $form->get('phone')->getData(),
                    $form->get('subject')->getData(),
                    $message
                );

                $translator = $this->get('translator');

                $this->addFlash('notice', [
                    'alert'   => 'success',
                    'title'   => $translator->trans('Success!'),
                    'message' => $translator->trans('Thank you, we will be in touch shortly.'),
                ]);

                return $this->redirectToRoute('web_contactus');
            } catch (\Exception $e) {
                $this->get("logger")->addError($e->getMessage(), ['exception' => $e]);
                $this->addFlash('notice', [
                    'alert'   => 'danger',
                    'title'   => $translator->trans('Error'),
                    'message' => $translator->trans("We couldn't deliver your message, please contact the administrator. Sorry for the inconvenience."),
                ]);
            }
        }

        /* Get twig */
        $twig = $this->container->get("twig");

        /* Settings Map */
        if ($this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
            and $contact_latitude = $this->container->get('settings')->getDomainSetting('contact_latitude')
            and $contact_longitude = $this->container->get('settings')->getDomainSetting('contact_longitude')
            and $googleMapsKey = $this->container->get('settings')->getDomainSetting('google_api_key')
        ) {
            /* New map defined */
            $map = new Map();
            $map->setStylesheetOptions([
                'width'  => '98%',
                'height' => '240px',
            ]);
            $domain = $this->get('multi_domain.information')->getId();
            $theme = lcfirst($this->get('theme.service')->getSelectedTheme()->getTitle());
            $defaultIconPath = '/assets/' . $theme . '/icons/listing.svg';
            $customIconPath = 'custom/domain_' . $domain . '/theme/' . $theme . '/icons/listing.svg';

            $mapZoom = ($this->container->get('settings')->getDomainSetting('contact_mapzoom') ? $this->container->get('settings')->getDomainSetting('contact_mapzoom') : 15);
            $map->setMapOption('zoom', (int) $mapZoom);

            /* sets the item's location the center of the map */
            $map->setCenter(new Coordinate((float) $contact_latitude, (float) $contact_longitude));

            $marker = new Marker(new Coordinate((float) $contact_latitude, (float) $contact_longitude, true));

            /* mark item in map */
            $marker->setOptions([
                'clickable' => false,
                'flat'      => true,
            ]);

            if (file_exists($customIconPath)) {
                $iconPath = '/' . $customIconPath;
            } else {
                $iconPath = $defaultIconPath;
            }

            $marker->setIcon(new Icon($this->container->get('request')->getSchemeAndHttpHost() . '/' . $iconPath));

            $map->getOverlayManager()->addMarker($marker);

            $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
            $apiHelper = ApiHelperBuilder::create()->setKey($googleMapsKey)->build()->render([$map]);

            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSBlock('::js/summary/map.html.twig');
            $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
            $jsHandler->addTwigParameter('apiHelper', $apiHelper);
            $twig->addGlobal('map', $map);
        }

        $this->get('widget.service')->setModule('');

        $contact = [
            'company' => $this->container->get('settings')->getDomainSetting('contact_company'),
            'address' => $this->container->get('settings')->getDomainSetting('contact_address'),
            'zipcode' => $this->container->get('settings')->getDomainSetting('contact_zipcode'),
            'country' => $this->container->get('settings')->getDomainSetting('contact_country'),
            'state'   => $this->container->get('settings')->getDomainSetting('contact_state'),
            'city'    => $this->container->get('settings')->getDomainSetting('contact_city'),
            'phone'   => $this->container->get('settings')->getDomainSetting('contact_phone'),
            'email'   => $this->container->get('settings')->getDomainSetting('contact_email'),
            'mapzoom' => $this->container->get('settings')->getDomainSetting('contact_mapzoom'),
        ];

        $twig->addGlobal('form', $form->createView());
        $twig->addGlobal('contact', $contact);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::CONTACT_US_PAGE);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }
}
