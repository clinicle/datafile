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
 * DataFile class for loading Excel XLSX files.
 * @package ClinicLE\DataFile
 * @author Rob Free
 */

class ExcelDataFile extends AbstractDataFile
{
    private $columnCount;
    private $sheet;

    protected function retrieveNextRow()
    {
        $currColumn = 0;
        $row = array();

        $headings = $this->getHeadings();

        foreach ($headings as $heading) {
            if (strlen($heading) == 0) {
                $currColumn++;
                continue;
            }

            $value = $this->sheet->getCellByColumnAndRow($currColumn, $this->currRow+1)->getValue();

            $row[$heading] = "$value";
            $currColumn++;
        }

        return $row;
    }

    protected function openFile()
    {
        try {
            $excel = \PHPExcel_IOFactory::load($this->getFileName());
        } 
        catch (\PHPExcel_Reader_Exception $ex) {
            if (!preg_match("/Could not open/", $ex->getMessage())) {
                throw $ex;
            }
            throw new FileNotFoundException($ex->getMessage());
        }
        $this->sheet = $excel->getActiveSheet();
        $this->columnCount = ord($this->sheet->getHighestDataColumn()) - 64;
        $this->rowCount = $this->sheet->getHighestRow();
    }

    protected function populateHeadings()
    {
        $headings = array();
        for ($currColumn = 0; $currColumn < $this->columnCount; $currColumn++) {
            $cell = $this->sheet->getCellByColumnAndRow($currColumn, 1);
            $headings[] = $cell->getValue();
        }
        $this->setHeadings($headings);
        $this->currRow++;
    }

    public function close()
    {

    }
}
