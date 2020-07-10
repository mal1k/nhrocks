<?php

namespace ArcaSolutions\ImportBundle\Services;

use ArcaSolutions\CoreBundle\Collections\CollectionInterface;
use ArcaSolutions\CoreBundle\Collections\CsvAnalyzer;
use ArcaSolutions\CoreBundle\Collections\XlsAnalyzer;
use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\ImportBundle\Annotation\Import;
use ArcaSolutions\ImportBundle\Exception\ImportException;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class Extractor
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Services
 * @since 11.3.00
 */
class Extractor
{

    /**
     * @var CollectionInterface
     */
    protected $collection;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;

    /**
     * @var \ReflectionProperty[]
     */
    protected $properties;

    /**
     * @var array
     */
    protected $propertiesAlias;

    /**
     * @var array
     */
    protected $propertiesDate;

    /**
     * @var \ArrayIterator
     */
    protected $violations;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var \ArrayIterator
     */
    protected $items;

    /**
     * @var array
     */
    private $columnHeaders;

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * Extractor constructor.
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ValidatorInterface $validator
     * @param Settings $settings
     */
    public function __construct(ValidatorInterface $validator, Settings $settings)
    {
        $this->reset();

        /* Create a new instance to validator */
        $this->validator = $validator;

        $this->dateFormat = $settings->getDomainSetting('date_format');
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function reset()
    {
        unset($this->violations);
        $this->violations = new \ArrayIterator();

        unset($this->items);
        $this->items = new \ArrayIterator();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param null $fileObject
     * @param bool $hasHeader
     * @param $separator
     * @return $this
     */
    public function fromCsvFile($fileObject = null, $hasHeader = true, $separator)
    {
        $this->collection = new CsvAnalyzer($fileObject, $separator);
        if ($hasHeader) {
            $this->collection->setHeaderRowNumber(0);
        }
        $this->columnHeaders = $this->collection->getColumnHeaders();

        return $this;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param null $fileObject
     * @param bool $hasHeader
     * @return $this
     */
    public function fromXlsFile($fileObject = null, $hasHeader = true)
    {
        $this->collection = new XlsAnalyzer($fileObject, true, 0, $hasHeader);
        if ($hasHeader) {
            $this->collection->setHeaderRowNumber(0);
        }
        $this->columnHeaders = $this->collection->getColumnHeaders();

        return $this;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $classType The entity to work on the extractor (Listing|Event|Classified)
     * @return $this
     */
    public function setClassType($classType)
    {
        $this->reflectionClass = new \ReflectionClass($classType);

        $properties = $this->reflectionClass->getProperties();
        $propertiesAlias = [];
        $propertiesDate = [];

        $reader = new AnnotationReader();

        $unlikeProperties = [];
        foreach ($properties as $property) {
            $annotation = $reader->getPropertyAnnotation($property, Import::class);
            if ($annotation != null) {
                $propertiesAlias[$property->name] = $annotation->name;
                if ($annotation->isDate) {
                    $propertiesDate[] = $property->name;
                }
            } else {
                $unlikeProperties[] = $property;
            }
        }

        $this->properties = array_diff($properties, $unlikeProperties);
        $this->propertiesAlias = $propertiesAlias;
        $this->propertiesDate = $propertiesDate;

        return $this;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function getClassType()
    {
        return $this->reflectionClass->getName();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return array
     */
    public function getClassPropertiesAlias()
    {
        return $this->propertiesAlias;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param array $mapping
     * @return $this
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return mixed
     */
    public function getColumnHeaders()
    {
        return $this->columnHeaders;
    }

    /**
     * Gets all data after extracting and validating
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return \ArrayIterator
     */
    public function getExtractItems()
    {
        if ($this->items->count() == 0) {
            $this->process($this->mapping);
        }

        return $this->items;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param array $mapping File mapping
     */
    public function process(array $mapping)
    {
        foreach ($this->collection as $index => $line) {
            $object = $this->reflectionClass->newInstance();
            foreach ($mapping as $column => $propertyName) {
                try {
                    $propertyValue = trim($line[$column]);

                    if ($this->isDate($propertyName)) {
                        $propertyValue = $this->convertIntToDate($propertyValue);
                    }

                    $property = $this->getPropertyByName($propertyName);
                    $property->setAccessible(true);
                    $property->setValue($object, $propertyValue);
                    $property->setAccessible(false);
                    unset($propertyValue);
                } catch (ImportException $e) {
//                    $this->exceptions->attach($e);
                }
            }

            if ($this->validationRow($object, $index)) {
                $this->items->append($object);
            }

            unset($object);
        }
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $field
     * @return bool
     */
    private function isDate($field)
    {
        return in_array($field, $this->propertiesDate);
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param int $value
     * @return false|string
     */
    private function convertIntToDate($value)
    {
        /* Checks if the value is an integer */
        if (is_numeric($value)) {
            $value = ($value - 25569) * 86400;
            $value = gmdate($this->dateFormat, $value);
        }

        return $value;
    }

    /**
     * Get the property name of the Class
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string|null $name The property name
     *
     * @return \ReflectionProperty
     * @throws ImportException
     */
    protected function getPropertyByName($name = null)
    {
        foreach ($this->properties as $property) {
            if ($property->name == $name) {
                return $property;
            }
        }

        throw new ImportException(sprintf("Property with name %s not found", $name), 404);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param object $object Object referring to the line to be validated
     * @param integer $row The line of the file being validated
     *
     * @return bool
     */
    protected function validationRow($object, $row)
    {
        /* Validates item */
        $violations = $this->validator->validate($object);

        /* @var ConstraintViolation $violation */
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $this->violations->append([
                    'code'    => $violation->getCode(),
                    'message' => $violation->getMessage(),
                    'line'    => $row,
                ]);
            }

            return false;
        }

        return true;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return \ArrayIterator
     */
    public function getExtractErrors()
    {
        return $this->violations;
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        return $this->collection->getFields();
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     *
     * @return int
     */
    public function getTotalRows()
    {
        return $this->collection->count();
    }
}
