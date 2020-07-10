<?php

namespace ArcaSolutions\ImportBundle\File;

use ArcaSolutions\ImportBundle\Annotation\Import;
use ArcaSolutions\ImportBundle\Exception\InvalidModuleException;
use ArcaSolutions\ImportBundle\Services\ImportService;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Class Mapping
 *
 * @author Diego de Biagi <diego.biagi@arcasolutions.com>
 * @since 11.3.00
 */
class Mapping
{
    /** @var ImportService */
    private $importService;

    /** @var array */
    private $requiredFields;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
        $this->initialize();
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @throws InvalidModuleException
     */
    private function initialize()
    {
        $reader = new AnnotationReader();

        foreach (ImportService::MODULES as $module) {
            $reflectionClass = new \ReflectionClass($this->importService->getClassTypeByString($module));
            $properties = $reflectionClass->getProperties();

            foreach ($properties as $property) {
                $annotation = $reader->getPropertyAnnotation($property, Import::class);

                if($annotation && $annotation->mappingRequired == true) {
                    $this->requiredFields[$module][$property->getName()] = $annotation->name;
                }
            }
        }
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @param $module
     * @return array
     */
    public function getRequiredFields($module)
    {
        return $this->requiredFields[$module];
    }

}