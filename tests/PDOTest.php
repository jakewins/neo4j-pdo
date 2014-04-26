<?php

namespace Neo4j\Tests;

use Neo4j\Neo4jPDO;

class PDOTest extends \PHPUnit_Framework_TestCase
{
    public function testCommit()
    {
        // Given
        $pdo = new Neo4jPDO("http://localhost:7474");
        $pdo->exec('MATCH (n:Commit) DELETE n');

        // When
        $pdo->beginTransaction();
        $affected = $pdo->exec('CREATE (n:Commit {name:"Jake"})');
        $pdo->commit();

        // Then 
        $this->assertEquals(3, $affected);
        $this->assertEquals(1, $pdo->query('MATCH (n:Commit {name:"Jake"}) RETURN *')->rowCount());
    }

    public function testPrepare()
    {
        // Given
        $pdo = new Neo4jPDO("http://localhost:7474");
        $pdo->exec('MERGE (n:Prepare {a:12})');
        
        // When
        $stmt = $pdo->prepare('MATCH (n) WHERE n.a={p} RETURN n');
        $stmt->execute(array("p"=>12));

        // Then 
        $this->assertEquals('00000', $stmt->errorCode());
        $this->assertEquals(1, $stmt->rowCount());
    }

    public function testRollback()
    {
        // Given
        $pdo = new Neo4jPDO("http://localhost:7474");
        $pdo->exec('MATCH (n:Rollback) DELETE n');

        // When
        $pdo->beginTransaction();
        $affected = $pdo->exec('CREATE (n:Rollback {name:"Jake"})');
        $pdo->rollback();

        // Then 
        $this->assertEquals(3, $affected);
        $this->assertEquals(0, $pdo->query('MATCH (n:Rollback {name:"Jake"}) RETURN *')->rowCount());
    }
}
