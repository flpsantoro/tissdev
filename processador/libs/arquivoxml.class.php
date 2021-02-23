<?php

require_once dirname( __FILE__ ) . '/exceptions.class.php';
require_once dirname( __FILE__ ) . '/processaxml.class.php';
require_once dirname( __FILE__ ) . '/buildretxml.class.php';

if ($_SERVER['HTTP_HOST'] !== 'tiss.fiosaude.org.br') {
	define('XSD_PATH', '/home/felipe/public_html/tiss/processador/schemas/tissV%s.xsd');
} else {
	define('XSD_PATH', '/var/www/tiss/processador/schemas/tissV%s.xsd');
}

function libxml_display_error($error) {
     $return = "<br/>\n";
     switch ($error->level) {
         case LIBXML_ERR_WARNING:
             $return .= "<b>Warning $error->code</b>: ";
             break;
         case LIBXML_ERR_ERROR:
             $return .= "<b>Error $error->code</b>: ";
             break;
         case LIBXML_ERR_FATAL:
             $return .= "<b>Fatal Error $error->code</b>: ";
             break;
     }
     $return .= trim($error->message);
     if ($error->file) {
         $return .=    " in <b>$error->file</b>";
     }
     $return .= " on line <b>$error->line</b>\n";

     return $return;
}

function libxml_display_errors() {
     $errors = libxml_get_errors();
     foreach ($errors as $error) {
         print libxml_display_error($error);
     }
     libxml_clear_errors();
}

class ArquivoXML
{
  const schema = XSD_PATH;
  //const schema = '/home/felipe/public_html/tiss/processador/schemas/tissV%s.xsd';
  //const schema = '/var/www/tiss/processador/schemas/tissV%s.xsd';
  const xmlns = 'http://www.ans.gov.br/padroes/tiss/schemas';

  private $versoes = array('2.02.01', '2.02.02', '2.02.03', '3.02.00');
  private $versao;
  private $versao_3;
  private $dom;
  private $xml;
  private $rxml;
  private $db;
  private $file;
  private $valor_total = 0;
  private $seq_importacao;

  public $CPF_CGC;

  public function __construct ( $file ) {
    global $db;

    libxml_use_internal_errors( true );

    $opts = array('http' => array('user_agent' => 'PHP libxml agent'));
    $context = stream_context_create($opts);
    libxml_set_streams_context($context);

		$this->file = $file;
    $this->dom = new DOMDocument('1.0', 'iso-8859-1');
    $this->dom->load( $file );
    $this->dom->normalizeDocument();
    $this->xml = @simplexml_import_dom( $this->dom );

    if (is_null($this->xml)) {
			throw new ErrorXMLInvalido;
		}
    $this->xml = $this->xml->children( self::xmlns );
    $this->db = &$db;
  }

  public function getOrigem() {
		if ($this->versao_3) {
			return ArquivoXML::getCPF_CGC($this->xml->cabecalho->origem->identificacaoPrestador);
		} else {
			return ArquivoXML::getCPF_CGC($this->xml->cabecalho->origem->codigoPrestadorNaOperadora);
		}
	}

  public function getValorTotal() {
	  return $this->valor_total;
  }

  public function getSeqImportacao() {
	  return $this->seq_importacao;
  }

  /**
   * processa
   *
   * @return void
   */
  public function processa ($ret_dir, &$msg) {
		$ret = true;
		$except = null;

    try {
      $this->validations();
      $this->processFile();
      $this->buildRetFile();

      $this->db_commit();
    }
    catch ( ErrorFrom $e ) {
      return false;
    }
    catch ( ErrorDB $e ) {
			$ret = false;
      $this->db_rollback();
      throw $e;
    }
    catch ( Exception $e ) {
      $this->db_rollback();
      $ret = false;

      if ($e instanceof TISSException) {
				$msg = $e->getObs();
			}

			if (($e instanceof ErrorSchema)
				|| ($e instanceof ErrorSchemaInvalido)
				|| ($e instanceof ErrorXMLInvalido)) {
				/* Erro no XML, recusa o arquivo */
				throw $e;
			}

      try {
        $this->buildRetFile($e);
        $this->db_commit();
      }
      catch (ErrorDB $e) {
        throw $e;
      }

      $except = $e;
    }

    $this->saveReturnFile($ret_dir);
    $this->db_commit();

		/* Erro no processamento */
    if ($except && $except instanceof TISSException) {
			throw $except;
		}

    return $ret;
  }

