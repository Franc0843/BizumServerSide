<?php
class DBConnection {

    private $password;
    private $user;
    private $databaseName;
    private $host;
    // private $port;
    private $db;

    public function __construct($host, $databaseName, $user, $password) {
        $this->host=$host;
        $this->databaseName=$databaseName;
        $this->user=$user;
        $this->password=$password;
        $this -> connectON();
    }

    private function connectON() {
        try {
            $this -> db = new PDO("sqlsrv:Server=$this->host;Database=$this->databaseName;TrustServerCertificate=true","$this->user","$this->password");
            $this -> db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           // $this -> consulta(); 
        } catch (Exception $error) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> No se ha podido conectar a la bd: <![CDATA[" . $error->getMessage() . "]]></message></response>";
        }
    }

    public function getPDOObject(){
        return $this -> db;
    }
}



?>