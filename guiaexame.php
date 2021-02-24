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
    <form id="guiaexame">
        <fieldset>
            <legend><h3>GUIA DE SERVIÇO PROFISSIONAL / SERVIÇO AUXILIAR DE DIAGNÓSTICO E TERAPIA - SP/SADT</h3></legend>
            <label for="2_nr_guia_prestador">2 - Nº da guia no prestador</label>
            <input type="text" size="20" maxlength="20" name="2_nr_guia_prestador">
            <br>
            <label for="1_registro_ans">1 - Registro ANS</label>
            <input type="text" size="6" maxlength="6" name="1_registro_ans">
            <br>
            <label for="3_nr_guia_principal">3 - Número da guia principal</label>
            <input type="text" size="20" maxlength="20" name="3_nr_guia_principal">
            <br>
            <label for="4_dt_autorizacao">4 - Data da autorização</label>
            <input type="date" size="8" maxlength="8" name="4_dt_autorizacao">
            <br>
            <label for="5_senha">5 - Senha</label>
            <input type="text" size="20" maxlength="20" name="5_senha">
            <br>
            <label for="6_dt_val_senha">6 - Data de validade da senha</label>
            <input type="date" size="8" maxlength="8" name="6_dt_val_senha">
            <br>
            <label for="7_nr_guia_operadora">7 - Número da guia atribuído pela operadora</label>
            <input type="text" size="20" maxlength="20" name="7_nr_guia_operadora">
        </fieldset>
        <fieldset>
            <legend>Dados do Beneficiário</legend>
            <label for="8_nr_carteira">8 - Número da carteira</label>
            <input type="text" size="20" maxlength="20" name="8_nr_carteira">
            <br>
            <label for="9_val_carteira">9 - Validade da carteira</label>
            <input type="date" size="8" maxlength="8" name="9_val_carteira">
            <br>
            <label for="10_nome">10 - Nome</label>
            <input type="text" size="70" maxlength="70" name="10_nome">
            <br>
            <label for="11_cartao_nac_saude">11 - Cartão Nacional de Saúde</label>
            <input type="text" size="15" maxlength="15" name="11_cartao_nac_saude">
            <br>
            <label for="12_atend_rn">12 - Atendimento a RN</label>
            <input type="text" size="1" maxlength="1" name="12_atend_rn">
        </fieldset>
        <fieldset>
            <legend>Dados do Solicitante</legend>
            <label for="13_cd_opr">13 - Código na operadora</label>
            <input type="text" size="14" maxlength="14" name="13_cd_opr">
            <br>
            <label for="14_nm_contratado">14 - Nome do contratado</label>
            <input type="text" size="70" maxlength="70" name="14_nm_contratado">
            <br>
            <label for="15_nm_prof_sol">15 - Nome do profissional solicitante</label>
            <input type="text" size="70" maxlength="70" name="15_nm_prof_sol">
            <br>
            <label for="16_conselho_prof">16 - Conselho Profissional</label>
            <input type="text" size="2" maxlength="2" name="16_conselho_prof">
            <br>
            <label for="17_nr_conselho">17 - Número no conselho</label>
            <input type="text" size="15" maxlength="15" name="17_nr_conselho">
            <br>
            <label for="18_uf">18 - UF</label>
            <input type="text" size="2" maxlength="2" name="18_uf">
            <br>
            <label for="19_cd_cbo">19 - Código CBO</label>
            <input type="text" size="6" maxlength="6" name="19_cd_cbo">
            <br>
            <label for="20_assinatura_sol">20 - Assinatura do Solicitante</label>
            <span>________________________</span>
        </fieldset>
        <fieldset>
            <legend>Dados da Solicitação / Procedimentos ou Itens Assistenciais Solicitados</legend>
            <label for="21_carater_atend">21 - Caráter do Atendimento</label>
            <input type="text" size="1" maxlength="1" name="21_carater_atend">
            <br>
            <label for="22_dt_atend">22 - Data da Solicitação</label>
            <input type="date" size="8" maxlength="8" name="22_dt_atend">
            <br>
            <label for="23_indicacao_clinica">23 - Indicação Clínica</label><br>
            <textarea maxlength="500" cols="100" rows="10" name="23_indicacao_clinica"
                      form="guiaexame"></textarea>
            <br>
            <label for="24_tabela">24 - Tabela</label>
            <ol>
                <li><input type="text" size="2" maxlength="2" name="24_tabela_1"></li>
                <li><input type="text" size="2" maxlength="2" name="24_tabela_2"></li>
                <li><input type="text" size="2" maxlength="2" name="24_tabela_3"></li>
                <li><input type="text" size="2" maxlength="2" name="24_tabela_4"></li>
                <li><input type="text" size="2" maxlength="2" name="24_tabela_5"></li>
            </ol>
            <label for="25_cd_proc_item_assist">25 - Código do procedimento ou item assistencial </label>
            <ol>
                <li><input type="text" size="10" maxlength="10" name="25_cd_proc_item_assist_1"></li>
                <li><input type="text" size="10" maxlength="10" name="25_cd_proc_item_assist_2"></li>
                <li><input type="text" size="10" maxlength="10" name="25_cd_proc_item_assist_3"></li>
                <li><input type="text" size="10" maxlength="10" name="25_cd_proc_item_assist_4"></li>
                <li><input type="text" size="10" maxlength="10" name="25_cd_proc_item_assist_5"></li>
            </ol>
            <label for="26_descricao">26 - Descrição</label>
            <ol>
                <li><input type="text" size="150" maxlength="150" name="26_descricao_1"></li>
                <li><input type="text" size="150" maxlength="150" name="26_descricao_2"></li>
                <li><input type="text" size="150" maxlength="150" name="26_descricao_3"></li>
                <li><input type="text" size="150" maxlength="150" name="26_descricao_4"></li>
                <li><input type="text" size="150" maxlength="150" name="26_descricao_5"></li>
            </ol>
            <label for="27_qtde_sol">27 - Qtde Solic</label>
            <ol>
                <li><input type="number" size="3" maxlength="3" name="27_qtde_sol_1"></li>
                <li><input type="number" size="3" maxlength="3" name="27_qtde_sol_2"></li>
                <li><input type="number" size="3" maxlength="3" name="27_qtde_sol_3"></li>
                <li><input type="number" size="3" maxlength="3" name="27_qtde_sol_4"></li>
                <li><input type="number" size="3" maxlength="3" name="27_qtde_sol_5"></li>
            </ol>
            <label for="28_qtde_autorizada">28 - Qtde Aut</label>
            <ol>
                <li><input type="number" size="3" maxlength="3" name="28_qtde_autorizada_1"></li>
                <li><input type="number" size="3" maxlength="3" name="28_qtde_autorizada_2"></li>
                <li><input type="number" size="3" maxlength="3" name="28_qtde_autorizada_3"></li>
                <li><input type="number" size="3" maxlength="3" name="28_qtde_autorizada_4"></li>
                <li><input type="number" size="3" maxlength="3" name="28_qtde_autorizada_5"></li>
            </ol>
        </fieldset>
        <fieldset>
            <legend>Dados do Contratado Executante</legend>
            <label for="29_cd_opr">29 - Código na operadora</label>
            <input type="text" size="14" maxlength="14" name="29_cd_opr">
            <br>
            <label for="30_nm_contratado">30 - Nome do contratado</label>
            <input type="text" size="70" maxlength="70" name="30_nm_contratado">
            <br>
            <label for="31_cd_cnes">31 - Código CNES</label>
            <input type="text" size="7" maxlength="7" name="31_cd_cnes">
        </fieldset>
        <fieldset>
            <legend>Dados do Atendimento</legend>
            <label for="32_tp_atendimento">32 - Tipo de Atendimento</label>
            <input type="text" size="2" maxlength="2" name="32_tp_atendimento">
            <br>
            <label for="33_indic_acidente">33 - Indicação de Acidente</label>
            <input type="text" size="1" maxlength="1" name="33_indic_acidente">
            <br>
            <label for="34_tp_consulta">34 - Tipo de consulta</label>
            <input type="text" size="1" maxlength="1" name="34_tp_consulta">
            <br>
            <label for="35_motivo_encerramento_atend">35 - Motivo de Encerramento do Atendimento</label>
            <input type="text" size="2" maxlength="2" name="35_motivo_encerramento_atend">
        </fieldset>
        <fieldset>
            <legend>Dados da Execução / Procedimentos e Exames Realizados</legend>
            <label for="36_data">36 - Data</label>
            <ol>
                <li><input type="date" size="8" maxlength="8" name="36_data_1"></li>
                <li><input type="date" size="8" maxlength="8" name="36_data_2"></li>
                <li><input type="date" size="8" maxlength="8" name="36_data_3"></li>
                <li><input type="date" size="8" maxlength="8" name="36_data_4"></li>
                <li><input type="date" size="8" maxlength="8" name="36_data_5"></li>
            </ol>
            <br>
            <label for="37_hora_inicial">37 - Hora Inicial</label>
            <ol>
                <li><input type="time" size="8" maxlength="8" name="37_hora_inicial_1"></li>
                <li><input type="time" size="8" maxlength="8" name="37_hora_inicial_2"></li>
                <li><input type="time" size="8" maxlength="8" name="37_hora_inicial_3"></li>
                <li><input type="time" size="8" maxlength="8" name="37_hora_inicial_4"></li>
                <li><input type="time" size="8" maxlength="8" name="37_hora_inicial_5"></li>
            </ol>
            <label for="38_hora_final">38 - Hora Final</label>
            <ol>
                <li><input type="time" size="8" maxlength="8" name="38_hora_final_1"></li>
                <li><input type="time" size="8" maxlength="8" name="38_hora_final_2"></li>
                <li><input type="time" size="8" maxlength="8" name="38_hora_final_3"></li>
                <li><input type="time" size="8" maxlength="8" name="38_hora_final_4"></li>
                <li><input type="time" size="8" maxlength="8" name="38_hora_final_5"></li>
            </ol>
            <label for="39_tabela">39 - Tabela</label>
            <ol>
                <li><input type="text" size="2" maxlength="2" name="39_tabela_1"></li>
                <li><input type="text" size="2" maxlength="2" name="39_tabela_2"></li>
                <li><input type="text" size="2" maxlength="2" name="39_tabela_3"></li>
                <li><input type="text" size="2" maxlength="2" name="39_tabela_4"></li>
                <li><input type="text" size="2" maxlength="2" name="39_tabela_5"></li>
            </ol>
            <label for="40_cd_proc">40 - Código do Procedimento</label>
            <ol>
                <li><input type="text" size="10" maxlength="10" name="40_cd_proc_1"></li>
                <li><input type="text" size="10" maxlength="10" name="40_cd_proc_2"></li>
                <li><input type="text" size="10" maxlength="10" name="40_cd_proc_3"></li>
                <li><input type="text" size="10" maxlength="10" name="40_cd_proc_4"></li>
                <li><input type="text" size="10" maxlength="10" name="40_cd_proc_5"></li>
            </ol>
            <label for="41_descricao">41 - Descrição</label>
            <ol>
                <li><input type="text" size="150" maxlength="150" name="41_descricao_1"></li>
                <li><input type="text" size="150" maxlength="150" name="41_descricao_2"></li>
                <li><input type="text" size="150" maxlength="150" name="41_descricao_3"></li>
                <li><input type="text" size="150" maxlength="150" name="41_descricao_4"></li>
                <li><input type="text" size="150" maxlength="150" name="41_descricao_5"></li>
            </ol>
            <label for="42_qtde">42 - Qtde</label>
            <ol>
                <li><input type="number" size="3" maxlength="3" name="42_qtde_1"></li>
                <li><input type="number" size="3" maxlength="3" name="42_qtde_2"></li>
                <li><input type="number" size="3" maxlength="3" name="42_qtde_3"></li>
                <li><input type="number" size="3" maxlength="3" name="42_qtde_4"></li>
                <li><input type="number" size="3" maxlength="3" name="42_qtde_5"></li>
            </ol>
            <label for="43_via">43 - Via</label>
            <ol>
                <li><input type="text" size="1" maxlength="1" name="43_via_1"></li>
                <li><input type="text" size="1" maxlength="1" name="43_via_2"></li>
                <li><input type="text" size="1" maxlength="1" name="43_via_3"></li>
                <li><input type="text" size="1" maxlength="1" name="43_via_4"></li>
                <li><input type="text" size="1" maxlength="1" name="43_via_5"></li>
            </ol>
            <label for="44_tec">44 - Téc</label>
            <ol>
                <li><input type="text" size="1" maxlength="1" name="44_tec_1"></li>
                <li><input type="text" size="1" maxlength="1" name="44_tec_2"></li>
                <li><input type="text" size="1" maxlength="1" name="44_tec_3"></li>
                <li><input type="text" size="1" maxlength="1" name="44_tec_4"></li>
                <li><input type="text" size="1" maxlength="1" name="44_tec_5"></li>
            </ol>
            <label for="45_fator_red_acresc">45 - Fator Red / Acrésc</label>
            <ol>
                <li><input type="number" size="4" min=0 max="9.99" step=0.01 name="45_fator_red_acresc"></li>
                <li><input type="number" size="4" min=0 max="9.99" step=0.01 name="45_fator_red_acresc"></li>
                <li><input type="number" size="4" min=0 max="9.99" step=0.01 name="45_fator_red_acresc"></li>
                <li><input type="number" size="4" min=0 max="9.99" step=0.01 name="45_fator_red_acresc"></li>
                <li><input type="number" size="4" min=0 max="9.99" step=0.01 name="45_fator_red_acresc"></li>
            </ol>
            <label for="46_valor_uni">46 - Valor Unitário</label>
            <ol>
                <li><input type="number" size="9" min=0 max="999999.99" step=0.01 name="46_valor_uni_1"></li>
                <li><input type="number" size="9" min=0 max="999999.99" step=0.01 name="46_valor_uni_2"></li>
                <li><input type="number" size="9" min=0 max="999999.99" step=0.01 name="46_valor_uni_3"></li>
                <li><input type="number" size="9" min=0 max="999999.99" step=0.01 name="46_valor_uni_4"></li>
                <li><input type="number" size="9" min=0 max="999999.99" step=0.01 name="46_valor_uni_5"></li>
            </ol>
            <label for="47_valor_total">47 - Valor Total</label>
            <ol>
                <li><input type="number" size="9" min=0 max="999999.99" step=0.01 name="47_valor_total_1"></li>
                <li><input type="number" size="9" min=0 max="999999.99" step=0.01 name="47_valor_total_2"></li>
                <li><input type="number" size="9" min=0 max="999999.99" step=0.01 name="47_valor_total_3"></li>
                <li><input type="number" size="9" min=0 max="999999.99" step=0.01 name="47_valor_total_4"></li>
                <li><input type="number" size="9" min=0 max="999999.99" step=0.01  name="47_valor_total_5"></li>
            </ol>
        </fieldset>
        <fieldset>
            <legend>Identificação do(s) Profissional(is) Executante(s)</legend>
            <label for="48_seq_ref">48 - Seq. Ref</label>
            <ul>
                <li><input type="text" size="2" maxlength="2" name="48_seq_ref_1"></li>
                <li><input type="text" size="2" maxlength="2" name="48_seq_ref_2"></li>
                <li><input type="text" size="2" maxlength="2" name="48_seq_ref_3"></li>
                <li><input type="text" size="2" maxlength="2" name="48_seq_ref_4"></li>
            </ul>
            <label for="1_registro_ans">49 - Grau Part</label>
            <ul>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
            </ul>
            <label for="1_registro_ans">50 - Código na Operadora / CPF</label>
            <ul>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
            </ul>
            <label for="1_registro_ans">51 - Nome do profissional</label>
            <ul>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
            </ul>
            <label for="1_registro_ans">52 - Conselho Profissional</label>
            <ul>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
            </ul>
            <label for="1_registro_ans">53 - Número no conselho</label>
            <ul>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
            </ul>
            <label for="1_registro_ans">54 - UF</label>
            <ul>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
            </ul>
            <label for="1_registro_ans">55 - Código CBO</label>
            <ul>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
                <li><input type="text" size="3" maxlength="" name="1_registro_ans"></li>
            </ul>
        </fieldset>
        <fieldset>
            <label for="1_registro_ans">56 - Data de realização de procedimentos em série</label>
            <label for="1_registro_ans">57 - Assinatura do beneficiário ou responsável na realização de procedimentos em
                série</label>
            <ol>
                <li><input type="text" size="8" maxlength="" name="1_registro_ans"><span> _______________</span></li>
                <li><input type="text" size="8" maxlength="" name="1_registro_ans"><span> _______________</span></li>
                <li><input type="text" size="8" maxlength="" name="1_registro_ans"><span> _______________</span></li>
                <li><input type="text" size="8" maxlength="" name="1_registro_ans"><span> _______________</span></li>
                <li><input type="text" size="8" maxlength="" name="1_registro_ans"><span> _______________</span></li>
                <li><input type="text" size="8" maxlength="" name="1_registro_ans"><span> _______________</span></li>
                <li><input type="text" size="8" maxlength="" name="1_registro_ans"><span> _______________</span></li>
                <li><input type="text" size="8" maxlength="" name="1_registro_ans"><span> _______________</span></li>
                <li><input type="text" size="8" maxlength="" name="1_registro_ans"><span> _______________</span></li>
                <li><input type="text" size="8" maxlength="" name="1_registro_ans"><span> _______________</span></li>
            </ol>
        </fieldset>
        <fieldset>
            <label for="1_registro_ans">58 - Observação/Justificativa</label><br>
            <textareamaxlength
            ="" 1_registro_ans" rows="10" cols="100" maxlength="500" form="guiaexame"></textarea>
        </fieldset>
        <fieldset>
            <label for="1_registro_ans">59 - Total de Procedimentos</label>
            <input type="text" size="6" maxlength="" name="1_registro_ans">
            <br>
            <label for="1_registro_ans">60 - Total de Taxas Diversas e Aluguéis</label>
            <input type="text" size="6" maxlength="" name="1_registro_ans">
            <br>
            <label for="1_registro_ans">61 - Total de Materiais</label>
            <input type="text" size="6" maxlength="" name="1_registro_ans">
            <br>
            <label for="1_registro_ans">62 - Total de OPME</label>
            <input type="text" size="6" maxlength="" name="1_registro_ans">
            <br>
            <label for="1_registro_ans">63 - Total de Medicamentos</label>
            <input type="text" size="6" maxlength="" name="1_registro_ans">
            <br>
            <label for="1_registro_ans">64 - Total Gases Medicinais</label>
            <input type="text" size="6" maxlength="" name="1_registro_ans">
            <br>
            <label for="1_registro_ans">65 - Total Geral</label>
            <input type="text" size="6" maxlength="" name="1_registro_ans">
        </fieldset>
        <fieldset>
            <p>
                <span id="1_registro_ans">___________________<br>66 - Assinatura do responsável pela Autorização<br></span>
                <span id="1_registro_ans">___________________<br>67 - Assinatura do Beneficiário ou Responsável<br></span>
                <span id="1_registro_ans">___________________<br>68 - Assinatura do contratado<br></span>
            </p>
        </fieldset>

    </form>
</div>