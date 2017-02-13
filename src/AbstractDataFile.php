<?php

/*
 * This file is part of the ClinicLE package.
 *
 * (c) Rob Free <rob@clinicle.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ClinicLE\DataFile;

use ClinicLE\Common\Log;
use ClinicLE\DataFile\Exception\HeadingsNotDefinedException;
use ClinicLE\DataFile\Exception\DuplicateHeadingException;
use ClinicLE\DataFile\Exception\FileNotOpenedException;


/**
 * Abstract class for row-based DataFile loading classes
 * @package ClinicLE\DataFile
 * @author Rob Free
 */

abstract class AbstractDataFile
{
    protected $dataItems = array();
    private $fileName = null;
    private $headings = null;
    private $limit = null;
    protected $rowCount = 0;
    protected $trimFields = false;
    protected $nonEmptyHeadingCount=0;
    protected $currRow = 0;
    private $opened=false;
    private $ignoreEmptyHeadings=false;

    /**
     * Constructor sets up {@link $dataItems} if given $fileName.
     *
     * @param string $fileName
     * @param array $headings
     * @throws DuplicateHeadingException
     */
    public function __construct($fileName = null, array $headings = null)
    {
        $this->setFileName($fileName);

        if ($headings!==null) {
            $this->setHeadings($headings);
        }
    }

    /**
     * Set data items in DataFile
     * @param array $dataItems
     */
    public function setDataItems(array $dataItems)
    {
        $this->dataItems = $dataItems;
    }

    /**
     * Get data items in DataFile
     * @return array
     */
    public function getDataItems()
    {
        return $this->dataItems;
    }

    /**
     * Set array of headings matching columns. An exception is thrown if there is a duplicate heading.
     * @param array $headings
     * @throws DuplicateHeadingException
     */
    public function setHeadings(array $headings)
    {
        $uniqueHeadings = array();
        foreach ($headings as $heading) {
            if($this->ignoreEmptyHeadings && strlen($heading)==0) {
                continue;
            }
            if (isset($uniqueHeadings[$heading])) {
                throw new DuplicateHeadingException(
                    "Column '$heading' appears multiple times in file (".$this->getFileName().")"
                );
            }
            $uniqueHeadings[$heading]=true;
        }

        $this->headings = $headings;
    }

    /**
     * Get array of headings
     * @return array
     */
    public function getHeadings()
    {
        return $this->headings;
    }

    /**
     * Get filename
     * @return null
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set filename
     * @param $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    private $headerRow = false;

    /**
     * Returns true if file has header row
     * @return bool
     */
    public function hasHeaderRow()
    {
        return $this->headerRow;
    }

    /**
     * Set to true if file has header row
     * @param $headerRow
     */
    public function setHeaderRow($headerRow)
    {
        $this->headerRow = $headerRow;
    }

    /**
     * Get number of rows in file
     * @return int
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * Set row number limit
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Get row number limit
     * @return null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Load the data file rows into an array of associative arrays accessible through getDataItems()
     * and close the file.
     */
    public function load()
    {
        $this->open();

        while (($item = $this->nextRow()) !== false) {
            if ($item===null) {
                continue;
            }
            $this->dataItems[] = $item;
        }
        $this->close();
    }

    /**
     * Open the file and set up headings if appropriate
     * @throws HeadingsNotDefinedException
     */
    public function open()
    {
        $this->openFile();

        if ($this->hasHeaderRow()===false && $this->getHeadings()===null) {
            throw new HeadingsNotDefinedException("No header row was defined and no headings were preset");
        }
        $this->opened=true;
        if (!$this->hasHeaderRow()) {
            $this->updateNonEmptyHeadingCount();
            return;
        }

        $this->populateHeadings();
        $this->updateNonEmptyHeadingCount();
    }
    private function updateNonEmptyHeadingCount()
    {
        foreach ($this->headings as $heading) {
            if (strlen($heading) == 0) {
                continue;
            }
            $this->nonEmptyHeadingCount++;
        }
    }
    abstract protected function populateHeadings();
    abstract protected function openFile();

    abstract public function close();
    abstract protected function retrieveNextRow();

    /**
     * Return row as an associative array, null if all items in the row are empty, or false if at the end of the file.
     * Throws exception if file has not been previously opened.
     * @return array|bool|null
     * @throws FileNotOpenedException
     * @throws \Exception
     */
    public function nextRow()
    {
        if (!$this->opened) {
            throw new FileNotOpenedException("DataFile '".$this->getFileName()."' has not been opened");
        }
        if ($this->isLastRow()) {
            return false;
        }
        $row = $this->retrieveNextRow();
        $this->currRow++;

        if($row===array("")) {
            return $this->nextRow();
        }
        if ($row===null) {
            return $this->nextRow();
        }
        if($row===false) {
            return $row;
        }

        $emptyCells=0;
	
        foreach ($row as $item) {
            if (strlen($item) == 0) {
                $emptyCells++;
            }
        }

        if ($emptyCells >= $this->nonEmptyHeadingCount) {
            return $this->nextRow();
        }
        if (!$this->trimFields) {
            return $row;
        }

        return array_map('trim', $row);
    }

    /**
     * Reset the file by reopening it
     * @throws HeadingsNotDefinedException
     */
    public function reset()
    {
        $this->rowCount = 0;
        $this->open();
    }

    private function isLastRow()
    {
        if ($this->getLimit()!==null && $this->currRow >= $this->getLimit()) {
            return true;
        }
        if ($this->currRow>$this->rowCount) {
            return true;
        }
        return false;
    }

    /**
     * Set to true to auto-trim spaces from data in fields.
     *
     * @param bool $trimFields
     */
    public function setTrimFields($trimFields)
    {
        $this->trimFields = $trimFields;
    }

    /**
     * @return boolean
     */
    public function doIgnoreEmptyHeadings()
    {
        return $this->ignoreEmptyHeadings;
    }

    /**
     * @param boolean $ignoreEmptyHeadings
     */
    public function setIgnoreEmptyHeadings($ignoreEmptyHeadings)
    {
        $this->ignoreEmptyHeadings = $ignoreEmptyHeadings;
    }

}
