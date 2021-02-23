<?php

class Guia_ResumoInternacao
{
  private $xml;
  private $guias;
  private $db;
  private $filedate;
  private $valor_total = 0;
  private $seq_importacao;
  private $versao_3;

  function __construct ( &$xml, $versao_3 = false) {
    global $db, $file_date;

    $this->xml =& $xml;
    if ($versao_3) {
			$this->guias = &$xml->guiasTISS->guiaResumoInternacao;
		} else {
			$this->guias = &$xml->guias->guiaFaturamento->guiaResumoInternacao;
		}
    $this->db = &$db;
    $this->filedate = date( 'd-M-Y', $file_date );
    $this->versao_3 = $versao_3;
  }

  public function getValorTotal() {
	  return $this->valor_total;
  }

  function processa ( $seq_importacao  ) {
		$this->seq_importacao = $seq_importacao;

    foreach ( $this->guias as $guia ) {
      $this->saveGuiaInternacao($guia);

      if ($this->versao_3) {
				foreach ($guia->procedimentosExecutados->procedimentoExecutado as $procedimento) {
					$sequencial = $this->saveProcedimentosRealizados($procedimento, $guia);
					if ($procedimento->identEquipe) {
						foreach($procedimento->identEquipe as $membro) {
							$this->saveMembroEquipe($membro->identificacaoEquipe, $guia, $sequencial);
						}
					}
				}
			} else {
				if ( property_exists( $guia, 'procedimentosRealizados')) {
					foreach($guia->procedimentosRealizados->procedimentos as $procedimento) {
						$sequencial = $this->saveProcedimentosRealizados($procedimento, $guia);
						if ($procedimento->equipe) {
							foreach($procedimento->equipe->membroEquipe as $membro) {
								$this->saveMembroEquipe($membro, $guia, $sequencial);
							}
						}
					}
				}
			}

      if ( property_exists($guia, 'OPMUtilizada') ) {
        foreach($guia->OPMUtilizada->OPMUtilizada->OPM as $OPM) {
          $this->saveOPMUtilizada($OPM, $guia);
        }
      }

      if (property_exists($guia, 'outrasDespesas')) {
       foreach($guia->outrasDespesas->despesa as $outrasDespesas) {
         $this->saveOutrasDespesas($outrasDespesas, $guia);
       }
      }
    }
  }

