<?php

namespace Neo4j\Tests;

use Neo4j\Neo4jPDO;

class PDOStatementTest extends \PHPUnit_Framework_TestCase
{
    public function testIterate()
    {
        // Given
        $pdo = new Neo4jPDO("http://localhost:7474");
        $pdo->exec('MERGE (n:ReadStmt {name:"Jake"})');

        // When
        $count = 0;
        foreach($pdo->query('MATCH (n:ReadStmt {name:"Jake"}) RETURN count(*)') as $row) 
        {
            $count = $row['count(*)'];
        }

        // Then 
        $this->assertEquals(1, $count);
        $this->assertEquals('00000', $pdo->errorCode());
    }

    public function testFetchColumn()
    {
        // Given
        $pdo = new Neo4jPDO("http://localhost:7474");
        $pdo->exec('MERGE (n:ReadStmt {name:"Jake"})');

        // When
        $column = $pdo->query('MATCH (n:ReadStmt {name:"Jake"}) RETURN count(*)')->fetchColumn(0);

        // Then 
        $this->assertEquals(array(1), $column);
        $this->assertEquals('00000', $pdo->errorCode());
    }

    public function testBindParams()
    {
        // Given
        $pdo = new Neo4jPDO("http://localhost:7474");
        $pdo->exec('MERGE (n:Prepare {a:12})');

        $param = 12;
        $stmt = $pdo->prepare('MATCH (n) WHERE n.a={p} RETURN n');
        
        // When
        $stmt->bindParam('p', $param);

        // Then
        $stmt->execute();
        $this->assertEquals(1, $stmt->rowCount());
        $param = 15;
        $stmt->execute();
        $this->assertEquals(0, $stmt->rowCount());
    }

    public function testTypes()
    {
        // Given
        $pdo = new Neo4jPDO("http://localhost:7474");
        $pdo->exec('MERGE (n:Types {name:"Jake"})-[:KNOWS]->(b)');

        // When
        $res = $pdo->query(
            'MATCH p=(n:Types {name:"Jake"})-->() RETURN 1 as num, "str", [1,2], n, p')->fetchAll();
        $res = $res[0];

        // Then 
        $this->assertEquals(1, $res['num']);
        $this->assertEquals('str', $res['"str"']);
        $this->assertEquals(array(1,2), $res['[1,2]']);
        $this->assertEquals('00000', $pdo->errorCode());
    }

    public function testErrors()
    {
        // Given
        $pdo = new Neo4jPDO("http://localhost:7474");

        // When
        $stmt = $pdo->query('nonsense');

        // Then 
        $this->assertEquals("Neo.ClientError.Statement.InvalidSyntax", $stmt->errorCode());
    }
}