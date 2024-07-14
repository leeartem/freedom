<?php

namespace App\Exceptions;

use Exception;

class UnknownTransactionStatusException extends Exception
{
    protected $message = 'Unknown transaction status';
}