  private function db_commit() {
    if (TESTING) {
      $this->db_rollback();
    }
    else {
      oci_commit($this->db);
    }
  }

  private function db_rollback() {
    oci_rollback($this->db);
  }

  public function db_query (&$stmt) {
    if (!@oci_execute($stmt, OCI_DEFAULT)) {
      throw new ErrorDB(oci_error($stmt));
    }
    return true;
  }

  public function getUF($codigo) {
		$uf = array(
				0  => null,
				11 => 'RO',
				12 => 'AC',
				13 => 'AM',
				14 => 'RR',
				15 => 'PA',
				16 => 'AP',
				17 => 'TO',
				21 => 'MA',
				22 => 'PI',
				23 => 'CE',
				24 => 'RN',
				25 => 'PB',
				26 => 'PE',
				27 => 'AL',
				28 => 'SE',
				29 => 'BA',
				31 => 'MG',
				32 => 'ES',
				33 => 'RJ',
				35 => 'SP',
				41 => 'PR',
				42 => 'SC',
				43 => 'RS',
				50 => 'MS',
				51 => 'MT',
				52 => 'GO',
				53 => 'DF',
				98 => 'EX');

		return $uf[intval($codigo)];
	}

	public function getConselho($codigo) {
			$siglas = array(
				0  => null,
				1  => 'CRAS',
				2  => 'COREN',
				3  => 'CRF',
				4  => 'CRFA',
				5  => 'CREFITO',
				6  => 'CRM',
				7  => 'CRN',
				8  => 'CRO',
				9  => 'CRP',
				10 => 'OUT');

		return $siglas[intval($codigo)];
	}

	public function getTecnicaUtilizada($codigo) {
		$tecnicas = array(0 => null, 1 => 'C', 2 => 'V', 3 => 'R');

		return $tecnicas[intval($codigo)];
	}

	public function getTipoFaturamento($codigo) {
		$tipo = array(0 => null, 1 => 'P', 2 => 'F', 3 => 'C', 4 => 'T');

		return $tipo[intval($codigo)];
	}

	public function getViaAcesso($codigo) {
		$tipo = array(0 => null, 1 => 'U', 2 => 'M', 3 => 'D');

		return $tipo[intval($codigo)];
	}

	public function getCaraterAtendimento($codigo) {
	  $carater = array(1 => 'E', 2 => 'U');

	  return $carater[intval($codigo)];
	}

  public function getCPF_CGC(&$node, $ret_zero = false) {
    if ( $node->CNPJ ) {
      $cpfcgc = ( string ) $node->CNPJ;
    }
    elseif ( $node->cpf ) {
      $cpfcgc = ( string ) $node->cpf;
    }
    elseif ($node->CPF) {
			$cpfcgc = (string) $node->CPF;
    }
    elseif ( $node->codigoPrestadorNaOperadora ) {
      $cpfcgc = ( string ) $node->codigoPrestadorNaOperadora;
		}
    elseif ( $node->cnpjPagador ) {
			$cpfcgc = ( string ) $node->cnpjPagador;
		}
		elseif ( $node->cpfContratado ) {
			$cpfcgc = (string) $node->cpfContratado;
		}
		elseif ( $node->cnpjContratado ) {
			$cpfcgc = (string) $node->cnpjContratado;
    }
    elseif ( $node->cnpjLocalExecutante ) {
			$cpfcgc = (string) $node->cnpjLocalExecutante;
		}
		elseif ( $node->codigonaOperadora ) {
			$cpfcgc = (string) $node->codigonaOperadora;
		}
		elseif ( $node->codigoNaOperadora ) {
			$cpfcgc = (string) $node->codigoNaOperadora;
		}
    else {
      $cpfcgc = 0;
    }

		if (empty($cpfcgc)) {
			return $ret_zero ? 0 : false;
		}

    return ArquivoXML::numero($cpfcgc);
  }

  public function simNao (&$node) {
    return (boolean) $node ? 'S' : 'N';
  }

