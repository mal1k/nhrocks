<?php

namespace ArcaSolutions\CoreBundle\Reader;


/**
 * Class ExcelReadFilter
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since 11.3.00
 * @package ArcaSolutions\CoreBundle\Reader
 */
class ExcelReadFilter implements \PHPExcel_Reader_IReadFilter
{

    /**
     * @var array
     */
    private $columns = [];

    /**
     * @var integer
     */
    private $rowsLimit;

    /**
     * ExcelReadFilter constructor.
     *
     * @param string $fromColumn
     * @param string $toColumn
     * @param bool $hasHeader
     * @param int $rowsLimit
     */
    public function __construct($fromColumn, $toColumn, $hasHeader=true, $rowsLimit = 5000)
    {
        if ($hasHeader)
            $rowsLimit++;

        $this->rowsLimit = $rowsLimit;

        $toColumn++;
        while ($fromColumn !== $toColumn) {
            $this->columns[] = $fromColumn++;
        }
    }

    /**
     * Should this cell be read?
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param  string $column String column index
     * @param  integer $row Row index
     * @param  string $worksheetName Optional worksheet name
     * @return boolean
     */
    public function readCell($column, $row, $worksheetName = '')
    {
        if (in_array($column, $this->columns) && $row <= $this->rowsLimit) {
            return true;
        }

        return false;
    }
}
