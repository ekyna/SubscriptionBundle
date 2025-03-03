<?php

namespace Ekyna\Bundle\SubscriptionBundle\Exception;

use Ekyna\Component\Resource\Exception\ResourceExceptionInterface;
use Exception;

class ForbiddenOperationException extends Exception implements ResourceExceptionInterface
{

}
