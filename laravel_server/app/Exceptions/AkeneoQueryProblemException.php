<?php
 
namespace App\Exceptions;
 
use Exception;
 
class AkeneoQueryProblemException extends Exception
{

	public function __construct(string $custom_message, int $code, \Throwable $exception)
    {
		parent::__construct($custom_message, $code, $exception);
    }

}