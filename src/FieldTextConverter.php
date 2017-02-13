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

use ClinicLE\DataFile\Exception\FieldTooLongException;

/**
 * Converts a line of text in ClinicLE FTX format into an associative array
 * @package ClinicLE\DataFile
 * @author Rob Free
 */

class FieldTextConverter
{
    /**
     * Convert the line
     *
     * @param  $fieldText
     * @return array
     * @throws FieldTooLongException
     */
    public function convert($fieldText)
    {
        $output = array();
        $output['Level'] = $this->determineLevel($fieldText);

        $title = $this->extractTitle($fieldText);
        $output['Title'] = $title;

        $output['Settings'] = $this->determineSettings($fieldText);
        $output['Options'] = $this->determineOptions($fieldText);

        if (isset($output['Settings'])) {
            $fieldText = str_replace('{'.$output['Settings'].'}', '', $fieldText);
        }
        if (isset($output['Options'])) {
            $fieldText = str_replace('['.$output['Options'].']', '', $fieldText);
        }

        $output['Field'] = $this->determineField($fieldText, $title);
        $rename = $this->determineRename($fieldText);
        if ($rename) {
            $output['Rename'] = $rename;
        }
        $output['Required'] = $this->determineRequired($fieldText);
        $output['Type'] = $this->determineType($fieldText);

        return $output;
    }

    private function determineRename($fieldText)
    {
        if (preg_match("/\@([A-Za-z0-9|_]+)\s{0,1}/", $fieldText, $matches)) {
            return $matches[1];
        }

        return;
    }

    private function determineLevel(&$fieldText)
    {
        $prevFieldText = $fieldText;
        $fieldText = preg_replace("/^[\t]*(-)/", '', $fieldText);
        if ($prevFieldText != $fieldText) {
            return 'DEL';
        }
        preg_match("/^([\t\s]*)(.+)/", $fieldText, $matches);

        return strlen($matches[1]) + 1;
    }

    private function determineType($fieldText)
    {
        $matches = array();
        if (!preg_match("/\:([a-z|_]+)\s{0,1}/", $fieldText, $matches)) {
            return 'markup';
        }

        return $matches[1];
    }

    private function extractTitle(&$fieldText)
    {
        if (preg_match('/^[\t\s\>]*\"(.+)\"(.+)/', $fieldText, $matches)) {
            $fieldText = trim($matches[2]);

            return trim($matches[1]);
        }

        preg_match("/^[\t\s\>]*([^:\$\*]*)/", $fieldText, $matches);

        return trim($matches[1]);
    }

    private function determineField($fieldText, $title)
    {
        $matches = array();
        $field = preg_match('/\$([A-Za-z0-9\-\_]+)/', $fieldText, $matches) ? $matches[1] : self::titleToField($title);
        if (strlen($field) > 40) {
            throw new FieldTooLongException(
                "Field '$field' determined from FieldText '$fieldText' and title '$title' is too long"
            );
        }

        return $field;
    }

    private function determineRequired($fieldText)
    {
        $matches = array();
        if (!preg_match("/(.*)\*(.*)/", $fieldText, $matches)) {
            return '';
        }

        return 1;
    }

    public static function titleToField($title)
    {
        $title = trim($title);
        $title = strip_tags($title);

        $title = preg_replace("/[^0-9a-zA-Z\s-]/", '', $title);

        if (strlen($title) > 30) {
            $title = substr($title, 0, 30);
        }
        $field = preg_replace('/[\s\-]/', '_', $title);

        return strtolower($field);
    }

    private function determineSettings($fieldText)
    {
        $matches = array();
        if (!preg_match('/\{(.+)?\}/', $fieldText, $matches)) {
            return '';
        }

        return $matches[1];
    }

    private function determineOptions($fieldText)
    {
        $matches = array();
        if (!preg_match('/\[(.+?)\]/', $fieldText, $matches)) {
            return '';
        }

        return $matches[1];
    }
}
