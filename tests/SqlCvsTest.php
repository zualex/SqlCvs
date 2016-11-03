<?php
use \SqlCvs\SqlCvs;

class SqlCvsTest extends \PHPUnit_Framework_TestCase
{
    private $file = 'tests/example.cvs';
    private $sqlCvs;

    protected function setUp()
    {
        $this->sqlCvs = new SqlCvs('mysql:dbname=test_db;host=127.0.0.1', 'root', '');
    }

    public function testImportCvs()
    {
        $table = $this->sqlCvs->import('test_import', $this->file);

        $this->assertEquals(7, $table->count());
    }

    public function testDropTable()
    {
        $table = $this->sqlCvs->import('test_drop', $this->file);
        $this->assertTrue($this->sqlCvs->isExistTable('test_drop'));

        $this->sqlCvs->dropTable('test_drop');
        $this->assertFalse($this->sqlCvs->isExistTable('test_drop'));
    }

    public function testGetRandomRow()
    {
        $table = $this->sqlCvs->import('test_random', $this->file);
        $row = $table->getRandomRow();

        $this->assertTrue(is_int($row['id']));
        $this->assertNotEquals(false, strpos($row['value'], ';'));
    }

    public function testUpdateRow()
    {
        $table = $this->sqlCvs->import('test_update', $this->file);
        $row = $table->getRandomRow();

        $table->update($row['id'], ['status' => 1]);
        $find = $table->find($row['id']);

        $this->assertEquals(1, $find['status']);
    }

    protected function tearDown()
    {
        $this->sqlCvs->dropTable('test_import');
        $this->sqlCvs->dropTable('test_random');
        $this->sqlCvs->dropTable('test_update');
    }
}
