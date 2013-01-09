<?php

class RDFLoader extends ALoader {

    private $file = "output.ttl";
    private $conn;
    
    public function __construct($config){
        parent::__construct($config);
        
        $this->conn = odbc_connect('VOS', 'dba', 'dba');
        echo 'ERROR: ' . odbc_errormsg();
    }

        public function execute(&$chunk) {
        //$gs = new EasyRdf_GraphStore('http://localhost:2222/update/');
        //$gs->insert($chunk);
        if (!$chunk->is_empty()) {
            $stringData = $chunk->to_rdfxml();

//            $fh = fopen($this->file, 'w') or die("can't open file");
//            fwrite($fh, $stringData);
//            fclose($fh);
            //echo $stringData;
            
            //$result = odbc_exec($conn, 'CALL DB.DBA.SPARQL_EVAL(\'' . $query . '\', NULL, 0)');
            $result = odbc_exec($this->conn, 'CALL DB.DBA.RDF_LOAD_RDFXML_MT(\'' . $stringData . '\',\'\', \'http://mytest.com\')');

            echo 'Triple loaded';
        }
    }

}
