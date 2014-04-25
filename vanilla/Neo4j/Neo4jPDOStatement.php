<?php

namespace Neo4j;

use \PDO as PDO;

class Neo4jPDOStatement implements \Iterator
{
    
    public $queryString;

    private $rows;
    private $columns = array();
    private $rowCount = 0;
    private $cursor = -1;
    private $fetchMode = PDO::FETCH_BOTH;

    private $boundParams = array();

    public function __construct($queryString, $session) 
    {
        $this->queryString = $queryString;
        $this->session = $session;
    }

    public function bindColumn ( $column, &$param, $type=0, $maxlen=1, 
                                 $driverdata=null) 
    {

    }

    public function bindParam ( $parameter, &$variable, $data_type=PDO::PARAM_STR, 
                                $length=null, $driver_options=null) 
    {
        $this->boundParams[$parameter] = &$variable;
    }

    public function bindValue ( $parameter, $value, $data_type=PDO::PARAM_STR ) 
    {

    }

    public function getColumnMeta ( $column ) 
    {

    }

    public function execute ($params=null) 
    {
        if($params != null)
        {
            $result = $this->session->exec(array(array( 
                "statement" => $this->queryString,  
                "parameters" => $params,
                "includeStats" => true )));   
        }
        else if(count($this->boundParams) > 0)
        {
            $result = $this->session->exec(array(array( 
                "statement" => $this->queryString,  
                "parameters" => $this->boundParams,
                "includeStats" => true )));
        } 
        else
        {
            $result = $this->session->exec(array(array( 
                "statement" => $this->queryString,  
                "includeStats" => true )));
        }

        if(isset($result['results'][0]))
        {
            $this->columns = $result['results'][0]['columns'];
            $this->rows = $result['results'][0]['data'];
            $this->rowCount = count($this->rows);
            $this->cursor = 0;
        }
    }

    public function fetch ($fetchStyle=PDO::ATTR_DEFAULT_FETCH_MODE, 
                           $cursorOrientation=PDO::FETCH_ORI_NEXT, 
                           $cursorOffset=0 ) 
    {

    }

    public function fetchAll ($fetchStyle=PDO::ATTR_DEFAULT_FETCH_MODE, 
                              $fetchArgument=0, 
                              $ctorArgs=null) 
    {
        if($fetchStyle === PDO::ATTR_DEFAULT_FETCH_MODE)
        {
            $fetchStyle = $this->fetchMode;
        }

        switch ($fetchStyle) 
        {
            case PDO::FETCH_BOTH:
                return $this->rowsAsMap();
        }        
    }

    public function fetchColumn ($columnNo=0) 
    {
        $column = array();
        for($i=0;$i<$this->rowCount;$i++)
        {
            $column[$i] = $this->rows[$i]['row'][$columnNo];
        }
        return $column;
    }

    public function fetchObject ($class_name = "stdClass", $ctor_args=null ) 
    {

    }

    public function setFetchMode ( $mode=PDO::FETCH_BOTH ) 
    {
        $this->fetchMode = $mode;
    }

    public function nextRowset ( ) {}

    /* Iterator */

    public function current () 
    {
        switch ($this->fetchMode) 
        {
            case PDO::FETCH_BOTH:
                return $this->rowAsMap( $this->rows[$this->cursor]['row'] );
        }
    }

    public function key () 
    {
        return $this->cursor;   
    }

    public function next () 
    {
        $this->cursor++;
    }

    public function valid () 
    {
        return $this->cursor < $this->rowCount;
    }

    public function rewind () {}

    /* Other */

    public function closeCursor () {}

    public function columnCount () 
    {
        return count($this->columns);
    }

    public function rowCount () 
    {
        return $this->rowCount;
    }
    
    public function setAttribute ( $attribute , $value ) {}
    public function getAttribute ( $attribute ) {}

    public function errorCode() 
    {
        return $this->session->errorCode();
    }

    public function errorInfo() 
    {
        return $this->session->errorInfo();
    }

    public function debugDumpParams ( ) {}

    private function rowsAsMap()
    {
        $projection = array();
        for($i=0;$i<$this->rowCount;$i++)
        {
            $projection[$i] = $this->rowAsMap( $this->rows[$i]['row'] );
        }
        return $projection;
    }

    private function rowAsMap( $row )
    {
        $projection = array();
        for($i=0;$i<count($this->columns);$i++)
        {
            $projection[$this->columns[$i]] = $row[$i];
            $projection[$i] = $row[$i];
        }
        return $projection;
    }
}
