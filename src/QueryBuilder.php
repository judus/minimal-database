<?php

namespace Maduser\Minimal\Database;

use Maduser\Minimal\Collections\Collection;
use Maduser\Minimal\Database\Exceptions\DatabaseException;
use Maduser\Minimal\Database\Connectors\PDO;
use Maduser\Minimal\Database\ORM\ORM;

/**
 * Class QueryBuilder
 *
 * @package Maduser\Minimal\Database
 */
class QueryBuilder
{
    /**
     * Database connection
     *
     * @var
     */
    protected $db;

    /**
     * @var
     */
    protected $primaryKey = 'id';

    /**
     * Whether to use automatic timestamps
     *
     * @var bool
     */
    protected $timestamps = false;

    /**
     * Column name for 'created at" timestamp
     *
     * @var string
     */
    protected $timestampCreatedAt = 'created';

    /**
     * Column name for 'updated at" timestamp
     *
     * @var string
     */
    protected $timestampUpdatedAt = 'updated';

    /**
     * @var
     */
    protected $select;

    /**
     * @var
     */
    protected $prefix;

    /**
     * @var
     */
    protected $table;

    /**
     * @var
     */
    protected $parameters = [];

    /**
     * @var
     */
    protected $where;

    /**
     * @var array
     */
    protected $wheres = [];

    /**
     * @var null
     */
    protected $orderBy;

    /**
     * @var null
     */
    protected $limit;

    /**
     * @var
     */
    private $result;

    /**
     * @var
     */
    protected $queryString;

    /**
     * @var
     */
    protected $lastQuery;

