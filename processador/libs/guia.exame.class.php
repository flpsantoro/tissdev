<?php

class Guia_Exame
{
  private $xml;
  private $guias;
  private $db;
  private $filedate;
  private $valor_total = 0;
  private $seq_importacao;
  private $versao_3;

  public function __construct ( &$xml, $versao_3 = false) {
    global $db, $file_date;

    $this->xml   = &$xml;
    if ($versao_3) {
			$this->guias = &$xml->guiasTISS->{'guiaSP-SADT'};
		} else {
			$this->guias = &$xml->guias->guiaFaturamento->guiaSP_SADT;
		}
    $this->db    = &$db;
    $this->filedate = date('d-M-Y', $file_date);
    $this->versao_3 = $versao_3;
  }

  public function getValorTotal() {
	  return $this->valor_total;
  }

  public function processa ($seq_importacao) {
		$this->seq_importacao = $seq_importacao;

    foreach ( $this->guias as $guia ) {
			$guia_ident    = array();
			$vl_total_guia = 0;
			$vl_total      = 0;

      $this->saveGuiaExame($guia, $vl_total_guia, $guia_ident);

			if ($this->versao_3) {
				if (property_exists( $guia, 'procedimentosExecutados')) {
					foreach($guia->procedimentosExecutados->procedimentoExecutado as $procedimento) {
						$this->saveProcedimentosRealizados($procedimento, $guia, $vl_total);
					}
				}
			} else {
				if ( property_exists( $guia, 'procedimentosRealizados') ) {
					foreach($guia->procedimentosRealizados->procedimentos as $procedimento) {
						$this->saveProcedimentosRealizados($procedimento, $guia, $vl_total);
					}
				}

				if ( property_exists($guia, 'OPMUtilizada') ) {
					foreach($guia->OPMUtilizada->OPM as $OPM) {
						$this->saveOPMUtilizada($OPM, $guia, $vl_total);
					}
				}
      }

      if (property_exists($guia, 'outrasDespesas')) {
       foreach($guia->outrasDespesas->despesa as $outrasDespesas) {
         $this->saveOutrasDespesas($outrasDespesas, $guia, $vl_total);
       }
      }
			/* Caso não tenha a tag <valorTotal> na guia, atualiza o valor pelo que foi processado dos itens */
      if (!$vl_total_guia) {
				$this->valor_total += $vl_total;

				$v = array();
				$v['VL_TOTAL_GUIA']     = $vl_total;
				$v['NR_SEQ_IMPORTACAO'] = $this->seq_importacao;

				$sql = "UPDATE TISS_GUIA_EXAME SET VL_TOTAL_GUIA = :VL_TOTAL_GUIA WHERE NR_SEQ_IMPORTACAO = :NR_SEQ_IMPORTACAO";
				if (!empty($guia_ident['NR_GUIA'])) {
					$sql .= ' AND NR_GUIA = :NR_GUIA';
					$v['NR_GUIA'] = $guia_ident['NR_GUIA'];
				}
				if (!empty($guia_ident['NR_GUIA_PRESTADOR'])) {
					$sql .= ' AND NR_GUIA_PRESTADOR = :NR_GUIA_PRESTADOR';
					$v['NR_GUIA_PRESTADOR'] = $guia_ident['NR_GUIA_PRESTADOR'];
				}
				if (!empty($guia_ident['NR_GUIA_PRINCIPAL'])) {
					$sql .= ' AND NR_GUIA_PRINCIPAL = :NR_GUIA_PRINCIPAL';
					$v['NR_GUIA_PRINCIPAL'] = $guia_ident['NR_GUIA_PRINCIPAL'];
				}

				$stmt = oci_parse($this->db, $sql);

				foreach (array_keys($v) as $k) {
					oci_bind_by_name($stmt, ':'.$k, $v[$k]);
				}

				try {
					ArquivoXML::db_query($stmt);
				} catch (Exception $e) {
					oci_free_statement($stmt);
				}
			}
		}
  }

