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

use ClinicLE\Common\Exception\FileNotFoundException;

/**
 * DataFile class for loading delimited text files.
 *
 * @package ClinicLE\DataFile
 * @author  Rob Free
 */

class DelimitedDataFile extends AbstractDataFile
{
    protected $handle = null;
    private $fieldSep = ',';


    /**
     * Set the field separator character(s).
     *
     * @param $fieldSep
     */
    public function setFieldSep($fieldSep)
    {
        $this->fieldSep = $fieldSep;
    }

    /**
     * Close the data file handle.
     */
    public function close()
    {
        fclose($this->handle);
    }

    protected function populateHeadings()
    {
        $headings = fgetcsv($this->handle, 0, $this->fieldSep);
        $this->setHeadings($headings);
    }

    protected function openFile()
    {
        // used to allow \n line endings (PHP doesn't support this by default!)
        ini_set('auto_detect_line_endings', true);


        if (($this->handle = @fopen($this->getFileName(), 'r')) === false) {
            throw new FileNotFoundException("Unable to open form file '".$this->getFileName()."'");
        }
        $output = array();
        exec("cat {$this->getFileName()} | wc -l", $output);
        $this->rowCount = $output[0];
    }

    protected function retrieveNextRow()
    {
        // loop through each CSV separated line of file
        $csv_data = fgetcsv($this->handle, 0, $this->fieldSep);
        if (!$csv_data) {
            return null;
        }

        $row = array();
        // if headings map data as assoc array using $headings
        $headingCount = 0;

        foreach ($this->getHeadings() as $heading) {
            $row[$heading] = isset($csv_data[$headingCount]) ? $csv_data[$headingCount] : null;
            $headingCount++;
        }

        return $row;
    }
}
