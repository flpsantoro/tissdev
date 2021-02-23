<?php

class Guia_HonorarioIndividual
{
  private $xml;
  private $guias;
  private $db;
  private $filedate;
  private $seq_importacao;
  private $valor_total = 0;
  private $versao_3;

  function __construct ( &$xml, $versao_3 = false ) {
    global $db, $file_date;

    $this->xml =& $xml;
    if ($versao_3) {
			$this->guias = &$xml->guiasTISS->guiaHonorarios;
		} else {
			$this->guias = &$xml->guias->guiaFaturamento->guiaHonorarioIndividual;
		}
    $this->db = &$db;
    $this->filedate = date( 'd-M-Y', $file_date );
    $this->versao_3 = $versao_3;
  }

  public function getValorTotal() {
	  return $this->valor_total;
  }

  function processa ( $seq_importacao ) {
		$this->seq_importacao = $seq_importacao;

    foreach ( $this->guias as $guia ) {
      $this->saveGuiaHonorario($guia);

      if ($this->versao_3) {
				foreach($guia->procedimentosRealizados->procedimentoRealizado as $procedimento) {
					$this->saveProcedimentosRealizados($procedimento, $guia);
				}
			} else {
				foreach($guia->procedimentosExamesRealizados->procedimentoRealizado as $procedimento) {
					$this->saveProcedimentosRealizados($procedimento, $guia);
				}
			}
    }
  }

  /**
   * saveGuiaHonorario
   *
   * @param mixed $guia
   * @return void
   */
  private function saveGuiaHonorario (&$guia) {
    $v = array();
    if ($this->versao_3) {
			$v['NR_GUIA']                = (string) $guia->identificacaoGuiaHonorarioIndividual->numeroGuiaOperadora;
			$v['NR_GUIA_SOLICITACAO']    = (string) $guia->numeroGuiaPrincipal;

			$dt_emissao = date('d-M-Y', strtotime((string) $guia->identificacaoGuiaHonorarioIndividual->dataEmissaoGuia));
			$v['DT_EMISSAO']             = $dt_emissao;
			$v['NR_CARTEIRA']            = ArquivoXML::numeroCarteira($guia->beneficiario->numeroCarteira);
			$v['CPF_CGC_EXECUTADO']      = ArquivoXML::getCPF_CGC($guia->localContratado->codigoContratado);
			$v['NM_PRESTADOR_EXECUTADO'] = (string) $guia->localContratado->nomeContratado;
			$v['CD_FUNCAO_TISS']         = null; // (string) $guia->contratadoExecutante->posicaoProfissional;

			$v['NM_MEDICO']              = null; // (string) $guia->contratadoExecutante->identificacaoProfissional->nomeExecutante;
			$v['CD_CONSELHO']            = null; // (string) $guia->contratadoExecutante->identificacaoProfissional->conselhoProfissional->siglaConselho;
			$v['NR_CRM']                 = null; // (string) $guia->contratadoExecutante->identificacaoProfissional->conselhoProfissional->numeroConselho;
			$v['CD_UF_CRM']              = null; // (string) $guia->contratadoExecutante->identificacaoProfissional->conselhoProfissional->ufConselho;

			$v['NR_GUIA_PRESTADOR']      = (string) $guia->cabecalhoGuia->numeroGuiaPrestador;
			$v['CPF_CGC_EXECUTANTE']     = ArquivoXML::getCPF_CGC($guia->dadosContratadoExecutante);
			$v['CD_CNES_EXECUTADO']      = (string) $guia->localContratado->cnes;
			$v['CD_DIARIA']              = (string) $guia->contratadoExecutante->tipoAcomodacao;
			$v['NR_CPF']                 = ArquivoXML::getCPF_CGC($guia->dadosContratadoExecutante);
			$v['DS_OBS']                 = (string) $guia->observacao;
			$v['VL_TOTAL']               = (string) $guia->valorTotalHonorarios;
			$v['NR_CNS']                 = (string) $guia->dadosBeneficiario->numeroCNS;
			$v['CD_CNES_EXECUTANTE']     = (string) $guia->dadosContratadoExecutante->cnesContratadoExecutante;
		} else {
			$v['NR_GUIA']                = (string) $guia->identificacaoGuiaHonorarioIndividual->numeroGuiaOperadora;
			$v['NR_GUIA_SOLICITACAO']    = (string) $guia->numeroGuiaPrincipal;

			$dt_emissao = date('d-M-Y', strtotime((string) $guia->identificacaoGuiaHonorarioIndividual->dataEmissaoGuia));
			$v['DT_EMISSAO']             = $dt_emissao;
			$v['NR_CARTEIRA']            = ArquivoXML::numeroCarteira($guia->dadosBeneficiario->numeroCarteira);
			$v['CPF_CGC_EXECUTADO']      = ArquivoXML::getCPF_CGC($guia->contratado->identificacao);
			$v['NM_PRESTADOR_EXECUTADO'] = (string) $guia->contratado->nomeContratado;
			$v['CD_FUNCAO_TISS']         = (string) $guia->contratadoExecutante->posicaoProfissional;

			$v['NM_MEDICO']              = (string) $guia->contratadoExecutante->identificacaoProfissional->nomeExecutante;
			$v['CD_CONSELHO']            = (string) $guia->contratadoExecutante->identificacaoProfissional->conselhoProfissional->siglaConselho;
			$v['NR_CRM']                 = (string) $guia->contratadoExecutante->identificacaoProfissional->conselhoProfissional->numeroConselho;
			$v['CD_UF_CRM']              = (string) $guia->contratadoExecutante->identificacaoProfissional->conselhoProfissional->ufConselho;

			$v['NR_GUIA_PRESTADOR']      = (string) $guia->identificacaoGuiaHonorarioIndividual->numeroGuiaPrestador;
			$v['CPF_CGC_EXECUTANTE']     = ArquivoXML::getCPF_CGC($guia->contratadoExecutante->identificacao);
			$v['CD_CNES_EXECUTADO']      = (string) $guia->contratado->numeroCNES;
			$v['CD_DIARIA']              = (string) $guia->contratadoExecutante->tipoAcomodacao;
			$v['NR_CPF']                 = ArquivoXML::getCPF_CGC($guia->contratadoExecutante->identificacao);
			$v['DS_OBS']                 = (string) $guia->observacao;
			$v['VL_TOTAL']               = (string) $guia->procedimentosExamesRealizados->totalGeralHonorario;
			$v['NR_CNS']                 = (string) $guia->dadosBeneficiario->numeroCNS;
			$v['CD_CNES_EXECUTANTE']     = (string) $guia->contratadoExecutante->numeroCNES;
		}

    $v['DT_RECEBIMENTO']         = (string) $this->filedate;
    $v['NR_PROCESSO_REFERENCIA'] = (string) $this->xml->numeroLote;
    $v['NR_SEQ_IMPORTACAO']      = $this->seq_importacao;

    $this->valor_total += $v['VL_TOTAL'];

    $f = array_keys($v);

    $sql = "INSERT INTO TISS_GUIA_HONORARIO (". join(',', $f) .") VALUES (:".join(', :', $f).")";
    $stmt = oci_parse($this->db, $sql);

    $i = 0;
    foreach ($f as $k) {
      oci_bind_by_name($stmt, ':'.$k, $v[$k]);
    }

    try {
      ArquivoXML::db_query($stmt);
    }
    catch (ErrorDB $e) {
      if ($e->getCode() == 1) {
        $obs = "CPF_CGC: {$v['CPF_CGC_EXECUTANTE']}, NR_GUIA: {$v['NR_GUIA']}, NR_GUIA_PRESTADOR: {$v['NR_GUIA_PRESTADOR']}";
        throw new ErrorGuiaJaApresentada($obs);
      }
      else throw $e;
    }
    oci_free_statement($stmt);
  }