  /**
   * saveGuiaInternacao
   *
   * @param mixed $guia
   * @return void
   */
  private function saveGuiaInternacao (&$guia) {
    $v = array();
    if ($this->versao_3) {
			$v['NR_GUIA']             = (string) $guia->cabecalhoGuia->numeroGuiaOperadora;
			$v['NR_GUIA_SOLICITACAO'] = (string) $guia->numeroGuiaSolicitacaoInternacao;
			$dt_emissao               = date('d-M-Y', strtotime((string) $guia->dadosAutorizacao->dataAutorizacao));
			$v['DT_EMISSAO']          = $dt_emissao;
			$v['NR_CARTEIRA']         = ArquivoXML::numeroCarteira($guia->dadosBeneficiario->numeroCarteira);
			$v['CD_TIPO_LOGRADOURO']  = null; // (string) $guia->identificacaoExecutante->enderecoContratado->tipoLogradouro;
			$v['NM_LOGRADOURO']       = null; // (string) $guia->identificacaoExecutante->enderecoContratado->logradouro;
			$v['DS_COMPLEMENTO']      = null; // (string) $guia->identificacaoExecutante->enderecoContratado->complemento;
			$v['NR_LOGRADOURO']       = null; // (string) $guia->identificacaoExecutante->enderecoContratado->numero;
			$v['DS_CIDADE']           = null; // (string) $guia->identificacaoExecutante->enderecoContratado->municipio;
			$v['CD_UF']               = null; // (string) $guia->identificacaoExecutante->enderecoContratado->codigoUF;
			$v['CD_IBGE']             = null; // (string) $guia->identificacaoExecutante->enderecoContratado->codigoIBGEMunicipio;
			$v['NR_CEP']              = null; // (string) $guia->identificacaoExecutante->enderecoContratado->cep;
			$v['TP_OCORRENCIA']       = ArquivoXML::getCaraterAtendimento((string) $guia->dadosInternacao->caraterAtendimento);
			$v['DT_INICIO_INTERNACAO'] = date('d-M-Y', strtotime((string) $guia->dadosInternacao->dataInicioFaturamento));
			$v['DT_FIM_INTERNACAO']    = date('d-M-Y', strtotime((string) $guia->dadosInternacao->dataFinalFaturamento));
			$v['CD_TIPO_INTERNACAO']   = (string) $guia->dadosInternacao->tipoFaturamento;
			$v['CD_REGIME_INTERNACAO'] = (string) $guia->dadosInternacao->regimeInternacao;
			$v['CD_PATOLOGIA_PRINCIPAL'] = (string) $guia->dadosSaidaInternacao->diagnostico;

			if ($guia->diagnosticosSecundarios) {
				$dia = 2;
				foreach($guia->diagnosticosSecundarios as $diagS) {
					$v['CD_PATOLOGIA'.$dia++] = null; // (string) $guia->diagnosticosSaidaInternacao->CID->codigoDiagnostico;
				}
			}

			$v['CD_INDICADOR_ACIDENTE']  = (int) $guia->dadosSaidaInternacao->indicadorAcidente;
			$v['CD_MOTIVO_SAIDA']        = (string) $guia->dadosSaidaInternacao->motivoEncerramento;
			$v['CD_PATOLOGIA_OBITO']     = null; // (string) $guia->diagnosticosSaidaInternacao->obito->CID->codigoDiagnostico;
			$v['NR_DECLARACAO_OBITO']    = null; // (string) $guia->diagnosticosSaidaInternacao->obito->numeroDeclaracao;
			$v['CPF_CGC_EXECUTANTE']     = ArquivoXML::getCPF_CGC($guia->dadosExecutante->contratadoExecutante);
			$v['NR_GUIA_PRESTADOR']      = (string) $guia->cabecalhoGuia->numeroGuiaPrestador;

			$v['VL_TOTAL_PROCEDIMENTO']  = (double) $guia->valorTotal->valorProcedimentos;
			$v['VL_TOTAL_DIARIA']        = (double) $guia->valorTotal->valorDiarias;
			$v['VL_TOTAL_TAXA']          = (double) $guia->valorTotal->valorTaxasAlugueis;
			$v['VL_TOTAL_MATERIAL']      = (double) $guia->valorTotal->valorMateriais;
			$v['VL_TOTAL_MEDICAMENTO']   = (double) $guia->valorTotal->valorMedicamentos;
			$v['VL_TOTAL_GASES']         = (double) $guia->valorTotal->valorGasesMedicinais;
			$v['VL_TOTAL_GERAL']         = (double) $guia->valorTotal->valorTotalGeral;
			$v['NR_SENHA']               = (string) $guia->dadosAutorizacao->senha;
			$v['CD_CNES']                = (string) $guia->dadosExecutante->CNES;
			$v['CD_DIARIA']              = null; // (string) $guia->acomodacao;
			$v['TP_FATURAMENTO']         = ArquivoXML::getTipoFaturamento((string) $guia->dadosInternacao->tipoFaturamento);
		} else {
			$v['NR_GUIA']             = (string) $guia->identificacaoGuiaInternacao->numeroGuiaOperadora;
			$v['NR_GUIA_SOLICITACAO'] = (string) $guia->numeroGuiaSolicitacao;
			$dt_emissao               = date('d-M-Y', strtotime((string) $guia->identificacaoGuiaInternacao->dataEmissaoGuia));
			$v['DT_EMISSAO']          = $dt_emissao;
			$v['NR_CARTEIRA']         = ArquivoXML::numeroCarteira($guia->dadosBeneficiario->numeroCarteira);
			$v['CD_TIPO_LOGRADOURO']  = (string) $guia->identificacaoExecutante->enderecoContratado->tipoLogradouro;
			$v['NM_LOGRADOURO']       = (string) $guia->identificacaoExecutante->enderecoContratado->logradouro;
			$v['DS_COMPLEMENTO']      = (string) $guia->identificacaoExecutante->enderecoContratado->complemento;
			$v['NR_LOGRADOURO']       = (string) $guia->identificacaoExecutante->enderecoContratado->numero;
			$v['DS_CIDADE']           = (string) $guia->identificacaoExecutante->enderecoContratado->municipio;
			$v['CD_UF']               = (string) $guia->identificacaoExecutante->enderecoContratado->codigoUF;
			$v['CD_IBGE']             = (string) $guia->identificacaoExecutante->enderecoContratado->codigoIBGEMunicipio;
			$v['NR_CEP']              = (string) $guia->identificacaoExecutante->enderecoContratado->cep;
			$v['TP_OCORRENCIA']       = (string) $guia->caraterInternacao;

			list($dt_int, $hr_int) = split('T', (string) $guia->dataHoraInternacao);
			$v['DT_INICIO_INTERNACAO'] = date('d-M-Y', strtotime($dt_int));
			if ((string) $guia->dataHoraSaidaInternacao) {
				list($dt_sint, $hr_sint) = split('T', (string) $guia->dataHoraSaidaInternacao);
				$v['DT_FIM_INTERNACAO'] = date('d-M-Y', strtotime($dt_sint));
			}
			$v['CD_TIPO_INTERNACAO']        = (string) $guia->tipoInternacao;
			$v['CD_REGIME_INTERNACAO']      = (string) $guia->regimeInternacao;
			$v['CD_PATOLOGIA_PRINCIPAL']    = (string) $guia->diagnosticosSaidaInternacao->diagnosticoPrincipal->codigoDiagnostico;

			if ($guia->diagnosticosSecundarios) {
				$dia = 2;
				foreach($guia->diagnosticosSecundarios as $diagS) {
					$v['CD_PATOLOGIA'.$dia++] = (string) $guia->diagnosticosSaidaInternacao->CID->codigoDiagnostico;
				}
			}

			if ($guia->diagnosticosSaidaInternacao) {
				$v['CD_INDICADOR_ACIDENTE']  = (int) $guia->diagnosticosSaidaInternacao->indicadorAcidente;
				$v['CD_MOTIVO_SAIDA']        = (string) $guia->diagnosticosSaidaInternacao->motivoSaidaInternacao;
				if ($guia->diagnosticosSaidaInternacao->obito) {
					if ($guia->diagnosticosSaidaInternacao->obito->CID) {
						$v['CD_PATOLOGIA_OBITO']     = (string) $guia->diagnosticosSaidaInternacao->obito->CID->codigoDiagnostico;
					}
					$v['NR_DECLARACAO_OBITO']    = (string) $guia->diagnosticosSaidaInternacao->obito->numeroDeclaracao;
				}
			}

			$v['CPF_CGC_EXECUTANTE']     = ArquivoXML::getCPF_CGC($guia->identificacaoExecutante->identificacao);
			$v['NR_GUIA_PRESTADOR']      = (string) $guia->identificacaoGuiaInternacao->numeroGuiaPrestador;
			$v['VL_TOTAL_PROCEDIMENTO']  = (double) $guia->valorTotal->servicosExecutados;
			$v['VL_TOTAL_DIARIA']        = (double) $guia->valorTotal->diarias;
			$v['VL_TOTAL_TAXA']          = (double) $guia->valorTotal->taxas;
			$v['VL_TOTAL_MATERIAL']      = (double) $guia->valorTotal->materiais;
			$v['VL_TOTAL_MEDICAMENTO']   = (double) $guia->valorTotal->medicamentos;
			$v['VL_TOTAL_GASES']         = (double) $guia->valorTotal->gases;
			$v['VL_TOTAL_GERAL']         = (double) $guia->valorTotal->totalGeral;
			$v['NR_SENHA']               = (string) $guia->dadosAutorizacao->senhaAutorizacao;
			$v['CD_CNES']                = (string) $guia->identificacaoExecutante->numeroCNES;
			$v['CD_DIARIA']              = (string) $guia->acomodacao;
			$v['TP_FATURAMENTO']         = (string) $guia->tipoFaturamento;
		}

    $v['SN_EM_GESTACAO']            = ArquivoXML::simNao($guia->internacaoObstetrica->emGestacao);
    $v['SN_ABORTO']                 = ArquivoXML::simNao($guia->internacaoObstetrica->aborto);
    $v['SN_TRANSTORNO']             = ArquivoXML::simNao($guia->internacaoObstetrica->transtornoMaternoRelGravidez);
    $v['SN_PUERPERIO']              = ArquivoXML::simNao($guia->internacaoObstetrica->complicacaoPeriodoPuerperio);
    $v['SN_ATEND_RN']               = ArquivoXML::simNao($guia->internacaoObstetrica->atendimentoRNSalaParto);
    $v['SN_NEONATAL']               = ArquivoXML::simNao($guia->internacaoObstetrica->complicacaoNeonatal);
    $v['SN_BAIXO_PESO']             = ArquivoXML::simNao($guia->internacaoObstetrica->baixoPeso);
    $v['SN_PARTO_CESAREO']          = ArquivoXML::simNao($guia->internacaoObstetrica->partoCesareo);
    $v['SN_PARTO_NORMAL']           = ArquivoXML::simNao($guia->internacaoObstetrica->partoNormal);
    $v['CD_OBITO_MULHER']           = (integer) $guia->internacaoObstetrica->obitoMulher;
    $v['QT_OBITO_NEONATAL_PRECOCE'] = (int) $guia->obitoNeonatal->qtdeobitoPrecoce;
    $v['QT_OBITO_NEONATAL_TARDIO']  = (int) $guia->obitoNeonatal->qtdeobitoTardio;
    // O XML permite mais de um numeroDN, e ai?
    $v['NR_DECLARACAO_NASCIMENTO']  = (string) $guia->internacaoObstetrica->declaracoesNascidosVivos->numeroDN;
    $v['QT_NASCIDO_VIVO']           = (int) $guia->internacaoObstetrica->qtdNascidosVivosTermo;
    $v['QT_NASCIDO_MORTO']          = (int) $guia->internacaoObstetrica->qtdNascidosMortos;
    $v['QT_NASCIDO_PREMATURO']      = (int) $guia->internacaoObstetrica->qtdVivosPrematuros;

    $v['DT_RECEBIMENTO']         = (string) $this->filedate;
    $v['NR_PROCESSO_REFERENCIA'] = (string) $this->xml->numeroLote;

    $v['NR_CNS']                 = (string) $guia->dadosBeneficiario->numeroCNS;
    $v['NR_SEQ_IMPORTACAO']      = $this->seq_importacao;

    $this->valor_total += $v['VL_TOTAL_GERAL'];

    $f = array_keys($v);

    $sql = "INSERT INTO TISS_GUIA_INTERNACAO (". join(',', $f) .") VALUES (:".join(', :', $f).")";
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
    $stmt = oci_parse($this->db, "SELECT TISS_S_ESP_INTERNACAO.NEXTVAL FROM DUAL");

    ArquivoXML::db_query($stmt);
    $row = oci_fetch_object($stmt);
    $sequencial = $row->NEXTVAL;
    oci_free_statement($stmt);

    $v                          = array();
    if ($this->versao_3) {
			$v['NR_GUIA']               = (string) $guia->cabecalhoGuia->numeroGuiaOperadora;
			$v['DT_EVENTO']             = date('d-M-Y', strtotime((string) $procedimento->dataExecucao));
			$v['HORA_INICIO']           = substr((string) $procedimento->horaInicial,0,5);
			$v['HORA_FIM']              = substr((string) $procedimento->horaFinal,0,5);
			$v['CD_TIPO_TABELA']        = (string) $procedimento->procedimento->codigoTabela;
			$v['CD_ESPECIALIDADE']      = (string) $procedimento->procedimento->codigoProcedimento;
			$v['QT_EVENTO']             = (string) $procedimento->quantidadeExecutada;
			$v['CD_VIA']                = ArquivoXML::getViaAcesso((string) $procedimento->viaAcesso);
			$v['CD_TECNICA']            = ArquivoXML::getTecnicaUtilizada((string) $procedimento->tecnicaUtilizada);
			$v['PCT_REDUCAO_ACRESCIMO'] = (double) $procedimento->fatorReducaoAcrescimo;
			$v['VL_EVENTO']             = (double) $procedimento->valorUnitario;
			$v['VL_TOTAL']              = (double) $procedimento->valorTotal;
			$v['NR_GUIA_PRESTADOR']     = (string) $guia->cabecalhoGuia->numeroGuiaPrestador;
			$v['CPF_CGC_EXECUTANTE']    = ArquivoXML::getCPF_CGC($guia->dadosExecutante->contratadoExecutante);
		} else {
			$v['NR_GUIA']               = (string) $guia->identificacaoGuiaInternacao->numeroGuiaOperadora;
			$v['DT_EVENTO']             = date('d-M-Y', strtotime((string) $procedimento->data));
			$v['HORA_INICIO']           = substr((string) $procedimento->horaInicio,0,5);
			$v['HORA_FIM']              = substr((string) $procedimento->horaFim,0,5);
			$v['CD_TIPO_TABELA']        = (string) $procedimento->procedimento->tipoTabela;
			$v['CD_ESPECIALIDADE']      = (string) $procedimento->procedimento->codigo;
			$v['QT_EVENTO']             = (string) $procedimento->quantidadeRealizada;
			$v['CD_VIA']                = (string) $procedimento->viaAcesso;
			$v['CD_TECNICA']            = (string) $procedimento->tecnicaUtilizada;
			$v['PCT_REDUCAO_ACRESCIMO'] = (double) $procedimento->reducaoAcrescimo;
			$v['VL_EVENTO']             = (double) $procedimento->valor;
			$v['VL_TOTAL']              = (double) $procedimento->valorTotal;
			$v['NR_GUIA_PRESTADOR']     = (string) $guia->identificacaoGuiaInternacao->numeroGuiaPrestador;
			$v['CPF_CGC_EXECUTANTE']    = ArquivoXML::getCPF_CGC($guia->identificacaoExecutante->identificacao);
		}

    $v['NR_SEQUENCIAL']         = (string) $sequencial;
    $v['AEIT_CPF_CGC_EXECUTANTE'] = $v['CPF_CGC_EXECUTANTE'];
    $v['NR_SEQ_IMPORTACAO']     = $this->seq_importacao;

    $f = array_keys($v);

    $sql = "INSERT INTO TISS_ESP_INTERNACAO (". join(',', $f) .") VALUES (:".join(', :', $f).")";

    //var_dump($f);

    $stmt = oci_parse($this->db, $sql);

    foreach ($f as $k) {
      oci_bind_by_name($stmt, ':'.$k, $v[$k]);
    }

    ArquivoXML::db_query($stmt);
    oci_free_statement($stmt);
    return $sequencial;
  }

