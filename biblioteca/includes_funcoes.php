<?php
/**
 * Arquivo de Funções Auxiliares
 * 
 * Este arquivo contém funções reutilizáveis que podem ser chamadas
 * de qualquer parte do sistema.
 * 
 * BOAS PRÁTICAS:
 * - Funções devem ter nomes descritivos
 * - Cada função deve fazer apenas UMA coisa
 * - Sempre documente o que a função faz
 * - Sempre valide os parâmetros recebidos
 */

// ========================================
// FUNÇÕES DE FORMATAÇÃO
// ========================================

/**
 * Formata uma data do formato MySQL para o formato brasileiro
 * 
 * @param string $data Data no formato Y-m-d (2025-11-06)
 * @return string Data no formato d/m/Y (06/11/2025) ou '-' se vazia
 * 
 * Exemplo de uso:
 * echo formatarData('2025-11-06'); // Saída: 06/11/2025
 */
function formatarData($data) {
    // Verifica se a data está vazia ou é nula
    if (empty($data) || $data == '0000-00-00') {
        return '-';
    }
    
    // Converte a string de data para timestamp e depois formata
    return date('d/m/Y', strtotime($data));
}

/**
 * Formata uma data e hora completa
 * 
 * @param string $dataHora DateTime no formato MySQL
 * @return string Data e hora formatadas ou '-' se vazia
 * 
 * Exemplo de uso:
 * echo formatarDataHora('2025-11-06 14:30:00'); // Saída: 06/11/2025 às 14:30
 */
function formatarDataHora($dataHora) {
    if (empty($dataHora)) {
        return '-';
    }
    
    return date('d/m/Y \à\s H:i', strtotime($dataHora));
}

/**
 * Formata um valor numérico para moeda brasileira
 * 
 * @param float $valor Valor numérico
 * @return string Valor formatado como R$ 0,00
 * 
 * Exemplo de uso:
 * echo formatarMoeda(10.5); // Saída: R$ 10,50
 */
