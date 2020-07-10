<?php

namespace ArcaSolutions\CoreBundle\Collections;

use ArcaSolutions\CoreBundle\Reader\ExcelReadFilter;
use PHPExcel;


/**
 * Class XlsAnalyzer
 *
 * @package ArcaSolutions\CoreBundle\Collections
 */
class XlsAnalyzer implements CollectionInterface, \SeekableIterator, \Countable
{

    /**
     * @var array
     */
    protected $workSheet;

    /**
     * @var integer
     */
    protected $pointer = 0;

    /**
     * @var integer
     */
    protected $headerRowNumber;

    /**
     * @var array
     */
    protected $columnHeaders;

    /**
     * Total number of rows
     *
     * @var integer
     */
    protected $count;

    /**
     * XlsAnalyzer constructor.
     *
     * @param \SplFileObject $fileObject File to be analyzed
     * @param bool $readyOnly If set to false, the reader take care of the excel formatting (slow)
     * @param integer $activeSheet Index of active seet to read from
     * @param bool $hasHeader
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function __construct(\SplFileObject $fileObject, $readyOnly = true, $activeSheet = null, $hasHeader = true)
    {
        $filterSubset = new ExcelReadFilter('A', 'BQ', $hasHeader);

        /* @var \PHPExcel_Reader_Excel2007|\PHPExcel_Reader_Excel5 $reader */
        $reader = \PHPExcel_IOFactory::createReaderForFile($fileObject->getPathname());
        $reader->setReadFilter($filterSubset);
        $reader->setReadDataOnly($readyOnly);

        /* @var PHPExcel $excel */
        $excel = $reader->load($fileObject->getPathname());

        if (null !== $activeSheet) {
            $excel->setActiveSheetIndex($activeSheet);
        }

        /**
         * TODO Improves memory management, change this to use RowIterator can be a solution
         */
        $this->workSheet = $excel->getActiveSheet()->toArray();
    }

    /**
     * Set column headers
     *
     * @param array $columnHeaders
     */
    public function setColumnHeaders(array $columnHeaders)
    {
        $this->columnHeaders = $columnHeaders;
    }

    /**
     * Get column headers
     *
     * @return array
     */
    public function getColumnHeaders()
    {
        return $this->columnHeaders;
    }

    /**
     * Set header row number
     *
     * @param integer $rowNumber Number of the row that constains column header names
     */
    public function setHeaderRowNumber($rowNumber)
    {
        $this->headerRowNumber = $rowNumber;
        $this->columnHeaders = $this->workSheet[$rowNumber];
    }

    /**
     * Rewind the Iterator to the first element
     *
     * If a header row has been set, the pointer is set just below the header row.
     */
    public function rewind()
    {
        $this->pointer = $this->headerRowNumber + 1;

        if (null === $this->headerRowNumber) {
            $this->pointer = 0;
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
        return $this->workSheet[$this->pointer];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->pointer++;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return isset($this->workSheet[$this->pointer]);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($pointer)
    {
        $this->pointer = $pointer;
    }

    /**
     *  {@inheritdoc}
     */
    public function count()
    {
        $count = count($this->workSheet);
        if (null !== $this->headerRowNumber) {
            $count--;
        }

        return $count;
    }

    /**
     * Get a row
     *
     * @param integer $number
     *
     * @return array
     */
    public function getRow($number)
    {
        $this->seek($number);

        return $this->current();
    }
}
