<?php

namespace App\Exceptions;

use Exception;

class UnknownTransactionTypeException extends Exception
{
    protected $message = 'Unknown transaction type';
}
