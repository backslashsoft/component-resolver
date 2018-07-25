<?php
/**
 * Created by PhpStorm.
 * User: helix
 * Date: 20-Jul-18
 * Time: 13:47
 */

namespace Gdev\DependencyResolver\Exceptions;

use Exception;

class DependencyResolverException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}