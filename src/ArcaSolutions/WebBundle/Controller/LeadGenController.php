<?php

namespace ArcaSolutions\WebBundle\Controller;

use ArcaSolutions\WebBundle\Services\LeadHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LeadGenController extends Controller
{
    public function postLeadAction(Request $request)
    {
        $translator = $this->get('translator');
        $jsonFormBuilder = $this->get('web.json_form_builder');
        $widgetPageId = $request->get('widgetPageId', 0);
        $filename = sprintf('save_%d.json', $widgetPageId);

        $form = $jsonFormBuilder->generate(null, $filename);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $jsonFormBuilder->serialize($form);
            $sendTo = explode(',', $this->getDoctrine()
                ->getRepository('WebBundle:Setting')
                ->getSetting('sitemgr_email'));

            $this->get('leadhandler')->add(
                LeadHandler::ITEMTYPE_GENERAL,
                '', '', '', '', '', '',
                $message
            );

            $pageWidget = $this->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->find($widgetPageId);

            $this->get('core.mailer')->newMail()
                ->setTo($sendTo)
                ->setSubject($translator->trans(
                    'New lead through the %widget% widget',
                    ['%widget%' => $pageWidget->getWidget()->getTitle()]
                ))
                ->setBody($this->get('twig')->render('::mailer/leadgen.html.twig', [
                    'widgetName' => $pageWidget->getWidget()->getTitle(),
                    'fields'     => $jsonFormBuilder->getFieldsWithValues($form),
                ]), 'text/html')
                ->send();

            return new JsonResponse([
                'status'  => 'ok'
            ]);
        }

        return new JsonResponse([
            'errors' => $form->getErrors(true),
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

}
