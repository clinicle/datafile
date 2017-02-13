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
 * Thrown when there is more than one column with the same heading in the file
 *
 * @package ClinicLE\DataFile\Exception
 */
class DuplicateHeadingException extends \Exception
{
}