  public function numeroCarteira (&$node) {
    $carteira = (string) str_replace('.', '', (string) $node);
    if (!is_numeric($carteira)) {
      throw new ErrorNrCarteira($carteira);
    }
    return $carteira;
  }

  public function numero ($valor) {
    return preg_replace('/[^0-9]*/', '', $valor);
  }

/*{{{ Validations*/

  /**
   * validations
   *
   * @return void
   */
  private function validations () {
    try {
      $this->schemaValidate();
      $this->fromValidate();
      $this->toValidate();
      $this->hashValidate();
      $this->tpGuiaValidate();
      $this->loteValidate();
    }
    catch ( Exception $e ) {
      throw $e;
    }
  }

  private function loteValidate() {
		$nr_lote = (string)$this->xml->prestadorParaOperadora->loteGuias->numeroLote;

    $stmt = oci_parse( $this->db, "
      SELECT COUNT(*) EXISTE
      FROM TISS_TRANSACAO_PROTOCOLO
      WHERE CPF_CGC = :from_id
        AND NR_LOTE = :nr_lote
        AND CD_STATUS_PROTOCOLO NOT IN (0, 4)"
    );
    oci_bind_by_name( $stmt, ':from_id', $this->CPF_CGC );
    oci_bind_by_name( $stmt, ':nr_lote', $nr_lote );
    $this->db_query($stmt);
    $row = oci_fetch_object( $stmt );

    if ( $row->EXISTE == "0" ) return;

    throw new ErrorLoteExistente($nr_lote);
  }

  private function tpGuiaValidate() {
		$tp_guias = array();
		$this->xml->registerXPathNamespace('a', self::xmlns);

    foreach ($this->xml->xpath('//a:loteGuias') as $lote) {
			$lote->registerXPathNamespace('a', self::xmlns);
			list($num) = $lote->xpath('//a:numeroLote');
			$num = (string) $num;

			foreach ($lote->xpath('//a:guias/a:guiaFaturamento/a:guiaConsulta|//a:guias/a:guiaFaturamento/a:guiaSP_SADT|//a:loteGuias/a:guias/a:guiaFaturamento/a:guiaResumoInternacao|//a:loteGuias/a:guias/a:guiaFaturamento/a:guiaHonorarioIndividual') as $xml) {
				$tp_guias[$num][$xml->getName()] = 1;
			}
		}

		foreach ($tp_guias as $lote) {
			if (count($tp_guias) > 1) {
				throw new ErrorDiferenteGuias;
			}
		}
  }

  /**
   * schemaValidate
   *
   * @access public
   * @return void
   */
  function schemaValidate () {
		$this->versao = $versao = (string) $this->xml->cabecalho->versaoPadrao;
		$this->versao_3 = strpos($this->versao, '3') === 0;

		if (!in_array($versao, $this->versoes)) {
			throw new ErrorSchemaInvalido();
		}

		if (TESTING) {
			echo "[+] Versão do Schema: " . $versao . PHP_EOL;
		}

		$this->schema_file = sprintf(self::schema, str_replace('.', '_', $versao));

		$rs = $this->dom->schemaValidate($this->schema_file);
		if ( !$rs ) {
			//libxml_display_errors();
			throw new ErrorSchema();
    }
  }

  /**
   * hashValidate
   *
   * @access public
   * @return void
   */
  function hashValidate () {
    $hash = strtolower( $this->generateHash($this->xml) );
    $hashonfile = strtolower( $this->xml->epilogo->hash );
    if ( $hash != $hashonfile ) {
      throw new ErrorHash( "Hash invalido! $hash" );
    }
  }

  /**
   * generateHash
   *
   * @param mixed $node
   * @access public
   * @return void
   */
  function generateHash ( &$node = null ) {
    //$msg = self::_generateHash( $node );
    $msg = self::atributosConcatenados($node->cabecalho);
    $msg .= self::atributosConcatenados($node->prestadorParaOperadora);
    $msg = utf8_decode( $msg );
    //print "Hash calculado sobre: /$msg/\n";

    return md5( $msg );
  }

