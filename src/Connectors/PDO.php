<?php namespace Maduser\Minimal\Database\Connectors;

use Exception;
use Maduser\Minimal\Database\Exceptions\DatabaseException;


/**
 * Class PDO
 *
 * @package Maduser\Minimal\Libraries\Database
 */
class PDO
{
    /**
     * The database handler to use
     *
     * @var \PDO
     */
    private static $handler = \PDO::class;

    /**
     * Define the database driver
     *
     * @var string
     */
    private static $driver = 'mysql';

    /**
     * The database host
     *
     * @var string
     */
    private static $host = '127.0.0.1';

    /**
     * The database port
     *
     * @var string
     */
    private static $port = '3306';

    /**
     * The database charset
     *
     * @var string
     */
    private static $charset = 'utf8';

    /**
     * The database name to use
     *
     * @var string
     */
    private static $database;

    /**
     * The login user
     *
     * @var string
     */
    private static $user;

    /**
     * The login password
     *
     * @var string
     */
    private static $password;

    /**
     * The options the handler accepts
     *
     * @var array
     */
    private static $handlerOptions = [];

    /**
     * The current connection
     *
     * @var \PDO
     */
    private static $connection;

    private static $executedQueries = [];

     /**
     * @return object
     */
    public static function getHandler()
    {
        return self::$handler;
    }

    /**
     * @param string $handler
     */
    public static function setHandler($handler)
    {
        self::$handler = $handler;
    }

    /**
     * @return string
     */
    public static function getDriver()
    {
        return self::$driver;
    }

    /**
     * @param string $driver
     */
    public static function setDriver($driver)
    {
        self::$driver = $driver;
    }

    /**
     * @return string
     */
    public static function getCharset()
    {
        return self::$charset;
    }

    /**
     * @param string $charset
     */
    public static function setCharset($charset)
    {
        self::$charset = $charset;
    }

    /**
     * @return string
     */
    public static function getPort()
    {
        return self::$port;
    }

    /**
     * @param string $port
     */
    public static function setPort($port)
    {
        self::$port = $port;
    }

    /**
     * @return mixed
     */
    public static function getHost()
    {
        return self::$host;
    }

    /**
     * @param mixed $host
     */
    public static function setHost($host)
    {
        self::$host = $host;
    }

    /**
     * @return mixed
     */
    public static function getDatabase()
    {
        return self::$database;
    }

    /**
     * @param mixed $database
     */
    public static function setDatabase($database)
    {
        self::$database = $database;
    }

    /**
     * @return mixed
     */
    public static function getUser()
    {
        return self::$user;
    }

    /**
     * @param mixed $user
     */
    public static function setUser($user)
    {
        self::$user = $user;
    }

    /**
     * @return mixed
     */
    public static function getPassword()
    {
        return self::$password;
    }

    /**
     * @param mixed $password
     */
    public static function setPassword($password)
    {
        self::$password = $password;
    }

    /**
     * @return array
     */
    public static function getHandlerOptions()
    {
        return self::$handlerOptions;
    }

    /**
     * @param array $handlerOptions
     */
    public static function setHandlerOptions($handlerOptions)
    {
        self::$handlerOptions = $handlerOptions;
    }

    /**
     * @param $key
     * @param $value
     */
    public static function addHandlerOptions($key, $value)
    {
        self::$handlerOptions[$key] = $value;
    }

    /**
     * @return \PDO
     */
    public static function getConnection()
    {
        return self::$connection;
    }

    /**
     * @param array $connection
     */
    public static function setConnection(array $connection)
    {
        self::$connection = $connection;
    }

    /**
     * @return array
     */
    public static function getExecutedQueries(): array
    {
        return self::$executedQueries;
    }

    /**
     * @param array $executedQueries
     */
    public static function setExecutedQueries(array $executedQueries)
    {
        self::$executedQueries = $executedQueries;
    }

    /**
     * @param $value
     */
    public static function addExecutedQuery($value)
    {
        self::$executedQueries[] = $value;
    }

    /**
     * Returns a PDO connection
     *
     * @param null $config
     * @param bool $forceConnect
     *
     * @return \PDO
     * @throws Exception
     */
    public static function connection($config = null, $forceConnect = false)
    {
        if (self::$connection && !$forceConnect) {
            return self::$connection;
        }

        ! isset($config) || self::config($config);

        $ref = new \ReflectionClass(self::getHandler());

        try {
            /** @var \PDO $connection */
            self::$connection = $ref->newInstanceArgs([
                self::getConnectionString(),
                self::getUser(),
                self::getPassword(),
                self::getHandlerOptions()
            ]);

        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage(), $config);
        }

        return self::$connection;
    }

    public static function config($config)
    {
        !isset($config['driver']) || self::setDriver($config['driver']);
        !isset($config['host']) || self::setHost($config['host']);
        !isset($config['port']) || self::setPort($config['port']);
        !isset($config['user']) || self::setUser($config['user']);
        !isset($config['password']) || self::setPassword($config['password']);
        !isset($config['database']) || self::setDatabase($config['database']);
        !isset($config['charset']) || self::setCharset($config['charset']);
        !isset($config['handler']) || self::setHandler($config['handler']);

        if (isset($config['handlerOptions'])) {
            foreach ($config['handlerOptions'] as $key => $value) {
                self::addHandlerOptions($key, $value);
            }
        }
    }

    /**
     * @return string
     */
    public static function getConnectionString()
    {
        $str = '';

        $str .= self::getDriver() . ':host=' . self::getHost() . ';';
        $str .= (self::getPort()) ? 'port=' . self::getPort() . ';' : '';
        $str .= (self::getDatabase()) ? 'dbname=' . self::getDatabase() . ';' : '';
        $str .= (self::getCharset()) ? 'charset=' . self::getCharset() . ';' : '';

        return $str;
    }
}