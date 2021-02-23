<?php
if (!isset($_SESSION['logado'])) return; 
require_once dirname(__FILE__) .'/config.php';

$_SESSION['dados'] = null; 
$_SESSION['nrLote'] ='-';

$dados = null;
$nrGuiaPrestador = null;
$seqImportacao = null;

if(isset($_POST['botao']) && $_POST['botao']=='Salvar')
{
	try 
	{
		echo '<font color="red">====>>>> ValidadeCarteira no POST: ',  $_POST['validadeCarteira'], '</font>' ."\n";
		$_SESSION['dados']['NR_GUIA'] = $_POST['nrGuiaAtrOperador'];
		
		$_SESSION['dados']['NR_CARTEIRA'] = $_POST['nrCarteira'];
		$_SESSION['dados']['DATA_ATENDIMENTO'] = $_POST['dataAtendimento'];
		$_SESSION['dados']['DT_VALIDADE'] = $_POST['validadeCarteira'];
		$_SESSION['dados']['SN_ATENDIMENTO_RN'] = $_POST['atendimentoRN'];
		$_SESSION['dados']['NR_CNS'] = $_POST['nrCartaoNacionalSaude'];
		$_SESSION['dados']['CD_CNES'] = $_POST['codigoCNES'];
		$_SESSION['dados']['CnpjCpf'] = $_SESSION['cpf_cnpj'];
		$_SESSION['dados']['nomeContrato'] = $_SESSION['nomefantasia'];
		$_SESSION['dados']['CD_CBO'] =  $_POST['cbo'];
		
		//$conselhoProfissional = $_POST['conselhoProfissional'];		
		$_SESSION['dados']['conselhoProfissional'] = 5265377;		

		
		$_SESSION['dados']['VL_TOTAL'] = $_POST['valorProcedimento'];
		$_SESSION['dados']['DATA_ATENDIMENTO'] = $_POST['dataAtendimento'];		

		$_SESSION['dados']['CD_TIPO_CONSULTA'] = $_POST['tipoConsulta'];
		$_SESSION['dados']['DS_OBS'] = $_POST['observacao'];
		$_SESSION['dados']['NR_LOTE'] = $_POST['lote'];		
		$_SESSION['dados']['CD_ESPECIALIDADE'] = $_POST[cdEspecialidade];

		$_SESSION['nrLote'] =  $_POST['lote'];

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$nrGuiaAtrOperador = $_POST['nrGuiaAtrOperador'];
		$nrCarteira = $_POST['nrCarteira'];
		$validadeCarteira = $_POST['validadeCarteira'];
		echo '<font color="red">====>>>> ValidadeCarteira: ',  $validadeCarteira, '</font>' ."\n";
		$atendimentoRN = $_POST['atendimentoRN'];
		$nrCartaoNacionalSaude = $_POST['nrCartaoNacionalSaude'];
		$codigoCNES = $_POST['codigoCNES'];
		$CnpjCpf = $_SESSION['cpf_cnpj'];
		$nomeContrato = $_SESSION['nomefantasia'];
		$cbo =  $_POST['cbo'];
		$nomeProfissionalExec = ($_SESSION['tp_credenciado'] == 'F') ? $_SESSION['nomefantasia'] : '';
		$numeroConselho = $_POST['numeroConselho'];
		$uf = $_POST['uf'];
		//$conselhoProfissional = $_POST['conselhoProfissional'];
		$conselhoProfissional = 5265377;
		$indAcidente = 9;
		
		//$nrGuiaPrestador = $_POST['nrGuiaPrestador'];
		$nrGuiaPrestador = $_POST['nrGuiaAtrOperador'];
		$valorProcedimento = $_POST['valorProcedimento'];
		$dataAtendimento = $_POST['dataAtendimento'];	
		$tipoConsulta = $_POST['tipoConsulta'];
		$observacao = $_POST['observacao'];
		$nrLote = $_POST['lote'];
		$cdEspecialidade = $_POST['cdEspecialidade'];

		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$conn->beginTransaction();				

	   	
	   	/** Comentado por Noel Rocha - 04/09/2017
	   	if(isset($_SESSION['seqImportacao']))
	   	{
	   		$seq = $_SESSION['seqImportacao'];	

	   		$sql = "DELETE FROM TISS_TRANSACAO_PROTOCOLO_DIG D WHERE D.NR_SEQ_IMPORTACAO =".$seq;	   		
	   		$stmt = $conn->prepare($sql);
	    	$stmt->execute();	
	   	}
		*/

		if ($nrLote == "-")
		{
			$sql = "SELECT MAX(NR_LOTE) NR_LOTE FROM TISS_TRANSACAO_PROTOCOLO_DIG ";
		    $stmt = $conn->prepare($sql);
		    $stmt->execute();
		    $stmt->bindColumn("NR_LOTE", $nrLote);
		    $row = $stmt->fetchAll();
		    $nrLote = $nrLote + 1;
		}
		#--------------------------------------------------------------------------------------------------
		#echo '<font color="red">====>>>> Início da verificação do número da guia do prestador!', '</font>' . "\n";
		#echo '<font color="red">====>>>> Guia: ',  $nrGuiaAtrOperador, '</font>' ."\n";	

		$sqlGuia = "SELECT COUNT(*) QTD_GUIA
            	      FROM TISS_GUIA_CONSULTA_DIG
		             WHERE CPF_CGC = :CPF_CGC
		               AND NR_GUIA = :NR_GUIA";
		$stmtGuia = $conn->prepare($sqlGuia);
		$stmtGuia->execute(array(':CPF_CGC' => $CnpjCpf,
		                         ':NR_GUIA' => $nrGuiaAtrOperador));
		$stmtGuia->bindColumn("QTD_GUIA", $qtdGuia);
		$row = $stmtGuia->fetchAll();
		
		if ($qtdGuia != 0)
		{
			 print '<script>alert("Esta Guia já foi utilizada anteriormente!");</script>';
			 $conn->rollback();
		} else 
		{
		
		$sql = "SELECT COUNT(*) NR_COUNT
            	  FROM TISS_GUIA_CONSULTA_DIG
		         WHERE CPF_CGC = :CPF_CGC
		           AND NR_PROCESSO_REFERENCIA = :NR_LOTE";
		#echo '<font color="red">====>>>> sql: ',  $sql, '</font>' ."\n";
	    #echo '<font color="red">====>>>> cpf_cgc: ', $CnpjCpf, ' Lote: ', $nrLote, '</font>' ."\n";
		$stmt3 = $conn->prepare($sql);
		$stmt3->execute(array(':CPF_CGC' => $CnpjCpf,
		                      ':NR_LOTE' => $nrLote));
		$stmt3->bindColumn("NR_COUNT", $nrCountGuiaDig);
		$row = $stmt3->fetchAll();
		#echo '<font color="red">====>>>> Qtde: ',  $nrCountGuiaDig, '</font>' ."\n";
		#--------------------------------------------------------------------------------------------------
		if ($nrCountGuiaDig == 0) {
	    #echo '<font color="red">====>>>> Qtde: ',  $nrCount ,' cpf_cgc: ', $CnpjCpf, ' Lote: ', $nrLote, '</font>' ."\n";
			$sql = "SELECT TISS_S_IMPORTACAO.NEXTVAL FROM DUAL";
			$stmt = $conn->prepare($sql);
			$stmt->execute();
			$stmt->bindColumn("NEXTVAL", $sequencialImportacao);
			$row = $stmt->fetchAll();
		} else {
		
		$sql = "SELECT NR_SEQ_IMPORTACAO NR_SEQ_IMPORTACAO_OLD
            	  FROM TISS_GUIA_CONSULTA_DIG
		         WHERE CPF_CGC = :CPF_CGC
		           AND NR_PROCESSO_REFERENCIA = :NR_LOTE";
		$stmt3 = $conn->prepare($sql);
		$stmt3->execute(array(':CPF_CGC' => $CnpjCpf,
		                      ':NR_LOTE' => $nrLote));
		$stmt3->bindColumn("NR_SEQ_IMPORTACAO_OLD", $nrSeqImportacaoOld);
		$row = $stmt3->fetchAll();
        $sequencialImportacao = $nrSeqImportacaoOld;
		}

		$stmt = $conn->prepare("insert into TISS_GUIA_CONSULTA_DIG (NR_GUIA,                   
											DT_EMISSAO,                
											NR_CARTEIRA,               
											NR_CNS,                    
											CPF_CGC,                   
											CD_CNES,                  
											NM_PROFISSIONAL_EXECUTANTE,
											CD_CONSELHO,               
											NR_CONSELHO,               
											CD_UF_CONSELHO,            
											CD_CBO,                    
											CD_INDICADOR_ACIDENTE,     
											DT_EVENTO,                 
											CD_TABELA,                 
											CD_ESPECIALIDADE,          
											CD_TIPO_CONSULTA,          
											DS_OBS,                    
											NR_PROCESSO_REFERENCIA,    
											DT_RECEBIMENTO,            
											NR_GUIA_PRESTADOR,         
											DT_IMPORT_SISTEMA,         
											CD_USUARIO_IMPORT_SISTEMA, 
											NR_SEQ_IMPORTACAO,         
											SN_ATENDIMENTO_RN,         
											VL_CONSULTA,               
											DT_VALIDADE_CARTEIRA)
						values
											
											(:NR_GUIA,                   
											 SYSDATE,                
											:NR_CARTEIRA,               
											:NR_CNS,                    
											:CPF_CGC,                   
											:CD_CNES,                   
											:NM_PROFISSIONAL_EXECUTANTE,
											:CD_CONSELHO,               
											:NR_CONSELHO,               
											:CD_UF_CONSELHO,            
											:CD_CBO,                    
											:CD_INDICADOR_ACIDENTE,     
											TO_DATE(:DT_EVENTO,'YYYY-MM-DD'),                 
											:CD_TABELA,                 
											:CD_ESPECIALIDADE,
											:CD_TIPO_CONSULTA,
											:DS_OBS,
											:NR_PROCESSO_REFERENCIA,
											SYSDATE,
											:NR_GUIA_PRESTADOR,
											SYSDATE,
											:CD_USUARIO_IMPORT_SISTEMA,
											:NR_SEQ_IMPORTACAO,
											:SN_ATENDIMENTO_RN,
											:VL_CONSULTA,
											TO_DATE(:DT_VALIDADE_CARTEIRA,'DD/MM/YYYY'))");

		$stmt->bindValue(':NR_GUIA',$nrGuiaAtrOperador);
		//$stmt->bindValue(':DT_EMISSAO','sysdate');                                
		$stmt->bindValue(':NR_CARTEIRA',$nrCarteira);
		$stmt->bindValue(':NR_CNS',$nrCartaoNacionalSaude);
		$stmt->bindValue(':CPF_CGC',$CnpjCpf);
		$stmt->bindValue(':CD_CNES',$codigoCNES);
		$stmt->bindValue(':NM_PROFISSIONAL_EXECUTANTE', ($_SESSION['tp_credenciado'] == 'F') ? $_SESSION['nomefantasia'] : '' );
		//$stmt->bindValue(':CD_CONSELHO', $conselhoProfissional);
		$stmt->bindValue(':CD_CONSELHO', 5265377);
		$stmt->bindValue(':NR_CONSELHO',$numeroConselho);
		$stmt->bindValue(':CD_UF_CONSELHO',strtoupper($uf));
		$stmt->bindValue(':CD_CBO',$cbo);
		$stmt->bindValue(':CD_INDICADOR_ACIDENTE',$indAcidente);
		$stmt->bindValue(':DT_EVENTO',$dataAtendimento);                
		$stmt->bindValue(':CD_TABELA','22');                
		$stmt->bindValue(':CD_ESPECIALIDADE',$cdEspecialidade);        
		$stmt->bindValue(':CD_TIPO_CONSULTA',$tipoConsulta);
		$stmt->bindValue(':DS_OBS',$observacao);        
		$stmt->bindValue(':NR_PROCESSO_REFERENCIA',$nrLote);
		//$stmt->bindValue(':NR_PROCESSO_REFERENCIA','0');  
		$stmt->bindValue(':NR_GUIA_PRESTADOR',$nrGuiaPrestador);  
		//$stmt->bindValue(':DT_IMPORT_SISTEMA','sysdate');  
		$stmt->bindValue(':CD_USUARIO_IMPORT_SISTEMA','0'); 
		$stmt->bindValue(':NR_SEQ_IMPORTACAO',$sequencialImportacao);
		$stmt->bindValue(':SN_ATENDIMENTO_RN',strtoupper($atendimentoRN));
		$stmt->bindValue(':VL_CONSULTA',$valorProcedimento);
		echo '<font color="red">====>>>> ValidadeCarteira Novamente: ',  $validadeCarteira, '</font>' ."\n";
		$stmt->bindValue(':DT_VALIDADE_CARTEIRA',$validadeCarteira);
		$stmt->execute();
		
		////////////////////////////////////////////
		/////// TISS_TRANSACAO_PROTOCOLO_DIG ///////
		////////////////////////////////////////////
        
		# Inicialmente devemos verificar se há registros nesta tabela para este cpf_cgc e número de lote
		# Caso negativo, Qtde = 0, devemos inserir um registro aqui nesta tabela para servir como cabeçalho.
		# Caso positivo, Qtde > 0, NÃO precisamos inserir mais registros nesta tabela pois o cabeçalho já foi inserido.
		$sql = "SELECT COUNT(*) NR_COUNT
            	  FROM TISS_TRANSACAO_PROTOCOLO_DIG
		         WHERE CPF_CGC = :CPF_CGC
		           AND NR_LOTE = :NR_LOTE";
		$stmt1 = $conn->prepare($sql);
		
		#$stmt1->execute(array(':CPF_CGC' => $_SESSION['CPF_CGC']));
		$stmt1->execute(array(':CPF_CGC' => $CnpjCpf,
		                      ':NR_LOTE' => $nrLote));
		$stmt1->bindColumn("NR_COUNT", $nrCount);
		$row = $stmt1->fetchAll();
	    #echo '<font color="red">====>>>> Qtde: ',  $nrCount ,' cpf_cgc: ', $CnpjCpf, ' Lote: ', $nrLote, '</font>' ."\n";
		#			          ':nr_lote' => $_SESSION['nr_lote']));
		#$stmt1->execute(array(':cpf_cgc' => $_SESSION['cpf_cgc'],
		#			          ':nr_lote' => $_SESSION['nr_lote']));

		#$sql = "SELECT MAX(NR_COUNT) NR_LOTE FROM TISS_TRANSACAO_PROTOCOLO_DIG ";
		#$stmt1 = $conn->prepare($sql);

        #$total = $nrCount;
		#echo '<font color="red">====>>>> Qtde: ',  $nrCount ,' cpf_cgc: ', $CnpjCpf, ' Lote: ', $nrLote, '</font>' ."\n";
		if ($nrCount == 0) {
	    #echo '<font color="red">====>>>> Qtde: ',  $nrCount ,' cpf_cgc: ', $CnpjCpf, ' Lote: ', $nrLote, '</font>' ."\n";
		$sql = "SELECT TISS_S_HISTORICO_TRANSACAO.NEXTVAL FROM DUAL";
	    $stmt = $conn->prepare($sql);
	    $stmt->execute();
	    $stmt->bindColumn("NEXTVAL", $sequencialTransacao);
	    $row = $stmt->fetchAll();	    

	    $statusProtocolo = 5;
	    $cnpjCpf = $_SESSION['cpf_cnpj'];
	    $tpGuia = 'GC';
	    $dtEnvio = null;
	    $dtRecebimento = null;
	    $vlTotal = $valorProcedimento;
	    $versao = "3.02.00";
		# $cont = 0;
		/*$sql = "SELECT COUNT(*)
            	  FROM TISS_TRANSACAO_PROTOCOLO_DIG
		         WHERE CPF_CGC = :CPF_CGC
		           AND NR_LOTE = :NR_LOTE";
		$stmt = $conn->prepare($sql);*/
		#print $_SESSION['cpf_cgc'];
		#print $_SESSION['nr_lote'];
		#$stmt->execute(array(':cpf_cgc' => $_SESSION['cpf_cgc'],
		#			         ':nr_lote' => $_SESSION['nr_lote']));
        #$stmt->bindColumn("total_dig", $total);
		#$stmt->bindColumn(1, $total);
		#$row = $stmt->fetchAll();
	    #				
		$stmt = $conn->prepare('insert into 
			TISS_TRANSACAO_PROTOCOLO_DIG ( NR_SEQUENCIAL_TRANSACAO,
										    NR_PROTOCOLO_RECEBIMENTO,
											CD_STATUS_PROTOCOLO,    
											NR_LOTE,             
											CPF_CGC,             
											TP_GUIA,             
											DT_ENVIO,            
											DT_RECEBIMENTO,      
											VL_TOTAL,
											NR_SEQ_IMPORTACAO,
											VERSAO) values											
											(:NR_SEQUENCIAL_TRANSACAO,
											:NR_PROTOCOLO_RECEBIMENTO,
											:CD_STATUS_PROTOCOLO,    
											:NR_LOTE,             
											:CPF_CGC,             
											:TP_GUIA,             
											:DT_ENVIO,            
											:DT_RECEBIMENTO,      
											:VL_TOTAL,
											:NR_SEQ_IMPORTACAO,
											:VERSAO)');

		$stmt->bindValue(':NR_SEQUENCIAL_TRANSACAO',$sequencialTransacao);
		$stmt->bindValue(':NR_PROTOCOLO_RECEBIMENTO', null);
		$stmt->bindValue(':CD_STATUS_PROTOCOLO',$statusProtocolo);    
		$stmt->bindValue(':NR_LOTE',$nrLote);
		$stmt->bindValue(':CPF_CGC',$cnpjCpf);             
		$stmt->bindValue(':TP_GUIA',$tpGuia);            
		$stmt->bindValue(':DT_ENVIO',$dtEnvio);
		$stmt->bindValue(':DT_RECEBIMENTO',$dtRecebimento);
		$stmt->bindValue(':VL_TOTAL',$vlTotal);
		$stmt->bindValue(':NR_SEQ_IMPORTACAO',$sequencialImportacao);
		$stmt->bindValue(':VERSAO',$versao);
		$stmt->execute();
		}  else {
		$sqlTOT = "SELECT VL_TOTAL
            	  FROM TISS_TRANSACAO_PROTOCOLO_DIG
		         WHERE CPF_CGC = :CPF_CGC
		           AND NR_LOTE = :NR_LOTE";
		$stmt1TOT = $conn->prepare($sqlTOT);
		
		$stmt1TOT->execute(array(':CPF_CGC' => $CnpjCpf,
		                      ':NR_LOTE' => $nrLote));
		$stmt1TOT->bindColumn("VL_TOTAL", $nrVL_TOTAL);
		$row = $stmt1TOT->fetchAll();
		#echo '<font color="red">====>>>> Valor Total: ',  $nrVL_TOTAL , '</font>' ."\n";
		#echo '<font color="red">====>>>> Novo Valor da Nova Guia: ',  $valorProcedimento , '</font>' ."\n";
		$sqlALTERATOT = "UPDATE TISS_TRANSACAO_PROTOCOLO_DIG
	                        SET VL_TOTAL = VL_TOTAL + :VL_PROCEDIMENTO
	                      WHERE CPF_CGC           = :CPF_CGC
                            AND NR_LOTE           = :NR_LOTE
                            AND NR_SEQ_IMPORTACAO = :NR_SEQ_IMPORTACAO";
		#echo '<font color="red">====>>>> $sqlALTERATOT: ', $sqlALTERATOT , '</font>' ."\n";
		#echo '<font color="red">====>>>> UPDATE ==>> cpf_cgc: ', $CnpjCpf, ' Lote: ', $nrLote, 'Nr_Seq_Importacao: ', $sequencialImportacao, 'valorProcedimento: ', $valorProcedimento , '</font>' ."\n";
		$stmt1ALTERATOT = $conn->prepare($sqlALTERATOT);
		$stmt1ALTERATOT->execute(array(':CPF_CGC' => $CnpjCpf,
									   ':NR_LOTE' => $nrLote,
								       ':NR_SEQ_IMPORTACAO' => $sequencialImportacao,
									   ':VL_PROCEDIMENTO' => $valorProcedimento));
		}
		$conn->commit();

		if ($stmt->rowCount()) {
			print '<script>alert("Guia gravada com sucesso!");</script>';
			$_SESSION['dados'] = null;
			$_SESSION['seqImportacao'] = null;
			
		} else {
			print '<script>alert("Erro ao gravar guia.");</script>';			
		}
		}
	}
	catch (Exception $e) 
	{
	    print '<script>alert("Erro ao gravar guia.");</script>';
	    echo '<font color="red">Exceção capturada: '.  $e->getMessage() .'</font>' ."\n";
	  
	}
	$cont = 1;
}

if((isset($_POST['botao']) && $_POST['botao']=='Consultar') || (isset($_GET['guia'])) || isset($_GET['seqimportacao']))
{	
	if(isset($_POST['nrGuiaPrestador']))
	{
		$nrGuiaPrestador = $_POST['nrGuiaPrestador'];
	}

	if(isset($_POST['nrGuiaAtrOperador']))
	{
		$nrGuiaAtrOperador = $_POST['nrGuiaAtrOperador'];
	}

	if(isset($_GET['guia']))
	{
		$nrGuiaPrestador = $_GET['guia'];
		$nrGuiaAtrOperador = $_GET['guia'];
	}
	
	if(isset($_GET['seqimportacao']))
	{
		$seqImportacao = $_GET['seqimportacao'];			
		$_SESSION['seqImportacao'] = $seqImportacao;
		
	}
	echo '<font color="red">====>>>> CPF_CGC: ', $_SESSION['cpf_cnpj'] , 'SeqImportacao: ', $seqImportacao, 'GuiaA: ' , $nrGuiaAtrOperador , '</font>' ."\n";
	if($seqImportacao != null)
	{
		
		echo '<font color="red">====>>>> CPF_CGC: ', $_SESSION['cpf_cnpj'] , 'SeqImportacao: ', $seqImportacao, 'GuiaA: ' , $nrGuiaAtrOperador , '</font>' ."\n";
		$rows = $conn->query(
		sprintf(/*"SELECT 
			   		  G.NR_GUIA,
					  TO_DATE(G.DT_EMISSAO,'DD/MM/YYYY') DT_EMISSAO,
					  G.NR_CARTEIRA,
					  G.NR_CNS,
					  G.CPF_CGC,
					  G.CD_CNES,
					  G.NM_PROFISSIONAL_EXECUTANTE,
					  G.CD_CONSELHO,
					  G.NR_CONSELHO,               
					  G.CD_UF_CONSELHO,
					  G.CD_CBO,                   
					  G.CD_INDICADOR_ACIDENTE,
					  TO_DATE(G.DT_EVENTO,'DD/MM/YYYY') DT_EVENTO,
					  G.CD_TABELA,                 
					  G.CD_ESPECIALIDADE,
					  G.CD_TIPO_CONSULTA,          
					  G.DS_OBS,
					  G.NR_PROCESSO_REFERENCIA,
					  TO_DATE(G.DT_RECEBIMENTO,'DD/MM/YYYY') DT_RECEBIMENTO,
					  G.NR_GUIA_PRESTADOR,
					  TO_DATE(G.DT_IMPORT_SISTEMA,'DD/MM/YYYY') DT_IMPORT_SISTEMA,
					  G.CD_USUARIO_IMPORT_SISTEMA,
					  G.NR_SEQ_IMPORTACAO,
					  G.SN_ATENDIMENTO_RN,
					  G.VL_CONSULTA,
					  TO_CHAR(G.DT_VALIDADE_CARTEIRA,'DD/MM/YYYY') DT_VALIDADE_CARTEIRA,
					  IP.CD_UF_CRM,
	          		  IP.NR_CRM,
	          		  C.NOME,
	          		  TO_CHAR(C.DT_VALIDADE,'DD/MM/YYYY') DT_VALIDADE,
	          		  P.NR_LOTE,
					  P.*
			   FROM  
			   		TISS_GUIA_CONSULTA_DIG G
			   LEFT JOIN  TISS_TRANSACAO_PROTOCOLO_DIG P
						ON G.NR_SEQ_IMPORTACAO = P.NR_SEQ_IMPORTACAO
			   LEFT JOIN IASM_PRESTADOR IP
	           			ON IP.CPF_CGC = '%s'
	           LEFT JOIN IASM_CARTEIRA C				
	           			ON C.NR_CARTEIRA = G.NR_CARTEIRA
			   WHERE G.NR_SEQ_IMPORTACAO = '%s'",$_SESSION['cpf_cnpj'],$seqImportacao));*/
			   "SELECT 
					G.NR_GUIA,
					TO_DATE(G.DT_EMISSAO,'DD/MM/YYYY') DT_EMISSAO,
					G.NR_CARTEIRA,
					G.NR_CNS,
					G.CPF_CGC,
					G.CD_CNES,
					G.NM_PROFISSIONAL_EXECUTANTE,
					G.CD_CONSELHO,
					G.NR_CONSELHO,               
					G.CD_UF_CONSELHO,
					G.CD_CBO,                   
					G.CD_INDICADOR_ACIDENTE,
					TO_DATE(G.DT_EVENTO,'DD/MM/YYYY') DT_EVENTO,
					G.CD_TABELA,                 
					G.CD_ESPECIALIDADE,
					G.CD_TIPO_CONSULTA,          
					G.DS_OBS,
					G.NR_PROCESSO_REFERENCIA,
					TO_DATE(G.DT_RECEBIMENTO,'DD/MM/YYYY') DT_RECEBIMENTO,
					G.NR_GUIA_PRESTADOR,
					TO_DATE(G.DT_IMPORT_SISTEMA,'DD/MM/YYYY') DT_IMPORT_SISTEMA,
					G.CD_USUARIO_IMPORT_SISTEMA,
					G.NR_SEQ_IMPORTACAO,
					G.SN_ATENDIMENTO_RN,
					G.VL_CONSULTA,
					TO_CHAR(G.DT_VALIDADE_CARTEIRA,'DD/MM/YYYY') DT_VALIDADE_CARTEIRA
			   FROM TISS_TRANSACAO_PROTOCOLO_DIG P,
				    TISS_GUIA_CONSULTA_DIG G
			  WHERE P.NR_SEQ_IMPORTACAO = G.NR_SEQ_IMPORTACAO
				AND P.CPF_CGC           = '%s'
				AND G.NR_SEQ_IMPORTACAO = '%s'
				AND G.NR_GUIA           = '%s'",$_SESSION['cpf_cnpj'],$seqImportacao,$_GET['guia']));
	}
	else
	{

		$rows = $conn->query(
		sprintf("
			   SELECT 
			   		  G.NR_GUIA,
					  TO_DATE(G.DT_EMISSAO,'DD/MM/YYYY') DT_EMISSAO,
					  G.NR_CARTEIRA,
					  G.NR_CNS,
					  G.CPF_CGC,
					  G.CD_CNES,
					  G.NM_PROFISSIONAL_EXECUTANTE,
					  G.CD_CONSELHO,
					  G.NR_CONSELHO,               
					  G.CD_UF_CONSELHO,
					  G.CD_CBO,                   
					  G.CD_INDICADOR_ACIDENTE,
					  TO_DATE(G.DT_EVENTO,'DD/MM/YYYY') DT_EVENTO,
					  G.CD_TABELA,                 
					  G.CD_ESPECIALIDADE,
					  G.CD_TIPO_CONSULTA,          
					  G.DS_OBS,
					  G.NR_PROCESSO_REFERENCIA,
					  TO_DATE(G.DT_RECEBIMENTO,'DD/MM/YYYY') DT_RECEBIMENTO,
					  G.NR_GUIA_PRESTADOR,
					  TO_DATE(G.DT_IMPORT_SISTEMA,'DD/MM/YYYY') DT_IMPORT_SISTEMA,
					  G.CD_USUARIO_IMPORT_SISTEMA,
					  G.NR_SEQ_IMPORTACAO,
					  G.SN_ATENDIMENTO_RN,
					  G.VL_CONSULTA,
					  TO_CHAR(G.DT_VALIDADE_CARTEIRA,'DD/MM/YYYY') DT_VALIDADE,
					  IP.CD_UF_CRM,
	          		  IP.NR_CRM,
	          		  C.NOME,
	          		  TO_CHAR(C.DT_VALIDADE,'DD/MM/YYYY'),
	          		  P.NR_LOTE,
					  P.*
			   FROM  
			   		TISS_GUIA_CONSULTA_DIG G
			   LEFT JOIN  TISS_TRANSACAO_PROTOCOLO_DIG P
						ON G.NR_SEQ_IMPORTACAO = P.NR_SEQ_IMPORTACAO
			   LEFT JOIN IASM_PRESTADOR IP
	           			ON IP.CPF_CGC = '%s'
	           LEFT JOIN IASM_CARTEIRA C				
	           			ON C.NR_CARTEIRA = G.NR_CARTEIRA
			   WHERE G.NR_GUIA  = '%s' or G.NR_GUIA_PRESTADOR = '%s'", $_SESSION['cpf_cnpj'], $nrGuiaPrestador, $nrGuiaAtrOperador));
	}

	foreach ($rows as $dados) {}
	
	if(empty($dados))
	{
		print '<script>alert("Guia não foi encontrada!");</script>';	
		
	}

	$_SESSION['dados'] = $dados; 	 	


	$_SESSION['dados']['DATA_ATENDIMENTO'] =date("Y-m-d", strtotime($dados['DT_EVENTO']));
}
?>

<div id="loading" style='display:none;'>
  <img src="css/loader.gif" class="ajax-loader">
</div>
<div id="guiaconsulta_fabio" style="height:1300px;" class="form-group" >
	<form id="login" name="login" method="post" onSubmit="return validate('');" autocomplete="off" action="index.php?pagina=guiaconsulta_fabio"> 
		<div id="node1" >
			<input type="text"size="10"  value="417548" disabled/>
		</div><!-- #node1 end DT_EVENTO DATA_ATENDIMENTO -->

		<div id="node31">
			<font size="4"><b>Lote: </b></font>
			<select name="lote" id="lote" style="width:165px;font-size:small;" autofocus tabindex="1">			
				<?php	
				foreach ($conn->query("SELECT DISTINCT(NR_LOTE) FROM TISS_TRANSACAO_PROTOCOLO_DIG WHERE NR_PROTOCOLO_RECEBIMENTO is null AND CPF_CGC =" .$_SESSION['cpf_cnpj'] . " ORDER BY NR_LOTE DESC") as $row2) { ?>
						<option value="<?php print $row2['NR_LOTE'] ?>" 
						<?php
						 if((isset($_SESSION['dados']['NR_LOTE'])) && ($_SESSION['dados']['NR_LOTE'] == $row2['NR_LOTE']) || ($_SESSION['nrLote'] ==$row2['NR_LOTE'])) { print('SELECTED');}?> ><?php print $row2['NR_LOTE'] ?></option>
				<?php } ?>
				<option value="-">Novo Lote</option>
			</select>
		</div>

		<div id="node2">
			<input type="text"  size="20" maxlength="20" name="nrGuiaAtrOperador" id="nrGuiaAtrOperador" tabindex="2" value="<?php print isset($_SESSION['dados']['NR_GUIA']) ? $_SESSION['dados']['NR_GUIA'] : ''; ?>"/>
		</div><!-- #node2 end -->

		<div id="node3">
			<input type="text"  size="20" maxlength="20" id="nrCarteira" name="nrCarteira" tabindex="3" value="<?php print isset($_SESSION['dados']['NR_CARTEIRA']) ? $_SESSION['dados']['NR_CARTEIRA'] : ''; ?>"/>	<input type='button' id='btnConsultarCarteira' name='btnConsultarCarteira' value='Carregar'/>
		</div><!-- #node3 end -->

		<div id="node4">
			<input type="text"  size="10" maxlength="10" name="validadeCarteira" tabindex="4"  value="<?php print isset($_SESSION['dados']['DT_VALIDADE']) ? $_SESSION['dados']['DT_VALIDADE'] : ''; ?>" disabled/>
		</div><!-- #node4 end DT_VALIDADE_CARTEIRA   DT_VALIDADE -->
		
		<div id="node7">
			<input type="text"  size="1" style="text-transform:uppercase;" style="text-align:center;" maxlength="1" name="atendimentoRN" id="atendimentoRN"  tabindex="7" value="<?php print isset($_SESSION['dados']['SN_ATENDIMENTO_RN']) ? $_SESSION['dados']['SN_ATENDIMENTO_RN'] : ''; ?>" onkeydown="validaAtendimentoRN(event);"  />
		</div>

		<div id="node8">
			<input type="text"  size="50" disabled name='nomeCarteira' id='nomeCarteira' 
			value="<?php print isset($_SESSION['dados']['NOME']) ? $_SESSION['dados']['NOME'] : ''; ?>"/>
		</div><!--NOME-->

		<div id="node9">
			<input type="text"  size="20" maxlength="15" name="nrCartaoNacionalSaude"  tabindex="8" value="<?php print isset($_SESSION['dados']['NR_CNS']) ? $_SESSION['dados']['NR_CNS'] : ''; ?>"/>
		</div><!-- Cartão Nacional de Saúde -->

		<div id="node12">
			<input type="text"  size="16" maxlength="15" name="codigoCNES" tabindex="10" value="<?php print isset($_SESSION['dados']['CD_CNES']) ? $_SESSION['dados']['CD_CNES'] : ''; ?>"/>
		</div><!--CNES -->

		<div id="node10">
			<input type="text"  size="20" name="CnpjCpf" disabled value="<?php print $_SESSION['cpf_cnpj'] ?>"/>
		</div><!--Código da Operadora -->

		<div id="node11">
			<input type="text"  size="60" name="nomeContrato" tabindex="9" value="<?php print $_SESSION['nomefantasia'] ?>" disabled/>
		</div><!-- Contrato -->

		<div id="node17">
			<select name="cbo" id="cbo" style="width:165px;font-size:small;" tabindex="14">
				<option value="-">    -    </option>				
				<?php	
				foreach ($conn->query('SELECT CD_CBO, DS_CBO FROM TISS_CBO order by CD_CBO') as $row) { ?>
						<option value="<?php print $row['CD_CBO'] ?>" 
						<?php if((isset($_SESSION['dados']['CD_CBO'])) && ($_SESSION['dados']['CD_CBO'] == $row['CD_CBO'])) { print('SELECTED');}?>><?php print $row['CD_CBO']. ' - '.$row['DS_CBO'] ?></option>
				<?php } ?>
				
			</select>
		</div><!-- cbo -->

		<div id="node13">
			<input type="text" style="text-transform:uppercase;" size="55" maxlength="55" name="nomeProfissionalExec" tabindex="10" value="<?php print $_SESSION['tp_credenciado'] == 'F' ? $_SESSION['nomefantasia'] : '' ?>" <?php print $_SESSION['tp_credenciado'] == 'F' ? 'DISABLED' : '' ?>/>
		</div><!-- #node13 end -->

		<div id="node15">
			<input type="text" style="text-transform:uppercase;" size="20" maxlength="15" name="numeroConselho" tabindex="12" value="<?php print $_SESSION['tp_credenciado'] == 'F' ? $_SESSION['nr_crm'] : '' ?>"/>
		</div>

		<div id="node16">
			<input type="text" style="text-transform:uppercase;" size="2" maxlength="2" name="uf" tabindex="13"  value="<?php print $_SESSION['tp_credenciado'] == 'F' ? $_SESSION['cd_uf_crm'] : '' ?>"/>
		</div><!-- #node16 end -->

		<div id="node14">
			<input type="text" style="text-transform:uppercase;" size="5" maxlength="5" name="conselhoProfissional" tabindex="11"  value="<?php print $_SESSION['tp_credenciado'] == 'F' ? 'CRM' : '' ?>" enable/>
		</div><!-- Conselho Profissional -->

		<div id="node18">
			<input type="text" style="text-align:center;" size="1"  name="indAcidente" value="9" disabled/>
		</div><!-- Indicação Acidente -->

		<div id="node24">
			<input type="text"  size="20" maxlength="8" value="<?php print isset($_SESSION['dados']['CD_ESPECIALIDADE']) ? $_SESSION['dados']['CD_ESPECIALIDADE'] : '10101012'; ?>" name='cdEspecialidade'/>
		</div><!-- Código Procedimento  -->

		<div id="node25">
			<input type="text"  size="20" width="20" maxlength="20" name="nrGuiaPrestador" tabindex="1" value="<?php print isset($_SESSION['dados']['NR_GUIA']) ? $_SESSION['dados']['NR_GUIA'] : ''; ?>" disabled/>
		</div><!-- #node25 end --> 

		<div id="node26">
			<input type="number" min="1" step="0.01"  size="8" maxlength="8"  placeholder='0.00'   name="valorProcedimento" tabindex="19" value="<?php print isset($_SESSION['dados']['VL_TOTAL']) ? $_SESSION['dados']['VL_TOTAL'] : ''; ?>"/>

		</div>

		<div id="node19">		
		<input type="date"  size="10" maxlength="10" name="dataAtendimento" tabindex="15" value="<?php print isset($_SESSION['dados']['DATA_ATENDIMENTO']) ? $_SESSION['dados']['DATA_ATENDIMENTO'] : ''; ?>"/>
		</div>
		

		<div id="node22">
			<select name="tipoConsulta" id="tipoConsulta" style="width:120px;font-size:small;" tabindex="18">
				<!-- #node22 end <option value="0">    -  style="background:#d1d0d0;width:1174px:"  </option>-->

				<option value="1" <?php  if((isset($_SESSION['dados']['CD_TIPO_CONSULTA'])) && ($_SESSION['dados']['CD_TIPO_CONSULTA'] == '1')) { print('SELECTED');} ?>>1- Primeira Consulta</option>
				<option value="2" <?php  if((isset($_SESSION['dados']['CD_TIPO_CONSULTA'])) && ($_SESSION['dados']['CD_TIPO_CONSULTA'] == '2')) { print('SELECTED');}  ?>  >2- Retorno</option>
				<option value="3" <?php  if((isset($_SESSION['dados']['CD_TIPO_CONSULTA'])) && ($_SESSION['dados']['CD_TIPO_CONSULTA'] == '3')) { print('SELECTED');} ?>  >3- Pré-natal</option>
				<option value="4" <?php  if((isset($_SESSION['dados']['CD_TIPO_CONSULTA'])) && ($_SESSION['dados']['CD_TIPO_CONSULTA'] == '4')) { print('SELECTED');} ?>  >4- Por Encaminhamento</option>
			</select>
		</div><!-- #node22 end -->

		<div id="node23">
			<input type="text" value="22" size="2" style="text-align:center;" disabled/>
		</div><!-- Tabela -->

		<div id="node27" tabindex="20">
			<textarea rows="7" cols="150" style="text-transform:uppercase;" maxlength="240" 
				id="observacao" name="observacao"><?php print isset($_SESSION['dados']['DS_OBS']) ? $_SESSION['dados']['DS_OBS'] : ''; ?>
					
			</textarea>
		</div>

		<div id="node30">
			<input type="submit" name="botao" value="Consultar" style="font-size:large;" onclick="return validate('Consultar')"/> 
			<input type="submit" name="botao" value="Salvar" style="font-size:large;" onclick="return validate('Salvar')"
			<?php if(isset($_SESSION['dados']['NR_PROTOCOLO_RECEBIMENTO'])) print 'DISABLED title="Não é possivel salvar, Protocolo Já gerado!"';  ?>/> 
			<input type="button" name="btnCancelar" id="btnCancelar" value="Cancelar" style="font-size:large;"/> 
			<!--
			<input type="button" name="btnImprimir" value="Imprimir" onClick ="printDiv();" style="font-size:large;" /> 
			-->
			<br>
		</div>
		<div id="aviso" name="aviso" ></div>
	</form> 
</div>

<script src="js/jquery-1.10.2.js"></script>
<script src="js/jquery-ui-1.10.4.custom.js"></script>
<script src="js/bootbox.min.js"></script>
<script src="js/bootstrap.min.js"></script>


<link href="js/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/bootstrap-theme.min.css">
<link href="css/agenda.css" rel="stylesheet" type="text/css" >
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">

<script>
$(document).ready(function() 
{
	$("#btnCancelar").click(function(){
	bootbox.confirm("Tem certeza que deseja cancelar o preenchimento da guia ?", function(result) {                
		
	  if (result ==true) {      
	  	$('#login')[0].reset();
	  	document.getElementById("aviso").innerHTML = '';			  	
	  	$('#login').attr('action', 'index.php?pagina=guiaconsulta_fabio');
	  	$('#login')[0].submit();
  	  } else {
    	return true;
  	  }
		});
	});

	$("#btnConsultarCarteira").click(function()
	{
		var value =$('#nrCarteira').val()

		$.ajax(
	    {
			type: 'POST',	
			dataType: 'JSON',     
			url: 'carts.php?codCarteira='+value,
			beforeSend: function()
			{ 
				$('#loading').show();
			},
			success: function(msg)
			{
				if(msg != '')
			    {
			        var dataValidade = msg[0].DT_VALIDADE;			        

			        $('input[name="validadeCarteira"]').val(msg[0].DT_VALIDADE);
			        $('input[name="nomeCarteira"]').val(msg[0].NOME);			        
			        $('input[name="nrCartaoNacionalSaude"]').val(msg[0].NR_CNS);
			        $('#loading').hide();			        
			    }
			    else
			    {
			      	alert('Carteira não encontrada');
			      	$('#loading').hide();			  
			      	$('input[name="validadeCarteira"]').val('');
			        $('input[name="nomeCarteira"]').val('');		        
			    }
			 },
			 error: function(XMLHttpRequest, textStatus, errorThrown) 
			 { 
        		alert("Status: " + textStatus); 
        		alert("Error: " + errorThrown);
        		$('#loading').hide();
			 }
		});
	});
});

function printDiv() 
{

/*
  var divToPrint=document.getElementById('guiaconsulta_fabio');  

  var newWin=window.open('','Print-Window');

  newWin.document.open();

  newWin.document.write('<html><link href="css/agenda.css" rel="stylesheet" type="text/css"><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');

  newWin.document.close();

  setTimeout(function(){newWin.close();},30);
  */

  alert(document.getElementById("guiaconsulta_fabio").innerHTML);

}


function validarTextArea(idObjeto)
{
	var texto = '';
	if (document.getElementsById(idObjeto) != null)
	{
		texto = document.getElementsById(idObjeto).value;
		alert(texto);
	}
    if (texto != null && texto.length > 0)
	{
		strMsg += '*Retornou verdadeiro!!<br>';
		alert(strMsg);
	    return true;
	}
	else
	{
		strMsg += '*Retornou falso!!<br>';
		alert(strMsg);
	    return false;
    }
}
	
function validate(botao) 
{
	//strMsg ='';

	vNrGuiaAtroperador = document.getElementsByName("nrGuiaAtrOperador")[0].value;
	vNrCarteira = document.getElementsByName("nrCarteira")[0].value;
	vCnpjCpf = document.getElementsByName("CnpjCpf")[0].value;
	vNumeroConselho = document.getElementsByName("numeroConselho")[0].value;
	vUf = document.getElementsByName("uf")[0].value;
 	vTipoConsulta = document.getElementsByName("tipoConsulta")[0].value;
    vNrGuiaPrestador = document.getElementsByName("nrGuiaPrestador")[0].value;
    vcbo = document.getElementsByName("cbo")[0].value;
	//vdataAtendimento = document.getElementsByName("dataAtendimento")[0].value;
	if(validarTextArea("dataAtendimento"))
	{
		alert("Funcionou!!");
		null;//strMsg += '*A Data de Atendimento é obrigatória!<br>';
	} else {alert("NÃO Funcionou!!");}
	
	var nome = "André";
    var cidade = "Rio Claro";
	var texto = "Meu nome é " + nome + " e sou de " + cidade + " Data de Atendimento: " + vdataAtendimento + " Mensagem: " + strMsg;
	alert(texto);
    
	strMsg += texto;
	
    if(botao == 'Salvar')
    {		
		if(!vNrGuiaAtroperador)
	    {      
	    	strMsg += '*A Guia Atribuída pela Operadora é obrigatória!<br>';
	    }
	 
	    if(!vNrCarteira)
	    {
	    	strMsg += '*O Número da Carteira é obrigatório!<br>';
	    }
	    
	    if(!vCnpjCpf)
	    {
			strMsg += '*O CNPJ/CPF é obrigatório!<br>';
	    }

	    if(!vNumeroConselho)
	    {
	    	strMsg += '*O Conselho Profissional é obrigatório!<br>';
	    }

	    if(!vUf)
	    {
	    	strMsg += '*A UF é obrigatória!<br>';
	    } 
		else if(IsNumeric(vUf))
		{
			strMsg += '*A UF está preenchida incorretamente!<br>';
		}

	    if(!vTipoConsulta)
	    {
	    	strMsg += '*O Tipo Consulta é obrigatório!<br>';
	    }

	    /*
	    if(!vNrGuiaPrestador)
	    {
	    	strMsg += '*A Guia Prestador é obrigatória!<br>';
	    } 
	    */  

	    if(vcbo == "-")
	    {
	    	strMsg += '*O Código CBO é obrigatório!<br>';
	    }
		
		//if(!vdataAtendimento)
		if(vdataAtendimento == "")
		{
			strMsg += '*A Data de Atendimento é obrigatóriaaaaaaaaaaaaaa!<br>';
		} else { strMsg += '*A Data de Atendimento é obroooooooooooooooooooooooooooo!<br>'; }

	}
	else
	{
	 	if(botao == 'Consultar')
	 	{		
			//if((!vNrGuiaAtroperador) && (!vNrGuiaPrestador))
			if((!vNrGuiaAtroperador))
	    	{      
	    		strMsg += '*Campo <b>Número da Guia Atribuído pela Operadora</b> pode ser usado como filtro.<br>';
	    		/*
	    		strMsg += '*Campo <b>Nº Guia no Prestador</b> pode ser usado como filtro.<br>';*/
	    	}
		}
	}
    
    if(strMsg != '')
	{
		document.getElementById("aviso").innerHTML = strMsg;
		document.getElementById("aviso").style.color = "Red";
		document.getElementById("aviso").style.textAlign = "left";
		return false;
	}
	else
	{
		document.getElementById("aviso").innerHTML = '';
		return true;
	}
}


function PopupCenter(url, title, w, h) {
    // Fixes dual-screen position                         Most browsers      Firefox
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;
    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

    // Puts focus on the newWindow
    if (window.focus) {
        newWindow.focus();
    }   
}

 function validaAtendimentoRN(event) 
 {
	var x = event.keyCode;

	if ((x == 83) || (x==115) || (x==78) || (x==110) 
		|| (x==9) || (x==27) || (x==46)) 
		return true;
	else
	{
		alert('Somente S ou N');		
		document.getElementById("atendimentoRN").value='';
		return false;
	}        
  }
</script>