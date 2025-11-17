<?php
/**
 * Gerador de PDF para Comprovante de Empréstimo ou Devolução
 * * Usa FPDF para buscar os dados do empréstimo e gerar um recibo formatado.
 *
 * @author Módulo 5 - Banco de Dados II
 * @version 1.5 (Suporte a Comprovante de Devolução, Multa e Data Real)
 */

// 1. INCLUSÕES E CONFIGURAÇÕES
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'includes/funcoes.php';

// =========================================================================
// CAMINHO FPDF CORRIGIDO: fpdf/fpdf.php
// A biblioteca FPDF agora está na pasta 'biblioteca/fpdf', conforme a estrutura.
// =========================================================================
require_once(__DIR__ . '/fpdf/fpdf.php');

$db = Database::getInstance();
$pdo = $db->getConnection();

$emprestimo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Verifica se o comprovante é de devolução para mudar o layout e dados
$tipo_comprovante = isset($_GET['tipo']) && $_GET['tipo'] === 'devolucao' ? 'DEVOLUCAO' : 'EMPRESTIMO';

// 2. BUSCA DE DADOS
if ($emprestimo_id <= 0) {
    // Se o ID for inválido, apenas exibe um erro e para a execução.
    header('Content-Type: text/plain; charset=utf-8');
    die("Erro: ID de empréstimo inválido.");
}

try {
    // Adiciona campos de devolução e multa na consulta
    $sql = "SELECT 
                e.id AS emprestimo_id,
                e.status AS status_emprestimo,
                e.multa,
                e.data_devolucao_real,
                c.nome AS nome_cliente,
                l.titulo AS titulo_livro,
                e.data_emprestimo,
                e.data_devolucao_prevista
            FROM 
                emprestimos e
            JOIN 
                clientes c ON e.cliente_id = c.id
            JOIN 
                livros l ON e.livro_id = l.id
            WHERE 
                e.id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $emprestimo_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dados) {
        header('Content-Type: text/plain; charset=utf-8');
        die("Erro: Dados do empréstimo #{$emprestimo_id} não encontrados.");
    }
    
    // Converte a multa para float para comparação
    $multa_valor = (float)$dados['multa'];

} catch (PDOException $e) {
    header('Content-Type: text/plain; charset=utf-8');
    die("Erro no banco de dados: " . $e->getMessage());
}

// 3. GERAÇÃO DO PDF
$pdf = new FPDF();
$pdf->AddPage();

// Configurações de Fonte e Título
$pdf->SetFont('Arial', 'B', 16);
if ($tipo_comprovante === 'DEVOLUCAO') {
    $pdf->Cell(0, 10, utf8_decode('Comprovante de Devolução'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 5, utf8_decode('Registro de Baixa e Pagamento de Multa (se aplicável)'), 0, 1, 'C');
} else {
    $pdf->Cell(0, 10, utf8_decode('Comprovante de Empréstimo'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 5, utf8_decode('Biblioteca - Sistema de Gestão de Acervo'), 0, 1, 'C');
}
$pdf->Ln(15); // Linha em branco

// Informações do Comprovante
$pdf->SetFillColor(230, 230, 230); // Cinza claro para o fundo das células

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, utf8_decode('ID do Empréstimo:'), 1, 0, 'L', 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $dados['emprestimo_id'] . ' (' . utf8_decode($dados['status_emprestimo']) . ')', 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, utf8_decode('Cliente:'), 1, 0, 'L', 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode($dados['nome_cliente']), 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, utf8_decode('Livro:'), 1, 0, 'L', 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode($dados['titulo_livro']), 1, 1, 'L');

$pdf->Ln(10); // Linha em branco

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, utf8_decode('Data do Empréstimo:'), 1, 0, 'L', 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, formatarData($dados['data_emprestimo']), 1, 1, 'L');

// --- Dados de Devolução/Previsão ---

if ($tipo_comprovante === 'DEVOLUCAO' && $dados['data_devolucao_real']) {
    // Se for comprovante de DEVOLUÇÃO
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Data de Devolução Prevista:'), 1, 0, 'L', 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, formatarData($dados['data_devolucao_prevista']), 1, 1, 'L');
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Data de Devolução Real:'), 1, 0, 'L', 1);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0, 100, 0); // Cor verde
    $pdf->Cell(0, 10, formatarData($dados['data_devolucao_real']), 1, 1, 'L');
    $pdf->SetTextColor(0, 0, 0); // Volta à cor preta

} else {
    // Se for comprovante de EMPRÉSTIMO ou RENOVAÇÃO
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Devolução Prevista:'), 1, 0, 'L', 1);
    $pdf->SetFont('Arial', 'B', 12); // Destaca a data de devolução
    $pdf->SetTextColor(200, 0, 0); // Cor vermelha para a data de devolução
    $pdf->Cell(0, 10, formatarData($dados['data_devolucao_prevista']) . ' (' . PRAZO_EMPRESTIMO_DIAS . ' dias)', 1, 1, 'L');
    $pdf->SetTextColor(0, 0, 0); // Volta à cor preta
}

// --- Campo de Multa (Apenas na devolução, se houver) ---
if ($tipo_comprovante === 'DEVOLUCAO' && $multa_valor > 0) {
    $pdf->Ln(10);
    
    $pdf->SetFillColor(255, 230, 230); // Fundo rosa/vermelho claro
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(200, 0, 0); // Cor vermelha para multa
    $pdf->Cell(60, 12, utf8_decode('VALOR DA MULTA:'), 1, 0, 'L', 1);
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 12, formatarMoeda($multa_valor), 1, 1, 'R', 1);
    
    $pdf->SetTextColor(0, 0, 0); // Volta à cor preta
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 5, utf8_decode('O valor acima foi registrado e cobrado no ato da devolução.'), 0, 1, 'C');
}
// --- Fim Campo Multa ---

$pdf->Ln(20);

// Rodapé/Instruções
$pdf->SetFont('Arial', 'I', 10);
if ($tipo_comprovante === 'DEVOLUCAO') {
    $pdf->Cell(0, 5, utf8_decode('Comprovante de registro de devolução e quitação de pendências.'), 0, 1, 'C');
} else {
    $pdf->Cell(0, 5, utf8_decode('Por favor, devolva o livro na data prevista para evitar bloqueios ou multas.'), 0, 1, 'C');
}
$pdf->Cell(0, 5, utf8_decode('Gerado em: ') . date('d/m/Y H:i:s'), 0, 1, 'C');


// Saída do PDF (I = Browser, D = Download)
$pdf->Output('I', "Comprovante_{$tipo_comprovante}_{$emprestimo_id}.pdf");

?>
