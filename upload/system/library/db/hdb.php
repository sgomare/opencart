<?php
namespace Opencart\System\Library\DB;
final class HDB {
	private $connection;
	private $statement;

	public function __construct($hostname, $username='', $password='', $database='', $port = '39013') {
		try {
			$this->connection = @new \PDO("odbc:DRIVER={/var/www/html/hdbclient/libodbcHDB.so};Servernode=".$hostname.":".$port.";DATABASENAME=".$database.";UID=".$username.";PWD=".$password);
			$this->connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
			$this->connection->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
		} catch (\PDOException $e) {
			throw new \Exception('Error: Could not make a database link using ' . $username . '@' . $hostname . '!');
		}

		$this->connection->exec("SET NAMES 'utf8'");
		$this->connection->exec("SET CHARACTER SET utf8");
		$this->connection->exec("SET CHARACTER_SET_CONNECTION=utf8");
		$this->connection->exec("SET SQL_MODE = ''");
	}

	public function execute() {
		try {
			if ($this->statement && $this->statement->execute()) {
				$data = [];

				while ($row = $this->statement->fetch()) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->row = (isset($data[0])) ? $data[0] : [];
				$result->rows = $data;
				$result->num_rows = $this->statement->rowCount();
			}
		} catch (\PDOException $e) {
			throw new \Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode());
		}
	}

	public function query($sql, $params = []) {
		$this->statement = $this->connection->prepare($sql);

		$result = false;
		try {
			if ($this->statement && $this->statement->execute($params)) {
				$data = [];

				while ($row = $this->statement->fetch()) {
					$data[] = $row;
                }
                
				$result = new \stdClass();
				$result->row = (isset($data[0]) ? $data[0] : []);
				$result->rows = $data;
				$result->num_rows = $this->statement->rowCount();
			}
		} catch (\PDOException $e) {
			throw new \Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode() . ' <br />' . $sql);
		}

		if ($result) {
			return $result;
		} else {
			$result = new \stdClass();
			$result->row = [];
			$result->rows = [];
			$result->num_rows = 0;

			return $result;
		}
	}

	public function prepare($sql) {
		$this->statement = $this->connection->prepare($sql);
	}

	public function bindParam($parameter, $variable, $data_type = \PDO::PARAM_STR, $length = 0) {
		if ($length) {
			$this->statement->bindParam($parameter, $variable, $data_type, $length);
		} else {
			$this->statement->bindParam($parameter, $variable, $data_type);
		}
	}

	public function escape($value) {
		return str_replace(["\\", "\0", "\n", "\r", "\x1a", "'", '"'], ["\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"'], $value);
	}

	public function countAffected() {
		if ($this->statement) {
			return $this->statement->rowCount();
		} else {
			return 0;
		}
	}

	public function getLastId() {
		return $this->connection->lastInsertId();
	}

	public function isConnected() {
		if ($this->connection) {
			return true;
		} else {
			return false;
		}
	}

	public function __destruct() {
		$this->connection = null;
	}
}
