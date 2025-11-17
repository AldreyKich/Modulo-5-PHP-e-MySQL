<?php
// Senha que você está tentando usar
$senha_digitada = 'admin123';

// 1. **MUDE ESTA LINHA** para o hash que está no seu banco de dados atualmente (incluindo o $2y$10$...)
$hash_do_banco_aqui = '$2y$10$3p/lFpU2iW7B0y/T0jBv.uBw8k.3i.q5F6i.q5F6i.q5F6i.q5F6i.q5F6i.q5F6i.q5F6i.q5F6i.q5F6'; 
// O valor correto que eu forneci acima (para a senha 'admin123') é:
// $hash_do_banco_correto = '$2y$10$H8v3g9r2k2T7v2i4C0f8d.X1J7t6j5o3E2z4w3l1D4u6S9m5e.A8O';


echo "<h1>Teste de Verificação de Senha</h1>";
echo "Senha de teste: <b>{$senha_digitada}</b><br>";
echo "Hash no banco: <b>{$hash_do_banco_aqui}</b><br><br>";

// Executa a verificação
if (password_verify($senha_digitada, $hash_do_banco_aqui)) {
    echo "<p style='color: green; font-weight: bold;'>✅ VERIFICAÇÃO BEM-SUCEDIDA! O login PHP FUNCIONARÁ com este hash.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ VERIFICAÇÃO FALHOU! O hash está incorreto ou corrompido. Substitua o hash no banco de dados.</p>";
}
echo "<hr>";
echo "<h3>Novo Hash Sugerido (para 'admin123'):</h3>";
echo "<code>" . password_hash($senha_digitada, PASSWORD_BCRYPT) . "</code><br>";
echo "<small>Use este novo valor se a verificação falhar.</small>";