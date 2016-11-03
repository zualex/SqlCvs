<?php
use \SqlCvs\SqlCvs;

class SqlCvsTest extends \PHPUnit_Framework_TestCase
{
    private $dbName = 'test.db';
    private $file = 'tests/example.cvs';
    private $sqlCvs;

    protected function setUp()
    {
        $this->sqlCvs = new SqlCvs('sqlite:' . $this->dbName);
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
        list($id, $string) = $table->getRandomRow();

        $this->assertTrue(is_int($id));
        $this->assertNotEquals(false, strpos($string, ';'));
    }

    public function testUpdateRow()
    {
        $table = $this->sqlCvs->import('test_update', $this->file);
        list($id, $string) = $table->getRandomRow();

        $table->update($id, ['status' => 1]);
        $find = $table->find($id);

        $this->assertEquals(1, $find['status']);
    }

    protected function tearDown()
    {
        $this->sqlCvs->dropTable('test_import');
    }
}
