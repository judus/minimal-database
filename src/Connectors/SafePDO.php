<?php namespace Maduser\Minimal\Database\Connectors;

/**
 * Class SafePDO
 *
 * @package Maduser\Minimal\Database
 */
class SafePDO extends \PDO
{
    /**
     * SafePDO constructor.
     * @param $dsn
     * @param string $username
     * @param string $password
     * @param array $driver_options
     */
    public function __construct($dsn, $username = '', $password = '', $driver_options = array())
    {
        // Change the PHP exception handler
        set_exception_handler(array(__CLASS__, 'exception_handler'));

        // Create a PDO object
        parent::__construct($dsn, $username, $password, $driver_options);

        // Reset the exception handler
        restore_exception_handler();
    }

    /**
     * @param $exception
     */
    public static function exception_handler($exception)
    {
        // Output the exception details
        /** @noinspection PhpUndefinedMethodInspection */
        die('Uncaught exception: ' . $exception->getMessage());
    }
}