  /**
   * saveProcedimentosRealizados
   *
   * @param mixed $procedimento
   * @param mixed $guia
   * @return void
   */
  private function saveProcedimentosRealizados(&$procedimento, &$guia) {
		$stmt = oci_parse($this->db, "SELECT TISS_S_ESP_HONORARIO.NEXTVAL FROM DUAL");
		ArquivoXML::db_query($stmt);
		$row = oci_fetch_object($stmt);
		$seq_esp = $row->NEXTVAL;
		oci_free_statement($stmt);

    $v = array();
    if ($this->versao_3) {
			$v['CD_TIPO_TABELA']        = (string) $procedimento->procedimento->codigoTabela;
			$v['CD_ESPECIALIDADE']      = (string) $procedimento->procedimento->codigoProcedimento;
			$v['NR_GUIA_PRESTADOR']     = (string) $guia->cabecalhoGuia->numeroGuiaPrestador;
			$v['CPF_CGC_EXECUTANTE']    = ArquivoXML::getCPF_CGC($guia->dadosContratadoExecutante);
			$v['NR_GUIA']               = (string) $guia->numeroGuiaOperadora;
			$data                       = date('d-M-Y', strtotime((string) $procedimento->dataExecucao));
			$v['DT_EVENTO']             = $data;
			$v['HORA_INICIO']           = substr((string) $procedimento->horaInicial,0,5);
			$v['HORA_FIM']              = substr((string) $procedimento->horaFinal,0,5);
			$v['QT_PROCEDIMENTO']       = (string) $procedimento->quantidadeExecutada;
			$v['ID_VIA_ACESSO']         = ArquivoXML::getViaAcesso((string) $procedimento->viaAcesso);
			$v['CD_TECNICA']            = ArquivoXML::getTecnicaUtilizada((string) $procedimento->tecnicaUtilizada);
			$v['PCT_REDUCAO_ACRESCIMO'] = (double) $procedimento->reducaoAcrescimo;
			$v['VL_PROCEDIMENTO']       = (double) $procedimento->valorUnitario;
			$v['VL_TOTAL']              = (double) $procedimento->valorTotal;
		} else {
			$v['CD_TIPO_TABELA']        = (string) $procedimento->procedimento->tipoTabela;
			$v['CD_ESPECIALIDADE']      = (string) $procedimento->procedimento->codigo;
			$v['NR_GUIA_PRESTADOR']     = (string) $guia->identificacaoGuiaHonorarioIndividual->numeroGuiaPrestador;
			$v['CPF_CGC_EXECUTANTE']    = ArquivoXML::getCPF_CGC($guia->contratadoExecutante->identificacao);
			$v['NR_GUIA']               = (string) $guia->identificacaoGuiaHonorarioIndividual->numeroGuiaOperadora;
			$data                       = date('d-M-Y', strtotime((string) $procedimento->data));
			$v['DT_EVENTO']             = $data;
			$v['HORA_INICIO']           = substr((string) $procedimento->horaInicio,0,5);
			$v['HORA_FIM']              = substr((string) $procedimento->horaFim,0,5);
			$v['QT_PROCEDIMENTO']       = (string) $procedimento->quantidadeRealizada;
			$v['ID_VIA_ACESSO']         = (string) $procedimento->viaAcesso;
			$v['CD_TECNICA']            = (string) $procedimento->tecnicaUtilizada;
			$v['PCT_REDUCAO_ACRESCIMO'] = (double) $procedimento->reducaoAcrescimo;
			$v['VL_PROCEDIMENTO']       = (double) $procedimento->valor;
			$v['VL_TOTAL']              = (double) $procedimento->valorTotal;
		}
    $v['NR_SEQ_IMPORTACAO']     = $this->seq_importacao;
    $v['NR_SEQUENCIAL']         = $seq_esp;

    $f = array_keys($v);

    $sql = "INSERT INTO TISS_ESP_HONORARIO (". join(',', $f) .") VALUES (:".join(', :', $f).")";
    $stmt = oci_parse($this->db, $sql);

    foreach ($f as $k) {
      oci_bind_by_name($stmt, ':'.$k, $v[$k]);
    }

    ArquivoXML::db_query($stmt);
    oci_free_statement($stmt);

		if ($this->versao_3) {
			foreach ($procedimento->profissionais as $profissional) {
				$stmt = oci_parse($this->db, "SELECT TISS_S_ESP_HONORARIO_FUNCAO.NEXTVAL FROM DUAL");
				ArquivoXML::db_query($stmt);
				$row = oci_fetch_object($stmt);
				$seq_func = $row->NEXTVAL;
				oci_free_statement($stmt);

				$v = array();
				$v['NR_SEQUENCIAL_ESP']    = $seq_esp;
				$v['NR_SEQUENCIAL_FUNCAO'] = $seq_func;
				$v['CD_FUNCAO']            = (string) $profissional->grauParticipacao;
				$v['CPF_CGC']              = ArquivoXML::getCPF_CGC($profissional->codProfissional);
				$v['NM_PROFISSIONAL']      = (string) $profissional->nomeProfissional;
				$v['CD_CONSELHO']          = ArquivoXML::getConselho((string) $profissional->conselhoProfissional);
				$v['NR_CRM']               = (string) $profissional->numeroConselhoProfissional;
				$v['CD_UF_CRM']            = (string) ArquivoXML::getUF((string) $profissional->UF);
				$v['NR_CPF']               = ArquivoXML::getCPF_CGC($profissional->codProfissional);
				$v['CD_CBO']               = (string) $profissional->CBOS;
				$v['NR_SEQ_IMPORTACAO']    = $this->seq_importacao;

				$f = array_keys($v);

				$sql = "INSERT INTO TISS_ESP_HONORARIO_FUNCAO (". join(',', $f) .") VALUES (:".join(', :', $f).")";
				$stmt = oci_parse($this->db, $sql);

				foreach ($f as $k) {
					oci_bind_by_name($stmt, ':'.$k, $v[$k]);
				}

				ArquivoXML::db_query($stmt);
				oci_free_statement($stmt);
			}
		}
  }
}

?>
