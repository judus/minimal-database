<?php namespace Maduser\Minimal\Database\Connectors;

use Maduser\Minimal\Database\Exceptions\DatabaseException;

/**
 * Class PDO
 *
 * @package Maduser\Minimal\Database
 */
class PDO
{
    private $name;

    /**
     * The database handler to use
     *
     * @var \PDO
     */
    private $handler = \PDO::class;

    /**
     * Define the database driver
     *
     * @var string
     */
    private $driver = 'mysql';

    /**
     * The database host
     *
     * @var string
     */
    private $host = '127.0.0.1';

    /**
     * The database port
     *
     * @var string
     */
    private $port = '3306';

    /**
     * The database charset
     *
     * @var string
     */
    private $charset = 'utf8';

    /**
     * The database name to use
     *
     * @var string
     */
    private $database;

    /**
     * The login user
     *
     * @var string
     */
    private $user;

    /**
     * The login password
     *
     * @var string
     */
    private $password;

    /**
     * The options the handler accepts
     *
     * @var array
     */
    private $handlerOptions = null;

    /**
     * The current connection
     *
     * @var \PDO
     */
    private $connection;

    private $executedQueries = [];

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return PDO
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return object
     */
    public function getHandler()
    {
        if (is_null($this->handler)) {
            $this->handler = \PDO::class;
        }

        return $this->handler;
    }

    /**
     * @param string $handler
     *
     * @return PDOConnector
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     *
     * @return PDOConnector
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     *
     * @return PDOConnector
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     *
     * @return PDOConnector
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return PDOConnector
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $database
     *
     * @return PDOConnector
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     *
     * @return PDOConnector
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return PDOConnector
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }


    /**
     * @return array
     */
    public function getHandlerOptions()
    {
        if (is_null($this->handlerOptions)) {
            $this->handlerOptions = [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ];
        }

        return $this->handlerOptions;
    }

    /**
     * @param array $handlerOptions
     */
    public function setHandlerOptions($handlerOptions)
    {
        $this->handlerOptions = $handlerOptions;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addHandlerOptions($key, $value)
    {
        $this->handlerOptions[$key] = $value;
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param array $connection
     */
    public function setConnection(array $connection)
    {
        $this->connection = $connection;
    }

    public function __construct($config = [], $name = null)
    {
        $this->setName($name ? $name : uniqid());
        $this->config($config);
    }

    /**
     * @return array
     */
    public function getExecutedQueries(): array
    {
        return $this->executedQueries;
    }

    /**
     * @param array $executedQueries
     */
    public function setExecutedQueries(array $executedQueries)
    {
        $this->executedQueries = $executedQueries;
    }

    /**
     * @param $value
     */
    public function addExecutedQuery($value)
    {
        $this->executedQueries[] = $value;
    }

    /**
     * Returns a PDO connection
     *
     * @param null $config
     * @param bool $forceConnect
     *
     * @return \PDO
     * @throws DatabaseException
     */
    public function connection($config = null, $forceConnect = false)
    {

        if ($this->connection && !$forceConnect) {
            return $this->connection;
        }

        !isset($config) || $this->config($config);

        $ref = new \ReflectionClass($this->getHandler());

        try {
            /** @var \PDO $connection */
            $this->connection = $ref->newInstanceArgs([
                $this->getConnectionString(),
                $this->getUser(),
                $this->getPassword(),
                $this->getHandlerOptions()
            ]);
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }

        return $this->connection;
    }

    public function config($config)
    {
        !isset($config['driver']) || $this->setDriver($config['driver']);
        !isset($config['host']) || $this->setHost($config['host']);
        !isset($config['port']) || $this->setPort($config['port']);
        !isset($config['user']) || $this->setUser($config['user']);
        !isset($config['password']) || $this->setPassword($config['password']);
        !isset($config['database']) || $this->setDatabase($config['database']);
        !isset($config['charset']) || $this->setCharset($config['charset']);
        !isset($config['handler']) || $this->setHandler($config['handler']);

        if (isset($config['handlerOptions'])) {
            foreach ($config['handlerOptions'] as $key => $value) {
                $this->addHandlerOptions($key, $value);
            }
        }
    }

    /**
     * @return string
     */
    public function getConnectionString()
    {
        $str = '';

        $str .= $this->getDriver() . ':host=' . $this->getHost() . ';';
        $str .= ($this->getPort()) ? 'port=' . $this->getPort() . ';' : '';
        $str .= ($this->getDatabase()) ? 'dbname=' . $this->getDatabase() . ';' : '';
        $str .= ($this->getCharset()) ? 'charset=' . $this->getCharset() . ';' : '';

        return $str;
    }
}