  /**
   * saveGuiaExame s
   *
   * @param mixed $guia
   * @return void
   */
  private function saveGuiaExame(&$guia, &$vl_total, &$ident) {
    $v = array();

    if ($this->versao_3) {
			$v['NR_GUIA']                    = ArquivoXML::numero((string) $guia->dadosAutorizacao->numeroGuiaOperadora);
			$v['NR_GUIA_PRESTADOR']          = (string) $guia->cabecalhoGuia->numeroGuiaPrestador;
			$v['NR_GUIA_PRINCIPAL']          = null;
			$v['CD_CONSELHO_SOLICITANTE']    = ArquivoXML::getConselho((string) $guia->dadosSolicitante->profissionalSolicitante->conselhoProfissional);
			$v['CD_UF_CRM_SOLICITANTE']      = ArquivoXML::getUF((string) $guia->dadosSolicitante->profissionalSolicitante->UF);
			$v['TP_OCORRENCIA']              = ArquivoXML::getCaraterAtendimento((string) $guia->dadosSolicitacao->caraterAtendimento);
			$v['CPF_CGC_EXECUTANTE']         = ArquivoXML::getCPF_CGC($guia->dadosExecutante->contratadoExecutante);
			$v['CD_CNES_EXECUTANTE']         = ArquivoXML::getCPF_CGC($guia->dadosExecutante->CNES);
			$v['NM_PROFISSIONAL_EXECUTANTE'] = (string) $guia->dadosExecutante->contratadoExecutante->nomeContratado;
			$v['CD_TIPO_ATENDIMENTO']        = (string) $guia->dadosAtendimento->tipoAtendimento;
			$v['CD_TIPO_SAIDA']              = 0 ; //(string) $guia->tipoSaida;
			$v['NM_SOLICITANTE']             = (string) $guia->dadosSolicitante->contratadoSolicitante->nomeContratado;
			$v['CPF_CGC_SOLICITANTE']        = ArquivoXML::getCPF_CGC($guia->dadosSolicitante->contratadoSolicitante);
			$v['CD_CNES_SOLICITANTE']        = null;
			$v['NR_CRM_SOLICITANTE']         = ArquivoXML::numero((string) $guia->dadosSolicitante->profissionalSolicitante->numeroConselhoProfissional);
			$v['CD_CBO_SOLICITANTE']         = ArquivoXML::numero((string) $guia->dadosSolicitante->profissionalSolicitante->CBOS);
			$v['DS_INDICACAO_CLINICA']       = (string) $guia->dadosSolicitacao->indicacaoClinica;
			$v['CD_PATOLOGIA_SOLICITACAO']   = null;
		  $senha                           = (string) $guia->dadosAutorizacao->senha;
			$v['NR_SENHA']                   = is_numeric($senha) ? ArquivoXML::numero($senha) : NULL;
			$v['NR_CPF_COMPLEMENTAR']        = null;
			$v['NM_PROFISSIONAL_COMPLEMENTAR'] = null;
			$v['CD_CONSELHO_EXECUTANTE']       = null;
			$v['NR_CRM_EXECUTANTE']            = null;
			$v['CD_UF_CRM_EXECUTANTE']         = null;
			$v['CD_CBO_EXECUTANTE']            = null;
			$v['CD_INDICADOR_ACIDENTE']        = null;
			$v['ID_TIPO_DOENCA']               = null;
			$v['NR_TEMPO_DOENCA']              = null;
			$v['ID_TEMPO_DOENCA']              = null;
			$v['NM_PROFISSIONAL_SOLICITANTE']  = (string) $guia->dadosSolicitante->profissionalSolicitante->nomeProfissional;

			$v['CD_TIPO_LOGRADOURO']         = null;
			$v['NM_LOGRADOURO']              = null;
			$v['NR_LOGRADOURO']              = null;
			$v['DS_COMPLEMENTO']             = null;
			$v['DS_CIDADE']                  = null;
			$v['CD_UF']                      = null;
			$v['CD_IBGE']                    = null;
			$v['NR_CEP']                     = null;

			$v['VL_TOTAL_PROCEDIMENTO']      = (double) $guia->valorTotal->valorProcedimentos;
			$v['VL_TOTAL_TAXA']              = (double) $guia->valorTotal->valorTaxasAlugueis;
			$v['VL_TOTAL_MATERIAL']          = (double) $guia->valorTotal->valorMateriais;
			$v['VL_TOTAL_MEDICAMENTO']       = (double) $guia->valorTotal->valorMedicamentos;
			$v['VL_TOTAL_DIARIA']            = (double) $guia->valorTotal->valorDiarias;
			$v['VL_TOTAL_GASES']             = (double) $guia->valorTotal->valorGasesMedicinais;
			$v['VL_TOTAL_GUIA']              = (double) $guia->valorTotal->valorTotalGeral;

			if ($dt_sol = (string) $guia->dadosSolicitacao->dataSolicitacao) {
				$v['DT_SOLICITACAO'] =  date('d-M-Y', strtotime($dt_sol));
				$v['HORA_SOLICITACAO'] =  null; // substr($hr_sol,0,5);
			} else if ($dt_sol = (string) $guia->dadosAutorizacao->dataAutorizacao) {
				$v['DT_SOLICITACAO'] =  date('d-M-Y', strtotime($dt_sol));
				$v['HORA_SOLICITACAO'] =  null; // substr($hr_sol,0,5);
			}
			$v['DT_EMISSAO']                 = $v['DT_SOLICITACAO'];
		} else {
			$v['NR_GUIA']                    = ArquivoXML::numero((string) $guia->identificacaoGuiaSADTSP->numeroGuiaOperadora);
			$v['NR_GUIA_PRESTADOR']          = (string) $guia->identificacaoGuiaSADTSP->numeroGuiaPrestador;
			$v['NR_GUIA_PRINCIPAL']          = ArquivoXML::numero((string) $guia->numeroGuiaPrincipal);
			$v['CD_CONSELHO_SOLICITANTE']    = (string) $guia->dadosSolicitante->profissional->conselhoProfissional->siglaConselho;
			$v['CD_UF_CRM_SOLICITANTE']      = (string) $guia->dadosSolicitante->profissional->conselhoProfissional->ufConselho;
			$v['TP_OCORRENCIA']              = (string) $guia->caraterAtendimento;
			$v['CPF_CGC_EXECUTANTE']         = ArquivoXML::getCPF_CGC($guia->prestadorExecutante->identificacao);
			$v['CD_CNES_EXECUTANTE']         = ArquivoXML::numero((string) $guia->prestadorExecutante->numeroCNES);
			$v['NM_PROFISSIONAL_EXECUTANTE'] = (string) $guia->prestadorExecutante->nomeContratado;
			$v['CD_TIPO_ATENDIMENTO']        = (string) $guia->tipoAtendimento;
			$v['CD_TIPO_SAIDA']              = (string) $guia->tipoSaida;
			$v['NM_SOLICITANTE']             = (string) $guia->dadosSolicitante->contratado->nomeContratado;
			$v['CPF_CGC_SOLICITANTE']        = ArquivoXML::getCPF_CGC($guia->dadosSolicitante->contratado->identificacao);
			$v['CD_CNES_SOLICITANTE']        = ArquivoXML::numero((string) $guia->dadosSolicitante->contratado->numeroCNES);
			$v['NR_CRM_SOLICITANTE']         = ArquivoXML::numero((string) $guia->dadosSolicitante->profissional->conselhoProfissional->numeroConselho);
			$v['CD_CBO_SOLICITANTE']         = ArquivoXML::numero((string) $guia->dadosSolicitante->profissional->cbos);
			$v['DS_INDICACAO_CLINICA']       = (string) $guia->indicacaoClinica;
			$v['DT_EMISSAO']                 = date('d-M-Y', strtotime((string) $guia->identificacaoGuiaSADTSP->dataEmissaoGuia));
		  $senha                           = (string) $guia->dadosAutorizacao->senhaAutorizacao;
			$v['NR_SENHA']                   = is_numeric($senha) ? ArquivoXML::numero($senha) : NULL;
			$v['NR_CPF_COMPLEMENTAR']        = ArquivoXML::getCPF_CGC($guia->prestadorExecutante->profissionalExecutanteCompl->codigoProfissionalCompl);
			$v['NM_PROFISSIONAL_COMPLEMENTAR'] = (string) $guia->prestadorExecutante->profissionalExecutanteCompl->nomeExecutante;
			$v['CD_CONSELHO_EXECUTANTE']       = (string) $guia->prestadorExecutante->conselhoProfissional->siglaConselho;
			$v['NR_CRM_EXECUTANTE']            = ArquivoXML::numero((string) $guia->prestadorExecutante->conselhoProfissional->numeroConselho);
			$v['CD_UF_CRM_EXECUTANTE']         = (string) $guia->prestadorExecutante->conselhoProfissional->ufConselho;
			$v['CD_CBO_EXECUTANTE']            = ArquivoXML::numero((string) $guia->prestadorExecutante->codigoCBOS);
			$v['NM_PROFISSIONAL_SOLICITANTE']  = (string) $guia->dadosSolicitante->nomeProfissional;

			if ($guia->diagnosticoGuia) {
			  $v['CD_PATOLOGIA_SOLICITACAO']     = (string) $guia->diagnosticoGuia->CID->codigoDiagnostico;
				$v['CD_INDICADOR_ACIDENTE']        = (string) $guia->diagnosticoGuia->indicadorAcidente;
				$v['ID_TIPO_DOENCA']               = (string) $guia->diagnosticoGuia->tipoDoenca;
				$v['NR_TEMPO_DOENCA']              = (string) $guia->diagnosticoGuia->tempoReferidoEvolucaoDoenca->valor;
				$v['ID_TEMPO_DOENCA']              = (string) $guia->diagnosticoGuia->tempoReferidoEvolucaoDoenca->unidadeTempo;
			}

			$v['CD_TIPO_LOGRADOURO']         = (string) $guia->prestadorExecutante->enderecoContratado->tipoLogradouro;
			$v['NM_LOGRADOURO']              = (string) $guia->prestadorExecutante->enderecoContratado->logradouro;
			$v['NR_LOGRADOURO']              = (string) $guia->prestadorExecutante->enderecoContratado->numero;
			$v['DS_COMPLEMENTO']             = (string) $guia->prestadorExecutante->enderecoContratado->complemento;
			$v['DS_CIDADE']                  = (string) $guia->prestadorExecutante->enderecoContratado->municipio;
			$v['CD_UF']                      = (string) $guia->prestadorExecutante->enderecoContratado->codigoUF;
			$v['CD_IBGE']                    = ArquivoXML::numero((string) $guia->prestadorExecutante->enderecoContratado->codigoIBGEMunicipio);
			$v['NR_CEP']                     = ArquivoXML::numero((string) $guia->prestadorExecutante->enderecoContratado->cep);

			$v['VL_TOTAL_PROCEDIMENTO']      = (double) $guia->valorTotal->servicosExecutados;
			$v['VL_TOTAL_TAXA']              = (double) $guia->valorTotal->taxas;
			$v['VL_TOTAL_MATERIAL']          = (double) $guia->valorTotal->materiais;
			$v['VL_TOTAL_MEDICAMENTO']       = (double) $guia->valorTotal->medicamentos;
			$v['VL_TOTAL_DIARIA']            = (double) $guia->valorTotal->diarias;
			$v['VL_TOTAL_GASES']             = (double) $guia->valorTotal->gases;
			$v['VL_TOTAL_GUIA']              = (double) $guia->valorTotal->totalGeral;

			if ($dt_sol = (string) $guia->dataHoraAtendimento) {
				list($dt_sol, $hr_sol) = split('T', $dt_sol);
				$v['DT_SOLICITACAO'] =  date('d-M-Y', strtotime($dt_sol));
				$v['HORA_SOLICITACAO'] =  substr($hr_sol,0,5);
			}
		}

		$ident = array('NR_GUIA' => $v['NR_GUIA'], 'NR_GUIA_PRESTADOR' => $v['NR_GUIA_PRESTADOR'], 'NR_GUIA_PRINCIPAL' => $v['NR_GUIA_PRINCIPAL']);

    $v['NR_CARTEIRA']                  = ArquivoXML::numeroCarteira($guia->dadosBeneficiario->numeroCarteira);
    $v['NR_CNS']                       = ArquivoXML::numero((string) $guia->dadosBeneficiario->numeroCNS);
    $v['DS_OBS']                       = (string) $guia->observacao;
    $v['NR_PROCESSO_REFERENCIA']       = (string) $this->xml->numeroLote;
    $v['DT_RECEBIMENTO']               = (string) $this->filedate;
    $v['NR_SEQ_IMPORTACAO']            = $this->seq_importacao;

    $this->valor_total += $v['VL_TOTAL_GUIA'];
    $vl_total = $v['VL_TOTAL_GUIA'];

    $f = array_keys($v);

    $sql = "INSERT INTO TISS_GUIA_EXAME (". join(',', $f) .") VALUES (:".join(', :', $f).")";
    $stmt = oci_parse($this->db, $sql);

    foreach ($f as $k) {
      oci_bind_by_name($stmt, ':'.$k, $v[$k]);
    }

    try {
      ArquivoXML::db_query($stmt);
    }
    catch (ErrorDB $e) {
			if ($e->getCode() == 1) {
				//$obs = "CPF_CGC: {$v['CPF_CGC_EXECUTANTE']}, NR_GUIA: {$v['NR_GUIA']}, NR_GUIA_PRESTADOR: {$v['NR_GUIA_PRESTADOR']}";
				$obs = "Guia já apresentada: {$v['NR_GUIA_PRESTADOR']}";
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
  private function saveProcedimentosRealizados(&$procedimento, &$guia, &$vl_total) {
		$stmt = oci_parse($this->db, "SELECT TISS_S_ESP_EXAME.NEXTVAL FROM DUAL");
		ArquivoXML::db_query($stmt);
		$row = oci_fetch_object($stmt);
		$seq_esp = $row->NEXTVAL;
		oci_free_statement($stmt);

    $v = array();
    if ($this->versao_3) {
			$v['NR_GUIA']               = ArquivoXML::numero((string) $guia->dadosAutorizacao->numeroGuiaOperadora);
			$v['CPF_CGC_EXECUTANTE']    = ArquivoXML::getCPF_CGC($guia->dadosExecutante->contratadoExecutante);
			$v['NR_GUIA_PRESTADOR']     = (string) $guia->cabecalhoGuia->numeroGuiaPrestador;
			$v['CD_TIPO_TABELA']        = (string) $procedimento->procedimento->codigoTabela;
			$v['CD_ESPECIALIDADE']      = (string) $procedimento->procedimento->codigoProcedimento;
			$v['QT_EVENTO']             = (string) $procedimento->quantidadeExecutada;
			$v['DT_EVENTO']             = date('d-M-Y', strtotime((string) $procedimento->dataExecucao));
			$v['HORA_INICIAL']          = substr((string) $procedimento->horaInicial,0,5);
			$v['HORA_FINAL']            = substr((string) $procedimento->horaFinal,0,5);
			$v['VL_EVENTO']             = (double) $procedimento->valorUnitario;
			$v['ID_VIA_ACESSO']         = ArquivoXML::getViaAcesso((string) $procedimento->viaAcesso);
			$v['ID_TECNICA_UTILIZADA']  = ArquivoXML::getTecnicaUtilizada((string) $procedimento->tecnicaUtilizada);
		} else {
			$v['NR_GUIA']               = (string) $guia->identificacaoGuiaSADTSP->numeroGuiaOperadora;
			$v['CPF_CGC_EXECUTANTE']    = ArquivoXML::getCPF_CGC($guia->prestadorExecutante->identificacao);
			$v['NR_GUIA_PRESTADOR']     = (string) $guia->identificacaoGuiaSADTSP->numeroGuiaPrestador;
			$v['CD_TIPO_TABELA']        = (string) $procedimento->procedimento->tipoTabela;
			$v['CD_ESPECIALIDADE']      = (string) $procedimento->procedimento->codigo;
			$v['QT_EVENTO']             = (string) $procedimento->quantidadeRealizada;
			$v['DT_EVENTO']             = date('d-M-Y', strtotime((string) $procedimento->data));
			$v['HORA_INICIAL']          = substr((string) $procedimento->horaInicio,0,5);
			$v['HORA_FINAL']            = substr((string) $procedimento->horaFim,0,5);
			$v['VL_EVENTO']             = (double) $procedimento->valor;
			$v['ID_VIA_ACESSO']         = (string) $procedimento->viaAcesso;
			$v['ID_TECNICA_UTILIZADA']  = (string) $procedimento->tecnicaUtilizada;
		}

		$v['PCT_REDUCAO_ACRESCIMO'] = (double) $procedimento->reducaoAcrescimo;
    $v['VL_TOTAL']              = (double) $procedimento->valorTotal;
    $v['NR_SEQ_IMPORTACAO']     = $this->seq_importacao;
    $v['NR_SEQUENCIAL']         = $seq_esp;

    $vl_total += $v['VL_TOTAL'];

    $f = array_keys($v);

    $sql = "INSERT INTO TISS_ESPECIALIDADE_EXAME (". join(',', $f) .") VALUES (:".join(', :', $f).")";
    $stmt = oci_parse($this->db, $sql);

    foreach ($f as $k) {
      oci_bind_by_name($stmt, ':'.$k, $v[$k]);
    }

    ArquivoXML::db_query($stmt);
    oci_free_statement($stmt);


		if ($this->versao_3) {
			$equipes = &$procedimento->equipeSadt;
		} else {
			$equipes = &$procedimento->equipe->membroEquipe;
		}

		foreach ($equipes as $profissional) {
			$stmt = oci_parse($this->db, "SELECT TISS_S_ESP_EXAME_FUNCAO.NEXTVAL FROM DUAL");
			ArquivoXML::db_query($stmt);
			$row = oci_fetch_object($stmt);
			$seq_func = $row->NEXTVAL;
			oci_free_statement($stmt);

			$v = array();
			if ($this->versao_3) {
				$cpfcnpj = ArquivoXML::getCPF_CGC($profissional->codProfissional);
				if (strlen($cpfcnpj) > 11) {
					$v['CPF_CGC']            = $cpfcnpj;
				} else {
					$v['NR_CPF']             = $cpfcnpj;
				}
				$v['CD_FUNCAO']            = (string) $profissional->grauPart;
				$v['NM_PROFISSIONAL']      = (string) $profissional->nomeProf;
				$v['CD_CONSELHO']          = ArquivoXML::getConselho((string) $profissional->conselho);
				$v['NR_CRM']               = (string) $profissional->numeroConselhoProfissional;
				$v['CD_UF_CRM']            = (string) ArquivoXML::getUF((string) $profissional->UF);
				$v['CD_CBO']               = (string) $profissional->CBOS;
			} else {
				$cpfcnpj = ArquivoXML::getCPF_CGC($profissional->codigoProfissional);
				if (strlen($cpfcnpj) > 11) {
					$v['CPF_CGC']              = strlen($cpfcnpj) > 11 ? $cpfcnpj : null;
				} else {
					$v['NR_CPF']               = $cpfcnpj;
				}
				$v['CD_FUNCAO']            = (string) $profissional->posicaoProfissional;
				$v['NM_PROFISSIONAL']      = (string) $profissional->identificacaoProfissional->nomeExecutante;
				$v['CD_CONSELHO']          = (string) $profissional->identificacaoProfissional->conselhoProfissional->siglaConselho;
				$v['NR_CRM']               = (string) $profissional->identificacaoProfissional->conselhoProfissional->numeroConselho;
				$v['CD_UF_CRM']            = (string) $profissional->identificacaoProfissional->conselhoProfissional->ufConselho;
				$v['CD_CBO']               = (string) $profissional->identificacaoProfissional->codigoCBOS;
			}

			$v['NR_SEQUENCIAL_ESP']    = $seq_esp;
			$v['NR_SEQUENCIAL_FUNCAO'] = $seq_func;
			$v['NR_SEQ_IMPORTACAO']    = $this->seq_importacao;

			$f = array_keys($v);

			$sql = "INSERT INTO TISS_ESP_EXAME_FUNCAO (". join(',', $f) .") VALUES (:".join(', :', $f).")";
			$stmt = oci_parse($this->db, $sql);

			foreach ($f as $k) {
				oci_bind_by_name($stmt, ':'.$k, $v[$k]);
			}

			ArquivoXML::db_query($stmt);
			oci_free_statement($stmt);
		}
  }

  /**
   * saveOPMUtilizada
   *
   * @param mixed $opm
   * @param mixed $guia
   * @return void
   */
  private function saveOPMUtilizada (&$opm, &$guia, &$vl_total) {
    $v = array();
    $v['NR_GUIA']           = (string) $guia->identificacaoGuiaSADTSP->numeroGuiaOperadora;
    $v['CD_TIPO_TABELA']    = (string) $opm->OPM->tipoTabela;
    $v['CD_OPM']            = (string) $opm->OPM->codigo;
    $v['QT_OPM']            = (int) $opm->quantidade;
    $v['CD_BARRAS']         = (string) $opm->codigoBarra;
    $v['VL_OPM_UNITARIO']   = (double) $opm->valorUnitario;
    $v['VL_OPM_TOTAL']      = (double) $opm->valorTotal;
    $v['NR_GUIA_PRESTADOR'] = (string) $guia->identificacaoGuiaSADTSP->numeroGuiaPrestador;
    $v['CPF_CGC_EXECUTANTE']= ArquivoXML::getCPF_CGC($guia->prestadorExecutante->identificacao);
    $v['NR_SEQ_IMPORTACAO'] = $this->seq_importacao;

    $vl_total += $v['VL_OPM_TOTAL'];

    $f = array_keys($v);

    $sql = "INSERT INTO TISS_OPM_UTIL_EXAME (". join(',', $f) .") VALUES (:".join(', :', $f).")";
    $stmt = oci_parse($this->db, $sql);

    foreach ($f as $k) {
      oci_bind_by_name($stmt, ':'.$k, $v[$k]);
    }

    ArquivoXML::db_query($stmt);

    oci_free_statement($stmt);
  }

  private function saveOutrasDespesas (&$outras, &$guia, &$vl_total) {
    $v = array();

    if ($this->versao_3) {
			$v['NR_GUIA_REFERENCIA']    = (string) $guia->cabecalhoGuia->numeroGuiaPrestador;
			$v['CPF_CGC_EXECUTANTE']    = ArquivoXML::getCPF_CGC($guia->dadosExecutante->contratadoExecutante);
			$v['CD_DESPESA']            = (string) $outras->codigoDespesa;
			$v['DT_DESPESA']            = date('d-M-Y', strtotime((string) $outras->servicosExecutados->dataExecucao));
			$v['HORA_INICIAL']          = substr((string) $outras->servicosExecutados->horaInicial, 0,5);
			$v['HORA_FINAL']            = substr((string) $outras->servicosExecutados->horaFinal, 0,5);
			$v['CD_TIPO_TABELA']        = (string) $outras->servicosExecutados->codigoTabela;
			$v['CD_ITEM']               = (string) $outras->servicosExecutados->codigoProcedimento;
			$v['QTD_DESPESA']           = (int) $outras->servicosExecutados->quantidadeExecutada;
			$v['PCT_REDUCAO_ACRESCIMO'] = (double) $outras->servicosExecutados->reducaoAcrescimo;
			$v['VL_UNITARIO']           = (double) $outras->servicosExecutados->valorUnitario;
			$v['VL_TOTAL']              = (double) $outras->servicosExecutados->valorTotal;
			$v['NR_GUIA_PRESTADOR']     = (string) $guia->cabecalhoGuia->numeroGuiaPrestador;
			$v['CD_CNES']               = null;
		} else {
			$v['NR_GUIA_REFERENCIA']    = (string) $guia->identificacaoGuiaSADTSP->numeroGuiaOperadora;
			$v['CPF_CGC_EXECUTANTE']    = ArquivoXML::getCPF_CGC($guia->prestadorExecutante->identificacao);
			$v['CD_DESPESA']            = (string) $outras->tipoDespesa;
			$v['DT_DESPESA']            = date('d-M-Y', strtotime((string) $outras->dataRealizacao));
			$v['HORA_INICIAL']          = substr((string) $outras->horaInicial, 0,5);
			$v['HORA_FINAL']            = substr((string) $outras->horaFinal, 0,5);
			$v['CD_TIPO_TABELA']        = (string) $outras->identificadorDespesa->tipoTabela;
			$v['CD_ITEM']               = (string) $outras->identificadorDespesa->codigo;
			$v['QTD_DESPESA']           = (int) $outras->quantidade;
			$v['PCT_REDUCAO_ACRESCIMO'] = (double) $outras->reducaoAcrescimo;
			$v['VL_UNITARIO']           = (double) $outras->valorUnitario;
			$v['VL_TOTAL']              = (double) $outras->valorTotal;
			$v['NR_GUIA_PRESTADOR']     = (string) $guia->identificacaoGuiaSADTSP->numeroGuiaPrestador;
			$v['CD_CNES']               = (string) $guia->prestadorExecutante->numeroCNES;
		}

    $v['NR_SEQ_IMPORTACAO'] = $this->seq_importacao;

    $f = array_keys($v);

    $vl_total += $v['VL_TOTAL'];

    $sql = "INSERT INTO TISS_OUTRAS_DESPESAS (". join(',', $f) .") VALUES (:".join(', :', $f).")";
    $stmt = oci_parse($this->db, $sql);

    foreach ($f as $k) {
      oci_bind_by_name($stmt, ':'.$k, $v[$k]);
    }

    ArquivoXML::db_query($stmt);

    oci_free_statement($stmt);
  }
}

?>
