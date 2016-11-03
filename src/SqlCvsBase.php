<?php
namespace SqlCvs;

use PDO;

class SqlCvsBase
{
    private $pdo;
    private $table = '';

    /**
     * __construct
     */
    public function __construct($dsn, $user = '', $password = '', $options = [])
    {
        try {
            $db = new PDO($dsn, $user, $password, $options);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->pdo = $db;
        } catch (\PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get PDO object
     * @return object PDO
     */
    public function PDO()
    {
        return $this->pdo;
    }

    /**
     * Set table name
     */
    public function setTable($tableName)
    {
        $this->table = $tableName;
    }

    /**
     * Get table name
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Check exists table
     * @param  string  $tableName
     * @return boolean
     */
    public function isExistTable($tableName) {
        try {
            $result = $this->PDO()->query("SELECT 1 FROM {$tableName} LIMIT 1");
        } catch (\Exception $e) {
            return FALSE;
        }

        return $result !== FALSE;
    }

    /**
     * Get count rows in table
     */
    public function count()
    {
        $result = $this->PDO()->query("SELECT COUNT(*) FROM {$this->getTable()}");
        $result->execute();

        return $result->fetchColumn();
    }

    /**
     * quoteArray
     * @param  array $list
     * @return array
     */
    public function quoteArray($list = [])
    {
        return array_map(function($item) {
            return $this->PDO()->quote($item);
        }, $list);
    }

    /**
     * Convert CVS file to array
     * @param  string $fileName
     * @param  string $delimiter
     * @return array
     */
    public function cvsToArray($fileName, $delimiter = ';')
    {
        return array_map(function($line) use ($delimiter) {
            return str_getcsv($line, $delimiter);
        }, file($fileName));
    }

    /**
     * __destruct
     */
    public function __destruct()
    {
        if ($this->pdo) {
            $this->pdo = null;
        }
    }
}
