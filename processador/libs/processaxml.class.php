<?php

class ProcessaXML
{
  private $xml;
  private $valor_total = 0;
  private $seq_importacao;
  private $tp_guia;

  function __construct(&$xml, $seq_importacao) {
    $this->xml = &$xml;
    $this->seq_importacao = $seq_importacao;
  }
  
  function getValorTotal() {
	  return $this->valor_total;
  }
  
  function getTpGuia() {
	  return $this->tp_guia;
  }

  function init () {
    $type = ( string ) $this->xml->cabecalho->identificacaoTransacao->tipoTransacao;
    
    try {
      switch ( $type ) {
        case 'ENVIO_LOTE_GUIAS':
          require_once dirname( __FILE__ ) . '/loteguias.class.php';
          $loteguias = new LoteGuias( $this->xml );
          $loteguias->processa( $this->seq_importacao  );
          $this->valor_total = $loteguias->getValorTotal();
          $this->tp_guia = $loteguias->getTpGuia();
          break;

        case 'SOLICITACAO_PROCEDIMENTOS':
        case 'SOLIC_STATUS_PROTOCOLO':
        case 'VERIFICA_ELEGIBILIDADE':
        case 'SOLICITA_STATUS_AUTORIZACAO':
        case 'SOLIC_DEMONSTRATIVO_RETORNO':
        case 'RE_APRENSENTACAO_GUIA':
        case 'CANCELA_GUIA':
			throw new Exception('Tipo de transação inválido!');
			break;
      }
    }
    catch (Exception $e) {
      throw $e;
    }
  }

}

?>
