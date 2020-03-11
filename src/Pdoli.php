<?php

namespace kongkawing;

if (!class_exists('PDO')) {
    exit('Error: PHP PDO module not exist.');
}

class Pdoli extends \PDO
{
    /**
     *
     * @var string sql
     */
    protected $_sql;
    /**
     *
     * @var array bind value
     */
    protected $_bindVales = array();
    /**
     *
     * @var array error
     */
    protected $_error;

    /**
     * @var String
     */
    protected $_where;
    /**
     * @var String
     */
    protected $_orderBy;
    /**
     * @var String
     */
    protected $_limit;
    /**
     * @var String
     */
    protected $_groupBy;
    /**
     * @var String
     */
    protected $_join;

    /**
     * @param $username
     * @param $password
     * @param $database
     * @param string $host
     * @param string $type
     * @return Pdoli|null
     */
    public static function conn($username, $password, $database, $host = 'localhost', $type = 'mysql')
    {
        static $o = NULL;
        if (is_null($o)) {
            $o = new self($username, $password, $database, $host, $type);
        }
        return $o;
    }

    /**
     * Pdoli constructor.
     * @param $username
     * @param $password
     * @param $database
     * @param string $host
     * @param int $port
     * @param string $type
     */

    public function __construct($username, $password, $database, $host = 'localhost', $port = 3306, $type = 'mysql')
    {
        $dsn = "{$type}:host={$host};port={$port};dbname={$database}";
        try {
            parent::__construct($dsn, $username, $password, array());
            parent::exec('SET NAMES utf8mb4');
        } catch (Exception $e) {
            exit('Database Connection Fail:' . $e->getMessage());
        }
        return $this;
    }

    /**
     * query sql statement and get result
     *
     * @param string $sql
     * @return array|bool|PDOStatement
     */
    public function query($sql)
    {
        $this->_sql = $sql;
        $rs = parent::query($sql);
        if ($rs === FALSE) {
            $this->_error = $this->errorInfo();
        }
        if ($rs) {
            return $rs->fetchAll(PDO::FETCH_ASSOC);
        }
        return array();
    }

    /**
     * execute sql statement and get affected rows
     *
     * @param string $sql
     * @return int
     */
    public function exec($sql)
    {
        $this->_sql = $sql;
        $rs = parent::exec($sql);
        if ($rs === FALSE) {
            $this->_error = $this->errorInfo();
        }
        return $rs;
    }

    /**
     *
     * Build where statement ->where(['name'=>'ray','last_name'=>'kong'])->where(['name'=>'%ray%'],'LIKE')->where(['create_time'=>'123456789'],'>=')
     *
     * @param array $condition
     * @param string $operator
     * @param string $glue
     * @param bool $endSymbol
     * @return $this
     */
    public function where(array $condition, $operator = '=', $glue = 'AND', $endSymbol = false)
    {
        $glue = " $glue ";
        $this->_where = ($this->_where) ? $this->_where . $glue : 'WHERE ';
        $this->_where .= $this->_buildCondition($condition, $operator, $glue);
        if ($endSymbol) {
            $this->_where .= $endSymbol;
        }
        return $this;
    }

    /**
     *
     * Build order by statement ->orderBy('type DESC')->orderBy(['id DESC','name ASC'])
     *
     * @param array $orders
     * @return $this
     */
    public function orderBy($orders)
    {
        $glue = ', ';
//        $this->_orderBy = ($this->_orderBy) ? $this->_orderBy . $glue : 'ORDER BY ';
        $this->_orderBy = 'ORDER BY ';
        if (is_array($orders)) {
            $arr = [];
            foreach ($orders as $k => $v) {
                $arr[] = "$k $v";
            }
            $this->_orderBy .= implode($glue, $arr);
        } else {
            $this->_orderBy .= $orders;
        }
        return $this;
    }

    /**
     *
     * Build limit statement ->limt('1'),->limit('0,10');
     *
     * @param string $limit
     * @return $this
     */
    public function limit($limit = "")
    {
        $this->_limit = "LIMIT $limit";
        return $this;
    }

    /**
     *
     * Build group by statement  ->groupBy(column),->groupBy([column1,column2,column3])
     *
     * @param $groups
     * @return $this
     *
     */
    public function groupBy($groups)
    {
        $glue = ', ';
//        $this->_groupBy = ($this->_groupBy) ? $this->_groupBy . $glue : 'GROUP BY ';
        $this->_groupBy = 'GROUP BY ';
        if (is_array($groups)) {
            $this->_groupBy .= implode($glue, $groups);
        } else {
            $this->_groupBy .= $groups;
        }
        return $this;
    }

    /**
     *
     * Build join statement ->join('details','master.id = details.master_id')->join('invoice','master.id = invoice.master_id')
     *
     * @param $table
     * @param $condition
     * @param string $type
     * @return $this
     */
    public function join($table, array $condition, $type = 'LEFT JOIN')
    {
        $glue = ' AND ';
        if (is_null($this->_join)) {
            $this->_join = '';
        } else {
            $this->_join .= "\n";
        }
        $this->_join .= "$type $table ON ";
        $arr = [];
        foreach ($condition as $k => $v) {
            $arr[] = "$k = $v";
        }
        $this->_join .= implode($glue, $arr);
        return $this;
    }

    /**
     * @param $table
     * @param null $field
     * @return bool|int
     */
    public function find($table, $field = NULL)
    {
        $sql = $this->_selectBuilder($table, $field);
        return $this->run($sql);
    }