  function atributosConcatenados($node) {
    $resp = '';

    foreach ($node as $item) {
      $resp .= self::atributosConcatenados($item);
    }

    if (empty($resp)) {
      $resp .= str_replace("\n", '', (string) $node);
      if (trim($resp) === '') {
				return '';
			}
    }

    return $resp;
  }

  function _generateHash($node) {
    $msg = '';
    foreach ( $node as $item ) {
      if ( $item->getName() == 'hash' ) { continue; }
      //$data = trim((string) $item);
      //$data = preg_replace('/^ *| *$/', '', $item);
      $data = str_replace("\n", '', (string) $item);
      $msg .= !empty($data) ? $data : self::_generateHash($item);
    }

    if( !$msg ) {
      //$msg = trim( ( string ) $node );
      $msg = (string) $node;
    }

    return $msg;
  }

  /**
   * fromValidate
   *
   * @access public
   * @return void
   */
  function fromValidate () {
		if ($this->versao_3) {
			$from =& $this->xml->cabecalho->origem->identificacaoPrestador;
		} else {
			$from =& $this->xml->cabecalho->origem->codigoPrestadorNaOperadora;
		}

    if ( $from->CNPJ ) {
      $from_id = ( string ) $from->CNPJ;
    }
    elseif ( $from->cpf ) {
      $from_id = ( string ) $from->cpf;
		}
    elseif ( $from->CPF ) {
      $from_id = ( string ) $from->CPF;
    }
    elseif ( $from->codigoPrestadorNaOperadora ) {
      $from_id = ( string ) $from->codigoPrestadorNaOperadora;
    }
    else {
      throw new ErrorFrom_Invalid();
    }

    if ( empty( $from_id ) ) {
      throw new ErrorFrom_NotFound();
    }

    $this->CPF_CGC = $from_id;

		if ($from_id != $_SESSION['cpf_cnpj']) {
			throw new ErrorPrestadorLogado();
		}

    $stmt = oci_parse( $this->db, "
      SELECT COUNT(*) EXISTE
      FROM TISS_USUARIO
      WHERE CPF_CNPJ = :from_id
        AND TP_USUARIO = 1
        AND ID_STATUS = 'A'"
    );
    oci_bind_by_name( $stmt, ':from_id', $from_id );
    $this->db_query($stmt);
    $row = oci_fetch_object( $stmt );

    if ( $row->EXISTE == "1" ) return;

    throw new ErrorFrom_NaoEncontrado;
  }

  /**
   * toValidate
   *
   * @access public
   * @return void
   */
  function toValidate () {
    $to_id = (string) $this->xml->cabecalho->destino->registroANS;

    if (empty($to_id)) {
			if (isset($this->xml->cabecalho->destino->codigoPrestadorNaOperadora)) {
				$to_id = ArquivoXML::getCPF_CGC($this->xml->cabecalho->destino->codigoPrestadorNaOperadora);
			} else {
				$to_id = (string) $this->xml->cabecalho->destino->cnpjPagador;
			}
		}

    $to_id = preg_replace('/[^0-9]+/', '', $to_id);

    if ($to_id === '417548' || $to_id === '03033006000153') return;

    throw new ErrorTo();
  }


  /*}}}*/

  /**
   * processFile
   *
   * @return void
   */
  private function processFile() {
    $stmt = oci_parse($this->db, "SELECT TISS_S_IMPORTACAO.NEXTVAL NEXTVAL FROM DUAL");
    $this->db_query($stmt);
    $row = oci_fetch_object($stmt);
    $this->seq_importacao = $row->NEXTVAL;
    oci_free_statement($stmt);

    try {
      $fxml = new ProcessaXML( $this->xml, $this->seq_importacao );
      $fxml->init();

      $this->valor_total = $fxml->getValorTotal();
      $this->tp_guia = $fxml->getTpGuia();
    }
    catch (Exception $e) {
      throw $e;
    }
  }

