<?php

namespace Neo4j;

/** A session with the Neo4j Database. */
class Session
{
    private $baseUri;
    private $ch;
    private $txUrl;
    private $inTransaction = false;

    private $errorCode = '00000';
    private $errorInfo;

    public function __construct($baseUri)
    {
        $this->baseUri = $baseUri;
        $this->ch = curl_init();
        $this->txUrl = $baseUri . TX_ENDPOINT . COMMIT;

    }

    public function beginTransaction()
    {
        if (!$this->inTransaction) {
            $this->inTransaction = true;
            $this->txUrl = $this->baseUri . TX_ENDPOINT;
        }

        return true;
    }

    public function rollback()
    {
        if ($this->inTransaction) {
            $this->inTransaction = false;
            $this->DELETE($this->txUrl);
            $this->txUrl = $this->baseUri . TX_ENDPOINT . COMMIT;
        }

        return true;
    }

    public function commit()
    {
        if ($this->inTransaction) {
            $this->inTransaction = false;
            $this->POST($this->txUrl . COMMIT, array("statements" => array()));
            $this->txUrl = $this->baseUri . TX_ENDPOINT . COMMIT;
        }

        return true;
    }

    /** Execute statements in the current transaction */
    public function exec($statements)
    {
        return $this->POST($this->txUrl, array("statements" => $statements ));
    }

    public function inTransaction()
    {
        return $this->inTransaction;
    }

    public function errorCode()
    {
        return $this->errorCode;
    }

    public function errorInfo()
    {
        return $this->errorInfo;
    }

    private function POST($path, $payload)
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
            'Content-Length: ' . strlen($data_string)
        ));

        $raw = curl_exec($this->ch);

        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $headers = substr($raw, 0, $header_size);
        $body = substr($raw, $header_size);

        $result = json_decode($body, true);
        $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if (preg_match('@^Location: (.*)$@m', $headers, $matches)) {
            $this->txUrl = trim($matches[1]);
        }

        if (count($result['errors']) > 0) {
            $this->errorCode = $result['errors'][0]['code'];
            $this->errorInfo = $result['errors'][0]['message'];
        } else {
            $this->errorCode = '00000';
            $this->errorInfo = '';
        }

        return $result;
    }

    private function DELETE($path)
    {
        curl_setopt($this->ch, CURLOPT_URL, $path);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        curl_exec($this->ch);
    }
}
