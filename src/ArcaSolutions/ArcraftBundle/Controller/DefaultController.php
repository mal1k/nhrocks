<?php

namespace ArcaSolutions\ArcraftBundle\Controller;

use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('@Arcraft/index.html.twig');
    }

    /**
     * @param $widgetName
     * @param $type
     * @param $module
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderWidgetAction($widgetName, $type, $module)
    {
        if($module){
            $this->get('widget.service')->setModule($module);
        }

        $template = $this->container->get('arcraft.service')->getTemplateByName($widgetName,$type);
        $widgetFile = key($template);
        $widgetValue = current($template);

        return $this->render('::arcraft.html.twig', [
            'widgetContent' => $this->renderView($widgetFile, $widgetValue),
            'templateName'  => $widgetFile
        ]);
    }

    public function dictionaryAction()
    {
        return $this->render('ArcraftBundle::dictionary.html.twig');
    }

    /**
     * @param $cardName
     * @param $columnQuantity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderCardAction($cardName, $columnQuantity)
    {
        $cardBlock = $this->container->get('arcraft.service')->getCardByName($cardName, $columnQuantity);

        return $this->render('::arcraft.html.twig', [
            'widgetContent' => $cardBlock,
            'templateName'  => 'widgets/cards/cards.html.twig'
        ]);
    }
}
