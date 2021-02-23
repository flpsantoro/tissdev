<?php

class Guia_Consulta
{
  private $xml;
  private $guias;
  private $db;
  private $filedate;
  private $valor_total = 0;
  private $versao_3;

  function __construct ( &$xml, $versao_3 = false ) {
    global $db, $file_date;

    $this->xml =& $xml;
    if ($versao_3) {
			$this->guias = &$xml->guiasTISS->guiaConsulta;
		} else {
			$this->guias = &$xml->guias->guiaFaturamento->guiaConsulta;
		}
    $this->db = &$db;
    $this->filedate = date( 'd-M-Y', $file_date );
    $this->versao_3 = $versao_3;
  }

  public function getValorTotal() {
	  return $this->valor_total;
  }

  function processa ( $seq_importacao ) {
    foreach ( $this->guias as $guia ) {
/*
      $sql = "INSERT INTO TISS_GUIA_CONSULTA (
          NR_GUIA, DT_EMISSAO, NR_CARTEIRA, NR_CNS, CPF_CGC, CD_CNES, NR_CEP, NM_LOGRADOURO, DS_COMPLEMENTO,
          DS_CIDADE, CD_UF, CD_IBGE, NM_PROFISSIONAL_EXECUTANTE, CD_CONSELHO, NR_CONSELHO, CD_UF_CONSELHO,
          CD_CBO, ID_TIPO_DOENCA, ID_TEMPO_DOENCA,  NR_TEMPO_DOENCA, CD_INDICADOR_ACIDENTE,
          CD_PATOLOGIA_PRINCIPAL, CD_PATOLOGIA_2, CD_PATOLOGIA_3, CD_PATOLOGIA_4, DT_EVENTO, CD_TABELA,
          CD_ESPECIALIDADE, CD_TIPO_CONSULTA, CD_TIPO_SAIDA, DS_OBS, CD_TIPO_LOGRADOURO, NR_LOGRADOURO,
          NR_PROCESSO_REFERENCIA, DT_RECEBIMENTO, NR_GUIA_PRESTADOR, VL_CONSULTA, NR_SEQ_IMPORTACAO)
        VALUES (
          :nr_guia, :dt_emissao, :nr_carteira, :nr_cns, :cpf_cgc, :cd_cnes, :nr_cep, :nm_logradouro,
          :ds_complemento, :ds_cidade, :cd_uf, :cd_ibge, :nm_profissional_executante, :cd_conselho,
          :nr_conselho, :cd_uf_conselho, :cd_cbo, :id_tipo_doenca, :id_tempo_doenca, :nr_tempo_doenca,
          :cd_indicador_acidente, :cd_patologia_principal, :cd_patologia_2, :cd_patologia_3, :cd_patologia_4,
          :dt_evento, :cd_tabela, :cd_especialidade, :cd_tipo_consulta, :cd_tipo_saida, :ds_obs,
          :cd_tipo_logradouro, :nr_logradouro, :nr_processo_referencia, :dt_recebimento, :nr_guia_prestador,
          :vl_consulta, :nr_seq_importacao
      )";

      $stmt = oci_parse ( $this->db, $sql );

      oci_bind_by_name( $stmt, ':nr_guia', $values['NR_GUIA']);
      oci_bind_by_name( $stmt, ':dt_emissao', $values['DT_EMISSAO']);
      oci_bind_by_name( $stmt, ':nr_carteira', $values['NR_CARTEIRA']);
      oci_bind_by_name( $stmt, ':nr_cns', $values['NR_CNS']);
      oci_bind_by_name( $stmt, ':cpf_cgc', $values['CPF_CGC']);
      oci_bind_by_name( $stmt, ':cd_cnes', $values['CD_CNES']);
      oci_bind_by_name( $stmt, ':nr_cep', $values['NR_CEP']);
      oci_bind_by_name( $stmt, ':nm_logradouro', $values['NM_LOGRADOURO']);
      oci_bind_by_name( $stmt, ':ds_complemento', $values['DS_COMPLEMENTO']);
      oci_bind_by_name( $stmt, ':ds_cidade', $values['DS_CIDADE']);
      oci_bind_by_name( $stmt, ':cd_uf', $values['CD_UF']);
      oci_bind_by_name( $stmt, ':cd_ibge', $values['CD_IBGE']);
      oci_bind_by_name( $stmt, ':nm_profissional_executante', $values['NM_PROFISSIONAL_EXECUTANTE']);
      oci_bind_by_name( $stmt, ':cd_conselho', $values['CD_CONSELHO']);
      oci_bind_by_name( $stmt, ':nr_conselho', $values['NR_CONSELHO']);
      oci_bind_by_name( $stmt, ':cd_uf_conselho', $values['CD_UF_CONSELHO']);
      oci_bind_by_name( $stmt, ':cd_cbo', $values['CD_CBO']);
      oci_bind_by_name( $stmt, ':id_tipo_doenca', $values['ID_TIPO_DOENCA']);
      oci_bind_by_name( $stmt, ':id_tempo_doenca', $values['ID_TEMPO_DOENCA']);
      oci_bind_by_name( $stmt, ':nr_tempo_doenca', $values['NR_TEMPO_DOENCA']);
      oci_bind_by_name( $stmt, ':cd_indicador_acidente', $values['CD_INDICADOR_ACIDENTE']);
      oci_bind_by_name( $stmt, ':cd_patologia_principal', $values['CD_PATOLOGIA_PRINCIPAL']);
      oci_bind_by_name( $stmt, ':cd_patologia_2', $values['CD_PATOLOGIA_2']);
      oci_bind_by_name( $stmt, ':cd_patologia_3', $values['CD_PATOLOGIA_3']);
      oci_bind_by_name( $stmt, ':cd_patologia_4', $values['CD_PATOLOGIA_4']);
      oci_bind_by_name( $stmt, ':dt_evento', $values['DT_EVENTO']);
      oci_bind_by_name( $stmt, ':cd_tabela', $values['CD_TABELA']);
      oci_bind_by_name( $stmt, ':cd_especialidade', $values['CD_ESPECIALIDADE']);
      oci_bind_by_name( $stmt, ':cd_tipo_consulta', $values['CD_TIPO_CONSULTA']);
      oci_bind_by_name( $stmt, ':cd_tipo_saida', $values['CD_TIPO_SAIDA']);
      oci_bind_by_name( $stmt, ':ds_obs', $values['DS_OBS']);
      oci_bind_by_name( $stmt, ':cd_tipo_logradouro', $values['CD_TIPO_LOGRADOURO']);
      oci_bind_by_name( $stmt, ':nr_logradouro', $values['NR_LOGRADOURO']);
      oci_bind_by_name( $stmt, ':nr_processo_referencia', $values['NR_PROCESSO_REFERENCIA']);
      oci_bind_by_name( $stmt, ':dt_recebimento', $values['DT_RECEBIMENTO']);
      oci_bind_by_name( $stmt, ':nr_guia_prestador', $values['NR_GUIA_PRESTADOR']);
      oci_bind_by_name( $stmt, ':vl_consulta', $values['VL_CONSULTA']);
      oci_bind_by_name( $stmt, ':nr_seq_importacao', $seq_importacao);
*/
      $v = $this->getValues($guia);

      $this->valor_total += $v['VL_CONSULTA'];
		  $v['NR_SEQ_IMPORTACAO'] = $seq_importacao;

			$f = array_keys($v);
			$sql = "INSERT INTO TISS_GUIA_CONSULTA (". join(',', $f) .") VALUES (:".join(', :', $f).")";
			$stmt = oci_parse($this->db, $sql);

			foreach ($f as $k) {
				oci_bind_by_name($stmt, ':'.$k, $v[$k]);
			}

      try {
        ArquivoXML::db_query($stmt);
      }
      catch (ErrorDB $e) {
        if ($e->getCode() == 1) {
          //$obs = "CPF_CGC: {$values['CPF_CGC']}, NR_GUIA: {$values['NR_GUIA']}, NR_GUIA_PRESTADOR: {$values['NR_GUIA_PRESTADOR']}";
          $obs = "Guia já apresentada: {$values['NR_GUIA_PRESTADOR']} Dt Emissão: {$values['DT_EMISSAO']}";
          throw new ErrorGuiaJaApresentada($obs);
        }
        else throw $e;
      }
    }
  }

