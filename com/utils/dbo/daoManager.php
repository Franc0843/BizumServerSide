<?php

// require_once 'DBCommand.php';

class DBManager {
    private $dbCommand;

    public function __construct($dbCommand) {
        $this->dbCommand = $dbCommand;
    }

    public function viewConnections() {
        try {
            $result = $this->dbCommand->execute('sp_list_connections');

            // Establecer el encabezado para XML
            header('Content-Type: text/xml');

            // Mostrar la respuesta XML
            echo $result;
        } catch (PDOException $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    public function viewHistoricConnections() {
        try {
            $result = $this->dbCommand->execute('sp_list_historic_connections');

            // Establecer el encabezado para XML
            header('Content-Type: text/xml');

            // Mostrar la respuesta XML
            echo $result;
        } catch (PDOException $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }
}

?>