  /**
   * saveMembroEquipe
   *
   * @param mixed $membro
   * @param mixed $guia
   * @param mixed $sequencial
   * @return void
   */
  private function saveMembroEquipe (&$membro, &$guia, $sequencial) {
    $stmt = oci_parse($this->db, "SELECT TISS_S_ESP_INTERNACAO_FUNCAO.NEXTVAL FROM DUAL");
    ArquivoXML::db_query($stmt);
    $row = oci_fetch_object($stmt);
    $seq_func = $row->NEXTVAL;
    oci_free_statement($stmt);

    $v = array();
    if ($this->versao_3) {
			$v['CD_FUNCAO']            = (string) $membro->grauPart;
			$v['CPF_CGC']              = ArquivoXML::getCPF_CGC($membro->codProfissional, true);
			$v['NM_PROFISSIONAL']      = (string) $membro->nomeProf;
			$v['CD_CONSELHO']          = ArquivoXML::getConselho((string) $membro->conselho);
			$v['NR_CRM']               = (string) $membro->numeroConselhoProfissional;
			$v['CD_UF_CRM']            = ArquivoXML::getUF((string) $membro->UF);
			$v['NR_CPF']               = $v['CPF_CGC'];
		} else {
			$v['CD_FUNCAO']            = (string) $membro->posicaoProfissional;
			$v['CPF_CGC']              = ArquivoXML::getCPF_CGC($membro->codigoProfissional, true);
			$v['NM_PROFISSIONAL']      = (string) $membro->identificacaoProfissional->nomeExecutante;
			$v['CD_CONSELHO']          = (string) $membro->identificacaoProfissional->conselhoProfissional->siglaConselho;
			$v['NR_CRM']               = (string) $membro->identificacaoProfissional->conselhoProfissional->numeroConselho;
			$v['CD_UF_CRM']            = (string) $membro->identificacaoProfissional->conselhoProfissional->ufConselho;
			$v['NR_CPF']               = (string) $membro->cpf;
		}
    $v['NR_SEQUENCIAL_ESP']    = $sequencial;
    $v['NR_SEQUENCIAL_FUNCAO'] = $seq_func;
    $v['NR_SEQ_IMPORTACAO']    = $this->seq_importacao;

    $f = array_keys($v);

    $sql = "INSERT INTO TISS_ESP_INTERNACAO_FUNCAO (". join(',', $f) .") VALUES (:".join(', :', $f).")";
    $stmt = oci_parse($this->db, $sql);

    foreach ($f as $k) {
      oci_bind_by_name($stmt, ':'.$k, $v[$k]);
    }

    ArquivoXML::db_query($stmt);
    oci_free_statement($stmt);
  }


