<?php
namespace SqlCvs;

class SqlCvs extends SqlCvsBase
{
    /**
     * Import CVS data in SQL table
     * @param  string $tableName
     * @param  string $fileName
     * @param  string $delimiter
     * @return object
     */
    public function import($tableName, $fileName, $delimiter = ';')
    {
        $list = array_slice($this->cvsToArray($fileName, $delimiter), 1);
        if (count($list)) {
            $this->setTable($tableName);

            $countColumns = count($list[0]);
            $this->createTable($tableName, $countColumns);

            $sqlInsert = $this->arrayToInsert($tableName, $list);
            $stmt = $this->PDO()->prepare($sqlInsert);
            $stmt->execute();

            return $this;
        }

        return false;
    }

    /**
     * Create table
     * @param  string  $tableName
     * @param  integer $countColumns
     */
    public function createTable($tableName, $countColumns = 0)
    {
        $fields = [];
        $fields[] = 'id INTEGER PRIMARY KEY';
        $fields[] = 'status INTEGER DEFAULT 0';
        foreach ($this->getExtraColumns($countColumns) as $field) {
            $fields[] = "{$field} VARCHAR(255)";
        }
        $fieldImplode = implode($fields, ',');

        $this->PDO()->exec("CREATE TABLE IF NOT EXISTS {$tableName} ({$fieldImplode})");
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
     * Convert array to insert string
     * @param  string $tableName
     * @param  array $list
     * @return string
     */
    private function arrayToInsert($tableName, $list = [])
    {
        if (count($list)) {
            $countColumns = count($list[0]);
            $columns = $this->getExtraColumns($countColumns);

            $values = array_map(function($row) {
                $val = $this->quoteArray($row);
                return "(" . implode($val, ", ") . ")";
            }, $list);

            $sql = sprintf('INSERT INTO %s (%s) VALUES %s',
                $tableName,
                implode($columns, ', '),
                implode($values, ', ')
            );

            return $sql;
        }

        return '';
    }

    /**
     * Get array extra columns
     * @param  integer $count
     * @return array
     */
    private function getExtraColumns($count = 0)
    {
        $fields = [];
        for ($i = 0; $i < $count; $i++) {
            $fields[] = "filed_{$i}";
        }

        return $fields;
    }
}