  function getValues(&$guia) {
    $values = array();

    if ($this->versao_3) {
			$values['NR_GUIA']                    = ArquivoXML::numero((string) $guia->numeroGuiaOperadora);
			$values['DT_EMISSAO']                 = date( 'd-M-Y', strtotime ( (string) $guia->dadosAtendimento->dataAtendimento ) );
			$values['NR_CARTEIRA']                = ArquivoXML::numeroCarteira($guia->dadosBeneficiario->numeroCarteira);
			$values['NR_CNS']                     = ArquivoXML::numero((string) $guia->dadosBeneficiario->numeroCNS);
			$values['CPF_CGC']                    = ArquivoXML::getCPF_CGC($guia->contratadoExecutante);
			$values['CD_CNES']                    = ArquivoXML::numero((string) $guia->contratadoExecutante->CNES);
			$values['NR_CEP']                     = null; // ArquivoXML::numero((string) $guia->dadosContratado->enderecoContratado->cep);
			$values['NM_LOGRADOURO']              = null; // (string) $guia->dadosContratado->enderecoContratado->logradouro;
			$values['DS_COMPLEMENTO']             = null; // (string) $guia->dadosContratado->enderecoContratado->complemento;
			$values['DS_CIDADE']                  = null; // (string) $guia->dadosContratado->enderecoContratado->municipio;
			$values['CD_UF']                      = null; // (string) $guia->dadosContratado->enderecoContratado->codigoUF;
			$values['CD_IBGE']                    = null; // ArquivoXML::numero((string) $guia->dadosContratado->enderecoContratado->codigoIBGEMunicipio);
			$values['CD_CONSELHO']                = ArquivoXML::getConselho((string) $guia->profissionalExecutante->conselhoProfissional);
			$values['CD_UF_CONSELHO']             = ArquivoXML::getUF((string) $guia->profissionalExecutante->UF);
			$values['NR_CONSELHO']                = ArquivoXML::numero((string) $guia->profissionalExecutante->numeroConselhoProfissional);
			$values['CD_CBO']                     = ArquivoXML::numero((string) $guia->profissionalExecutante->CBOS);
			$values['ID_TIPO_DOENCA']             = null; // (string) $guia->hipoteseDiagnostica->tipoDoenca;
			$values['ID_TEMPO_DOENCA']            = null; // (string) $guia->hipoteseDiagnostica->tempoReferidoEvolucaoDoenca->unidadeTempo;
			$values['NR_TEMPO_DOENCA']            = null; // (int) $guia->hipoteseDiagnostica->tempoReferidoEvolucaoDoenca->valor;
			$values['CD_INDICADOR_ACIDENTE']      = null; // (string) $guia->hipoteseDiagnostica->indicadorAcidente;
			$values['CD_PATOLOGIA_PRINCIPAL']     = null; // (string) str_replace( '.', '', $guia->hipoteseDiagnostica->CID->codigoDiagnostico );
			$values['CD_PATOLOGIA_2']             = null;
			$values['CD_PATOLOGIA_3']             = null;
			$values['CD_PATOLOGIA_4']             = null;
			$values['CD_TIPO_SAIDA']              = null; // (int) $guia->dadosAtendimento->tipoSaida;
		  $values['CD_TIPO_LOGRADOURO']         = null; // (string) $guia->dadosContratado->enderecoContratado->tipoLogradouro;
      $values['NR_LOGRADOURO']              = null; // (string) $guia->dadosContratado->enderecoContratado->numero;
      $values['NR_GUIA_PRESTADOR']          = (string) $guia->cabecalhoConsulta->numeroGuiaPrestador;
      $values['VL_CONSULTA']                = (double) $guia->dadosAtendimento->procedimento->valorProcedimento;
		} else {
			$values['NR_GUIA']                    = ArquivoXML::numero((string) $guia->identificacaoGuia->numeroGuiaOperadora);
			$values['DT_EMISSAO']                 = date( 'd-M-Y', strtotime ( (string) $guia->identificacaoGuia->dataEmissaoGuia ) );
			$values['NR_CARTEIRA']                = ArquivoXML::numeroCarteira($guia->beneficiario->numeroCarteira);
			$values['NR_CNS']                     = ArquivoXML::numero((string) $guia->beneficiario->numeroCNS);
			$values['CPF_CGC']                    = ArquivoXML::getCPF_CGC($guia->dadosContratado->identificacao);
			$values['CD_CNES']                    = ArquivoXML::numero((string) $guia->dadosContratado->numeroCNES);
			$values['NR_CEP']                     = ArquivoXML::numero((string) $guia->dadosContratado->enderecoContratado->cep);
			$values['NM_LOGRADOURO']              = (string) $guia->dadosContratado->enderecoContratado->logradouro;
			$values['DS_COMPLEMENTO']             = (string) $guia->dadosContratado->enderecoContratado->complemento;
			$values['DS_CIDADE']                  = (string) $guia->dadosContratado->enderecoContratado->municipio;
			$values['CD_UF']                      = (string) $guia->dadosContratado->enderecoContratado->codigoUF;
			$values['CD_IBGE']                    = ArquivoXML::numero((string) $guia->dadosContratado->enderecoContratado->codigoIBGEMunicipio);
			$values['CD_CONSELHO']                = (string) $guia->profissionalExecutante->conselhoProfissional->siglaConselho;
			$values['CD_UF_CONSELHO']             = (string) $guia->profissionalExecutante->conselhoProfissional->ufConselho;
			$values['NR_CONSELHO']                = ArquivoXML::numero((string) $guia->profissionalExecutante->conselhoProfissional->numeroConselho);
			$values['CD_CBO']                     = ArquivoXML::numero((string) $guia->profissionalExecutante->cbos);

			if ($guia->hipoteseDiagnostica) {
				$values['ID_TIPO_DOENCA']             = (string) $guia->hipoteseDiagnostica->tipoDoenca;
				$values['ID_TEMPO_DOENCA']            = (string) $guia->hipoteseDiagnostica->tempoReferidoEvolucaoDoenca->unidadeTempo;
				$values['NR_TEMPO_DOENCA']            = (int) $guia->hipoteseDiagnostica->tempoReferidoEvolucaoDoenca->valor;
				$values['CD_INDICADOR_ACIDENTE']      = (string) $guia->hipoteseDiagnostica->indicadorAcidente;
				$values['CD_PATOLOGIA_PRINCIPAL']     = (string) str_replace('.', '', $guia->hipoteseDiagnostica->CID->codigoDiagnostico);
			} else {
				$values['ID_TIPO_DOENCA']             = null;
				$values['ID_TEMPO_DOENCA']            = null;
				$values['NR_TEMPO_DOENCA']            = null;
				$values['CD_INDICADOR_ACIDENTE']      = null;
				$values['CD_PATOLOGIA_PRINCIPAL']     = null;
			}
			$values['CD_TIPO_SAIDA']              = (int) $guia->dadosAtendimento->tipoSaida;
		  $values['CD_TIPO_LOGRADOURO']         = (string) $guia->dadosContratado->enderecoContratado->tipoLogradouro;
      $values['NR_LOGRADOURO']              = (string) $guia->dadosContratado->enderecoContratado->numero;
      $values['NR_GUIA_PRESTADOR']          = (string) $guia->identificacaoGuia->numeroGuiaPrestador;
      $values['VL_CONSULTA']                = 0;

			if ( isset( $guia->hipoteseDiagnostica->diagnosticoSecundario->CID[0] ) ) {
				$values['CD_PATOLOGIA_2'] = (string) str_replace( '.', '', $guia->hipoteseDiagnostica->diagnosticoSecundario->CID[0]->codigoDiagnostico );
			} else {
				$values['CD_PATOLOGIA_2'] = null;
			}
			if ( isset( $guia->hipoteseDiagnostica->diagnosticoSecundario->CID[1] ) ) {
				$values['CD_PATOLOGIA_3'] = (string) str_replace( '.', '', $guia->hipoteseDiagnostica->diagnosticoSecundario->CID[1]->codigoDiagnostico );
			} else {
				$values['CD_PATOLOGIA_3'] = null;
			}
			if ( isset( $guia->hipoteseDiagnostica->diagnosticoSecundario->CID[2] ) ) {
				$values['CD_PATOLOGIA_4'] = (string) str_replace( '.', '', $guia->hipoteseDiagnostica->diagnosticoSecundario->CID[2]->codigoDiagnostico );
			} else {
				$values['CD_PATOLOGIA_4'] = null;
			}
		}
    $values['NM_PROFISSIONAL_EXECUTANTE'] = (string) $guia->profissionalExecutante->nomeProfissional;
    $values['DT_EVENTO']              = date( 'd-M-Y', strtotime( (string) $guia->dadosAtendimento->dataAtendimento ) );
    $values['CD_TABELA']              = (string) $guia->dadosAtendimento->procedimento->codigoTabela;
    $values['CD_ESPECIALIDADE']       = (string) $guia->dadosAtendimento->procedimento->codigoProcedimento;
    $values['CD_TIPO_CONSULTA']       = (string) $guia->dadosAtendimento->tipoConsulta;
    $values['DS_OBS']                 = (string) $guia->observacao;
    $values['NR_PROCESSO_REFERENCIA'] = (string) $this->xml->numeroLote;
    $values['DT_RECEBIMENTO']         = (string) $this->filedate;

    foreach( $values as $key => $val ) {
      if ( empty( $values[$key] ) ) {
          $values[$key] = NULL;
      }
    }

    return $values;
  }
}

?>
