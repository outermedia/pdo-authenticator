<?php
/**
 * User: ballmann
 * Date: 4/15/16
 */

namespace Om\Pdo\Authenticator;

require dirname(__FILE__) . "/../../../../../../vendor/autoload.php";

/**
 * Class DatabaseQueryBuilderTest
 * @package Om\Pdo\Authenticator
 * 
 * Test methods of DatabaseQueryBuilder.
 */
class DatabaseQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildNoReturnFieldNoCondition()
    {
        $builder = DatabaseQueryBuilder::newBuilder()->table("T1");
        $this->assertEquals('select * from T1', $this->buildSql($builder), "Expected different sql");
    }

    public function testBuildOneReturnFieldNoCondition()
    {
        $builder = DatabaseQueryBuilder::newBuilder()->table("T1")->returnField('f1');
        $this->assertEquals('select f1 from T1', $this->buildSql($builder), "Expected different sql");
    }

    public function testBuildTwoReturnFieldsNoCondition()
    {
        $builder = DatabaseQueryBuilder::newBuilder()->table("T1")->returnField('f1')->returnField('f2');
        $this->assertEquals('select f1,f2 from T1', $this->buildSql($builder), "Expected different sql");
    }

    public function testBuildOneReturnFieldOneCondition()
    {
        $builder = DatabaseQueryBuilder::newBuilder()->table("T1")->ifEqual('f1', 'a');
        $this->assertEquals('select * from T1 where f1=:var0', $this->buildSql($builder), "Expected different sql");
    }

    public function testBuildOneReturnFieldTwoConditions()
    {
        $builder = DatabaseQueryBuilder::newBuilder()->table("T1")->ifEqual('f1', 'a')->ifEqual('f2', 'b');
        $this->assertEquals('select * from T1 where f1=:var0 and f2=:var1', $this->buildSql($builder), "Expected different sql");
    }

    /**
     * Get the sql from the builder.
     * 
     * @param DatabaseQueryBuilder $builder
     * @return string
     */
    protected function buildSql($builder)
    {
        return $builder->buildSql()->getSql();
    }
}