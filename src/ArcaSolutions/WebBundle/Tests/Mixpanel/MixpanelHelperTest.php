<?php

namespace ArcaSolutions\WebBundle\Tests\Mixpanel;

use ArcaSolutions\WebBundle\Mixpanel\MixpanelHelper;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MixpanelHelperTest extends KernelTestCase
{
    /** @var MixpanelHelper */
    private $mixpanel;

    public function testCreateProfile()
    {
        $this->assertInstanceOf(MixpanelHelper::class, $this->mixpanel);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();

        $this->mixpanel = $container->get('mixpanel.helper');
    }
}