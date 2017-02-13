<?php
namespace ClinicLE\Test\DataFile;
use ClinicLE\DataFile\DelimitedDataFile;
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/09/16
 * Time: 15:24
 */
class DelimitedDataFileTest extends \PHPUnit_Framework_TestCase
{
    private $SUT;
    function setUp() {
        $this->SUT = new DelimitedDataFile(__DIR__."/data/mrc_grade.csv");
    }

    /**
     * @test
     */
    function it_should_load_all_rows_in_the_file_with_headings() {
        $this->SUT->setHeaderRow(true);
        $this->SUT->setLimit(10);
        $this->SUT->load();
        $this->assertCount(5,$this->SUT->getDataItems());
    }

    /**
     * @test
     * @expectedException ClinicLE\Common\Exception\FileNotFoundException
     * @medium
     */
    function fails_if_delimited_file_does_not_exist() {
        $this->SUT = new DelimitedDataFile(__DIR__."/data/test-notexist.csv");
        $this->SUT->setHeaderRow(true);
        $this->SUT->open();
    }

}