<?php

namespace ArcaSolutions\ImportBundle\File;

use ArcaSolutions\ImportBundle\Services\Extractor;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Analyses import file and identify entity violations.
 *
 * @author Diego de Biagi <diego.biagi@arcasolutions.com>
 * @since 11.3.00
 */
class FileAnalyser
{
    const CRITICAL_ERRORS = [
        'L-00100',
    ];
    /** @var Extractor */
    private $extractor;
    /** @var string */
    private $separator = ',';
    /** @var boolean */
    private $hasHeader = true;
    /** @var array */
    private $errors;
    /** @var \ArrayIterator */
    private $rowsItems;

    public function __construct(Extractor $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * Analyse file to search for errors.
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @param \SplFileObject $file
     * @param array $mapping
     * @param string $classType
     * @return FileAnalyser
     * @throws \Exception When file extension is not supported
     */
    public function configure(\SplFileObject $file, $mapping, $classType)
    {
        switch ($file->getExtension()) {
            case 'csv':
                $this->extractor->fromCsvFile($file, $this->hasHeader, $this->separator);
                break;
            case 'xls':
            case 'xlsx':
                $this->extractor->fromXlsFile($file, $this->hasHeader);
                break;
            default:
                throw new \Exception('File extension not supported: '.$file->getExtension());
        }

        $this->extractor->setClassType($classType)
            ->setMapping($mapping);

        return $this;
    }

    public function analyse() {
        $this->rowsItems = $this->extractor->getExtractItems();

        $this->errors = $this->extractor->getExtractErrors()->getArrayCopy();
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * @param string $separator
     * @return FileAnalyser
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasHeader()
    {
        return $this->hasHeader;
    }

    /**
     * @param bool $hasHeader
     * @return FileAnalyser
     */
    public function setHasHeader($hasHeader)
    {
        $this->hasHeader = $hasHeader;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return string "success" | "warning"
     */
    public function getResult()
    {
        if ($this->getTotalValidItens() == 0) {
            return 'error';
        }

        return count($this->errors) > 0 ? 'warning' : 'success';
    }

    /**
     * Get total itens that will be imported
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @return int
     */
    public function getTotalValidItens()
    {
        return $this->extractor->getExtractItems()->count();
    }

    /**
     * Get total invalid itens
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @return int
     */
    public function getTotalInvalidItens() {
        $invalids = [];

        foreach ($this->errors as $error) {
            $invalids[] = $error['line'];
        }

        return count(array_unique($invalids));
    }

    /**
     * Resets analyser parameters
     */
    public function reset()
    {
        $this->separator = null;
        $this->hasHeader = false;
        $this->errors = [];
    }

    /**
     * Get total itens
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @return int
     */
    public function getTotalItens()
    {
        return $this->extractor->getTotalRows();
    }
}
