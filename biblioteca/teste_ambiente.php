<?php
// Verificar versão do PHP
echo "Versão do PHP: " . phpversion() . "<br>";

// Verificar extensões necessárias
echo "MySQLi: " . (extension_loaded('mysqli') ? 'Instalado' : 'Não instalado') . "<br>";
echo "PDO: " . (extension_loaded('pdo') ? 'Instalado' : 'Não instalado') . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Instalado' : 'Não instalado') . "<br>";

// Exibir informações detalhadas do PHP
phpinfo();
?>
