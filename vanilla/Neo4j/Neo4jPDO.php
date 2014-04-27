<?php

namespace Neo4j;

const TX_ENDPOINT = "/db/data/transaction";
const COMMIT = "/commit";

class Neo4jPDO
{
    private $session;

    public function __construct($dsn, $username = null, $password = null, $driver_options = array())
    {
        $this->session = new Session($dsn);
    }

    public function beginTransaction()
    {
        return $this->session->beginTransaction();
    }

    public function commit()
    {
        return $this->session->commit();
    }

    public function rollBack()
    {
        return $this->session->rollback();
    }

    public function prepare($statement, $driver_options = array())
    {
        return new Neo4jPDOStatement($statement, $this->session);
    }

    public function query($statement)
    {
        $stmt = $this->prepare($statement);
        $stmt->execute();

        return $stmt;
    }

    public function exec($statement)
    {
        $result = $this->session->exec(
            array(
                array("statement" => $statement, "includeStats" => true)
            )
        );
        $stats = $result['results'][0]['stats'];

        return $stats['nodes_created'] + $stats['nodes_deleted'] + $stats['properties_set']
            + $stats['relationships_created'] + $stats['relationship_deleted']
            + $stats['labels_added'] + $stats['labels_removed'];
    }

    public function errorCode()
    {
        return $this->session->errorCode();
    }

    public function errorInfo()
    {
        return $this->session->errorInfo();
    }

    public function inTransaction()
    {
        return $this->session->inTransaction();
    }

    public function lastInsertId($name = null)
    {

    }

    public function quote($string, $parameter_type = null)
    {

    }
    public function setAttribute($attribute, $value)
    {

    }
    public function getAttribute($attribute)
    {

    }
}