  /**
   * saveOPMUtilizada
   *
   * @param mixed $opm
   * @param mixed $guia
   * @return void
   */
  private function saveOPMUtilizada (&$opm, &$guia) {
    $v = array();
    $v['NR_GUIA']            = (string) $guia->identificacaoGuiaInternacao->numeroGuiaOperadora;
    $v['NR_GUIA_PRESTADOR']  = (string) $guia->identificacaoGuiaInternacao->numeroGuiaPrestador;
    $v['CD_TIPO_TABELA']     = (string) $opm->OPM->tipoTabela;
    $v['CD_OPM']             = (string) $opm->OPM->codigo;
    $v['QT_OPM']             = (int) $opm->quantidade;
    $v['CD_BARRAS']          = (string) $opm->codigoBarra;
    $v['VL_OPM_UNITARIO']    = (double) $opm->valorUnitario;
    $v['VL_OPM_TOTAL']       = (double) $opm->valorTotal;
    $v['CPF_CGC_EXECUTANTE'] = ArquivoXML::getCPF_CGC($guia->identificacaoExecutante->identificacao);
    $v['NR_SEQ_IMPORTACAO']  = $this->seq_importacao;

    $f = array_keys($v);

    $sql = "INSERT INTO TISS_OPM_INTERNACAO (". join(',', $f) .") VALUES (:".join(', :', $f).")";
    $stmt = oci_parse($this->db, $sql);

    foreach ($f as $k) {
      oci_bind_by_name($stmt, ':'.$k, $v[$k]);
    }

    ArquivoXML::db_query($stmt);
    oci_free_statement($stmt);
  }

