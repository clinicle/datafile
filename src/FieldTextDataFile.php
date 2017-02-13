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

/**
 * Uses the FieldTextConverter class to convert FTX lines into ClinicLE compatible field rows
 * @package ClinicLE\DataFile
 * @author Rob Free
 */

class FieldTextDataFile extends DelimitedDataFile
{
    private $fieldTextConverter;

    public function __construct($formPath)
    {
        parent::__construct($formPath);
        $this->fieldTextConverter = new FieldTextConverter();
	$this->setHeadings(array(
            'Rename', 'Level', 'Title', 'Field', 'Type', 'Prefix', 'Suffix', 'Size', 'Options',
            'Min', 'Max', 'Default', 'Required', 'Settings', 'Concepts'
        ));
    }

    public function retrieveNextRow()
    {
	// loop through each CSV separated line of file
        $line = fgets($this->handle);
	
        if (!$line) {
            return false;
        }
        $this->rowCount++;
        if (strlen(trim($line)) == 0) {
	    
            return null;
        }
	
        $item = $this->fieldTextConverter->convert($line);
        return $item;
    }
}