function formatarMoeda($valor) {
    // number_format(número, casas decimais, separador decimal, separador de milhares)
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Formata um telefone no padrão brasileiro
 * 
 * @param string $telefone Telefone sem formatação
 * @return string Telefone formatado (00) 00000-0000
 * 
 * Exemplo de uso:
 * echo formatarTelefone('11999887766'); // Saída: (11) 99988-7766
 */
function formatarTelefone($telefone) {
    // Remove tudo que não é número
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Verifica o tamanho e formata adequadamente
    if (strlen($telefone) == 11) {
        // Celular: (00) 00000-0000
        return '(' . substr($telefone, 0, 2) . ') ' . 
               substr($telefone, 2, 5) . '-' . 
               substr($telefone, 7, 4);
    } elseif (strlen($telefone) == 10) {
        // Fixo: (00) 0000-0000
        return '(' . substr($telefone, 0, 2) . ') ' . 
               substr($telefone, 2, 4) . '-' . 
               substr($telefone, 6, 4);
    }
    
    // Se não tiver tamanho adequado, retorna como está
    return $telefone;
}

/**
 * Formata um CPF
 * 
 * @param string $cpf CPF sem formatação
 * @return string CPF formatado 000.000.000-00
 */
function formatarCPF($cpf) {
    // Remove tudo que não é número
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Formata se tiver 11 dígitos
    if (strlen($cpf) == 11) {
        return substr($cpf, 0, 3) . '.' . 
               substr($cpf, 3, 3) . '.' . 
               substr($cpf, 6, 3) . '-' . 
               substr($cpf, 9, 2);
    }
    
    return $cpf;
}

// ========================================
// FUNÇÕES DE CÁLCULO
// ========================================

/**
 * Calcula quantos dias de atraso há em uma devolução
 * 
 * @param string $data_prevista Data prevista de devolução (Y-m-d)
 * @return int Número de dias de atraso (0 se não há atraso)
 * 
 * Exemplo de uso:
 * $dias = calcularDiasAtraso('2025-11-01'); 
 * // Se hoje é 06/11, retorna 5 dias
 */
function calcularDiasAtraso($data_prevista) {
    // Converte as datas para timestamp (números)
    $hoje = strtotime(date('Y-m-d'));
    $prevista = strtotime($data_prevista);
    
    // Se a data prevista já passou
    if ($hoje > $prevista) {
        // Calcula a diferença em dias
        // 86400 = número de segundos em um dia (60*60*24)
        $dias = floor(($hoje - $prevista) / 86400);
        return $dias;
    }
    
    // Não há atraso
    return 0;
}

/**
 * Calcula o valor da multa baseado nos dias de atraso
 * 
 * @param int $dias_atraso Número de dias de atraso
 * @return float Valor da multa
 * 
 * Exemplo de uso:
 * $multa = calcularMulta(5); // Retorna 12.50 (5 * 2.50)
 */
function calcularMulta($dias_atraso) {
    if ($dias_atraso <= 0) {
        return 0.00;
    }
    
    // Usa a constante definida em config.php
    return $dias_atraso * VALOR_MULTA_DIA;
}

/**
 * Calcula a data de devolução prevista
 * 
 * @param string $data_emprestimo Data do empréstimo (Y-m-d)
 * @param int $dias_prazo Número de dias de prazo (padrão: constante)
 * @return string Data prevista de devolução (Y-m-d)
 */
function calcularDataDevolucao($data_emprestimo = null, $dias_prazo = null) {
    // Se não informar a data, usa a data atual
    if ($data_emprestimo === null) {
        $data_emprestimo = date('Y-m-d');
    }
    
    // Se não informar o prazo, usa a constante do sistema
    if ($dias_prazo === null) {
        $dias_prazo = PRAZO_EMPRESTIMO_DIAS;
    }
    
    // Adiciona os dias ao timestamp e formata
    return date('Y-m-d', strtotime($data_emprestimo . " +{$dias_prazo} days"));
}

// ========================================
// FUNÇÕES DE VALIDAÇÃO
// ========================================

/**
 * Valida um endereço de e-mail
 * 
 * @param string $email E-mail a ser validado
 * @return bool TRUE se válido, FALSE se inválido
 */
function validarEmail($email) {
    // filter_var é uma função nativa do PHP para validação
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida um CPF brasileiro
 * 
 * @param string $cpf CPF a ser validado
 * @return bool TRUE se válido, FALSE se inválido
 */
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais (CPF inválido)
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    
    // Validação dos dígitos verificadores
    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

/**
 * Limpa e sanitiza uma string de entrada
 * 
 * @param string $dados Dados a serem limpos
 * @return string Dados limpos e seguros
 * 
 * Esta função é essencial para SEGURANÇA!
 * Remove espaços extras e converte caracteres especiais
 */
function limparInput($dados) {
    // Se for um array, aplica a função recursivamente
    if (is_array($dados)) {
        return array_map('limparInput', $dados);
    }
    
    // Remove espaços do início e fim
    $dados = trim($dados);
    
    // Remove barras invertidas
    $dados = stripslashes($dados);
    
    // Converte caracteres especiais em entidades HTML
    // Isso previne ataques XSS (Cross-Site Scripting)
    $dados = htmlspecialchars($dados, ENT_QUOTES, 'UTF-8');
    
    return $dados;
}

// ========================================
// FUNÇÕES DE MENSAGENS
// ========================================

/**
 * Exibe uma mensagem formatada para o usuário
 * 
 * @param string $tipo Tipo da mensagem (sucesso, erro, aviso, info)
 * @param string $mensagem Texto da mensagem
 * @return void (imprime HTML diretamente)
 * 
 * Exemplo de uso:
 * exibirMensagem('sucesso', 'Cliente cadastrado com sucesso!');
 */
function exibirMensagem($tipo, $mensagem) {
    // Define as cores para cada tipo de mensagem
    $cores = [
        'sucesso' => ['bg' => '#d4edda', 'borda' => '#28a745', 'texto' => '#155724'],
        'erro'    => ['bg' => '#f8d7da', 'borda' => '#dc3545', 'texto' => '#721c24'],
        'aviso'   => ['bg' => '#fff3cd', 'borda' => '#ffc107', 'texto' => '#856404'],
        'info'    => ['bg' => '#d1ecf1', 'borda' => '#17a2b8', 'texto' => '#0c5460']
    ];
    
    // Define ícones para cada tipo
    $icones = [
        'sucesso' => '✓',
        'erro'    => '✗',
        'aviso'   => '⚠',
        'info'    => 'ℹ'
    ];
    
    // Pega as cores do tipo (ou usa info como padrão)
    $cor = isset($cores[$tipo]) ? $cores[$tipo] : $cores['info'];
    $icone = isset($icones[$tipo]) ? $icones[$tipo] : $icones['info'];
    
    // Imprime a mensagem com estilos inline
    echo "<div style='background-color:{$cor['bg']}; 
                     color:{$cor['texto']}; 
                     padding:15px 20px; 
                     margin:15px 0; 
                     border-left:4px solid {$cor['borda']};
                     border-radius:4px;
                     display:flex;
                     align-items:center;
                     gap:10px;'>";
    echo "<strong style='font-size:20px;'>{$icone}</strong>";
    echo "<span>{$mensagem}</span>";
    echo "</div>";
}

/**
 * Redireciona para outra página com mensagem
 * 
 * @param string $pagina URL da página destino
 * @param string $tipo Tipo da mensagem
 * @param string $mensagem Texto da mensagem
 * @return void
 */
function redirecionarComMensagem($pagina, $tipo, $mensagem) {
    // Codifica a mensagem para URL
    $mensagem_encoded = urlencode($mensagem);
    
    // Adiciona os parâmetros na URL
    $separador = (strpos($pagina, '?') !== false) ? '&' : '?';
    $url = $pagina . $separador . "msg_tipo={$tipo}&msg={$mensagem_encoded}";
    
    // Redireciona
    header("Location: {$url}");
    exit;
}

/**
 * Verifica e exibe mensagens da URL (se existirem)
 * 
 * @return void
 * 
 * Chamado no início das páginas para exibir mensagens de redirecionamento
 */
function verificarExibirMensagens() {
    if (isset($_GET['msg_tipo']) && isset($_GET['msg'])) {
        $tipo = limparInput($_GET['msg_tipo']);
        $mensagem = limparInput($_GET['msg']);
        exibirMensagem($tipo, $mensagem);
    }
}

// ========================================
// FUNÇÕES DE UTILIDADE
// ========================================

/**
 * Gera um resumo de texto (truncate)
 * 
 * @param string $texto Texto completo
 * @param int $limite Número máximo de caracteres
 * @param string $complemento String a adicionar no final (padrão: "...")
 * @return string Texto resumido
 */
function resumirTexto($texto, $limite = 100, $complemento = '...') {
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    
    // Corta no limite e adiciona o complemento
    return substr($texto, 0, $limite) . $complemento;
}

/**
 * Retorna a classe CSS baseada no status de um empréstimo
 * 
 * @param string $status Status do empréstimo
 * @return string Nome da classe CSS
 */
function obterClasseStatus($status) {
    $classes = [
        'Ativo' => 'status-ativo',
        'Devolvido' => 'status-devolvido',
        'Atrasado' => 'status-atrasado',
        'Cancelado' => 'status-cancelado'
    ];
    
    return isset($classes[$status]) ? $classes[$status] : 'status-default';
}

/**
 * Debug melhorado - exibe variáveis de forma legível
 * 
 * @param mixed $variavel Qualquer variável para debug
 * @param bool $die Se TRUE, para a execução após exibir
 * @return void
 * 
 * Use apenas em DESENVOLVIMENTO!
 */
function debug($variavel, $die = false) {
    echo '<pre style="background:#f4f4f4; padding:15px; border:1px solid #ddd; margin:10px 0;">';
    print_r($variavel);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}
