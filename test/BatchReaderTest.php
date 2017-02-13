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
use ClinicLE\DataFile\BatchReader;
use ClinicLE\DataFile\DelimitedDataFile;

class BatchReaderTest extends \PHPUnit_Framework_TestCase {
    const BATCHSIZE = 3;

    function setUp() {
        $this->dataFile = $this->getMock('\ClinicLE\Test\DataFile\BRDataFileImpl');
		$this->SUT = new BatchReader($this->dataFile);
    }

    /**
     * @test
     */
    function correct_number_of_batches_are_returned_with_different_batch_sizes() {
        $this->dataFile->expects($this->any())->method("getRowCount")->will($this->returnValue(46));
        $this->SUT->setBatchSize(25);
        $this->assertEquals(2,$this->SUT->getBatchCount());
        $this->SUT->reset();
        $this->SUT->setBatchSize(10);
        $this->assertEquals(5,$this->SUT->getBatchCount());
        $this->SUT->reset();
        $this->SUT->setBatchSize(60);
        $this->assertEquals(1,$this->SUT->getBatchCount());
        $this->SUT->reset();
        $this->SUT->setBatchSize(23);
        $this->assertEquals(2,$this->SUT->getBatchCount());
    }
    /**
     * @test
     */
    function batch_size_can_be_retrieved_and_set() {
		$this->SUT->setBatchSize(self::BATCHSIZE);
        $this->assertEquals(self::BATCHSIZE,$this->SUT->getBatchSize());
    }

    /**
     * @test
     */
    function batch_count_will_be_calculated_correctly_if_not_exact_number_in_batch() {
        $this->SUT->setBatchSize(50);
        $this->dataFile->expects($this->once())->method("getRowCount")->will($this->returnValue(223));
        $this->assertEquals(5,$this->SUT->getBatchCount());
    }

    /**
     * @test
     */
    function batch_will_be_dealt_with_correctly_if_only_one_batch() {
        $this->setupExpectedBatch(2);
        $this->SUT->setBatchSize(50);
        $observedBatch = $this->SUT->nextBatch();

        $this->assertEquals($this->expectedBatch,$observedBatch);
        $this->assertEquals(1,$this->SUT->getLastBatchNo());
        $this->assertEquals(1,$this->SUT->getBatchCount());
        $this->assertFalse($this->SUT->nextBatch());
    }

	/**
     * @test
     */
    function batch_count_will_be_calculated_correctly_if_exact_number_in_batch() {
		$this->SUT->setBatchSize(50);
		$this->dataFile->expects($this->once())->method("getRowCount")->will($this->returnValue(200));
        $this->assertEquals(4,$this->SUT->getBatchCount());
    }

    /**
     * @test
     * @expectedException ClinicLE\DataFile\Exception\InvalidBatchSizeException
     */
    function fails_if_zero_batch_size_is_set() {
		$this->SUT->setBatchSize(0);
    }

    /**
     * @test
     */
    function if_less_rows_remaining_than_batch_size_return_just_remaining_rows() {
		$this->SUT->setBatchSize(self::BATCHSIZE);
		$rows = array(array("field"=>"row1"),array("field"=>"row2"));
		$this->dataFile->expects($this->at(0))->method("nextRow")->will($this->returnValue($rows[0]));
        $this->dataFile->expects($this->at(1))->method("nextRow")->will($this->returnValue($rows[1]));
        
		$this->dataFile->expects($this->at(2))->method("nextRow")->will($this->returnValue(false));
        $observedBatch = $this->SUT->nextBatch();
        $this->assertEquals($rows,$observedBatch);
    }

    /**
     * @test
     */
    function if_more_rows_remaining_than_batch_size_just_return_single_batch() {
		$this->SUT->setBatchSize(self::BATCHSIZE);
        $this->setupExpectedBatch(intval(self::BATCHSIZE)+2);
        
		$observedBatch1 = $this->SUT->nextBatch();
		$this->assertEquals(array_slice($this->expectedBatch,0,3),$observedBatch1);
		
		
		$observedBatch2 = $this->SUT->nextBatch();
		
		$this->assertEquals(array_slice($this->expectedBatch,3,2),$observedBatch2);
    }
	
	/**
     * @test
     */
    function if_multiple_large_batches_return_correct_batch_based_on_current_state() {
		$this->SUT->setBatchSize(50);
        $this->setupExpectedBatch(223);
        
		$observedBatch1 = $this->SUT->nextBatch();
		$this->assertEquals(array_slice($this->expectedBatch,0,50),$observedBatch1);
		$this->assertEquals(1,$this->SUT->getLastBatchNo());
		$observedBatch2 = $this->SUT->nextBatch();
		$this->assertEquals(array_slice($this->expectedBatch,50,50),$observedBatch2);
		$this->assertEquals(2,$this->SUT->getLastBatchNo());
		
		$observedBatch3 = $this->SUT->nextBatch();
		$this->assertEquals(array_slice($this->expectedBatch,100,50),$observedBatch3);
		$this->assertEquals(3,$this->SUT->getLastBatchNo());
		
		$observedBatch4 = $this->SUT->nextBatch();
		$this->assertEquals(array_slice($this->expectedBatch,150,50),$observedBatch4);
		$this->assertEquals(4,$this->SUT->getLastBatchNo());
		
		$observedBatch5 = $this->SUT->nextBatch();
		$this->assertEquals(array_slice($this->expectedBatch,200),$observedBatch5);
		$this->assertEquals(5,$this->SUT->getLastBatchNo());
    }
	
	function setupExpectedBatch($batchSize) {
		$this->expectedBatch = array();
        for($i=0;$i<$batchSize;$i++) {
            $row = array("field" => "row" . $i);
			$this->expectedBatch[]=$row;	
			$this->dataFile->expects($this->at($i))->method("nextRow")->will($this->returnValue($this->expectedBatch[$i]));

        }
        $this->dataFile->expects($this->at($batchSize))->method("nextRow")->will($this->returnValue(false));
	}
	
	/**
     * @test
     */
    function if_no_rows_remaining_then_return_false() {
		$this->SUT->setBatchSize(self::BATCHSIZE);
        $this->dataFile->open();
        $this->dataFile->expects($this->at(0))->method("nextRow")->will($this->returnValue(array("field"=>"row1")));
        $this->dataFile->expects($this->at(1))->method("nextRow")->will($this->returnValue(false));
        $expectedBatch = array(array("field"=>"row1"));
        $this->SUT->nextBatch();
        
		$this->assertFalse($this->SUT->nextBatch());
    }
}

class BRDataFileImpl extends \ClinicLE\DataFile\AbstractDataFile {

	protected function openFile() {
		
	}

	protected function populateHeadings() {
		
	}

	protected function retrieveNextRow() {
		
	}

	public function close() {
		
	}

}