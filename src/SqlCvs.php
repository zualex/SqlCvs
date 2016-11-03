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

            $sqlInsert = $this->arrayToInsert($tableName, $list, $delimiter);
            $stmt = $this->PDO()->prepare($sqlInsert);
            $stmt->execute();

            return $this;
        }

        return false;
    }

    public function getRandomRow()
    {
        $result = $this->PDO()->query("SELECT * FROM {$this->getTable()} ORDER BY RANDOM() LIMIT 1");
        $row = [];
        foreach ($result as $line) {
            $row['id'] = intval($line['id']);
            $row['value'] = implode($this->getOnlyCvsFileds($line), $line['delimiter']);
        }

        return $row;
    }

    /**
     * getDefaultFields
     * @return array
     */
    public function getDefaultFields()
    {
        return [
            'id INTEGER PRIMARY KEY',
            'status INTEGER DEFAULT 0',
            'delimiter CHAR(1)',
        ];
    }

    /**
     * Create table
     * @param  string  $tableName
     * @param  integer $countColumns
     */
    public function createTable($tableName, $countColumns = 0)
    {
        $fields = $this->getDefaultFields();
        $extraFields = [];
        foreach ($this->getExtraColumns($countColumns) as $field) {
            $extraFields[] = "{$field} VARCHAR(255)";
        }
        $fields = array_merge($fields, $extraFields);
        $fieldImplode = implode($fields, ',');

        $this->PDO()->exec("CREATE TABLE IF NOT EXISTS {$tableName} ({$fieldImplode})");
    }

    /**
     * Get onlu cvs fileds
     * @param  array $row
     * @return array
     */
    public function getOnlyCvsFileds($row = [])
    {
        $data = array_flip(array_filter(array_flip($row), function($key) {
            return is_numeric($key);
        }));
        $countSlice = count($this->getDefaultFields());

        return array_slice($data, $countSlice);
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
     * @param  string $delimiter
     * @return string
     */
    private function arrayToInsert($tableName, $list = [], $delimiter = ';')
    {
        if (count($list)) {
            $countColumns = count($list[0]);
            $columns = $this->getExtraColumns($countColumns);
            $columns[] = 'delimiter';

            $values = array_map(function($row) use ($delimiter) {
                $row = array_merge($row, [$delimiter]);

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
