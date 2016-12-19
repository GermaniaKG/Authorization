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
     * @var \Psr\Log\LoggerInterface
     */
    public $log;


    /**
     * @param array           $acl                 Array with tasks and allowed roles
     * @param bool            $default_permission  Default permission if a task is not found
     * @param LoggerInterface $logger              PSR-3 Logger
     */
    public function __construct( array $acl, $default_permission, LoggerInterface $logger = null)
    {
        $this->acl = $acl;
        $this->default_permission = $default_permission;
        $this->log = $logger ?: new NullLogger;
    }


    /**
     * @param  string $task
     * @param  array  $user_roles
     * @return bool
     */
    public function __invoke( $task, array $user_roles, LoggerInterface $logger = null)
    {
        return $this->authorize( $task, $user_roles, $logger);
    }


    /**
     * @param  string $task
     * @param  array  $user_roles
     * @return bool
     */
    public function authorize( $task, array $user_roles, LoggerInterface $logger = null )
    {
        $permitted = (!isset( $this->acl[ $task ]))
        ? $this->default_permission
        : (count(array_intersect( $user_roles, $this->acl[$task])) > 0);

        $log = $logger ?: $this->log;

        $log->info("Authorize '$task'", [
            'user_roles' => implode(",", $user_roles),
            'permitted'  => $permitted ? "YES" : "NO"
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
