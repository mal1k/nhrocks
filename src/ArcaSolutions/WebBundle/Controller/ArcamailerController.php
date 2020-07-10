<?php

namespace ArcaSolutions\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ArcamailerController extends Controller
{
    public function registerAction(Request $request)
    {
        $arcamailer = $this->get('arcamailer.service');

        $name = $request->get('name');
        $email = $request->get('email');
        $country = $request->get('country');
        $timezone = $request->get('timezone');

        try {
            $customerId = $arcamailer->register($name, $email, $country, $timezone);

            return new JsonResponse([
                'success'    => true,
                'customerId' => $customerId,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }

    }

    public function loginAction(Request $request)
    {
        $arcamailer = $this->get('arcamailer.service');

        $email = $request->get('email');
        $password = $request->get('password');

        try {
            $customerId = $arcamailer->login($email, $password);

            return new JsonResponse(['success' => true, 'customerId' => $customerId]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function createListAction(Request $request)
    {
        $arcamailer = $this->get('arcamailer.service');
        $name = $request->get('name');

        try {
            $listId = $arcamailer->createList($name);

            return new JsonResponse(['success' => true, 'listId' => $listId]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false]);
        }
    }

    public function getInfoAction()
    {
        $info = $this->get('arcamailer.service')->getInfo();
        $trans = $this->get('translator');

        $timezones = sprintf('<option>%s</option>', $trans->trans('Timezone', [], 'messages'));
        foreach ($info['timezones'] as $timezone) {
            $timezones .= sprintf('<option value="%1$s">%1$s</option>', $timezone);
        }

        $countries = sprintf('<option>%s</option>', $trans->trans('Country', [], 'messages'));
        foreach ($info['contries'] as $country) {
            $countries .= sprintf('<option value="%1$s">%1$s</option>', $country);
        }

        return new JsonResponse([
            'timezones' => $timezones,
            'countries' => $countries,
        ]);
    }
}