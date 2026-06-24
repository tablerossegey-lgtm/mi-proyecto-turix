<?php
$env = file_get_contents('.env');
$lines = explode("\n", $env);
$cfg = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || $line[0] === '#') continue;
    if (strpos($line, '=') !== false) {
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);
        if ($val[0] === "'" || $val[0] === '"') {
            $val = substr($val, 1, -1);
        }
        $cfg[$key] = $val;
    }
}

$hostname = $cfg['database.default.hostname'] ?? 'localhost';
$database = $cfg['database.default.database'] ?? '';
$username = $cfg['database.default.username'] ?? '';
$password = $cfg['database.default.password'] ?? '';

$mysqli = new mysqli($hostname, $username, $password, $database);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$idCliente = 449;
$resCompras = $mysqli->query("SELECT * FROM t_cuenta_cliente WHERE idCliente = $idCliente");
$compras = [];
while ($c = $resCompras->fetch_assoc()) {
    $compras[] = $c;
}

$comprasActivas = array_filter($compras, function($c) { 
    $unpaid = ($c['estatusCompra'] == '0');
    $undelivered = (($c['estatus_entrega'] ?? 0) != 2);
    return $unpaid || $undelivered; 
});

echo "COMPRAS ACTIVAS EN FILTRADO:\n";
print_r($comprasActivas);

$mysqli->close();
