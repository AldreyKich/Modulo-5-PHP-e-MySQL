<?php
/**
 * Pagina de Listagem e Gerenciamento de Usuarios
 * Exibe a lista de usuarios em uma tabela e contem links para cadastrar, editar e excluir (agora como botoes).
 */
// Inclui arquivos essenciais
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcoes.php';

// Inicia a sessao e verifica se o usuario esta autenticado e tem permissao (ex: 'admin')
//if (!sessaoAtiva() || $_SESSION['perfil'] !== 'admin') {
//    redirecionarComMensagem('login.php', 'erro', 'Acesso negado. Apenas administradores podem gerenciar usuarios.');/
//}

// Lista de perfis validos para o dropdown (nao usado aqui, mas mantido)
$perfis_validos = ['admin', 'bibliotecario', 'membro'];

// Obtem a conexao PDO
try {
    $db_instance = Database::getInstance();
    $conexao = $db_instance->getConnection();
} catch (Exception $e) {
    die("Erro de conexao: " . $e->getMessage());
}

// Funcao para buscar todos os usuarios
function buscarUsuarios($conexao) {
    try {
        $stmt = $conexao->prepare("SELECT id_usuario, nome, email, perfil, ativo FROM usuario ORDER BY nome ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Em um ambiente de producao, registre o erro ao inves de mostra-lo
        echo "Erro ao buscar usuarios: " . $e->getMessage();
        return []; // Retorna um array vazio em caso de erro
    }
}

$usuarios = buscarUsuarios($conexao);

// Inclui o cabecalho (que deve conter a estrutura HTML e possivelmente o Tailwind CSS)
include_once __DIR__ . '/includes/header.php'; 
?>

<div class="container mx-auto p-4 md:p-8">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow-2xl">
        
        <h1 class="text-3xl font-extrabold text-gray-800 mb-6 border-b-2 pb-2">Gerenciamento de Usuários</h1>

        <?php verificarExibirMensagens(); // Exibe mensagens de sucesso/erro ?>

        <div class="flex justify-end mb-4">
            <a href="login_cadastrar.php" class="btn btn-success">
                + Novo Usuário
            </a>
            <a href="index.php" class="btn btn-secondary">Voltar</a>
        </div>

        <?php if (empty($usuarios)): ?>
            <div class="p-4 bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500 rounded-md">
                Nenhum usuário cadastrado encontrado.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-mail</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr class="hover:bg-indigo-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($usuario['nome']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($usuario['email']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo ucfirst(htmlspecialchars($usuario['perfil'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                        $ativo_class = $usuario['ativo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                        $ativo_texto = $usuario['ativo'] ? 'Ativo' : 'Inativo';
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $ativo_class; ?>">
                                        <?php echo $ativo_texto; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    
                                    <a href="login_editar.php?id=<?php echo $usuario['id_usuario']; ?>" 
                                       class="btn btn-warning btn-small">
                                        Editar
                                    </a>
                                    
                                    <a href="login_excluir.php?id=<?php echo $usuario['id_usuario']; ?>" 
                                       onclick="return confirm('Tem certeza que deseja excluir o usuário <?php echo addslashes($usuario['nome']); ?>?');"
                                       class="btn btn-danger btn-small confirm-delete">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
// Inclui o rodape
include_once __DIR__ . '/includes/footer.php'; 
?>
