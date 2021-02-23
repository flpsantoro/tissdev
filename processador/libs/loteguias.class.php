<?php

class LoteGuias
{
  private $xml;
  private $db;
  private $valor_total = 0;
  private $tp_guia;

  function __construct ( &$xml ) {
    global $db;

    $this->xml =& $xml;
    $this->db = &$db;
  }

  function getValorTotal() {
	  return $this->valor_total;
  }

  function getTpGuia() {
	  return $this->tp_guia;
  }

  function processa ($seq_importacao) {
    try {
      $loteGuias = &$this->xml->prestadorParaOperadora->loteGuias;
      $versao_3 = strpos((string)$this->xml->cabecalho->versaoPadrao, '3') === 0;

			if ($versao_3) {
				$guias = &$loteGuias->guiasTISS;
			} else {
				$guias = &$loteGuias->guias->guiaFaturamento;
			}

      if ( property_exists( $guias, 'guiaConsulta' ) ) {
        require_once dirname( __FILE__ ) . '/guia.consulta.class.php';
        $guia = new Guia_Consulta( $loteGuias, $versao_3 );
        $guia->processa($seq_importacao);
        $this->tp_guia = 'GC';
        $this->valor_total = $guia->getValorTotal();
      }
      elseif ( property_exists( $guias, 'guiaSP_SADT' ) || property_exists( $guias, 'guiaSP-SADT' )) {
        require_once dirname( __FILE__ ) . '/guia.exame.class.php';
        $guia = new Guia_Exame( $loteGuias, $versao_3 );
        $guia->processa($seq_importacao);
        $this->valor_total = $guia->getValorTotal();
        $this->tp_guia = 'GE';
      }
      elseif (property_exists($guias, 'guiaResumoInternacao')) {
        require_once dirname( __FILE__ ) . '/guia.resumointernacao.class.php';
        $guia = new Guia_ResumoInternacao( $loteGuias, $versao_3 );
        $guia->processa($seq_importacao);
        $this->valor_total = $guia->getValorTotal();
        $this->tp_guia = 'GI';
      }
      elseif (property_exists($guias, 'guiaHonorarioIndividual') || property_exists($guias, 'guiaHonorarios')) {
        require_once dirname( __FILE__ ) . '/guia.honorarioindividual.class.php';
        $guia = new Guia_HonorarioIndividual( $loteGuias, $versao_3 );
        $guia->processa($seq_importacao);
        $this->valor_total = $guia->getValorTotal();
        $this->tp_guia = 'GH';
      }
//      elseif (property_exists($guias, 'guiaOdontologia')) {
//      }
      else {
        throw new ErrorTipoGuia;
      }
    }
    catch (Exception $e) {
      throw $e;
    }
  }
}

?>
