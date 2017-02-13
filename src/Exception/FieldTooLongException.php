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
 * Thrown when a field name is greater than 40 characters in length
 *
 * @package ClinicLE\DataFile\Exception
 */
class FieldTooLongException extends \Exception
{
}
