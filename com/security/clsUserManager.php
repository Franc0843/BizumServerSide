<?php

// require_once 'DBCommand.php'; 

class UserManager
{
    private $dbCommand;

    public function __construct($dbCommand)
    {
        $this->dbCommand = $dbCommand;
    }

    public function register($username, $name, $lastname, $password, $email, $gender, $def_lang)
    {
        if (empty($username) || empty($name) || empty($lastname) || empty($password) || empty($email) || empty($gender) || empty($def_lang)) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Todos los campos son obligatorios.</message></response>";
        } else {
            try {
                $result = $this->dbCommand->execute('sp_user_register', array($username, $name, $lastname, $password, $email, $gender, $def_lang));
                $xml = simplexml_load_string($result);
                if ($xml->head->errors->error->num_error == "0") {
                    $register_code = $this->dbCommand->execute('sp_wdev_get_registercode', array($username, 0));

                    // URL del Web App desplegado en Google Apps Script
                    //url pau
                    // $url = 'https://script.google.com/macros/s/AKfycbzs-WaweIA_cKNVVgqqPmianx7dn4wPI7AflDvM78iUcP8pUoYNh5u5Dg7nBlkofdKu/exec';

                    //url Pol
                    $url = 'https://script.google.com/macros/s/AKfycbxAQsgiFCg31C-G1MzD27GjZTo0Owa22XBoGJQzu2AT-WV8lWj76kud2WOuxLaxpH6OYw/exec';

                    // Parámetros del correo electrónico
                    $destinatario = $email;
                    $asunto = 'Código de registro.';
                    $cuerpo = $name . ', su código de verificación es ' . $register_code . '.';
                    $adjunto = null;

                    // Llamada a la función para enviar el correo
                    enviarCorreo($url, $destinatario, $asunto, $cuerpo, $adjunto);
                }
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

    public function recoverPasswordEmail($email)
    {
        try {
            $result = $this->dbCommand->execute('sp_wdev_recover_password_email', array($email));
            $xml = simplexml_load_string($result);
            if ($xml->ws_response->head->errors->error->num_error == "0") {
                $username = $xml->USERNAME;
                $recovery_code = $xml->RECOVERY_CODE;

                // URL del Web App desplegado en Google Apps Script
                //url pau
                // $url = 'https://script.google.com/macros/s/AKfycbzs-WaweIA_cKNVVgqqPmianx7dn4wPI7AflDvM78iUcP8pUoYNh5u5Dg7nBlkofdKu/exec';

                //url Pol
                $url = 'https://script.google.com/macros/s/AKfycbxAQsgiFCg31C-G1MzD27GjZTo0Owa22XBoGJQzu2AT-WV8lWj76kud2WOuxLaxpH6OYw/exec';

                // Parámetros del correo electrónico
                $destinatario = $email;
                $asunto = 'Recuperar contraseña.';
                $cuerpo = $username . ', su código de recuperación de contraseña es ' . $recovery_code . '.';
                $adjunto = null;

                // Llamada a la función para enviar el correo
                enviarCorreo($url, $destinatario, $asunto, $cuerpo, $adjunto);
            }
            //Establecer el encabezado para XML
            header('Content-Type: text/xml');

            // Mostrar la respuesta XML
            echo $xml->asXML();

        } catch (PDOException $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    public function recoverPasswordPIN($email, $pin, $password)
    {
        try {
            $result = $this->dbCommand->execute('sp_wdev_recover_password_pin', array($email, $pin, $password));
            $xml = simplexml_load_string($result);
            //Establecer el encabezado para XML
            header('Content-Type: text/xml');

            // Mostrar la respuesta XML
            echo $xml->asXML();

        } catch (PDOException $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Todos los campos son obligatorios.</message></response>";
        } else {
            try {
                $result = $this->dbCommand->execute('sp_user_login', array($username, $password));
                $xml = simplexml_load_string($result);
                header('Content-Type: text/xml');
                echo $xml->asXML();
            } catch (PDOException $e) {
                header('Content-Type: text/xml');
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error: <![CDATA[" . $e->getMessage() . "]]></message></response>";
            }
        }
    }

    public function logout($username)
    {
        try {
            $result = $this->dbCommand->execute('sp_user_logout', array($username));
            $xml = simplexml_load_string($result);
            header('Content-Type: text/xml');
            echo $xml->asXML();
        } catch (PDOException $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    public function changePassword($ssid, $password, $newpassword)
    {
        try {
            $result = $this->dbCommand->execute('sp_user_change_password', array($ssid, $password, $newpassword));

            // Establecer el encabezado para XML
            header('Content-Type: text/xml');

            // Mostrar la respuesta XML
            echo $result;
        } catch (PDOException $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    public function accountValidate($username, $code)
    {
        if (empty($username) || empty($code)) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Todos los campos son obligatorios.</message></response>";
        } else {
            try {
                $result = $this->dbCommand->execute('sp_user_accountvalidate', array($username, $code));

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

    public function listusers($ssid)
    {
        if (empty($ssid)) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Todos los campos son obligatorios.</message></response>";
        } else {
            try {
                $result = $this->dbCommand->execute('sp_list_users2', array($ssid));

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

    public function checkpwd($pwd)
    {
        if (!isset($_GET['pwd'])) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> No se recibió ninguna contraseña.</message></response>";
            return;
        }

        try {
            // Llamar a la procedure sp_check_pwd
            $sql = "DECLARE @score INT, @xml XML;
                EXEC sp_check_pwd :pwd, @score OUTPUT, @xml OUTPUT;
                SELECT @score AS score, @xml AS xml;";

            $result = $this->dbCommand->execute2($sql, array($pwd));

            // Obtener el resultado
            $result = $result->fetch(PDO::FETCH_ASSOC);
            $score = $result['score'];
            $xml = $result['xml'];

            // Definir el mensaje según la puntuación
            header('Content-Type: text/xml');
            $status = "success";
            $message = "";
            $missing = "";
            if ($score <= 10) {
                $status = "error";
                $message = " Muy débil";
                $missing = $xml;
            } elseif ($score <= 20) {
                $status = "warning";
                $message = " Débil";
                $missing = $xml;
            } elseif ($score <= 40) {
                $status = "warning";
                $message = " Aceptable";
                $missing = $xml;
            } else {
                $status = "success";
                $message = " Fuerte";
            }

            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>$status</status><message>$message</message><missing><![CDATA[$missing]]></missing></response>";
        } catch (Exception $e) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Error en la validación: <![CDATA[" . $e->getMessage() . "]]></message></response>";
        }
    }

    public function blockUser($ssid)
    {
        if (empty($ssid)) {
            header('Content-Type: text/xml');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><status>error</status><message> Todos los campos son obligatorios.</message></response>";
        } else {
            try {
                $result = $this->dbCommand->execute('sp_user_block', array($ssid));

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

    // public function add_transaction($sender, $receiver, $amount)
    // {
    //     if (empty($sender) || empty($receiver) || empty($amount)) {
    //         echo "Todos los campos son obligatorios.";
    //     } else {
    //         try {
    //             if (!isset($_SESSION["BlockGenesis"])) {
    //                 $this->dbCommand->execute('AddBlock', array(null));
    //                 $_SESSION["BlockGenesis"] = true;
    //                 $_SESSION["id"] = 1;
    //                 $id = $_SESSION["id"];
    //             } else {
    //                 $id = $_SESSION["id"];
    //             }
    //             $this->dbCommand->execute('AddTransaction', array($sender, $receiver, $amount, $id));
    //             $_SESSION["id"]++;

    //         } catch (PDOException $e) {
    //             echo 'Error: ' . $e->getMessage();
    //         }
    //     }
    // }
}

?>