    /**
     * @param $table
     * @param null $field
     * @return bool|int
     */
    public function findOne($table, $field = NULL)
    {
        $sql = $this->_selectBuilder($table, $field);
        return $this->run($sql, 'one');
    }

    /**
     * @param $table
     * @param null $field
     * @return bool|int
     */
    public function findCount($table, $field = NULL)
    {
        $sql = $this->_selectBuilder($table, $field);
        return $this->run($sql, 'count');
    }

    /**
     * @param $table
     * @param array $data
     * @return array|bool|int
     */
    public function insert($table, array $data)
    {
        $sql = "INSERT INTO `$table` \n";
        $sql .= '(' . implode(', ', array_keys($data)) . ") \n";
        $arr = array();
        foreach ($data as $k => $v) {
            $bindKey = ":$k" . count($this->_bindVales);
            $this->_bindVales[$bindKey] = $v;
            $arr[] = $bindKey;
        }
        $sql .= 'VALUES (' . implode(', ', $arr) . ');';
        return $this->run($sql);
    }

    /**
     * @param $table
     * @param array $data
     * @return bool|int
     */
    public function update($table, array $data)
    {
        $sql = "UPDATE `$table` \n";
        $sql .= 'SET ' . $this->_buildCondition($data) . " \n";
        if (empty($this->_where)) {
            return FALSE;
        }
        $sql .= "$this->_where \n";
        if (!empty($this->_limit)) {
            $sql .= "$this->_limit \n";
        }
        return $this->run($sql, $data);
    }

    /**
     * @param $table
     * @return bool|int
     */
    public function delete($table)
    {
        $sql = "DELETE FROM `$table` \n";
        if (empty($this->_where)) {
            return FALSE;
        }
        $sql .= "$this->_where \n";

        return $this->run($sql);
    }

    public function lastSQL()
    {
        return $this->_sql;
    }

    public function error()
    {
        return $this->_error;
    }

    /**
     * @param $table
     * @param $field
     * @return string
     */
    private function _selectBuilder($table, $field)
    {
        if (is_null($field)) {
            $field = '*';
        } else if (is_array($field)) {
            $field = implode(', ', $field);
        }
        $sql = "SELECT $field \n";
        $sql .= "FROM $table \n";
        if (!empty($this->_join)) {
            $sql .= "$this->_join \n";
        }
        if (!empty($this->_where)) {
            $sql .= "$this->_where \n";
        }
        if (!empty($this->_groupBy)) {
            $sql .= "$this->_groupBy \n";
        }
        if (!empty($this->_orderBy)) {
            $sql .= "$this->_orderBy \n";
        }
        if (!empty($this->_limit)) {
            $sql .= "$this->_limit \n";
        }
        return $sql;
    }

    private function _buildCondition($data, $operator = '=', $glue = ', ')
    {
        $param = [];
        //Handling IN Clause
        if ($operator === 'IN' || $operator === 'NOT IN') {
            foreach ($data as $k => $v) {
                //Passed value must be array
                if (!is_array($v) || !$v) {
                    return FALSE;
                }
                $bindKey = array();
                foreach ($v as $key => $value) {
                    $bindKey[$key] = str_replace('.', '_', ":{$k}_IN_" . count($this->_bindVales));
                    $this->_bindVales[$bindKey[$key]] = $value;
                }
                $param[] = "$k $operator (" . implode(', ', $bindKey) . ')';
            }
            return implode($glue, $param);
        }
        foreach ($data as $k => $v) {
            //Remove . from bind key e.g :master.id -> :master_id form join table
            $bindKey = str_replace('.', '_', ":${k}_" . count($this->_bindVales));
            $this->_bindVales[$bindKey] = $v;
//          $caller = debug_backtrace()[1]['function'];
            //If value is NULL set to IS clause from WHERE condition
//            if (is_null($v) && $caller == 'where') {
//                $param[] = "{$k} IS $bindKey";
//                continue;
//            }
            $param[] = "{$k} {$operator} $bindKey";
        }
        return implode($glue, $param);
    }

    /**
     *
     * execute sql with bindValue
     *
     * @param $sql
     * @param string $opt
     * @return bool|int|array
     */
    private function run($sql, $opt = '')
    {
        $this->_sql = trim($sql);
        $bindValues = $this->_bindVales;
        $this->_clean();
        try {
            $stmt = $this->prepare($this->_sql);
            foreach ($bindValues as $k => $v) {
                if (is_null($v)) {
                    $stmt->bindValue($k, $v, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue($k, $v);
                }
            }
            if ($stmt->execute() !== false) {
                if (preg_match("/^(" . implode("|", array("select", "describe", "pragma")) . ") /i", $this->_sql)) {
                    if ($opt == 'one') {
                        return $stmt->fetch(PDO::FETCH_ASSOC);
                    } else if ($opt == 'count') {
                        return $stmt->rowCount();
                    } else {
                        return $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                } elseif (preg_match("/^(" . implode("|", array("delete", "update")) . ") /i", $this->_sql)) {
                    return $stmt->rowCount();
                } elseif (preg_match("/^(" . implode("|", array("insert")) . ") /i", $this->_sql)) {
                    return $this->lastInsertId();
                }
            } else {
                $this->_error = $stmt->errorInfo();
                return FALSE;
            }
        } catch (PDOException $e) {
            $this->_error = $e->getMessage();
            return FALSE;
        }
        return FALSE;
    }

    /**
     *
     * Clean all properties
     *
     * @return $this
     */
    private function _clean()
    {
        $this->_join = $this->_where = $this->_groupBy = $this->_orderBy = $this->_limit = $this->_error = (string)NULL;
        $this->_bindVales = array();
        return $this;
    }
}