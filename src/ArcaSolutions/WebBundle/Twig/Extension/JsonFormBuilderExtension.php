<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use ArcaSolutions\WebBundle\Form\Builder\JsonFormBuilder;
use ArcaSolutions\WebBundle\Services\LeadHandler;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;

class JsonFormBuilderExtension extends \Twig_Extension
{
    const NAME = 'leadgen';

    /** @var JsonFormBuilder */
    private $jsonFormBuilder;
    /** @var RouterInterface */
    private $router;

    public function __construct(JsonFormBuilder $jsonFormBuilder, RouterInterface $router)
    {
        $this->jsonFormBuilder = $jsonFormBuilder;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('json_form_builder_get_form', [$this, 'getForm'])
        ];
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param $id
     * @return FormView
     */
    public function getForm($id)
    {
        $form = $this->jsonFormBuilder->generate(null, sprintf('save_%d.json', $id), self::NAME);

        return $form->createView();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
