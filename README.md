# Neo4j PDO
[![Build Status](https://travis-ci.org/jakewins/neo4j-pdo.svg?branch=master)](https://travis-ci.org/jakewins/neo4j-pdo)

This is a PHP Data Objects driver for the Neo4j graph database, it allows you to 
connect to a Neo4j server from PHP using the standard PDO API.

The project currently only implements a "vanilla" PHP implementation, which means 
you need to use a special constructor to create your PDO connection.
PHP extensions to register the driver with PDO in HHVM and Zend VM are planned
future work.

## Installation

Neo4jPDO uses Composer, please checkout the [composer website](http://www.getcomposer.org) for more information.

To use Neo4j/PDO in your project, simply add it as a composer dependency:

    "require": {
        "neo4j/pdo": "dev-master"
    }

## Minimum Viable Snippet

    // Include dependencies installed with composer
    require 'vendor/autoload.php';

    $conn = new \Neo4j\Neo4jPDO("http://localhost:7474");
    
    foreach($conn->query('MATCH (n) RETURN count(*) as count') as $row) 
    {
        echo $row['count'];
    }

Please refer to the PDO documentation in the PHP manual for detailed documentation
of the API.

## Unsupported features

- PDO->lastInsertId: Neo4j does not provide this functionality.
- PDO->quote: Use parameterized queries instead

- PDOStatement->getColumnMeta: Not yet implemented
- PDOStatement->bindValue: Not yet implemented
- PDOStatement->bindColumn: Not yet implemented
- PDOStatement->fetch: Not yet implemented
- PDOStatement->fetchObject: Not yet implemented

Many of the PDO::FETCH_** flags are not yet supported, specifically, currently only PDO::FETCH_BOTH is implemented.

## License

http://opensource.org/licenses/MIT
