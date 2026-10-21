<?php
/**
 * SQLite Helper Functions
 *
 * Helper functions to make SQLite3 work similarly to MySQLi
 */

/**
 * Execute prepared statement and return result
 * @param SQLite3Stmt $stmt Prepared statement
 * @param array $params Parameters to bind
 * @return SQLite3Result|bool
 */
function execute_prepared($stmt, $params = []) {
    foreach ($params as $i => $param) {
        $type = SQLITE3_TEXT;
        if (is_int($param)) {
            $type = SQLITE3_INTEGER;
        } elseif (is_float($param)) {
            $type = SQLITE3_FLOAT;
        } elseif (is_null($param)) {
            $type = SQLITE3_NULL;
        }
        $stmt->bindValue($i + 1, $param, $type);
    }
    return $stmt->execute();
}

/**
 * Fetch all rows as associative array
 * @param SQLite3Result $result Query result
 * @return array
 */
function fetch_all_assoc($result) {
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Get last insert ID
 * @param SQLite3 $conn Database connection
 * @return int
 */
function get_last_insert_id($conn) {
    return $conn->lastInsertRowID();
}

/**
 * Get number of affected rows
 * @param SQLite3 $conn Database connection
 * @return int
 */
function get_affected_rows($conn) {
    return $conn->changes();
}

/**
 * Check if a table exists
 * @param SQLite3 $conn Database connection
 * @param string $table Table name
 * @return bool
 */
function table_exists($conn, $table) {
    $result = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='" . SQLite3::escapeString($table) . "'");
    return $result->fetchArray() !== false;
}

/**
 * SQLite Statement Wrapper Class
 * Provides MySQLi-like interface for SQLite3
 */
class SQLiteStatement {
    private $stmt;
    private $conn;
    private $bound_params = [];

    public function __construct($conn, $query) {
        $this->conn = $conn;
        $this->stmt = $conn->prepare($query);
    }

    public function bind_param($types, ...$params) {
        for ($i = 0; $i < strlen($types); $i++) {
            $type = $types[$i];
            $param = $params[$i];

            $sqlite_type = SQLITE3_TEXT;
            if ($type === 'i') {
                $sqlite_type = SQLITE3_INTEGER;
            } elseif ($type === 'd') {
                $sqlite_type = SQLITE3_FLOAT;
            }

            $this->stmt->bindValue($i + 1, $param, $sqlite_type);
        }
        return true;
    }

    public function execute() {
        $result = $this->stmt->execute();
        return new SQLiteResult($result);
    }

    public function get_result() {
        $result = $this->stmt->execute();
        return new SQLiteResult($result);
    }

    public function close() {
        $this->stmt->close();
    }

    public function __get($name) {
        if ($name === 'error') {
            return $this->conn->lastErrorMsg();
        }
        return null;
    }
}

/**
 * SQLite Result Wrapper Class
 * Provides MySQLi-like interface for SQLite3 results
 */
class SQLiteResult {
    private $result;
    private $rows = [];
    private $current_row = 0;
    private $loaded = false;

    public function __construct($result) {
        $this->result = $result;
    }

    private function loadRows() {
        if (!$this->loaded) {
            while ($row = $this->result->fetchArray(SQLITE3_ASSOC)) {
                $this->rows[] = $row;
            }
            $this->result->finalize();
            $this->loaded = true;
        }
    }

    public function fetch_assoc() {
        $this->loadRows();
        if ($this->current_row < count($this->rows)) {
            return $this->rows[$this->current_row++];
        }
        return null;
    }

    public function fetch_all($mode = MYSQLI_ASSOC) {
        $this->loadRows();
        return $this->rows;
    }

    public function num_rows() {
        $this->loadRows();
        return count($this->rows);
    }

    public function __get($name) {
        if ($name === 'num_rows') {
            $this->loadRows();
            return count($this->rows);
        }
        return null;
    }
}

/**
 * SQLite Connection Wrapper
 * Provides MySQLi-like interface for SQLite3
 */
class SQLiteConnection {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function prepare($query) {
        return new SQLiteStatement($this->conn, $query);
    }

    public function query($query) {
        return $this->conn->query($query);
    }

    public function exec($query) {
        return $this->conn->exec($query);
    }

    public function real_escape_string($string) {
        return SQLite3::escapeString($string);
    }

    public function close() {
        $this->conn->close();
    }

    public function __get($name) {
        if ($name === 'error') {
            return $this->conn->lastErrorMsg();
        }
        return null;
    }
}

/**
 * Column existence check for SQLite
 * @param mixed $conn Database connection (SQLiteConnection or raw SQLite3)
 * @param string $table Table name
 * @param string $column Column name
 * @return bool
 */
function columnExists($conn, $table, $column) {
    // For SQLite, use PRAGMA table_info
    $result = $conn->query("PRAGMA table_info($table)");
    if ($result) {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($row['name'] === $column) {
                $result->finalize();
                return true;
            }
        }
        $result->finalize();
    }
    return false;
}

/**
 * Convert SQLite3 connection to wrapper
 */
function wrap_sqlite_connection($conn) {
    return new SQLiteConnection($conn);
}
