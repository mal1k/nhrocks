<?php

namespace ArcaSolutions\ImageBundle\Tests\Services;

use ArcaSolutions\ImageBundle\Services\TransparencyChecker;

class TransparencyCheckerTest extends \PHPUnit_Framework_TestCase
{
    /** @var TransparencyChecker */
    private $transparencyChecker;

    public function setUp()
    {
        $this->transparencyChecker = new TransparencyChecker();
    }

    public function testImageWithoutTransparencyCheck() {
        $result = $this->transparencyChecker->hasTransparency(__DIR__ . '/image_without_transparency.png');

        $this->assertFalse($result);
    }

    public function testImageWithTransparencyCheck() {
        $result = $this->transparencyChecker->hasTransparency(__DIR__.'/image_with_transparency.png');

        $this->assertTrue($result);
    }
}