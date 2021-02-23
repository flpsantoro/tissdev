<?php

class TISSException  extends Exception {
  public $tiss_message;
  public $tiss_code;
  public $tiss_obs;

  public function __construct() {
    $message = $this->tiss_message;
    $code    = $this->tiss_code;
    $obs     = $this->tiss_obs;

    if (TESTING) {
      print "ERRO: $message. (Code: $code) (OBS: $obs)\n";
    }
    parent::__construct($message, $code);
  }

  public function getObs() {
	  return $this->tiss_obs;
  }
}

class ErrorSchema extends TISSException {
  public $tiss_message = 'Não foi possível validar o arquivo XML.';
  public $tiss_code = 5002;
  public $tiss_obs = '';

  public function __construct() {
    $errors = libxml_get_errors();
    foreach ($errors as $error) {
        $this->tiss_obs .= $this->get_xml_error($error) . "\n\n";
    }
    libxml_clear_errors();
    parent::__construct();
  }

  private function get_xml_error($error) {
		$return = '';
    switch ($error->level) {
      case LIBXML_ERR_WARNING:
        $return .= "Warning $error->code: ";
        break;
       case LIBXML_ERR_ERROR:
        $return .= "Error $error->code: ";
        break;
      case LIBXML_ERR_FATAL:
        $return .= "Fatal Error $error->code: ";
        break;
    }

    $return .= trim($error->message);

    return $return;
  }
}

class ErrorHash extends TISSException {
  public $tiss_message = 'Código Hash inválido. Mensagem pode estar corrompida.';
  public $tiss_code = 5014;
  public $tiss_obs;

  public function __construct($obs) {
    $this->tiss_obs = $obs;
    parent::__construct();
  }
}

class ErrorXMLInvalido extends TISSException {
  public $tiss_message = 'Arquivo XML inválido';
  public $tiss_obs; //  = 'Arquivo XML inválido';
  public $tiss_code = 5002;
};

class ErrorSchemaInvalido extends TISSException {
  public $tiss_message = 'Versão do TISS inválida';
  public $tiss_obs = 'Verifique em nosso site as versões disponíveis';
  public $tiss_code = 5002;
};

class ErrorFrom_Invalid extends TISSException {
  public $tiss_message = 'CPF / CNPJ inválido';
  public $tiss_obs = 'CPF / CNPJ inválido';
  public $tiss_code = 1206;
};

class ErrorFrom_NaoEncontrado extends TISSException {
  public $tiss_message = 'Prestador não encontrado';
  public $tiss_obs; // = 'Prestador não encontrado';
  public $tiss_code = 1207;
};

class ErrorFrom_NotFound extends TISSException {
  public $tiss_message = 'Remetente não identificado';
  public $tiss_code = 5005;
}

class ErrorTo extends TISSException {
  public $tiss_message = 'Destinatário não identificado';
  public $tiss_code = 5006;
  public $tiss_obs = "Favor utilizar o campo 'registroANS' com o valor 417548";
}

class ErrorDB extends TISSException {
  public $tiss_message;
  public $tiss_code;
  public $tiss_obs;

  public function __construct($exception) {
    $this->tiss_message = $exception['message'];
    $this->tiss_code    = $exception['code'];
    $this->tiss_obs     = $exception['sqltext'];
    parent::__construct();
  }
}

class ErrorNrCarteira extends TISSException {
  public $tiss_message = 'Número da carteira inválido';
  public $tiss_code = 1001;

  public function __construct($obs = null) {
    $this->tiss_obs = $obs;
    parent::__construct();
  }
}

class ErrorGuiaJaApresentada extends TISSException {
  public $tiss_message = 'Guia já Aprensentada';
  public $tiss_code    = 1308;
  public $tiss_obs;

  public function __construct($obs = null) {
    $this->tiss_obs = $obs;
    parent::__construct();
  }
}

class ErrorPrestadorLogado extends TISSException {
  public $tiss_message = 'Prestador no XML diferente do logado no sistema!';
  public $tiss_code = 0;
}

class ExtensaoInvalida extends TISSException {
  public $tiss_message = 'Extensão de arquivo inválida! Somente aceitamos XML.';
  public $tiss_code = 0;
}

class ErrorLoteExistente extends TISSException {
  public $tiss_message = 'Lote já aprensentado';
  public $tiss_code    = 1308;
  public $tiss_obs;

  public function __construct($obs = null) {
    $this->tiss_obs = $obs;
    parent::__construct();
  }
}

class ErrorDiferenteGuias extends TISSException {
  public $tiss_message = 'Diferente tipo de guias no mesmo lote';
  public $tiss_code    = 1308;
  public $tiss_obs;

  public function __construct($obs = null) {
    $this->tiss_obs = $obs;
    parent::__construct();
  }
}

class ErrorTipoGuia extends TISSException {
  public $tiss_message = 'Tipo de guia não aceita pelo sistema!';
  public $tiss_code    = 1308;
  public $tiss_obs;

  public function __construct($obs = null) {
    $this->tiss_obs = $obs;
    parent::__construct();
  }
}



?>
