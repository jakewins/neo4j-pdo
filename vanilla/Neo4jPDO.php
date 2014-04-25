<?php

namespace Neo4j;

const TX_ENDPOINT = "/db/data/transaction";
const COMMIT = "/commit";

class Neo4jPDO
{
    private $session;

    public function __construct( $dsn, $username=NULL, $password=NULL, $driver_options=array() )
    {
        $this->session = new Session( $dsn );
    }

    public function beginTransaction () 
    {
        return $this->session->beginTransaction();
    }

    public function commit () 
    {
        return $this->session->commit();
    }

    public function rollBack () 
    {
        return $this->session->rollback();
    }

    public function prepare ( $statement, $driver_options = array() ) 
    {
        return new Neo4jPDOStatement($statement, $this->session);
    }

    public function query ( $statement ) 
    {
        $stmt = $this->prepare($statement);
        $stmt->execute();
        return $stmt;
    }

    public function exec ( $statement ) 
    {
        $result = $this->session->exec(array(array( "statement" => $statement, "includeStats" => true )));
        $stats = $result['results'][0]['stats'];
        return $stats['nodes_created'] + $stats['nodes_deleted'] + $stats['properties_set'] + $stats['relationships_created'] 
             + $stats['relationship_deleted'] + $stats['labels_added'] + $stats['labels_removed'];
    }

    public function errorCode() 
    {
        return $this->session->errorCode();
    }

    public function errorInfo() 
    {
        return $this->session->errorInfo();
    }

    public function inTransaction () {}
    
    public function lastInsertId ($name = NULL) {}

    public function quote ( $string, int $parameter_type = NULL) {}
    public function setAttribute ( int $attribute , mixed $value ) {}
    public function getAttribute ( int $attribute ) {}
}

/** A session with the Neo4j Database. */
class Session 
{
    private $baseUri;
    private $ch;
    private $txUrl;
    private $inTransaction = false;

    private $errorCode = '00000';
    private $errorInfo;

    public function __construct( $baseUri ) 
    {
        $this->baseUri = $baseUri;
        $this->ch = curl_init();
        $this->txUrl = $baseUri . TX_ENDPOINT . COMMIT;

    }

    public function beginTransaction () 
    {
        if(!$this->inTransaction)
        {
            $this->inTransaction = true;
            $this->txUrl = $this->baseUri . TX_ENDPOINT;
        }
        return true;
    }

    public function rollback () 
    {
        if($this->inTransaction)
        {
            $this->inTransaction = false;
            $this->DELETE($this->txUrl);
            $this->txUrl = $this->baseUri . TX_ENDPOINT . COMMIT;

        }
        return true;
    }

    public function commit()
    {
        if($this->inTransaction)
        {
            $this->inTransaction = false;
            $this->POST($this->txUrl . COMMIT, array("statements"=>array()));
            $this->txUrl = $this->baseUri . TX_ENDPOINT . COMMIT;

        }
        return true;    
    }

    /** Execute statements in the current transaction */
    public function exec( $statements ) 
    {
        return $this->POST( $this->txUrl, array("statements" => $statements ));
    }

    public function errorCode() 
    {
        return $this->errorCode;
    }

    public function errorInfo() 
    {
        return $this->errorInfo;
    }

    private function POST( $path, $payload ) 
    {
        $data_string = json_encode($payload);
        // echo $data_string;
        // echo $path;
        curl_setopt($this->ch, CURLOPT_URL, $path); 
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );

        $raw = curl_exec($this->ch);

        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $headers = substr($raw, 0, $header_size);
        $body = substr($raw, $header_size);

        $result = json_decode($body , true);
        $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if(preg_match('@^Location: (.*)$@m', $headers, $matches))
        {
            $this->txUrl = trim($matches[1]);
        }

        if(count($result['errors']) > 0) 
        {
            $this->errorCode = $result['errors'][0]['code'];
            $this->errorInfo = $result['errors'][0]['message'];
        }
        else
        {
            $this->errorCode = '00000';
            $this->errorInfo = '';
        }

        return $result;
    }

    private function DELETE( $path ) 
    {
        curl_setopt($this->ch, CURLOPT_URL, $path); 
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        curl_exec($this->ch);
    }
}