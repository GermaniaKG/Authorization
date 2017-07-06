<?php
namespace Germania\Authorization;

use Interop\Container\Exception\NotFoundException;
use Psr\Container\NotFoundExceptionInterface;

class TaskNotFoundException extends \Exception implements NotFoundException, NotFoundExceptionInterface
{

}
