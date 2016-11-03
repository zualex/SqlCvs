<?php
use \SqlCvs\SqlCvs;

class SqlCvsTest extends \PHPUnit_Framework_TestCase
{
    private $dbName = 'test.db';
    private $fileName = 'example.cvs';
    private $sqlCvs;

    protected function setUp()
    {
        $this->sqlCvs = new SqlCvs('sqlite:' . $this->dbName);
    }

    public function testImportCvs()
    {
        $table = $this->sqlCvs->importCvs('test_import', $this->fileName);

        $this->assertEquals(8, $table->count());
    }

    public function testDropTable()
    {
        $table = $this->sqlCvs->importCvs('test_drop', $this->fileName);
        $this->sqlCvs->dropTable('test_drop');

        $this->assertFalse($this->sqlCvs->isExistTable('test_drop'));
    }

    public function testGetRandomRow()
    {
        $table = $this->sqlCvs->importCvs('test_random', $this->fileName);
        list($id, $string) = $table->getRandomRow();

        $this->assertTrue(is_int($id));
        $this->assertNotEquals(false, strpos($string, ';'));
    }

    public function testUpdateRow()
    {
        $table = $this->sqlCvs->importCvs('test_update', $this->fileName);
        list($id, $string) = $table->getRandomRow();

        $table->update($id, ['status' => 1]);
        $find = $table->find($id);

        $this->assertEquals(1, $find['status']);
    }

    protected function tearDown()
    {
        // TODO drop tables
    }
}
