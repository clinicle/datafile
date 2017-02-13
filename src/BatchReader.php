<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 07/04/15
 * Time: 19:47
 */

namespace ClinicLE\DataFile;
use ClinicLE\Common\Log;
use ClinicLE\DataFile\Exception\InvalidBatchSizeException;

/**
 * Loads rows from a DataFile in batches
 *
 * @package ClinicLE\DataFile
 * @author  Rob Free
 */

class BatchReader
{
    private $dataFile;
    private $batchSize = 5000;
	private $batchCount;
    private $finalBatch = false;
	private $lastBatchNo = 0;
	
    public function __construct(AbstractDataFile $dataFile) 
    {
        $this->dataFile = $dataFile;
    }
    /**
     * Return a batch of rows, false if the final batch and null if there are no records left in the batch.
     *
     * @return array|bool|null
     */
    public function nextBatch()
    {
        if ($this->finalBatch) {
            return false;
        }
        $batchSize = $this->getBatchSize();
        $batch = array();
        $numberInBatch = 0;

        $row = $this->dataFile->nextRow();
        while ($row!==false) {
            $batch[] = $row;
            $numberInBatch++;
			if ($numberInBatch == $batchSize) {
                break;
            }
            $row = $this->dataFile->nextRow();

        }
        if (count($batch) == 0) {
            return false;
        }
		if(count($batch)<$batchSize) {
			$this->finalBatch = true;
		}
        $this->lastBatchNo++;

        return $batch;
    }


    /**
     * Get size of row batches.
     *
     * @return int
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    /**
     * Set the size of the row batches.
     *
     * @param $batchSize
     */
    public function setBatchSize($batchSize)
    {
        if($batchSize===0) { throw new InvalidBatchSizeException("Cannot set batch size to zero"); 
        }
        $this->batchSize = $batchSize;
    }


	public function getBatchCount() {
		if($this->batchCount) {
			return $this->batchCount;
		}
		$rowCount = $this->dataFile->getRowCount();

        if($rowCount<$this->batchSize) {
            $batchCount = 1;
            $this->batchCount = $batchCount;
            return $batchCount;
        }
		$batchCount = intval($rowCount/$this->batchSize);

		if($rowCount % $this->batchSize > 0) {
			$batchCount++;
		}
		$this->batchCount = $batchCount;
		return $batchCount;
	}
	
	public function getLastBatchNo() {
		return $this->lastBatchNo;
	}

	public function reset() {
	    $this->batchCount = null;
        $this->finalBatch=false;
        $this->lastBatchNo=0;
    }
}