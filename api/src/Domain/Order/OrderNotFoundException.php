<?php

declare(strict_types=1);

namespace App\Domain\Order;

use App\Domain\DomainException\DomainRecordNotFoundException;

class OrderNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The order you requested does not exist.';
}
