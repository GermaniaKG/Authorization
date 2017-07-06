<?php
namespace Germania\Authorization;

use Psr\Container\ContainerInterface;
use Interop\Container\ContainerInterface as InteropContainerInterface;

interface AuthorizationInterface extends ContainerInterface, InteropContainerInterface
{

    /**
     * Checks if one of the given roles is permitted to perform the given task.
     *
     * @param  string $task
     * @param  array  $user_roles
     * @return bool
     */
    public function authorize( $task, array $user_roles );

}
