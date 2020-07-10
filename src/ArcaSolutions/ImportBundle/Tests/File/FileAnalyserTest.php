<?php

namespace ArcaSolutions\ImportBundle\Tests\File;

use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\File\FileAnalyser;
use ArcaSolutions\ImportBundle\Services\Extractor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class FileAnalyserTest
 *
 * @author Diego de Biagi <diego.biagi@arcasolutions.com>
 * @since 11.3.00
 */
class FileAnalyserTest extends WebTestCase
{
    /** @var  Extractor */
    private $extractor;

    /** @var \SplFileObject */
    private $xlsFile;

    /**
     * Required fields mapped but other fields has no value
     */
    const WARNING_MAPPING = [
        11 => "listingTitle"
    ];

    /**
     * Required fields not mapped
     */
    const ERROR_MAPPING = [
        14 => "listingEmail",
    ];

    protected function setUp()
    {
        self::bootKernel();
        $this->extractor = self::$kernel->getContainer()->get('import.extractor');
        $this->xlsFile = new \SplFileObject(__DIR__ . '/../Fixtures/listing_with_headers.xls');
    }

    protected function tearDown()
    {
        if($this->extractor !== null) {
            $this->extractor->reset();
        }

        parent::tearDown();
    }

    public function testAnalyse()
    {
        $flags = [
            'warning' => self::WARNING_MAPPING,
            'error' => self::ERROR_MAPPING
        ];

        foreach ($flags as $flag => $mapping) {
            $this->analyseShouldReturnCorrectFlag($flag, $mapping);
        }
    }

     private function analyseShouldReturnCorrectFlag($flag, $mapping) {
        $analyser = new FileAnalyser($this->extractor);
        $analyser->setHasHeader(true);
        $analyser->configure($this->xlsFile, $mapping, ListingImport::class)
            ->analyse();

        $this->assertEquals($flag, $analyser->getResult());
    }
}