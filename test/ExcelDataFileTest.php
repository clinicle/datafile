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
use ClinicLE\DataFile\ExcelDataFile;

class ExcelDataFileTest extends \PHPUnit_Framework_TestCase {
    function setUp() {
        $this->SUT = new ExcelDataFile(__DIR__."/data/test1.xlsx");
        $this->expectedHeadings = array(
            null,"Level","Title","Field Name","Field Type","Prefix","Suffix","Size",
            "Options","Min","Max","Default","Required","Settings"
        );
    }

    /**
     * @test
     * @expectedException ClinicLE\Common\Exception\FileNotFoundException
     * @medium
     */
    function fails_if_excel_file_does_not_exist() {
        $this->SUT = new ExcelDataFile(__DIR__."/data/test-notexist.xlsx");
        $this->SUT->setHeaderRow(true);
        $this->SUT->open();
    }

    /**
     * @test
     * @medium
     */
    function if_hasHeaderRow_it_should_open_the_file_and_setup_headings_that_match_the_file() {
        $this->SUT->setHeaderRow(true);
        $this->SUT->open();
        $this->assertEquals($this->expectedHeadings,$this->SUT->getHeadings());
    }

    /**
     * @test
     * @medium
     */
    function opening_file_should_set_the_row_count_to_match_the_number_of_rows_in_the_file() {
        $this->SUT->setHeaderRow(true);
        $this->SUT->open();
        $this->assertEquals(160,$this->SUT->getRowCount());
    }

    /**
     * @test
     * @medium
     */
    function if_file_has_headings_the_nextRow_should_return_the_first_data_row() {
        $this->SUT->setHeaderRow(true);
        $this->SUT->open();
        $expectedItem = array (
            'Level' => '1',
            'Title' => 'Date first seen',
            'Field Name' => 'date_first_seen',
            'Field Type' => 'date',
            'Prefix' => '',
            'Suffix' => '',
            'Size' => '',
            'Options' => '',
            'Min' => '',
            'Max' => '',
            'Default' => '',
            'Required' => '',
            'Settings' => '',
        );
        $observedItem = $this->SUT->nextRow();
        $this->assertEquals($expectedItem,$observedItem);
    }

    /**
     * @test
     * @medium
     */
    function if_file_headings_are_set_separately_the_nextRow_should_return_the_header_row_as_data() {
        $this->setSeparateHeadings();
        $this->SUT->open();
        $expectedItem = array (
            'field1'=>'',
            'field2' => 'Level',
            'field3' => 'Title',
            'field4' => 'Field Name',
            'field5' => 'Field Type',
            'field6' => 'Prefix',
            'field7' => 'Suffix',
            'field8' => 'Size',
            'field9' => 'Options',
            'field10' => 'Min',
            'field11' => 'Max',
            'field12' => 'Default',
            'field13' => 'Required',
            'field14' => 'Settings',
        );
        $observedItem = $this->SUT->nextRow();
        $this->assertEquals($expectedItem,$observedItem);
    }

    /**
     * @test
     */
    function if_file_has_headings_the_second_call_to_nextRow_should_return_the_third_row() {
        $this->SUT->setHeaderRow(true);
        $this->SUT->open();
        $expectedItem = array (
            'Level' => '3',
            'Title' => 'Occupational cause/worsening of asthma',
            'Field Name' => 'cause_worsening',
            'Field Type' => 'radio_buttons',
            'Prefix' => '',
            'Suffix' => '',
            'Size' => '',
            'Options' => 'Y=Yes,N=No',
            'Min' => '',
            'Max' => '',
            'Default' => '',
            'Required' => '',
            'Settings' => '',
        );
        $this->SUT->nextRow();
        $observedItem = $this->SUT->nextRow();

        $this->assertEquals($expectedItem,$observedItem);
    }

    /**
     * @test
     * @medium
     */
    function if_file_has_no_headings_the_second_call_to_nextRow_should_return_the_third_row() {
        $this->setSeparateHeadings();
        $this->SUT->open();

        $expectedItem = array (
            'field1' => '',
            'field2' => '1',
            'field3' => 'Date first seen',
            'field4' => 'date_first_seen',
            'field5' => 'date',
            'field6' => '',
            'field7' => '',
            'field8' => '',
            'field9' => '',
            'field10' => '',
            'field11' => '',
            'field12' => '',
            'field13' => '',
            'field14'=>''
        );
        $this->SUT->nextRow();
        $observedItem = $this->SUT->nextRow();

        $this->assertEquals($expectedItem,$observedItem);
    }

    /**
     * @test
     * @medium
     */
    function load_should_read_all_non_blank_rows_into_data_items() {
        $this->SUT->setHeaderRow(true);
        $this->SUT->load();
		$dataItems = $this->SUT->getDataItems();
        
        $this->assertCount(157,$dataItems);
		$this->assertEquals(array (
            'Level' => '2',
            'Title' => 'Allergen testing',
            'Field Name' => 'allergen_testing',
            'Field Type' => 'tab',
            'Prefix' => '',
            'Suffix' => '',
            'Size' => '',
            'Options' => '',
            'Min' => '',
            'Max' => '',
            'Default' => '',
            'Required' => '',
            'Settings' => '',
        ),$dataItems[71]);
        $this->assertEquals(array (
            'Level' => '3',
            'Title' => 'Prednisolone level',
            'Field Name' => 'prednisolone_level',
            'Field Type' => 'number',
            'Prefix' => '',
            'Suffix' => '',
            'Size' => '',
            'Options' => '',
            'Min' => '',
            'Max' => '',
            'Default' => '',
            'Required' => '',
            'Settings' => '',
        ),$dataItems[143]);
        $this->assertEquals(array (
            'Level' => '2',
            'Title' => 'CT scan',
            'Field Name' => 'ct_scan',
            'Field Type' => 'tab',
            'Prefix' => '',
            'Suffix' => '',
            'Size' => '',
            'Options' => '',
            'Min' => '',
            'Max' => '',
            'Default' => '',
            'Required' => '',
            'Settings' => '',
        ),$dataItems[144]);
        $this->assertEquals(array (
            'Level' => '3',
            'Title' => 'Scan reported as normal by radiologist',
            'Field Name' => 'reported_normal_by_radiologist',
            'Field Type' => 'radio_buttons',
            'Prefix' => '',
            'Suffix' => '',
            'Size' => '',
            'Options' => 'Y=Yes,N=No',
            'Min' => '',
            'Max' => '',
            'Default' => '',
            'Required' => '',
            'Settings' => '',
        ),$dataItems[156]);
    }

    private function setSeparateHeadings() {
        $headings=array();
        for($i=1;$i<=14;$i++) {
            $headings[]="field".$i;
        }
        $this->SUT->setHeadings($headings);
    }
}

