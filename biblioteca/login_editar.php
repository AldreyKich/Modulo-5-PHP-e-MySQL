<?php
/**
 * Página de Edição de Usuário
 * Carrega os dados de um usuário pelo ID e exibe o formulário para edição.
 */
 
// Inclui arquivos essenciais
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcoes.php';

// Inicia a sessão e verifica se o usuário está autenticado e tem permissão (ex: 'admin')
// if (!sessaoAtiva() || $_SESSION['perfil'] !== 'admin') {
//     redirecionarComMensagem('login.php', 'erro', 'Acesso negado. Apenas administradores podem editar usuários.');
// }

// 1. Obtém e valida o ID do usuário (vindo da URL via GET)
$id_usuario = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id_usuario) {
    redirecionarComMensagem('login_listar.php', 'erro', 'ID de usuário inválido ou ausente para edição.');
}

// 2. Obtém a conexão PDO
try {
    $db_instance = Database::getInstance();
    $conexao = $db_instance->getConnection();
} catch (Exception $e) {
    redirecionarComMensagem('login_listar.php', 'erro', 'Erro de conexão com o banco de dados.');
}

// 3. Busca os dados do usuário
$usuario = null;
try {
    $stmt = $conexao->prepare("SELECT id_usuario, nome, email, perfil, ativo FROM usuario WHERE id_usuario = :id");
    $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        redirecionarComMensagem('login_listar.php', 'erro', "Usuário com ID {$id_usuario} não encontrado.");
    }
} catch (PDOException $e) {
    redirecionarComMensagem('login_listar.php', 'erro', 'Erro ao buscar dados do usuário: ' . $e->getMessage());
}

// Inclui o cabeçalho
include_once __DIR__ . '/includes/header.php'; 

// Variáveis para preenchimento do formulário
$nome = htmlspecialchars($usuario['nome'] ?? '');
$email = htmlspecialchars($usuario['email'] ?? '');
$perfil = htmlspecialchars($usuario['perfil'] ?? '');
$ativo_checked = ($usuario['ativo'] ?? 0) ? 'checked' : '';
?>

<div class="container mx-auto p-4 md:p-8">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow-2xl">
        
        <h1 class="text-3xl font-extrabold text-gray-800 mb-6 border-b-2 pb-2">Editar Usuário</h1>

        <?php verificarExibirMensagens(); // Exibe mensagens de sucesso/erro ?>

        <form action="login_atualizar.php" method="POST">
            
            <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">

            <div class="mb-4">
                <label for="nome" class="block text-sm font-medium text-gray-700">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo $nome; ?>" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">E-mail:</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            
            <div class="mb-4">
                <label for="perfil" class="block text-sm font-medium text-gray-700">Perfil:</label>
                <select id="perfil" name="perfil" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    
                    <option value="admin" <?php echo ($perfil === 'admin' ? 'selected' : ''); ?>>Administrador</option>
                    <option value="bibliotecario" <?php echo ($perfil === 'bibliotecario' ? 'selected' : ''); ?>>Bibliotecário</option>
                    <option value="membro" <?php echo ($perfil === 'membro' ? 'selected' : ''); ?>>Membro</option>
                </select>
            </div>

            <div class="mb-6 flex items-center">
                <input id="ativo" name="ativo" type="checkbox" value="1" <?php echo $ativo_checked; ?> 
                       class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="ativo" class="ml-2 block text-sm text-gray-900">Usuário Ativo</label>
            </div>
            
            <div class="mb-6 border-t pt-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Alterar Senha (Deixe em branco para manter a atual)</h2>
                <label for="senha" class="block text-sm font-medium text-gray-700">Nova Senha:</label>
                <input type="password" id="senha" name="senha" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       placeholder="********">
            </div>

            <div class="flex justify-between">
                <a href="login_listar.php" class="px-4 py-2 text-sm font-medium rounded-md text-gray-600 bg-gray-200 hover:bg-gray-300 transition duration-150 ease-in-out">
                    Voltar
                </a>
                <button type="submit" 
                        class="px-6 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
// Inclui o rodapé
include_once __DIR__ . '/includes/footer.php'; 
?>