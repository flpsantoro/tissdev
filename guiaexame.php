<?php /*
if (!isset($_SESSION['logado'])) return;
require_once dirname(__FILE__) . '/config.php';

$_SESSION['dados'] = null;
$_SESSION['nrLote'] = '-';

$dados = null;
$nrGuiaPrestador = null;
$seqImportacao = null;*/
?>
<div>
    <form>
        <label for="1_registro_ans">1 - Registro ANS</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="2_nr_guia_prestador">2 - Nº da guia no prestador</label>
        <input type="text" size="20" name="2_nr_guia_prestador">

        <label for="1_registro_ans">3 - Número da guia principa</label>
        <input type="text" size="20" name="1_registro_ans">

        <label for="1_registro_ans">4 - Data da autorização</label>
        <input type="date" size="6" name="1_registro_ans">

        <label for="1_registro_ans">5 - Senha</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">6 - Data de validade da senha</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">7 - Número da guia atribuído pela operadora</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">8 - Número da carteira</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">9 - Validade da carteira</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">10 - Nome</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">11 - Cartão Nacional de Saúde</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">12 - Atendimento a RN</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">13 - Código na operadora</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">14 - Nome do contratado</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">15 - Nome do profissional solicitante</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">16 - Conselho Profissional</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">17 - Número no conselho</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">18 - UF</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">19 - Código CBO</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">20 - Assinatura do Solicitante</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">21 - Caráter do Atendimento</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">22 - Data da Solicitação</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">23 - Indicação Clínica</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">24 - Tabela</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">25 - Código do procedimento ou item assistencial </label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">26 - Descrição</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">27 - Qtde Solic</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">28 - Qtde Aut</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">29 - Código na operadora</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">30 - Nome do contratado</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">31 - Código CNES</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">32 - Tipo de Atendimento</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">33 - Indicação de Acidente</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">34 - Tipo de consulta</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">35 - Motivo de Encerramento do Atendimento</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">36 - Data</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">37 - Hora Inicial</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">38 - Hora Final</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">39 - Tabela</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">40 - Código do Procedimento</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">41 - Descrição</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">42 - Qtde</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">43 - Via</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">44 - Téc</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">45 - Fator Red / Acrésc</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">46 - Valor Unitário</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">47 - Valor Total</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">48 - Seq. Ref</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">49 - Grau Part</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">50 - Código na Operadora / CPF</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">51 - Nome do profissional</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">52 - Conselho Profissional</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">53 - Número no conselho</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">54 - UF</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">55 - Código CBO</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">56 - Data de realização de procedimentos em série</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">57 - Assinatura do beneficiário ou responsável na realização de procedimentos em
            série</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">58 - Observação/Justificativa</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">59 - Total de Procedimentos</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">60 - Total de Taxas Diversas e Aluguéis</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">61 - Total de Materiais</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">62 - Total de OPME</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">63 - Total de Medicamentos</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">64 - Total Gases Medicinais</label>
        <input type="text" size="6" name="1_registro_ans">

        <label for="1_registro_ans">65 - Total Geral</label>
        <input type="text" size="6" name="1_registro_ans">

        <p>
            <span id="1_registro_ans">___________________<br>Assinatura do responsável pela Autorização<br></span>
            <span id="1_registro_ans">___________________<br>Assinatura do Beneficiário ou Responsável<br></span>
            <span id="1_registro_ans">___________________<br>Assinatura do contratado<br></span>
        </p>

    </form>
</div>