  /**
   * saveOutrasDespesas
   *
   * @param mixed $outras
   * @param mixed $guia
   * @return void
   */
  private function saveOutrasDespesas (&$outras, &$guia) {
    $v = array();

    if ($this->versao_3) {
			$v['NR_GUIA_REFERENCIA']    = (string) $guia->dadosAutorizacao->numeroGuiaOperadora;
			$cpf                        = ArquivoXML::getCPF_CGC($guia->dadosExecutante->contratadoExecutante);
			$v['CPF_CGC_EXECUTANTE']    = $cpf;
			$v['NR_GUIA_PRESTADOR']     = (string) $guia->cabecalhoGuia->numeroGuiaPrestador;
			$v['CD_DESPESA']            = (string) $outras->codigoDespesa;
			$v['CD_CNES']               = (string) $guia->dadosExecutante->CNES;
			$v['NR_SEQ_IMPORTACAO']     = $this->seq_importacao;

			foreach ($outras->servicosExecutados as $servico) {
				$v['CD_ITEM']               = (string) $servico->codigoProcedimento;
				$v['DT_DESPESA']            = date('d-M-Y', strtotime((string) $servico->dataExecucao));
				$v['HORA_INICIAL']          = substr((string) $servico->horaInicial, 0,5);
				$v['HORA_FINAL']            = substr((string) $servico->horaFinal, 0,5);
				$v['QTD_DESPESA']           = (int) $servico->quantidadeExecutada;
				$v['CD_TIPO_TABELA']        = (string) $servico->codigoTabela;
				$v['PCT_REDUCAO_ACRESCIMO'] = (double) $servico->reducaoAcrescimo;
				$v['VL_UNITARIO']           = (double) $servico->valorUnitario;
				$v['VL_TOTAL']              = (double) $servico->valorTotal;

				$f = array_keys($v);
				$sql = "INSERT INTO TISS_OUTRAS_DESPESAS (". join(',', $f) .") VALUES (:".join(', :', $f).")";
				$stmt = oci_parse($this->db, $sql);

				foreach ($f as $k) {
					oci_bind_by_name($stmt, ':'.$k, $v[$k]);
				}

				ArquivoXML::db_query($stmt);
				oci_free_statement($stmt);
			}
		} else {
			$v['NR_GUIA_REFERENCIA']    = (string) $guia->identificacaoGuiaInternacao->numeroGuiaOperadora;
			$cpf                        = ArquivoXML::getCPF_CGC($guia->identificacaoExecutante->identificacao);
			$v['CPF_CGC_EXECUTANTE']    = $cpf;
			$v['NR_GUIA_PRESTADOR']     = (string) $guia->identificacaoGuiaInternacao->numeroGuiaPrestador;
			$v['CD_DESPESA']            = (string) $outras->tipoDespesa;
			$v['CD_ITEM']               = (string) $outras->identificadorDespesa->codigo;
			$v['CD_CNES']               = (string) $guia->identificacaoExecutante->numeroCNES;

			$data                       = date('d-M-Y', strtotime((string) $outras->dataRealizacao));
			$v['DT_DESPESA']            = $data;
			$v['HORA_INICIAL']          = substr((string) $outras->horaInicial, 0,5);
			$v['HORA_FINAL']            = substr((string) $outras->horaFinal, 0,5);
			$v['CD_TIPO_TABELA']        = (string) $outras->identificadorDespesa->tipoTabela;
			$v['QTD_DESPESA']           = (int) $outras->quantidade;
			$v['PCT_REDUCAO_ACRESCIMO'] = (double) $outras->reducaoAcrescimo;
			$v['VL_UNITARIO']           = (double) $outras->valorUnitario;
			$v['VL_TOTAL']              = (double) $outras->valorTotal;
			$v['NR_SEQ_IMPORTACAO']     = $this->seq_importacao;

			$f = array_keys($v);

			$sql = "INSERT INTO TISS_OUTRAS_DESPESAS (". join(',', $f) .") VALUES (:".join(', :', $f).")";
			$stmt = oci_parse($this->db, $sql);

			foreach ($f as $k) {
				oci_bind_by_name($stmt, ':'.$k, $v[$k]);
			}

			ArquivoXML::db_query($stmt);
			oci_free_statement($stmt);
		}
  }

}

?>
