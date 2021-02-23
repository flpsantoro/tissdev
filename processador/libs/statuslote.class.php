<?php

class statusProtocolo
{
  private $xml;
  private $pxml;
  private $pr;
  private $dxml;
  private $db;
  private $sequencialTransacao;

  /**
   * __construct 
   * 
   * @param mixed $sequencial Sequecial da transacao
   * @param mixed $xml XML para retorno
   * @param mixed $pxml XML recebido
   * @access public
   * @return void
   */
  public function __construct ( $sequencial, &$xml, &$pxml ) {
    global $db;

    $this->xml = &$xml;
    $this->pxml = &$pxml;
    $this->db = &$db;
    $this->pr = $this->xml->operadoraParaPrestador->addChild( 'situacaoProtocolo' );
    $this->dxml =& $this->pxml->prestadorParaOperadora->solicitacaoStatusProtocolo;
    $this->sequencialTransacao = $sequencial;
  }

  /**
   * processa 
   * 
   * @access public
   * @return void
   */
  public function processa () {
    $this->pr->addChild('registroANS', '337404');
    $this->pr->addChild('nomeOperadora', 'FioSaude');
    $this->pr->addChild('CNPJ', '28954717000191');

    $this->adicionarDadosPrestador();

    $lote = $this->pr->addChild('lote');
    foreach ($this->dxml->detalheProtocolo->numeroProtocolo as $protocolo) {
      $det = $lote->addChild('detalheLote');
      $this->adicionarDadosLote($det, $protocolo);
    }
  }

