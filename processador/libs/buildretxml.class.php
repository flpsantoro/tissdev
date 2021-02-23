<?php

class BuildRetXML
{
  const xmlns = 'http://www.ans.gov.br/padroes/tiss/schemas';

  private $xml;
  private $dom;
  public $rxml;
  private $pxml;
  private $axml;
  private $e;
  private $sequencialTransacao;

  function __construct ( &$exception, &$xml, $seq, $axml) {
    $this->e = $exception;
    $this->pxml = &$xml;

    $skeleton = '<?xml version="1.0" encoding="ISO-8859-1"?><ans:mensagemTISS xmlns:ans="http://www.ans.gov.br/padroes/tiss/schemas" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.ans.gov.br/padroes/tiss/schemas http://www.ans.gov.br/padroes/tiss/schemas/tissV2_01_01.xsd"><ans:cabecalho/><ans:operadoraParaPrestador/><ans:epilogo/></ans:mensagemTISS>';

    $this->rxml = new SimpleXMLElement( $skeleton );
    $this->xml = $this->rxml->children( self::xmlns );
    $this->sequencialTransacao = $seq;
    $this->axml = $axml;
  }

  function buildHeader() {
    $ident = $this->xml->cabecalho->addChild( 'identificacaoTransacao');
    $ident->addChild('tipoTransacao', $this->getTipoTransacao());
    $ident->addChild('sequencialTransacao', $this->sequencialTransacao);
    $ident->addChild('dataRegistroTransacao', date('Y-m-d' ));
    $ident->addChild('horaRegistroTransacao', date('H:i:s' ));

    if (!is_null($this->e)) {
      $falha = $this->xml->cabecalho->addChild('falhaNegocio');
      $falha->addChild('codigoGlosa', (string) $this->e->getCode());
      $falha->addChild('descricaoGlosa', (string) $this->e->getMessage());

      $obs = $this->e->getObs();
      if (!empty($obs)) {
        $falha->addChild('observacao', (string) $obs);
      }
    }

    $origem = $this->xml->cabecalho->addChild( 'origem' );
    $origem->addChild( 'registroANS', 417548 );

    $dest = $this->xml->cabecalho->addChild( 'destino' );
    $cod = $dest->addChild( 'codigoPrestadorNaOperadora' );

    // $from =& $this->pxml->cabecalho->origem->codigoPrestadorNaOperadora;
    // $from_id = ArquivoXML::getCPF_CGC($from);
    $from_id = $this->axml->getOrigem();
    $cod->addChild( 'codigoPrestadorNaOperadora', $from_id );

    $this->xml->cabecalho->addChild( 'versaoPadrao', '2.01.02' );
  }

  function buildFooter(  ) {
    $hash = ArquivoXML::generateHash( $this->xml );
    $this->xml->epilogo->addChild( 'hash', $hash );
  }

  function init () {
    $this->buildHeader();
    //if (is_null($this->e)) {
      $this->buildBody();
    //}
    $this->buildFooter();
  }

  function getTipoTransacao() {
    $type = ( string ) $this->pxml->cabecalho->identificacaoTransacao->tipoTransacao;
    switch ( $type  ) {
      case 'ENVIO_LOTE_GUIAS':            return 'PROTOCOLO_RECEBIMENTO';
      case 'SOLIC_STATUS_PROTOCOLO':      return 'SITUACAO_PROTOCOLO';
      case 'SOLICITACAO_PROCEDIMENTOS':   return 'RESPOSTA_SOLICITACAO';
      case 'SOLICITA_STATUS_AUTORIZACAO': return 'STATUS_AUTORIZACAO';
      case 'VERIFICA_ELEGIBILIDADE':      return 'SITUACAO_ELEGIBILIDADE';
      case 'CANCELA_GUIA':                return 'CANCELAMENTO_GUIA_RECIBO';
      case 'SOLIC_DEMONSTRATIVO_RETORNO':
        return 'DEMONSTRATIVO_PAGAMENTO';
        return 'DEMONSTRATIVO_ANALISE_CONTA';
        return 'DEMONSTRATIVO_ODONTOLOGIA';

      case 'RE_APRENSENTACAO_GUIA':       return 'PROTOCOLO_RECEBIMENTO';
    }

  }

  function buildBody() {
    $type = (string) $this->pxml->cabecalho->identificacaoTransacao->tipoTransacao;

    switch ($type) {
      case 'ENVIO_LOTE_GUIAS':
        require_once dirname( __FILE__ ) . '/protocolorecebimento.class.php';

        $loteguias = new protocoloRecebimento( $this->sequencialTransacao,
                                               $this->xml,
                                               $this->pxml,
                                               is_null($this->e),
                                               $this->axml);

        $loteguias->processa();
        break;

      case 'SOLIC_STATUS_PROTOCOLO':
        require_once dirname( __FILE__ ) . '/statuslote.class.php';
        $statusprotocolo =  new statusProtocolo ($this->sequencialTransacao,
                                                 $this->xml,
                                                 $this->pxml);
        $statusprotocolo->processa();
        break;

      case 'SOLICITACAO_PROCEDIMENTOS':    break;
      case 'SOLICITA_STATUS_AUTORIZACAO':  break;
      case 'VERIFICA_ELEGIBILIDADE':       break;
      case 'CANCELA_GUIA':                 break;
      case 'SOLIC_DEMONSTRATIVO_RETORNO':  break;
      case 'RE_APRENSENTACAO_GUIA':        break;
    }
  }

}

?>