  private function startTransaction($exception) {
    $stmt = oci_parse($this->db, "SELECT TISS_S_HISTORICO_TRANSACAO.NEXTVAL NEXTVAL FROM DUAL");
    $this->db_query($stmt);
    $row = oci_fetch_object($stmt);
    $seq_transacao = $row->NEXTVAL;
    oci_free_statement($stmt);

    $sql = "INSERT INTO TISS_HISTORICO_TRANSACAO (NR_SEQUENCIAL, CPF_CGC, DT_TRANSACAO, TP_TRANSACAO, MSG_TRANSACAO, CD_GLOSA_TISS)
      VALUES (:NR_TRANSACAO, :CPF_CGC, :DT_TRANSACAO, :TP_TRANSACAO, :MSG_TRANSACAO, :CD_GLOSA_TISS)";
    $stmt = oci_parse($this->db, $sql);

    $header =& $this->xml->cabecalho->identificacaoTransacao;

    $dt_transacao = date('d-M-Y', strtotime((string) $header->dataRegistroTransacao));
    $tp_transacao = (string) $header->tipoTransacao;

    if (is_null($exception)) {
      $msg_transacao = 'Transacao OK';
      $cd_glosa = NULL;
    }
    else {
      $msg_transacao = $exception->tiss_obs;
      $cd_glosa = $exception->getCode();
    }

    oci_bind_by_name($stmt, ':NR_TRANSACAO', $seq_transacao);
    oci_bind_by_name($stmt, ':CPF_CGC', $this->CPF_CGC);
    oci_bind_by_name($stmt, ':DT_TRANSACAO', $dt_transacao);
    oci_bind_by_name($stmt, ':TP_TRANSACAO', $tp_transacao);
    oci_bind_by_name($stmt, ':CD_GLOSA_TISS', $cd_glosa);
    oci_bind_by_name($stmt, ':MSG_TRANSACAO', $msg_transacao);

    $this->db_query($stmt);

    oci_free_statement($stmt);

    return $seq_transacao;
  }

  /**
   * buildRetFile
   *
   * @param mixed $exception
   * @return void
   */
  private function buildRetFile( $exception = NULL ) {
    $seq_transacao = $this->startTransaction($exception);

    $rxml = new BuildRetXML ( $exception, $this->xml, $seq_transacao, $this );
    $rxml->init();

    $dom = dom_import_simplexml( $rxml->rxml )->ownerDocument;

		if (!$this->schema_file) {
			$this->schema_file = sprintf(self::schema, str_replace('.', '_', '2.02.01'));
		}
    $rs = $dom->schemaValidate( $this->schema_file );

    $this->rxml = & $rxml->rxml;
  }

  /**
   * saveReturnFile
   *
   * @return void
   */
  private function saveReturnFile($ret_dir) {
    $this->rxml->registerXPathNamespace('a', self::xmlns);
    $e = $this->rxml->xpath('//a:codigoPrestadorNaOperadora/a:codigoPrestadorNaOperadora|//a:sequencialTransacao|//a:hash');

    $seq  = (string) $e[0];
    $cod  = (string) $e[1];
    $hash = (string) $e[2];

    $path = $ret_dir . $_SESSION['cpf_cnpj'] .'/';
    $file_name = str_pad( $seq, 11, '0', STR_PAD_LEFT ) . '_' . $hash . '.xml';
    $ret_file = $path . $file_name;
    $orig_file = basename($this->file);

    if ( !file_exists( $path ) ) {
      mkdir( $path );
    }

    if (TESTING) {
      print $this->rxml->asXML();
    }
    else {
			if (TESTING) {
				echo "[+] Arquivo de retorno: " . $ret_file . PHP_EOL;
			}
			file_put_contents( $ret_file, $this->rxml->asXML(  ) );
    }

    $sql = "UPDATE TISS_TRANSACAO_PROTOCOLO
				SET XML_RETORNO = :ARQUIVO,
					XML_ENVIADO = :ARQUIVO2,
					VERSAO = :VERSAO
			WHERE NR_SEQUENCIAL_TRANSACAO = :NR_SEQUENCIAL_TRANSACAO";
    $stmt = oci_parse($this->db, $sql);

    oci_bind_by_name($stmt, ':ARQUIVO',  $file_name);
    oci_bind_by_name($stmt, ':ARQUIVO2', $orig_file);
    oci_bind_by_name($stmt, ':VERSAO',   $this->versao);
    oci_bind_by_name($stmt, ':NR_SEQUENCIAL_TRANSACAO', $seq);

    ArquivoXML::db_query($stmt);
    oci_free_statement($stmt);
  }
}

?>
