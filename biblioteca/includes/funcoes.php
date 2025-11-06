<?php
function formatarData($data) {
    if (empty($data)) return '-';
    return date('d/m/Y', strtotime($data));
}

function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function calcularDiasAtraso($data_prevista) {
    $hoje = strtotime(date('Y-m-d'));
    $prevista = strtotime($data_prevista);

    if ($hoje > $prevista) {
        return ($hoje - $prevista) / (60 * 60 * 24);
    }

    return 0;
}

function exibirMensagem($tipo, $mensagem) {
    $cores = [
        'sucesso' => '#4CAF50',
        'erro'    => '#f44336',
        'aviso'   => '#ff9800',
        'info'    => '#2196F3'
    ];

    $cor = isset($cores[$tipo]) ? $cores[$tipo] : $cores['info'];

    echo "<div style='background:$cor; color:white; padding:15px; margin:10px 0;'>";
    echo $mensagem;
    echo "</div>";
}

function validarFormulario($campos_obrigatorios, $dados) {
    $erros = [];

    foreach ($campos_obrigatorios as $campo => $nome) {
        if (!isset($dados[$campo]) || empty(trim($dados[$campo]))) {
            $erros[] = "O campo '$nome' é obrigatório.";
        }
    }

    return $erros;
}

function limparInput($dados) {
    if (is_array($dados)) {
        return array_map('limparInput', $dados);
    }
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}
?>
