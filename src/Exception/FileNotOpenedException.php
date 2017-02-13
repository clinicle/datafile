<?php

/*
 * This file is part of the ClinicLE package.
 *
 * (c) Rob Free <rob@clinicle.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ClinicLE\DataFile\Exception;

/**
 * Thrown when the file has not been opened prior to retrieving rows
 *
 * @package ClinicLE\DataFile\Exception
 */
class FileNotOpenedException extends \Exception
{
}
