<?php
class Bizum
{
    private $dbCommand;

    public function __construct($dbCommand)
    {
        $this->dbCommand = $dbCommand;
    }

    public function checkuser($ssid, $username)
    {
        try {
            // Llamar a la procedure sp_check_pwd
            $result = $this->dbCommand->execute('sp_uc_check_username', array($ssid, $username));

            // var_dump($result);
            // Obtener el resultado
            // $result = $result->fetch(PDO::FETCH_ASSOC);

            header('Content-Type: text/xml');
            if ($result[0] == "1") {
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>success</status><message> Usuario existente</message></response>";
            } elseif ($result[0] == "2") {
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>warning</status><message> No te puedes enviar un bizum a ti mismo</message></response>";
            } else {
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Usuario no disponible</message></response>";
            }
        } catch (Exception $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error en la validación: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    public function checkbalance($ssid)
    {
        try {
            $result = $this->dbCommand->execute("sp_uc_check_balance", array($ssid));

            // Establecer el encabezado para XML
            header('Content-Type: text/xml');

            // Mostrar la respuesta XML
            echo $result;
        } catch (Exception $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error en la validación: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    public function checkLastTransaction($ssid)
    {
        try {
            $result = $this->dbCommand->execute('sp_uc_get_last_user_transaction', array($ssid));

            // Establecer el encabezado para XML
            header('Content-Type: text/xml');

            // Mostrar la respuesta XML
            echo $result;
        } catch (Exception $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error en la validación: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    public function getTransactions($ssid) {
        try {
            $result = $this->dbCommand->execute('sp_uc_get_user_transactions', array($ssid));

            // Establecer el encabezado para XML
            header('Content-Type: text/xml');

            // Mostrar la respuesta XML
            echo $result;
        } catch (Exception $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error al obtener transacciones: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    public function sendBizum($ssid, $reciever, $amount)
    {
        if ($amount < 0) {
            header('Content-Type: text/xml');
            try {
                // Intentamos pedirle el XML de error a la base de datos para que sea consistente
                $sql = "DECLARE @xml XML; EXEC sp_xml_error_message 601, @xml OUTPUT, 'send_bizum'; SELECT @xml AS xml;";
                $stmt = $this->dbCommand->execute2($sql);
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                echo $res['xml'];
            } catch (Exception $e) {
                // Si la base de datos falla (ej. no se aplicó el código 601 aún), usamos un fallback manual
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><ws_response><head><errors><error><num_error>601</num_error><message_error>No se pueden enviar bizums negativos.</message_error><severity>ERROR</severity><user_message>No se pueden enviar bizums negativos.</user_message></error></errors></head><body><response_data>Operation failed</response_data></body></ws_response>";
            }
            return;
        }
        try {
            $result = $this->dbCommand->execute('sp_uc_send_bizum', array($ssid, $reciever, $amount));

            // Establecer el encabezado para XML
            header('Content-Type: text/xml');

            // Mostrar la respuesta XML
            echo $result;
        } catch (Exception $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error en el envio: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    // public function checkAmountSender($sender,$amount,$action){
    //     $result = $this->dbCommand->execute('CheckAmount', array($sender,$amount,$this->url(),$action));

    //     return $result;
    // }

    // private function __executeTransaction($sender, $reciever, $amount,$pdoObject,$action){

    //     $myBlockchain = new Blockchain($this->dbCommand,$pdoObject);

    //     $transaction1 = new Transaction($this->dbCommand,$sender, $reciever, $amount,$this->url(),$action);

    //     $latestIndex= $myBlockchain->getLatestBlock()->index;
    //     $newBlockId = $latestIndex + 1;

    //     $block = new Block($this->dbCommand,$newBlockId, null, [$transaction1]);

    //     $myBlockchain->addBlock($block);

    //     $myBlockchain->addTransaction($transaction1,$newBlockId);

    //     if ($myBlockchain->isChainValid()) {
    //         //echo "La cadena es correcta! ";

    //         $this->__executeBizum($sender, $reciever, $amount,$action);

    //         return;
    //     } else {
    //         //echo "la cadena NO es correcta!";
    //     }

    // }

    // private function __executeBizum($sender, $reciever, $amount,$action){

    //     $result = $this->dbCommand->execute('sp_wdev_create_bizzum', array($sender,$reciever,$amount,$this->url(),$action));


    //     header('Content-Type: text/xml');
    //     echo $result;
    //     exit;

    // }

    public function viewTransaction($sender, $action)
    {

        $result = $this->dbCommand->execute('ViewUserTransactions', array($sender, $this->url(), $action));

        header('Content-Type: text/xml');
        echo $result;

    }

    public function url()
    {
        // Obtener el protocolo
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

        // Obtener el host y la URI de la solicitud
        $host = $_SERVER['HTTP_HOST'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Concatenar todo para obtener la URL completa
        $url = $protocol . '://' . $host . $requestUri;

        return $url;
    }

    public function method()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        return $method;
    }
}
?>