    /**
     * @return mixed
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param mixed $db
     *
     * @return QueryBuilder
     */
    public function setDb($db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param mixed $primaryKey
     *
     * @return QueryBuilder
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @return bool
     */
    public function useTimestamps(): bool
    {
        return $this->timestamps;
    }

    /**
     * @param bool $timestamps
     *
     * @return QueryBuilder
     */
    public function setTimestamps(bool $timestamps): QueryBuilder
    {
        $this->timestamps = $timestamps;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimestampCreatedAt(): string
    {
        return $this->timestampCreatedAt;
    }

    /**
     * @param string $timestampCreatedAt
     *
     * @return QueryBuilder
     */
    public function setTimestampCreatedAt(string $timestampCreatedAt
    ): QueryBuilder {
        $this->timestampCreatedAt = $timestampCreatedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimestampUpdatedAt(): string
    {
        return $this->timestampUpdatedAt;
    }

    /**
     * @param string $timestampUpdatedAt
     *
     * @return QueryBuilder
     */
    public function setTimestampUpdatedAt(string $timestampUpdatedAt
    ): QueryBuilder {
        $this->timestampUpdatedAt = $timestampUpdatedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSelect()
    {
        return !empty($this->select) ? $this->select : "*";
    }

    /**
     * @param mixed $select
     *
     * @return QueryBuilder
     */
    public function setSelect($select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     *
     */
    public function clearSelect()
    {
        $this->select = "*";
    }

    /**
     * @param $select
     *
     * @return $this
     */
    public function select($select)
    {
        $this->setSelect($select);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param mixed $prefix
     *
     * @return QueryBuilder
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @param bool $withPrefix
     *
     * @return mixed
     */
    public function getTable($withPrefix = true)
    {
        if (is_null($this->table)) {
            $classBasename = (new \ReflectionClass($this))->getShortName();
            $this->setTable(strtolower($classBasename));
        }

        if ($withPrefix) {
            return $this->getPrefix() . $this->table;
        }

        return $this->table;
    }

    /**
     * @param mixed $table
     *
     * @return QueryBuilder
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function addParameter($key, $value)
    {
        $this->parameters[':' . $key] = $value;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function clearParameters()
    {
        $this->parameters = [];
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {

        //if (empty($this->where)) {
            $condition = '';
            $i = 0;
            foreach ($this->getWheres() as $where) {
                list($key, $con, $val, $and) = $this->getCondition($where, $i++);
                $condition .= empty($condition) ? '' : $and;

                if ($con == ' IN ') {
                    $condition .= $key . $con . '(' . $val . ')';
                } else {
                    $condition .= $key . $con . $val;
                }


            }
        //} else {
            //$condition = $this->where;
        //}

        return !empty($condition) ?
            " WHERE (" . $condition . ")" : null;
    }

    /**
     * @param mixed $where
     *
     * @return QueryBuilder
     */
    public function setWhere($where)
    {
        $this->where = $where;

        return $this;
    }

    /**
     * @return array
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * @param $strOrArray
     */
    public function addWheres($strOrArray)
    {
        $this->wheres[] = $strOrArray;
    }

    /**
     * @param $strOrArray
     *
     * @return $this
     */
    public function where($strOrArray)
    {

        if (func_num_args() === 1 && is_string($strOrArray)) {
            $this->setWhere($strOrArray);
        } else {
            foreach (func_get_args() as $arg) {
                $this->addWheres($arg);
            }
        }

        return $this;
    }

    /**
     *
     */
    public function clearWhere()
    {
        $this->where = '';
    }

    /**
     *
     */
    public function clearWheres()
    {
        $this->wheres = [];
    }

    /**
     * @param array $wheres
     *
     * @return QueryBuilder
     */
    public function setWheres(array $wheres): QueryBuilder
    {
        $this->wheres = $wheres;

        return $this;
    }

    /**
     * @return null
     */
    public function getOrderBy()
    {
        $orderBy = !empty($this->orderBy) ?
            $this->orderBy : $this->getPrimaryKey();


        $direction = 'ASC';

        if (preg_match('/\sASC/i', $orderBy)) {
            $orderBy = trim(str_ireplace(' ASC', '', $orderBy));
        }

        if (preg_match('/\sDESC/i', $orderBy)) {
            $direction = 'DESC';
            $orderBy = trim(str_ireplace(' DESC', '', $orderBy));
        }

        if ($orderBy == 'RAND()') {
            $orderBy = " ORDER BY RAND()";
        } else {
            $orderBy = " ORDER BY `" . $orderBy . "` " . $direction;
        }

        return $orderBy;
    }

    /**
     * @param null $orderBy
     *
     * @return QueryBuilder
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @return null
     */
    public function getLimit()
    {
        if (empty($this->limit)) {
            return null;
        }

        return " LIMIT " . intval($this->limit);
    }

    /**
     * @param null $limit
     *
     * @return QueryBuilder
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->setLimit($limit);

        return $this;
    }

    /**
     *
     */
    public function clearLimit()
    {
        $this->limit = null;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     *
     * @return QueryBuilder
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @param mixed $queryString
     *
     * @return QueryBuilder
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    /**
     * @param      $string
     * @param null $params
     *
     * @return QueryBuilder
     */
    public function setLastQuery($string, $params = null)
    {
        $this->lastQuery = [$string, $params];
        PDO::addExecutedQuery($this->lastQuery);

        return $this;
    }

    /**
     * @return mixed
     */
    public function lastQuery()
    {
        return $this->getLastQuery();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = PDO::connection();
    }


    public function clearQuery()
    {
        $this->clearParameters();
        $this->clearSelect();
        $this->clearWhere();
        $this->clearWheres();
        $this->clearLimit();
    }

    /**
     * @param $param
     *
     * @return array
     */
    protected function getCondition($param, $i)
    {
        $key = null;
        $cond = "=";
        $value = null;
        $and = "AND";

        if (is_array($param)) {

            $count = count($param);

            if ($count > 1) {
                $key = $param[0];
                $value = $param[1];
            }

            if ($count > 2) {
                $key = $param[0];
                $cond = $param[1];
                $value = $param[2];
            }

            if ($count > 3) {
                $key = $param[0];
                $cond = $param[1];
                $value = $param[2];
                $and = $param[3];
            }
        }

        if (is_null($value) && $cond == "=") {
            return [
                "ISNULL(" . $key . ")",
                null,
                null,
                " " . trim($and) . " "
            ];
        }

        if ($cond == 'IN') {
            //$value = "(" . $value . ")";
        } else {
            //$value = $this->db->quote($value);
        }

        $suffix = '_' . $i;

        $this->addParameter($key . $suffix, $value);

        return [
            str_replace('``', '', "`" . $key . "`"),
            " " . trim($cond) . " ",
            //is_null($value) ? "NULL" : $value,
            ":" . $key . $suffix,
            " " . trim($and) . " "
        ];
    }

    /**
     * @param null $sql
     *
     * @return $this
     * @throws DatabaseException
     */
    public function query($sql = null)
    {
        if (!$sql) {
            $sqlWhere = '';
            if (!empty($this->getWhere())) {
                $sqlWhere = $this->getWhere();
            }

            $sqlLimit = '';
            if (!empty($this->getLimit())) {
                $sqlLimit = $this->getLimit();
            }

            $sqlOrder = '';
            $orderBy = $this->getOrderBy();
            if (!empty($orderBy)) {
                $sqlOrder = $orderBy;
            }

            $sqlSelect = "SELECT " . $this->getSelect();
            $sqlFrom = " FROM " . $this->getTable();

            $sql = $sqlSelect . $sqlFrom . $sqlWhere . $sqlOrder . $sqlLimit;
        }
        $this->setLastQuery($sql);

        try {
            $stmt = $this->db->prepare($sql);
            $this->setLastQuery($stmt->queryString, $this->getParameters());
            $stmt->execute($this->getParameters());
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage() . '<br>' . $this->getLastQuery()[0]);
        }

        $this->setResult($stmt);
        $this->clearQuery();

        return $this;
    }

    /**
     * Return all the rows of this table
     *
     * @param null $sql Optional query string
     *
     * @return array|null
     * @throws DatabaseException
     */
    public function getAll($sql = null)
    {
        if ($sql) {
            $this->setLastQuery($sql);

            try {
                $results = $this->db->query($sql);
            } catch (\PDOException $e) {
                throw new DatabaseException($e->getMessage() . '<br>' . $this->getLastQuery()[0]);
            }

            return $this->fetchAssoc($results);
        }

        try {
            $results = $this->query();
        } catch (\PDOException $e) {

            throw new DatabaseException($e->getMessage() . '<br>' . $this->getLastQuery()[0]);
        }

        $this->clearQuery();

        return $results->fetchAssoc();
    }

    /**
     * Return the first the matching row
     *
     * @param null $sql Optional query string
     *
     * @return array|null
     * @throws DatabaseException
     */
    public function getFirst($sql = null)
    {
        if ($sql) {
            $this->setLastQuery($sql);

            try {
                $result = $this->db->query($sql);
            } catch (\PDOException $e) {
                throw new DatabaseException($e->getMessage() . '<br>' . $this->getLastQuery()[0]);
            }

            $data = $this->fetchAssoc($result);

            if (isset($data[0])) {
                return $data[0];
            }

            return null;
        }

        return $this->limit(1)->query()->fetchAssoc();
    }

    /**
     * Select a row by id from this table
     *
     * @param $id
     *
     * @return array|null
     * @throws DatabaseException
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE " . $this->getPrimaryKey() . " = " . intval($id) . ";";

        try {
            $result = $this->db->query($sql);
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage() . '<br>' . $sql);
        }
        return $this->fetchAssoc($result);

    }

    /**
     * @param $attributes
     *
     * @return null
     * @throws DatabaseException
     */
    public function insert($attributes)
    {
        $params = [];
        $setStr = "";

        foreach ($attributes as $key => $value) {
            if ($key != $this->getPrimaryKey()) {
                $setStr .= "`" . str_replace("`", "``",
                        $key) . "` = :" . $key . ",";
                $value = is_array($value) ? json_encode($value) : $value;
                $params[':' . $key] = $value;
            }
        }

        if ($this->timestamps && !is_null($this->timestampCreatedAt)) {
            $params[':' . $this->timestampCreatedAt] = date('Y-m-d H:i:s');
        }

        $paramStr = implode("`" . ',', array_keys($params));
        $colStr = str_replace(':', "`", $paramStr) . "`";
        $paramStr = str_replace("`", "", $paramStr);

        $sql = "INSERT INTO " . $this->getTable() .
            " (" . $colStr . ") VALUES (" . $paramStr . ")";

        try {
            $stmt = $this->db->prepare($sql);
            $this->setLastQuery($stmt->queryString, $params);
            $stmt->execute($params);
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage() . '<br>' . $this->getLastQuery()[0]);
        }

        $insertId = $this->getInsertId();

        return $insertId;
    }

    /**
     * @return mixed
     */
    public function getInsertId()
    {
        return $this->db->lastInsertId();
    }

    /**
     * @param $id
     * @param $attributes
     *
     * @return null
     * @throws DatabaseException
     */
    public function update($id, $attributes)
    {
        $params = [];
        $setStr = "";

        foreach ($attributes as $key => $value) {
            if (!in_array($key, [
                $this->getPrimaryKey(),
                $this->timestampCreatedAt,
                $this->timestampUpdatedAt
            ])
            ) {
                $setStr .= "`" . str_replace("`", "``",
                        $key) . "` = :" . $key . ",";
                $params[':' . $key] = is_array($value) ? json_encode($value) : $value;
            }
        }

        if ($this->timestamps && !is_null($this->timestampUpdatedAt)) {
            $params[':' . $this->timestampUpdatedAt] = date('Y-m-d H:i:s');
            $setStr .= "`" . str_replace("`", "``", $this->timestampUpdatedAt) .
                "` = :" . $this->timestampUpdatedAt . ",";
        }

        $params[':id'] = $id;

        $sql = "UPDATE " . $this->getTable() . " SET " . rtrim($setStr, ',') .
            " WHERE " . $this->getPrimaryKey() . " = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $this->setLastQuery($stmt->queryString, $params);

            $stmt->execute($params);
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage() . '<br>' . $this->getLastQuery()[0]);

        }

        return $stmt->rowCount();
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function delete($id)
    {
        $delete = "DELETE FROM " . $this->getTable() . " " .
            "WHERE " . $this->getPrimaryKey() . " = '" . intval($id) . "' LIMIT 1 ;";

        return $this->db->query($delete);
    }

    /**
     * Count rows
     *
     * @return int|null
     * @throws DatabaseException
     */
    public function count()
    {
        $this->select("COUNT(" . $this->getPrimaryKey() . ") as count");

        try {
            $results = $this->query();
        } catch (\PDOException $e) {

            throw new DatabaseException($e->getMessage() . '<br>' . $this->getLastQuery()[0]);
        }

        $results = $results->fetchAssoc();

        return $results[0]['count'];
    }


    /**
     * Mysql result fetch
     *
     * @param $result
     *
     * @return array|null
     */
    public function fetchAssoc($result = null)
    {
        $result = $result ? $result : $this->getResult();

        $rows = [];

        if ($result->rowCount() > 0) {
            $results = $result->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($results as $key => $value) {
                $rows[$key] = $value;
            }
        }

        return $rows;
    }

    /**
     * Mysql result fetch
     *
     * @param $result
     *
     * @return Collection|null
     */
    public function collect($result = null)
    {
        $result = $result ? $result : $this->getResult();

        if ($result->rowCount() > 0) {
            $collection = new Collection();
            foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $collection->add($row);
            }

            return $collection;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function tableExists()
    {
        $sql = "SELECT * 
            FROM information_schema.tables
            WHERE table_schema = '" . PDO::getDatabase() . "' 
                AND table_name = '" . $this->getTable() . "'
            LIMIT 1;";

        $result = $this->db->query($sql);

        return count($this->fetchAssoc($result)) > 0;
    }

    /**
     * @return bool|string
     */
    public function createTable()
    {
        if (!$this->tableExists()) {

            $str = '';
            /** @noinspection PhpUndefinedMethodInspection */
            // TODO: protect columns
            foreach ($this->getColumns() as $column) {

                if ($column['type']) {
                    $str = empty($str) ? $str : $str . ", ";

                    if ($this->getPrimaryKey() == $column['name']) {
                        $str .= "`" . $column['name'] . "` " . strtolower($column['type']);
                    } else {
                        $str .= "`" . $column['name'] . "` " . strtolower($column['type']);
                    }
                }

            }

            $sql = "CREATE TABLE `" . $this->getTable() . "` (" . $str . ");";

            try {
                $this->setLastQuery($sql);
                $this->db->exec($sql);

                return $sql;
            } catch (DatabaseException $e) {
                return $e->getMessage() . ': ' . $sql;
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws DatabaseException
     */
    public function truncate()
    {
        $sql = "TRUNCATE TABLE `" . $this->getTable(true) . "`;";
        try {
            $this->db->query($sql);
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage() . '<br>' . $sql);
        }

        return true;
    }

    /**
     * @param array  $ids
     * @param int    $id
     * @param string $table
     * @param string $foreignKey
     * @param string $localKey
     */
    public function attach(
        Array $ids,
        int $id,
        string $table,
        string $foreignKey,
        string $localKey
    ) {
        is_array($ids) || $ids = [$ids];

        foreach ($ids as $relatedId) {

            $sql = "SELECT " . $localKey . " FROM " . $table . " 
				WHERE " . $localKey . " = '" . $id . "' AND " . $foreignKey . " = '" . intval($relatedId) . "'";

            $result = $this->db->query($sql);

            if (count($this->fetchAssoc($result)) == 0) {
                $sqlInsert = "INSERT INTO " . $table . " 
					(" . $localKey . ", " . $foreignKey . ") 
					VALUES ('" . $id . "', '" . intval($relatedId) . "')";
                $this->db->query($sqlInsert);
            }
        }
    }

    /**
     * @param array  $ids
     * @param int    $id
     * @param string $table
     * @param string $foreignKey
     * @param string $localKey
     */
    public function detach(
        Array $ids,
        int $id,
        string $table,
        string $foreignKey,
        string $localKey
    ) {
        is_array($ids) || $ids = [$ids];

        foreach ($ids as $relatedId) {
            $sql = "DELETE FROM " . $table . "
				WHERE " . $localKey . " = '" . $id . "' AND " . $foreignKey . " = '" . intval($relatedId) . "'";
            $this->db->query($sql);
        }
    }

    /**
     * @param ORM $relatedModel
     *
     * @return string
     */
    public function guessPivotTable(ORM $relatedModel)
    {
        $tableNames = [$this->getTable(false), $relatedModel->getTable(false)];
        sort($tableNames);

        return $this->getPrefix() . $tableNames[0] . "_" . $tableNames[1];
    }

}