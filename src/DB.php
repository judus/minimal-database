<?php namespace Maduser\Minimal\Database;

use Maduser\Minimal\Database\Connectors\PDO;
use Maduser\Minimal\Database\Exceptions\DatabaseException;

class DB
{
    /**
     * @var array
     */
    private static $connections = [];

    /**
     * @var string
     */
    private static $use;

    /**
     * @return array
     */
    public static function getConnections()
    {
        return self::$connections;
    }

    /**
     * @param array $connections
     */
    public static function setConnections($connections)
    {
        self::$connections = $connections;
    }

    /**
     * @param PDO    $connector
     * @param string $name
     *
     * @throws DatabaseException
     */
    public static function add(PDO $connector, $name = 'default')
    {
        if (isset(self::$connections[$name])) {
            throw new DatabaseException('Connection with name ' . $name . ' already exists');
        }

        self::$connections[$name] = $connector;
    }

    /**
     * Get a PDO-Connection
     *
     * @param string $name
     *
     * @return null
     */
    public static function get($name = 'default')
    {
        if (!isset(self::$connections[$name])) {
            return null;
        }

        $connector = self::$connections[$name];

        return $connector->connection();
    }

    public static function connector($name = null)
    {
        self::$use = $name ? $name : self::$use;
        return self::$connections[self::$use];
    }

    /**
     * Get a PDO-Connection (alias for get())
     *
     * @param string $name
     *
     * @return null
     */
    public static function connection($name = null)
    {
        self::$use = $name ? $name : self::$use;
        return self::get(self::$use);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(
            [static::getInstance(), $name], $arguments);
    }


    public static function connections(array $array)
    {
        foreach ($array as $name => $config) {
            self::add(new PDO($config, $name), $name);
        }

        self::use(reset(self::$connections)->getName());

        return self::get(self::$use);
    }

    public static function use($name)
    {
        self::$use = $name;

        return self::get($name);
    }
}