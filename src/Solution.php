<?php

namespace OjiSatriani\Attendance;

class Solution
{
    protected static $ipAddress;
    protected static $port;
    protected static $key;

    public function __construct(array $config = []) {
        $this->ipAddress    = $config['ip_address'] ?? '';
        $this->port         = $config['port'] ?? '';
        $this->key          = $config['key'] ?? 0;
    }

    public static function init($config = NULL)
    {
        return new static($config);
    }

    private function parse($data,$tag_opening,$tag_closing){
        $data               = " " . $data;
        $result             = "";
        $start              = strpos($data, $tag_opening);
        if($start != "")
        {
            $end            = strpos(strstr($data, $tag_opening), $tag_closing);
            if($end != "")
            {
                $result     = substr($data, $start+strlen($tag_opening), $end-strlen($tag_opening));
            }
        }
        return $result;    
    }

    public function connect()
    {
        return is_resource(@fsockopen($this->ipAddress, $this->port, $errno, $errstr, 1)) ? true : false;
    }

    public function getAttLog()
    {
        if ($this->connect())
        {
            $soap_request   = "<GetAttLog><ArgComKey xsi:type=\"xsd:integer\">".$this->key."</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetAttLog>";
            $newLine        = "\r\n";
            fputs($konek, "POST /iWsService HTTP/1.0". $newLine);
            fputs($konek, "Content-Type: text/xml". $newLine);
            fputs($konek, "Content-Length: ". strlen($soap_request) . $newLine . $newLine);
            fputs($konek, $soap_request . $newLine);
            $buffer="";
            while($Response=fgets($konek, 1024)){
                    $buffer=$buffer.$Response;
            }
            $log_response   = $this->parse($buffer, "<GetAttLogResponse>", "</GetAttLogResponse>");
            $log_response   = str_replace("\r\n", "\n", $log_response);
            $a_log_response = explode("\n", $log_response);
            krsort($a_log_response);
            $xml            = simplexml_load_string($a_log_response);
            $json           = json_encode($xml);
            $array          = json_decode($json, TRUE);
            return $array;
        } else {
            return NULL;
        }
    }
}
