<?php
namespace Core;
use \PDO;

class Database
{
	// PDO instance
	public ?PDO $pdo = null;

	// Select statement
	private $select = '*';
	// Table name
	private $table = null;

	// Where statement
	public $where = null;

	// Limit statement
	private $limit = null;

	// Join statement
	private $join = null;

	// Order By statement
	private $orderBy = null;

	// Group By statement
	private $groupBy = null;

	// Having statement
	private $having = null;

	// Last instert id
	private $insertId = null;

	// Custom query
	private $custom = null;

	// SQL Statement
	private $sql = null;

	// Table prefix
	private $prefix = null;

	// using pdo driver
	private $driver = null;

	// Error
	private $error = null;

	// Number of total rows
	private $numRows = 0;

	// Group flag for where and having statements
	private $grouped = 0;

	// Singleton Class
	private static $_instance;

	/**
	 * Initializing
	 *
	 * @return object
	 */
	public function __construct()
	{
		$start = microtime(true);
		$config = (object) require_once(APP_DIR . "/config/database.php");

		$this->driver = $config->driver;
		$this->prefix = $config->prefix;

		$dsn = '';
		// Setting connection string
		if ($config->driver == 'mysql' || $config->driver == 'pgsql' || $config->driver == '')
			$dsn = $config->driver . ':host=' . $config->host . ';dbname=' . $config->name;
		elseif ($config->driver == 'sqlite')
			$dsn = 'sqlite:' . $config->name;
		elseif ($config->driver == 'oracle')
			$dsn = 'oci:dbname=' . $config->host . '/' . $config->name;
		elseif ($config->driver == "sqlsrv")
			$dsn = "sqlsrv:Server=" . $config->host . ";";

		if (empty($config->name))
			return;

		// Connecting to server
		try {
			$attr[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
			$attr[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_OBJ;

			#if($config->driver == "sqlsrv")
			#$attr[PDO::SQLSRV_ATTR_ENCODING]	=	PDO::SQLSRV_ENCODING_UTF8;

			if ($config->driver == "mysql")
				$attr[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8'";

			if($config->driver == "sqlsrv")
			{
				$attr[PDO::SQLSRV_ATTR_FORMAT_DECIMALS] = true;
				$attr[PDO::SQLSRV_ATTR_DECIMAL_PLACES] = 2;
			}

			$this->pdo = new PDO($dsn, $config->user, $config->password, $attr);
		}
		catch (PDOException $e) {
			$errorMessage = "<b>DB ERROR</b><hr>Can not connect to Database<br><br>";

			if (ini_get("display_errors"))
				$errorMessage .= $e->getMessage();

			die($errorMessage);
		}

		$now = microtime(true);
		$time = $now - $start;
		$time += $now - $_SERVER["REQUEST_TIME_FLOAT"];

		stackMessages("Database connected succesfully in ".number_format($time * 1000, 2)." ms");
	}

	public static function get() : Database
	{
		if (self::$_instance === null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Defines columns to select
	 *
	 * @param string $select
	 * @return $this
	 */
	public function select($select = null)
	{
		if (! is_null($select))
			$this->select = $select;

		return $this;
	}

	public function prefix($db)
	{
		$this->prefix = $this->driver == "sqlsrv" ? $db . ".." : $db;
		return $this;
	}
	/**
	 * Defines table
	 *
	 * @param string $table
	 * @return $this
	 */
	public function from($table)
	{
		$this->table = $this->prefix . $table;

		return $this;
	}

	/**
	 * Defines 'Left Join' operation
	 *
	 * @param string $table
	 * @param string $op
	 * @return $this
	 */
	public function leftJoin($table, $op)
	{
		$this->_join($table, $op, 'LEFT');

		return $this;
	}

	/**
	 * Defines 'Right Join' operation
	 *
	 * @param string $table
	 * @param string $op
	 * @return $this
	 */
	public function rightJoin($table, $op)
	{
		$this->_join($table, $op, 'RIGHT');

		return $this;
	}

	/**
	 * Defines 'Inner Join' operation
	 *
	 * @param string $table
	 * @param string $op
	 * @return $this
	 */
	public function innerJoin($table, $op)
	{
		$this->_join($table, $op, 'INNER');

		return $this;
	}

	/**
	 * Defines 'Full Outer Join' operation
	 *
	 * @param string $table
	 * @param string $op
	 * @return $this
	 */
	public function fullOuterJoin($table, $op)
	{
		$this->_join($table, $op, 'FULL OUTER');

		return $this;
	}

	/**
	 * Defines 'Left Outer Join' operation
	 *
	 * @param string $table
	 * @param string $op
	 * @return $this
	 */
	public function leftOuterJoin($table, $op)
	{
		$this->_join($table, $op, 'LEFT OUTER');

		return $this;
	}

	/**
	 * Defines 'Right Outer Join' operation
	 *
	 * @param string $table
	 * @param string $op
	 * @return $this
	 */
	public function rightOuterJoin($table, $op)
	{
		$this->_join($table, $op, 'RIGHT OUTER');

		return $this;
	}

	/**
	 * Defines 'Join' operation
	 *
	 * @param string $table
	 * @param string $op
	 * @return $this
	 */
	public function join($table, $op)
	{
		$this->_join($table, $op, '');

		return $this;
	}

	/**
	 * Defines 'Join' operations
	 *
	 * @param string $table
	 * @param string $op
	 * @return $this
	 */
	private function _join($table, $op, $join)
	{
		$this->join = $this->join . ' ' . $join . ' JOIN ' . $this->prefix . $table . ' ON ' . $op;
	}

	/**
	 * Escape data
	 *
	 * @param string $data
	 * @return string
	 */
	private function _escape($data)
	{
		return $this->pdo->quote(trim($data));
	}

	/**
	 * Defines 'where' operation
	 *
	 * @param string $column
	 * @param string $op
	 * @param string $value
	 * @param string $logic
	 * @return $this
	 */
	public function where($column, $op = '=', $value = '', $logic = 'AND', $escape = true)
	{
		if (is_null($this->where)) {
			$this->where = 'WHERE ' . $column . $op . ($escape ? $this->_escape($value) : $value);
		}
		else {
			if ($this->grouped > 0) {
				$this->where .= ' ' . $column . $op . ($escape ? $this->_escape($value) : $value);
				;
				$this->grouped = 0;
			}
			else {
				$this->where .= ' ' . $logic . ' ' . $column . $op . ($escape ? $this->_escape($value) : $value);
				;
			}
		}

		return $this;
	}

	/**
	 * Defines 'or where' operation
	 *
	 * @param string $column
	 * @param string $op
	 * @param string $value
	 * @return $this
	 */
	public function or_where($column, $op = '=', $value = '')
	{
		$this->where($column, $op, $value, 'OR');

		return $this;
	}

	/**
	 * Start a group of 'where' operation
	 *
	 * @param string $logic
	 * @return $this
	 */
	public function whereGroupStart($logic = 'AND')
	{
		$this->where .= ' ' . $logic . ' (';
		$this->grouped++;

		return $this;
	}

	/**
	 * End a group of 'where' operation
	 *
	 * @return $this
	 */
	public function whereGroupEnd()
	{
		$this->where .= ' )';
		$this->grouped = 0;

		return $this;
	}

	/**
	 * Start a group of 'having' operation
	 *
	 * @param string $logic
	 * @return $this
	 */
	public function havingGroupStart($logic = 'AND')
	{
		$this->having .= ' ' . $logic . ' (';
		$this->grouped++;

		return $this;
	}

	/**
	 * End a group of 'having' operation
	 *
	 * @return $this
	 */
	public function havingGroupEnd()
	{
		$this->having .= ' )';
		$this->grouped = 0;

		return $this;
	}

	/**
	 * Defines 'Order By' operation
	 *
	 * @param string $column
	 * @param string $sort
	 * @return $this
	 */
	public function orderBy($column, $sort = 'asc')
	{
		if (is_null($this->orderBy))
			$this->orderBy = ' ORDER BY ' . $column . ' ' . $sort;
		else
			$this->orderBy .= ', ' . $column . ' ' . $sort;

		return $this;
	}

	/**
	 * Defines 'Limit' operation
	 *
	 * @param integer $start
	 * @param integer $row
	 * @return $this
	 */
	public function limit($start, $rows = 0)
	{
		if ($rows === 0) {
			if ($this->driver == "sqlsrv") {
				$this->limit = ' TOP ' . $start;
			}
			else {
				$this->limit = ' LIMIT ' . $start;
			}
		}
		else
			$this->limit = ' LIMIT ' . $start . ', ' . $rows;

		return $this;
	}

	/**
	 * Defines 'Group By' operation
	 *
	 * @param array $data
	 * @return $this
	 */
	public function groupBy($data)
	{
		$this->groupBy = "GROUP BY " . join(",", $data);

		return $this;
	}

	/**
	 * Defines 'Group By' operation
	 *
	 * @param string $column
	 * @return $this
	 */
	public function groupByOne($column)
	{
		if (is_null($this->groupBy))
			$this->groupBy = ' GROUP BY ' . $column;
		else
			$this->groupBy .= ', ' . $column;

		return $this;
	}
	/**
	 * Defines 'Having' operation
	 *
	 * @param string $column
	 * @param string $op
	 * @param string $value
	 * @param string $logic
	 * @return $this
	 */
	public function having($column, $op = '=', $value = '', $logic = 'AND')
	{
		if (is_null($this->having))
			$this->having = 'HAVING ' . $column . $op . $this->_escape($value);
		else {
			if ($this->grouped > 0) {
				$this->having .= ' ' . $column . $op . $value;
				$this->grouped = 0;
			}
			else {
				$this->having .= ' ' . $logic . ' ' . $column . $op . $this->_escape($value);
			}
		}

		return $this;
	}

	/**
	 * Defines 'Or Having' operation
	 *
	 * @param string $column
	 * @param string $op
	 * @param string $value
	 * @return $this
	 */
	public function orHaving($column, $op = '=', $value = '')
	{
		$this->having($column, $op, $value, 'OR');

		return $this;
	}

	/**
	 * Defines 'Like' operation
	 *
	 * @param string $column
	 * @param string $value
	 * @param string $logic
	 * @return $this
	 */
	public function like($column, $value, $logic = 'AND')
	{
		$this->where($column, ' LIKE ', $value, $logic);

		return $this;
	}

	/**
	 * Defines 'Or Like' operation
	 *
	 * @param string $column
	 * @param string $value
	 * @return $this
	 */
	public function orLike($column, $value)
	{
		$this->like($column, $value, 'OR');

		return $this;
	}

	/**
	 * Defines 'Not Like' operation
	 *
	 * @param string $column
	 * @param string $value
	 * @param string $logic
	 * @return $this
	 */
	public function notLike($column, $value, $logic = 'AND')
	{
		$this->where($column, ' NOT LIKE ', $value, $logic);

		return $this;
	}

	/**
	 * Defines 'Or Not Like' operation
	 *
	 * @param string $column
	 * @param string $value
	 * @return $this
	 */
	public function orNotLike($column, $value)
	{
		$this->notLike($column, $value, 'OR');

		return $this;
	}

	/**
	 * Defines 'In' operation
	 *
	 * @param string $column
	 * @param array $list
	 * @param string $logic
	 * @return $this
	 */
	public function in($column, $list = [], $logic = 'AND')
	{
		$in_list = '';

		foreach ($list as $element) {
			$in_list .= $this->_escape($element) . ',';
		}
		$in_list = '(' . rtrim($in_list, ',') . ')';

		$this->where($column, ' in', $in_list, $logic, false);

		return $this;
	}

	/**
	 * Defines 'Or In' operation
	 *
	 * @param string $column
	 * @param array $list
	 * @return $this
	 */
	public function orIn($column, $list = [])
	{
		$this->in($column, $list, 'OR');

		return $this;
	}

	/**
	 * Defines 'Not In' operation
	 *
	 * @param string $column
	 * @param array $list
	 * @param string $logic
	 * @return $this
	 */
	public function notIn($column, $list = [], $logic = 'AND')
	{
		$in_list = '';

		foreach ($list as $element) {
			$in_list .= $this->_escape($element) . ',';
		}
		$in_list = '(' . rtrim($in_list, ',') . ')';

		$this->where($column, 'NOT IN', $in_list, $logic);

		return $this;
	}

	/**
	 * Defines 'Or Not In' operation
	 *
	 * @param string $column
	 * @param array $list
	 * @return $this
	 */
	public function orNotIn($column, $list = [])
	{
		$this->notIn($column, $list, 'OR');

		return $this;
	}

	/**
	 * Defines 'Between' operation
	 *
	 * @param string $column
	 * @param array $first
	 * @param string $second
	 * @param string $logic
	 * @return $this
	 */
	public function between($column, $first, $second, $logic = 'AND')
	{
		if (is_null($this->where))
			$this->where = 'WHERE ' . $column . " BETWEEN " . $this->_escape($first) . " $logic " . $this->_escape($second);
		else {
			if ($this->grouped > 0) {
				$this->where .= ' ' . $column . " BETWEEN " . $this->_escape($first) . " $logic " . $this->_escape($second);
				$this->grouped = 0;
			}
			else {
				$this->where .= ' AND ' . $column . " BETWEEN " . $this->_escape($first) . " $logic " . $this->_escape($second);
			}
		}

		return $this;
	}

	/**
	 * Defines 'Or Between' operation
	 *
	 * @param string $column
	 * @param array $first
	 * @param string $second
	 * @return $this
	 */
	public function orBetween($column, $first, $second)
	{
		$this->between($column, $first, $second, 'OR');

		return $this;
	}

	/**
	 * Defines 'Not Between' operation
	 *
	 * @param string $column
	 * @param array $first
	 * @param string $second
	 * @param string $logic
	 * @return $this
	 */
	public function notBetween($column, $first, $second, $logic = 'AND')
	{
		$this->where($column, ' NOT BETWEEN ', $first . ' AND ' . $second, $logic);

		return $this;
	}

	/**
	 * Defines 'Or Not Between' operation
	 *
	 * @param string $column
	 * @param array $first
	 * @param string $second
	 * @return $this
	 */
	public function orNotBetween($column, $first, $second)
	{
		$this->notBetween($column, $first, $second, 'OR');

		return $this;
	}

	/**
	 * Fetch a row
	 *
	 * @param string $fetch
	 * @return object|array
	 */
	public function result($fetch = 'object', $params = [])
	{
		try {
			$this->_prepare();
			$query = $this->pdo->prepare($this->sql);
			$run = $query->execute($params);

			$this->_reset();

			if ($fetch == 'object')
				$row = $query->fetch(PDO::FETCH_OBJ);
			else
				$row = $query->fetch(PDO::FETCH_ASSOC);

			return $row;
		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
			echo $this->error;
		}
	}

	/**
	 * Fetch a recordset
	 *
	 * @param string $fetch
	 * @return object|array
	 */
	public function results($fetch = 'object', $params = [])
	{
		try {
			$this->_prepare();
			$query = $this->pdo->prepare($this->sql);
			$run = $query->execute($params);

			$this->_reset();
			if ($run)
				if ($fetch == 'array')
					$result = $query->fetchAll(PDO::FETCH_ASSOC);
				else
					$result = $query->fetchAll(PDO::FETCH_OBJ);
			else
				return false;

			$this->numRows = $query->rowCount();
			return $result;
		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
	}


	public function first($params = [])
	{
		$this->_prepare();
		$query = $this->pdo->prepare($this->sql);
		$run = $query->execute($params);
		$this->_reset();
		if ($run)
			return $query->fetchColumn(0);
	}

	/**
	 * Execute a query
	 *
	 * @param string $query
	 * @return object
	 */
	private function _query($query)
	{
		$this->_reset();

		return $this->pdo->query($query);
	}

	/**
	 * Prepare a query
	 *
	 * @return void
	 */
	private function _prepare()
	{
		if (! $this->custom)
			$this->sql = rtrim('SELECT ' . ($this->driver == "sqlsrv" ? $this->limit : "") . ' ' . $this->select . ' FROM ' . $this->table . ' ' . $this->join . ' ' . $this->where . ' ' . $this->groupBy . ' ' . $this->having . ' ' . $this->orderBy . ' ' . ($this->driver != "sqlsrv" ? $this->limit : ""));
	}

	/**
	 * Execute a custom query
	 *
	 * @param string $query
	 * @return mixed
	 */
	public function query($query) : Database
	{
		$this->custom = true;
		$this->sql = $query;
		return $this;
	}

	/**
	 * Insert a row to table
	 *
	 * @param array $data
	 * @return integer
	 */
	public function insert($data)
	{
		$insert_sql = 'INSERT INTO ' . $this->table . '(';
		$col = [];
		$val = [];
		$stmt = [];
		$insert_sql .= implode(',', array_keys($data)) . ') VALUES(';
		foreach ($data as $column => $value) {
			$val[] = $value;
			$col[] = ' ? ';
			$stmt[] = $this->_escape($value);
		}

		$this->sql = $insert_sql . implode(', ', $stmt) . ');';
		$insert_sql .= implode(',', $col);
		$insert_sql .= ');';

		if ($this->driver == "sqlsrv")
			$insert_sql .= " select scope_identity()";

		try {
			$query = $this->pdo->prepare($insert_sql);
			$executing = $query->execute($val);

			$this->_reset();

			if (! $executing)
				return false;

			if ($this->driver == "sqlsrv") {
				$query->nextRowSet();
				$this->insertId = $query->fetchColumn(0);
			}

			$this->insertId = $this->pdo->lastInsertId();
		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
		}

		return true;
	}

	/**
	 * Update operations
	 *
	 * @param array $data
	 * @return integer
	 */
	public function update($data = [], $op = "=")
	{
		if (is_null($this->table))
			throw new Exception('DB Hatası', 'UPDATE işlemi yapılacak tablo seçilmedi.');

		$update_sql = 'UPDATE ' . $this->table . ' SET ';

		$col = [];
		$val = [];
		$stmt = [];

		foreach ($data as $column => $value) {
			$val[] = $value;
			$col[] = $column . '= ? ';
			$stmt[] = $column . $op . $this->_escape($value);
		}

		$this->sql = $update_sql . implode(', ', $stmt);
		$update_sql .= implode(',', $col);

		$this->sql .= ' ' . $this->where;
		$update_sql .= ' ' . $this->where;
		try {
			$query = $this->pdo->prepare($update_sql);
			$update = $query->execute($val);
			// reset
			$this->_reset();

			if (! $update)
				return false;

			return $query->rowCount();

		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
	}

	/**
	 * Delete operations
	 *
	 * @return integer
	 */
	public function delete()
	{
		if (is_null($this->table))
			throw new Exception('DB Hatası', 'DELETE işlemi yapılacak tablo seçilmedi.');

		$delete_sql = 'DELETE FROM ' . $this->table . ' ' . $this->where;
		$this->sql = $delete_sql;

		try {
			$query = $this->pdo->prepare($delete_sql);
			$executeQuery = $query->execute();
			// reset
			$this->_reset();

			if (! $executeQuery) {
				return 0;
			}

			return $query->rowCount();

		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
	}

	/**
	 * Fetch a row
	 *
	 * @param string $fetch
	 * @return object|array
	 */
	public function proc($params = [], $fetch = 'object', $first = false)
	{
		try {
			$this->sql = "
				set nocount on
				declare @r int
				exec @r = $this->sql
				select @r result
			";

			$this->_prepare();
			$query = $this->pdo->prepare($this->sql);
			$run = $query->execute($params);

			$this->_reset();

			if (! $first) {
				if ($fetch == 'object')
					$row = $query->fetch(PDO::FETCH_OBJ);
				else
					$row = $query->fetch(PDO::FETCH_ASSOC);
			}
			else
				return $query->fetchColumn(0);

			return $row;
		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
			echo $this->error;
		}
	}

	/**
	 * Analyze a table
	 *
	 * @param string $table
	 * @param string $fetch
	 * @return object|array
	 */
	public function analyze($table, $fetch = 'object')
	{
		$this->sql = 'ANALYZE TABLE ' . $this->prefix . $table;
		try {
			$query = $this->pdo->query($this->sql);

			if ($fetch == 'array')
				return $query->fetch(PDO::FETCH_ASSOC);
			else
				return $query->fetch(PDO::FETCH_OBJ);
		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
	}

	/**
	 * Check a table
	 *
	 * @param string $table
	 * @param string $fetch
	 * @return object|array
	 */
	public function check($table, $fetch = 'object')
	{
		$this->sql = 'CHECK TABLE ' . $this->prefix . $table;
		try {
			$query = $this->pdo->query($this->sql);

			if ($fetch == 'array')
				return $query->fetch(PDO::FETCH_ASSOC);
			else
				return $query->fetch(PDO::FETCH_OBJ);
		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
	}

	/**
	 * CheckSum a table
	 *
	 * @param string $table
	 * @param string $fetch
	 * @return object|array
	 */
	public function checksum($table, $fetch = 'object')
	{
		$this->sql = 'CHECKSUM TABLE ' . $this->prefix . $table;
		try {
			$query = $this->pdo->query($this->sql);

			if ($fetch == 'array')
				return $query->fetch(PDO::FETCH_ASSOC);
			else
				return $query->fetch(PDO::FETCH_OBJ);
		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
	}

	/**
	 * Optimize a table
	 *
	 * @param string $table
	 * @param string $fetch
	 * @return object|array
	 */
	public function optimize($table, $fetch = 'object')
	{
		$this->sql = 'OPTIMIZE TABLE ' . $this->prefix . $table;
		try {
			$query = $this->pdo->query($this->sql);

			if ($fetch == 'array')
				return $query->fetch(PDO::FETCH_ASSOC);
			else
				return $query->fetch(PDO::FETCH_OBJ);
		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
	}

	/**
	 * Repair a table
	 *
	 * @param string $table
	 * @param string $fetch
	 * @return object|array
	 */
	public function repair($table, $fetch = 'object')
	{
		$this->sql = 'REPAIR TABLE ' . $this->prefix . $table;
		try {
			$query = $this->pdo->query($this->sql);

			if ($fetch == 'array')
				return $query->fetch(PDO::FETCH_ASSOC);
			else
				return $query->fetch(PDO::FETCH_OBJ);
		}
		catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
	}

	/**
	 * Returns last executed query statement
	 *
	 * @return string
	 */
	public function lastQuery()
	{
		return $this->sql;
	}

	/**
	 * Returns last insert id
	 *
	 * @return integer
	 */
	public function lastInsertId()
	{
		return $this->insertId;
	}

	/**
	 * Returns record count
	 *
	 * @return integer
	 */
	public function numRows()
	{
		return $this->numRows;
	}

	/**
	 * Throw error messages
	 *
	 * @return void
	 */
	public function getError() : mixed
	{
		if (null !== $this->error) {
			if (DEBUG)
				return $this->error . "<br>" . $this->lastQuery();

			return $this->error;
		}

		return false;
	}

	/**
	 * Reset all statements
	 *
	 * @return void
	 */
	private function _reset()
	{
		$this->prefix = "";
		$this->select = '*';
		$this->table = null;
		$this->where = null;
		$this->limit = null;
		$this->join = null;
		$this->orderBy = null;
		$this->groupBy = null;
		$this->having = null;
		$this->custom = null;
		$this->numRows = 0;
		$this->grouped = 0;
	}

	function __destruct()
	{
		$this->_reset();
		$this->pdo = null;
	}
}
?>