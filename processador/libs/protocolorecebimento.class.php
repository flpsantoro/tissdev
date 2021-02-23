<?php

class protocoloRecebimento
{
  private $xml;
  private $pxml;
  private $pr;
  private $db;
  private $sequencialTransacao;
  private $axml;

  /**
   * __construct
   *
   * @param mixed $sequencial Sequecial da transacao
   * @param mixed $xml XML para retorno
   * @param mixed $pxml XML recebido
   * @access protected
   * @return void
   */
  function __construct ( $sequencial, &$xml, &$pxml, $sucesso, $axml ) {
    global $db;

    $this->xml = &$xml;
    $this->pxml = &$pxml;
    $this->db = &$db;
    $this->pr = $this->xml->operadoraParaPrestador->addChild( 'protocoloRecebimento' );
    $this->sequencialTransacao = $sequencial;
    $this->sucesso = $sucesso;
    $this->axml = $axml;
  }

  /**
   * processa
   *
   * @access public
   * @return void
   */
  function processa() {
    $ope = $this->pr->addChild('dadosOperadora');
    $ope->addChild('registroANS', 417548);
    $ope->addChild('nomeOperadora', 'FIOSAÚDE');
    $ope->addChild('CNPJ', '03033006000153');

    $pre = $this->pr->addChild('dadosPrestador');

    $dest = $pre->addChild('identificacao');
    $from_id = (string) $this->xml->cabecalho->destino->codigoPrestadorNaOperadora->codigoPrestadorNaOperadora;
    $dest->addChild('codigoPrestadorNaOperadora', $from_id);

    $stmt = oci_parse($this->db, "SELECT * FROM TISS_USUARIO WHERE CPF_CNPJ = :from_id AND TP_USUARIO = 1 AND ROWNUM = 1");
    oci_bind_by_name($stmt, ':from_id', $from_id);
    ArquivoXML::db_query($stmt);
    $row = oci_fetch_object($stmt);

		$pre->addChild('nomeContratado', empty($row) ? '' : $row->NM_USUARIO);

    //$end = $pre->addChild( 'enderecoContratado' );
    //$pre->addChild( 'numeroCNES' );

    $this->pr->addChild('dataEnvioLote', (string) $this->pxml->cabecalho->identificacaoTransacao->dataRegistroTransacao);
    $this->pr->addChild('numeroLote', (string) $this->pxml->prestadorParaOperadora->loteGuias->numeroLote);

    $nr_protocolo = $this->get_ProtocoloRecebimento();
    $this->pr->addChild( 'numeroProtocoloRecebimento', $nr_protocolo );
  }

  /**
   * get_ProtocoloRecebimento
   *
   * @return integer Numero do Protocolo
   */
  private function get_ProtocoloRecebimento() {
    $stmt = oci_parse($this->db, "SELECT TISS_S_TRANSACAO_PROTOCOLO.NEXTVAL NEXTVAL FROM DUAL");
    ArquivoXML::db_query($stmt);
    $row = oci_fetch_object($stmt);
    $nr_protocolo = $row->NEXTVAL;
    oci_free_statement($stmt);

    $sql = "INSERT INTO TISS_TRANSACAO_PROTOCOLO
      (NR_SEQUENCIAL_TRANSACAO, CD_STATUS_PROTOCOLO, CPF_CGC, NR_LOTE,
       NR_PROTOCOLO_RECEBIMENTO, DT_ENVIO, VL_TOTAL, NR_SEQ_IMPORTACAO, TP_GUIA)
      VALUES
        (:NR_SEQUENCIAL_TRANSACAO, :CD_STATUS_PROTOCOLO, :CPF_CGC, :NR_LOTE,
         :NR_PROTOCOLO_RECEBIMENTO, SYSDATE, :VL_TOTAL, :NR_SEQ_IMPORTACAO, :TP_GUIA)";
    $stmt = oci_parse($this->db, $sql);

    oci_bind_by_name($stmt, ':NR_SEQUENCIAL_TRANSACAO', $this->sequencialTransacao);
    $status = $this->sucesso ? 1 : 0;
    oci_bind_by_name($stmt, ':CD_STATUS_PROTOCOLO', $status);
    $cpf = $_SESSION['cpf_cnpj'];// (string) $this->xml->cabecalho->destino->codigoPrestadorNaOperadora->codigoPrestadorNaOperadora;
    oci_bind_by_name($stmt, ':CPF_CGC', $cpf);
    $lote = (string) $this->pxml->prestadorParaOperadora->loteGuias->numeroLote;
    oci_bind_by_name($stmt, ':NR_LOTE', $lote);
    oci_bind_by_name($stmt, ':NR_PROTOCOLO_RECEBIMENTO', $nr_protocolo);
    oci_bind_by_name($stmt, ':VL_TOTAL', $this->axml->getValorTotal());
    oci_bind_by_name($stmt, ':NR_SEQ_IMPORTACAO', $this->axml->getSeqImportacao());
    oci_bind_by_name($stmt, ':TP_GUIA', $this->axml->tp_guia);

    ArquivoXML::db_query($stmt);
    oci_free_statement($stmt);

    return $nr_protocolo;
  }
}

?>
