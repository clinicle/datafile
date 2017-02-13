<?php
/*
 * This file is part of the ClinicLE package.
 *
 * (c) Rob Free <rob@clinicle.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ClinicLE\Test\DataFile;
use ClinicLE\Common\Log;
use ClinicLE\DataFile\AbstractDataFile;

class AbstractDataFileTest extends \PHPUnit_Framework_TestCase {
    const FILENAME="test_file";
    public $headings = array("field1","field2","field3");
    const LIMIT = 50;

    /**
     * @test
     */
    function by_default_it_will_not_set_headings_if_none_provided() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $this->assertNull($this->SUT->getHeadings());
    }

    /**
     * @test
     */
    function it_accepts_and_returns_a_filename_and_headings_as_parameters() {
        $this->SUT = new DataFileImpl(self::FILENAME,$this->headings);
        $this->assertEquals(self::FILENAME,$this->SUT->getFileName());
        $this->assertEquals($this->headings,$this->SUT->getHeadings());
    }

    /**
     * @test
     */
    function it_returns_true_if_header_row_is_set_to_true() {
        $this->SUT = new DataFileImpl(self::FILENAME,$this->headings);
        $this->SUT->setHeaderRow(true);
        $this->assertTrue($this->SUT->hasHeaderRow());
    }

    /**
     * @test
     * @expectedException ClinicLE\DataFile\Exception\HeadingsNotDefinedException
     */
    function fails_if_file_has_no_defined_headings_when_opened() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $this->SUT->open();
    }

    /**
     * @test
     */
    function it_opens_if_headings_are_set_separately() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $this->SUT->setHeadings($this->headings);
        $this->SUT->open();
        $this->assertEquals($this->headings,$this->SUT->getHeadings());
    }

    /**
     * @test
     */
    function by_default_load_opens_and_reads_rows_then_closes_the_file_but_does_not_trim_the_row_data() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $this->SUT->setHeadings($this->headings);
        $this->SUT->load();
        $this->assertEquals(array("openFile",array("row1"),array(" row2 "),"close"),$this->SUT->getDataItems());
        $this->assertEquals(4,$this->SUT->getRowCount());
    }

    /**
     * @test
     */
    function if_trim_is_set_load_opens_reads_rows_trims_them_and_then_closes_the_file() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $this->SUT->setTrimFields(true);
        $this->SUT->setHeadings($this->headings);
        $this->SUT->load();
        $this->assertEquals(array("openFile",array("row1"),array("row2"),"close"),$this->SUT->getDataItems());
        $this->assertEquals(4,$this->SUT->getRowCount());
    }

    /**
     * @test
     */
    function if_limit_is_set_to_1_load_only_reads_a_single_row() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $this->SUT->setLimit(1);
        $this->SUT->setHeadings($this->headings);
        $this->SUT->load();
        $this->assertEquals(array("openFile",array("row1"),"close"),$this->SUT->getDataItems());
        $this->assertEquals(4,$this->SUT->getRowCount());
    }

    /**
     * @test
     */
    function data_items_can_be_retrieved_and_set() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $dataItems = array(array("id"=>1));
        $this->SUT->setDataItems($dataItems);
        $this->assertEquals($dataItems,$this->SUT->getDataItems());
    }

    /**
     * @test
     */
    function row_limit_can_be_retrieved_and_set() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $this->SUT->setLimit(self::LIMIT);
        $this->assertEquals(self::LIMIT,$this->SUT->getLimit());
    }

    /**
     * @test
     */
    function reset_will_re_open_the_file() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $this->SUT->setHeaderRow(true);
        $this->SUT->reset();
        $this->assertEquals(array("openFile","populateHeadings"),$this->SUT->getDataItems());
    }

    /**
     * @test
     * @expectedException ClinicLE\DataFile\Exception\DuplicateHeadingException
     */
    function it_fails_if_duplicate_headings_are_set() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $this->SUT->setHeadings(array("field1","field2","field1"));
    }

    /**
     * @test
     * @expectedException ClinicLE\DataFile\Exception\FileNotOpenedException
     */
    function it_fails_if_try_to_get_next_row_when_file_has_not_been_opened() {
        $this->SUT = new DataFileImpl(self::FILENAME);
        $this->SUT->setHeadings(array("field1","field2"));
        $this->SUT->nextRow();
    }

}

class DataFileImpl extends AbstractDataFile {
    public function close()
    {
        $this->dataItems[]="close";
    }

    protected function retrieveNextRow()
    {
        if(!array_key_exists($this->currRow,$this->rows)) {
            return false;
        }
        $row = $this->rows[$this->currRow];
        return $row;
    }

    protected function populateHeadings()
    {
        $this->setHeadings(array("heading1"));
        $this->dataItems[]="populateHeadings";
    }

    protected function openFile()
    {
        $this->dataItems[]="openFile";
        $this->rowCount=4;
        $this->rows = array(array("row1"),array(""),null,array(" row2 "));
    }

}