  private function adicionarDadosLote(&$xml, $numeroProtocolo) {
    $stmt = oci_parse($this->db, "
      SELECT * FROM ASM_TRANSACAO_PROTOCOLO 
      WHERE NR_PROTOCOLO_RECEBIMENTO = :nrProtocolo
    ");
    oci_bind_by_name($stmt, ':nrProtocolo', $numeroProtocolo);
    ArquivoXML::db_query($stmt);
    $protocolo = oci_fetch_object($stmt);

    $xml->addChild('numeroProtocolo', $numeroProtocolo);

    if (!$protocolo) {
      $xml->addChild('numeroLote');
      $xml->addChild('dataEnvioLote');
      $xml->addChild('status', 7);
      $xml->addChild('guias');
      return;
    }

    $xml->addChild('numeroLote', $protocolo->NR_LOTE);
    $xml->addChild('dataEnvioLote', $protocolo->DT_TRANSACAO);

    $this->adicionaValores($xml, $protocolo);

    $xml->addChild('status', $protocolo->CD_STATUS_PROTOCOLO);

    $guias = $xml->addChild('guias');
  }

  /**
   * adicionaDadosPrestador 
   * 
   * @return void
   */
  private function adicionarDadosPrestador() {
    $cpf_cgc = ArquivoXML::getCPF_CGC($this->dxml->dadosPrestador->identificacao);

    $stmt = oci_parse($this->db, "
        SELECT p.tp_prestador, p.nm_prestador, e.cd_cnes
        FROM asm_prestador p, asm_endereco e
        WHERE p.cpf_cgc = e.cpf_cgc
          and e.SN_PRINCIPAL = 'S'
          and p.cpf_cgc = :CPF_CGC
    ");

    oci_bind_by_name($stmt, ':CPF_CGC', $cpf_cgc);
    ArquivoXML::db_query($stmt);
    $row = oci_fetch_object($stmt);

    $prestador = $this->pr->addChild('dadosPrestador');
    $ident = $prestador->addChild('identificacao');
    if ($row->TP_PRESTADOR == 'J') {
      $ident->addChild('CNPJ', $row->CPF_CGC);
    }
    else {
      $ident->addChild('cpf', $row->CPF_CGC);
    }

    $prestador->addChild('nomePrestador', $row->NM_PRESTADOR);
    $prestador->addChild('numeroCNES', $row->CD_CNES);

    $this->pr->addChild('CNESPrestador', $row->CD_CNES);
  }
    
  /**
   * adicionaValores 
   * 
   * @param mixed $xml 
   * @param mixed $protocolo 
   * @return void
   */
  private function adicionaValores (&$xml, $protocolo) {
    //$xml->addChild('numeroFatura');
   
    $stmt = oci_parse($this->db, "
      SELECT * FROM ASM_PROCESSO 
      WHERE CPF_CGC = :cpf_cgc AND NR_PROCESSO_REFERENCIA = :lote
    ");
    oci_bind_by_name($stmt, ':cpf_cgc', $protocolo->CPF_CGC);
    oci_bind_by_name($stmt, ':lote', $protocolo->NR_LOTE);
    ArquivoXML::db_query($stmt);
    $processo = oci_fetch_object($stmt);

    switch ($processo->TP_GUIA) {
      case 'GC':
        list($vl_processado, $vl_liberado, $vl_glosa) 
          = $this->valorGuiaExame($processo);
        break;

      case 'GE':
        list($vl_processado, $vl_liberado, $vl_glosa) 
          = $this->valorGuiaExame($processo);
        break;

      case 'GI':
        list($vl_processado, $vl_liberado, $vl_glosa) 
          = $this->valorGuiaInternacao($processo);
        break;

      case 'GH':
        list($vl_processado, $vl_liberado, $vl_glosa)
          = $this->valorGuiaHonorario($processo);
        break;
    }

    $xml->addChild('valorProcessado', $vl_processado);
    $xml->addChild('valorLiberado', $vl_liberado);
    $xml->addChild('valorGlosa', $vl_glosa);
  }

  private function valorGuiaConsulta ($processo) {
    $stmt = oci_parse($this->db, "
      SELECT
        SUM(VL_COBRADO) VL_COBRADO, SUM(VL_PAGO) VL_PAGO, 
        SUM(VL_GLOSA) VL_GLOSA
      FROM (
        select
          NVL(sum(vl_cobrado), 0) VL_COBRADO, NVL(sum(vl_pago),0) VL_PAGO, 
          0 VL_GLOSA
        from asm_guia_consulta
        where cd_convenio = 1 and nr_processo = :processo
        union all
        select 0, 0, NVL(SUM(glosa.vl_glosa), 0) VL_GLOSA
        from asm_guia_consulta guia, asm_glosa_guia glosa
        where guia.cd_convenio = 1
          and guia.nr_processo = :processo
          and glosa.nr_guia = guia.nr_guia
          and glosa.cd_convenio = 1
          and glosa.tp_guia = 'GC'
      )
    ");
    oci_bind_by_name($stmt, ':processo', $processo->NR_PROCESSO);
    ArquivoXML::db_query($stmt);
    $totais = oci_fetch_object($stmt);

    return array($totais->VL_COBRADO, $totais->VL_PAGO, $totais->VL_GLOSA);
  }

  private function valorGuiaExame ($processo) {
    $stmt = oci_parse($this->db, "
      SELECT
        SUM(VL_COBRADO) VL_COBRADO, SUM(VL_PAGO) VL_PAGO, 
        SUM(VL_GLOSA) VL_GLOSA
      FROM (
        select
          NVL(sum(vl_total_cobrado), 0) VL_COBRADO,
          NVL(sum(vl_total_pago),0) VL_PAGO, 0 VL_GLOSA
        from asm_guia_exame
        where cd_convenio = 1 and nr_processo = :processo
        UNION ALL
        select 0, 0, NVL(SUM(gl.vl_glosa),0) VL_GLOSA
        from asm_glosa_guia gl, asm_guia_exame ge
        where gl.nr_guia = ge.nr_guia
          and gl.cd_convenio = ge.cd_convenio
          and ge.cd_convenio = 1
          and ge.nr_processo = :processo
          and gl.tp_guia = 'GE'
      )
    ");
    oci_bind_by_name($stmt, ':processo', $processo->NR_PROCESSO);
    ArquivoXML::db_query($stmt);
    $totais = oci_fetch_object($stmt);

    return array($totais->VL_COBRADO, $totais->VL_PAGO, $totais->VL_GLOSA);
  }

  private function valorGuiaInternacao ($processo) {
    $stmt = oci_parse($this->db, "
      SELECT
        SUM(VL_COBRADO) VL_COBRADO, SUM(VL_PAGO) VL_PAGO,
        SUM(VL_GLOSA) VL_GLOSA
      FROM (
        select
          NVL(sum(vl_total_cobrado), 0) VL_COBRADO,
          NVL(sum(vl_total_pago),0) VL_PAGO, 0 VL_GLOSA
        from asm_guia_internacao
        where cd_convenio = 1 and nr_processo = :processo
        UNION ALL
        select 0, 0, NVL(SUM(gl.vl_glosa),0) VL_GLOSA
        from asm_glosa_guia gl, asm_guia_internacao gi
        where gl.nr_guia = gi.nr_guia
          and gl.cd_convenio = gi.cd_convenio
          and gi.cd_convenio = 1
          and gi.nr_processo = :processo
          and gl.tp_guia = 'GI'
      )
    ");
    oci_bind_by_name($stmt, ':processo', $processo->NR_PROCESSO);
    ArquivoXML::db_query($stmt);
    $totais = oci_fetch_object($stmt);

    return array($totais->VL_COBRADO, $totais->VL_PAGO, 0);
  }

  private function valorGuiaHonorario ($processo) {
    $stmt = oci_parse($this->db, "
        select SUM(VL_COBRADO) VL_COBRADO, SUM(VL_PAGO) VL_PAGO
        FROM (
            select sum(c.vl_cobrado) vl_cobrado, sum(c.vl_pago) vl_pago
            from asm_esp_honorario_clinico c,asm_guia_honorario h
            where h.cd_convenio = 1 and h.nr_processo = :processo
              and c.nr_guia = h.nr_guia and c.cd_convenio = 1
            union all
            select SUM(f1.VL_COBRADO) VL_COBRADO, SUM(f1.VL_PAGO) VL_PAGO
            from asm_guia_honorario g1, asm_especialidade_honorario e1,
              asm_esp_honorario_funcao f1
            where g1.cd_convenio = 1 and g1.nr_processo = :processo
              and e1.CD_CONVENIO_HONORARIO = 1
              and e1.NR_GUIA_HONORARIO = g1.NR_GUIA
              and f1.NR_SEQUENCIAL_ESP = e1.NR_SEQUENCIAL
          )
      ");
    oci_bind_by_name($stmt, ':processo', $processo->NR_PROCESSO);
    ArquivoXML::db_query($stmt);
    $totais = oci_fetch_object($stmt);

    return array($totais->VL_COBRADO, $totais->VL_PAGO, 0);
  }

  /**
   * adicionarDadosGuia 
   *
   * @TODO
   * @param mixed $xml 
   * @return void
   */
  private function adicionarDadosGuia (&$xml) {
    // Seleciona as guias

    // Se nao tiver nada, retorna
    
    // Se tiver, adiciona os dados para cada guia.
    //foreach ($row = oci_fetch_object($rs)) {
    //  $guia = $xml->addChild('detalheguia');
    //  $ident = $guia->addChild('identificacaoGuia');
    //}

  }
}

?>
