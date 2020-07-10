<?php

namespace ArcaSolutions\WebBundle\Tests\Mixpanel;

use ArcaSolutions\WebBundle\Mixpanel\MixpanelFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MixpanelFactoryTest extends KernelTestCase
{
    /** @var MixpanelFactory */
    private $mixpanelFactory;

    public function testMixpanelFactoryInstance()
    {
        $this->assertInstanceOf(MixpanelFactory::class, $this->mixpanelFactory);
    }

    public function testCreateMixpanel()
    {
        $mixpanel = $this->mixpanelFactory->createMixpanel();

        $this->assertInstanceOf(MixpanelFactory::class, $mixpanel);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        static::bootKernel();

        $container = static::$kernel->getContainer();

        $this->mixpanelFactory = $container->get('mixpanel.factory');


    }
}