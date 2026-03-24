# Endpoints de la Aplicación (ws.php)

Todos los endpoints deben comenzar con: `http://localhost:8081/ws.mybizums.com/ws.php?action=[ACTION]`

> [!NOTE]
> La mayoría de los parámetros se envían por `GET`. Los nombres de los parámetros son sensibles a mayúsculas/minúsculas.

## Usuarios y Seguridad

### Login
- **Parámetros**: `username`, `password`
- **Ejemplo**: `.../ws.php?action=login&username=Franc&password=1234abcd.Z`

### Registro
- **Parámetros**: `username`, `name`, `lastname`, `password`, `email`, `gender` (M/F), `def_lang` (ESP/ENG)
- **Ejemplo**: `.../ws.php?action=register&username=Franc&name=Franc&lastname=Palacio&password=1234abcd.Z&email=manlucky0843@gmail.com&gender=M&def_lang=ESP`

### Validar Cuenta
- **Parámetros**: `username`, `code`
- **Ejemplo**: `.../ws.php?action=accvalidate&username=pepe&code=12345`

### Logout
- **Parámetros**: `ssid` (Session ID o ID de usuario)
- **Ejemplo**: `.../ws.php?action=logout&ssid=ID_DEL_USUARIO`

### Cambiar Contraseña
- **Parámetros**: `ssid`, `password` (actual), `newpassword`
- **Ejemplo**: `.../ws.php?action=changepass&ssid=ID&password=old&newpassword=new`
### Al cambiar la contraseña no la transforma en un hash y la guarda normal en la db 

---

## Operaciones Bizum

### Consultar Saldo
- **Parámetros**: `ssid`
- **Ejemplo**: `.../ws.php?action=checkbalance&ssid=ID`

### Enviar Bizum (addTransaction)
- **Parámetros**: `ssid` (emisor), `receiver` (username del receptor), `amount` (cantidad)
- **Ejemplo**: `.../ws.php?action=addTransaction&ssid=ID&receiver=paco&amount=10`

### Obtener Transacciones
- **Parámetros**: `ssid`
- **Ejemplo**: `.../ws.php?action=getTransactions&ssid=ID`

### Comprobar Usuario (Ver si existe para Bizum)
- **Parámetros**: `ssid`, `username`
- **Ejemplo**: `.../ws.php?action=checkuser&ssid=ID&username=pako`
### SACA HTML (corregir!!!)

---

## Administración y Debug

### Ver Conexiones Activas
- **Ejemplo**: `.../ws.php?action=viewcon`

### Ver Histórico de Conexiones
- **Ejemplo**: `.../ws.php?action=viewconhist`

### Listar Usuarios
- **Parámetros**: `ssid`
- **Ejemplo**: `.../ws.php?action=listusers&ssid=ID`
