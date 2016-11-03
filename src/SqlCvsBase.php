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
     * Find row
     * @param  integer $id
     * @return array
     */
    public function find($id)
    {
        $stmt = $this->PDO()->prepare("SELECT * FROM {$this->getTable()} WHERE id=:id");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
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
     * Update by id
     * @param  integer $id
     * @param  array $updates
     * @return boolean
     */
    public function update($id, $updates = [])
    {
        $query = "UPDATE {$this->getTable()} SET";

        $values = [':id' => $id];
        foreach ($updates as $name => $value) {
            $query .= " {$name} = :{$name},";
            $values[':'.$name] = $value;
        }
        $query = substr($query, 0, -1);
        $query .= ' WHERE id=:id';

        $stmt = $this->PDO()->prepare($query);

        return $stmt->execute($values);
    }

    /**
     * Drop table
     * @param  string  $tableName
     */
    public function dropTable($tableName)
    {
        $this->PDO()->exec("DROP TABLE IF EXISTS {$tableName}");
    }

    /**
     * quoteArray
     * @param  array $list
     * @return array
     */
    protected function quoteArray($list = [])
    {
        return array_map(function($item) {
            return $this->PDO()->quote($item);
        }, $list);
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
