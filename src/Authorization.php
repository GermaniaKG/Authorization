<?php
namespace Germania\Authorization;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Usage:
 *
 *      <?php
 *      $acl = array(
 *        '/foo' => ["registered"],
 *        '/bar' => ["admin", "superuser"]
 *      );
 *
 *      $authorization = new Authorization( $acl, new NullLogger );
 *      ?>
 */
class Authorization implements AuthorizationInterface
{

    /**
     * @var array
     */
    public $acl = [];

    /**
     * @var bool
     */
    public $default_permission;

    /**
     * @var Psr\Log\LoggerInterface
     */
    public $log;


    /**
     * @param array           $acl                 Array with tasks and allowed roles
     * @param bool            $default_permission  Default permission if a task is not found
     * @param LoggerInterface $log                 PSR-3 Logger
     */
    public function __construct( array $acl, $default_permission, LoggerInterface $log = null)
    {
        $this->acl = $acl;
        $this->default_permission = $default_permission;
        $this->log = $log ?: new NullLogger;
    }


    /**
     * @param  string $task
     * @param  array  $user_roles
     * @return bool
     */
    public function __invoke( $task, array $user_roles)
    {
        return $this->authorize( $task, $user_roles);
    }


    /**
     * @param  string $task
     * @param  array  $user_roles
     * @return bool
     */
    public function authorize( $task, array $user_roles )
    {
        $permitted = (!isset( $this->acl[ $task ]))
        ? $this->default_permission
        : (count(array_intersect( $user_roles, $this->acl[$task])) > 0);

        $this->log->info("Authorize $task", [
            'user_roles' => implode(",", $user_roles),
            'permitted'  => $permitted
        ]);

        return $permitted;
    }



    /**
     * @param  string $task The task
     * @return bool
     */
    public function has( $task )
    {
        return array_key_exists($task, $this->acl);
    }

    /**
     * @param  string $task The task
     * @return array        Allowed roles
     */
    public function get( $task )
    {
        if ($this->has($task)):
            return $this->acl[ $task ];
        endif;

        throw new TaskNotFoundException;
    }

}
