<?php

namespace ArcaSolutions\CoreBundle\Collections;


/**
 * Class CsvAnalyzer
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @package ArcaSolutions\CoreBundle\Analyzers
 */
class CsvAnalyzer implements CollectionInterface, \SeekableIterator, \Countable
{

    /**
     * @var \SplFileObject
     */
    private $file;

    /**
     * @var integer
     */
    private $headerRowNumber;

    /**
     * @var array
     */
    private $columnHeaders;

    /**
     * @var integer
     */
    private $headersCount;

    /**
     * Total number of rows in the CSV file
     *
     * @var integer
     */
    private $count;

    /**
     * CsvAnalyzer constructor.
     *
     * @param \SplFileObject $fileObject File to be analyzed
     * @param string         $delimiter  Column delimiter
     * @param string         $enclosure  Text delimiter
     * @param string         $escape     Character to escape
     */
    public function __construct(\SplFileObject $fileObject, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        /* Detecting line break */
        ini_set('auto_detect_line_endings', true);

        $this->file = $fileObject;

        /* Defines the CSV Flags */
        $this->file->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::DROP_NEW_LINE
        );

        /* Defines the CSV Controls */
        $this->file->setCsvControl(
            $delimiter,
            $enclosure,
            $escape
        );
    }

    /**
     * Set column headers
     *
     * @param array $columnHeaders
     */
    public function setColumnHeaders(array $columnHeaders)
    {
        $this->columnHeaders = array_count_values($columnHeaders);
        $this->headersCount = count($columnHeaders);
    }

    /**
     * Set header row number
     *
     * @param integer $rowNumber Number of the row that constains column header names
     */
    public function setHeaderRowNumber($rowNumber)
    {
        $this->headerRowNumber = $rowNumber;
        $headers = $this->readHeaderRow($rowNumber);
        $this->setColumnHeaders($headers);
    }

    /**
     * Get column headers
     *
     * @return array
     */
    public function getColumnHeaders()
    {
        return array_keys($this->columnHeaders);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * If a header row has been set, the pointer is set just below the header row.
     */
    public function rewind()
    {
        $this->file->rewind();

        if (null !== $this->headerRowNumber) {
            $this->file->seek($this->headerRowNumber + 1);
        }
    }

    /**
     * Get the field (column, property) names
     *
     * @return array
     */
    public function getFields()
    {
        return $this->getColumnHeaders();
    }

    /**
     * {@inheritdoc}
     *
     */
    public function current()
    {
        return $this->file->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->file->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->file->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->file->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function seek($position)
    {
        $this->file->seek($position);
    }

    /**
     *  {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->count) {
            $position = $this->key();
            $this->count = iterator_count($this);
            $this->seek($position);
        }

        return $this->count;
    }

    /**
     * Read header row from CSV file
     *
     * @param integer $rowNumber Row number
     *
     * @return array
     */
    protected function readHeaderRow($rowNumber)
    {
        $this->file->seek($rowNumber);
        $headers = $this->file->current();

        return $headers;
    }
}
