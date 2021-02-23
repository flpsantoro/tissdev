<?php
if (!isset($_SESSION['logado'])) return; 
include_once("dbconfig.php");
?>

<script>
function PopupCenter(url, title, w, h) {
    // Fixes dual-screen position                         Most browsers      Firefox
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;
    var newWindow = window.open(url, title, 'resizable=0, toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

    // Puts focus on the newWindow
    if (window.focus) {
        newWindow.focus();
    }
}
</script>

<?php

if(isset($_POST['lote']))
    $_SESSION['lote'] = $_POST['lote'];

if((isset($_GET['acao']) && ($_GET['acao']=='apagarguia') && (isset($_GET['seqimportacao']))))
{
    try
    {
        $db_con->beginTransaction();      
        echo '<font color="red">====>>>> Guia: ', $_GET['guia'], '</font>' ."\n";
        echo '<font color="red">====>>>> antesNrSeqImportacao: ', $_GET['seqimportacao'], '</font>' ."\n";
        #$sql = 'delete 
		#          from tiss_transacao_protocolo_dig
  		#	     where nr_seq_importacao = '.$_GET['seqimportacao'].
  		#		 ' and nr_guia = '.$_GET['guia'];

        #$stmt = $db_con->prepare($sql);            
        #$stmt->execute();

        #----------- Ok!! ---------------------------
		#$sql = 'delete
		#          from tiss_guia_consulta_dig
		#	     where nr_seq_importacao = '.$_GET['seqimportacao'].
		#		 ' and nr_guia = '.$_GET['guia'];
        #$stmt = $db_con->prepare($sql);            
        #$stmt->execute();
		#----------- Ok!! ---------------------------
        $sql = "SELECT COUNT(*) NR_COUNT
            	  FROM TISS_TRANSACAO_PROTOCOLO_DIG
		         WHERE CPF_CGC = :CPF_CGC
		           AND NR_LOTE = :NR_LOTE";
		$stmt1 = $db_con->prepare($sql);
		$stmt1->execute(array(':CPF_CGC' => $_SESSION['cpf_cnpj'],
		                      ':NR_LOTE' => $_SESSION['lote']));
		$stmt1->bindColumn("NR_COUNT", $nrCountProt);
		$row = $stmt1->fetchAll();
		echo '<font color="red">====>>>> Qtde: ', $nrCountProt ,' Cpf_cgc: ', $_SESSION['cpf_cnpj'], ' Lote: ', $_SESSION['lote'], '</font>' ."\n";
		/*
		if ($nrCountProt == 0) {
			$sql = 'delete 
					  from tiss_transacao_protocolo_dig
  			         where nr_seq_importacao = '.$_GET['seqimportacao'];
        
			$stmt = $db_con->prepare($sql);            
			$stmt->execute();
		}*/
        $db_con->commit();

        if ($stmt->rowCount()) {
            print '<script>alert("Guia ' .$_GET['guia']. ' apagada com sucesso!");</script>';
                    //print '<script>PopupCenter("url", "title", 200, 200);</script>';
        } else {
            print '<script>alert("Guia ' .$_GET['guia']. ' não foi apagada!");</script>';
                }
    }
    catch (Exception $e) 
    {
        print '<script>alert("Erro ao gerar Protocolo.");</script>';
        echo '<font color="red">Exceção capturada: '.  $e->getMessage() .'</font>' ."\n";            
    } 
}

if((isset($_POST['botao'])) && ($_POST['botao'] == 'Gerar Protocolo'))
{    
    //if((isset($_POST['lote']) && ($_POST['lote'] != '0')))
    if($_SESSION['lote'] != null)
    {
       try
        {
            $db_con->beginTransaction();      
            /*
            $sqlTot = "SELECT TISS_S_TRANSACAO_PROTOCOLO.NEXTVAL FROM DUAL";
            $stmtTot = $db_con->prepare($sqlTot);
            $stmtTot->execute();
            $stmtTot->bindColumn("NEXTVAL", $sequenciaProtocolo);  
            $rowTot = $stmtTot->fetchAll();
			*/
            $sql = "SELECT TISS_S_TRANSACAO_PROTOCOLO.NEXTVAL FROM DUAL";
            $stmt = $db_con->prepare($sql);
            $stmt->execute();
            $stmt->bindColumn("NEXTVAL", $sequenciaProtocolo);  
            $row = $stmt->fetchAll();
        
            #$sql = 'UPDATE TISS_TRANSACAO_PROTOCOLO_DIG T 
			#          SET T.NR_PROTOCOLO_RECEBIMENTO ='.$sequenciaProtocolo.
            #      ' WHERE T.NR_LOTE ='.$_POST['lote'].'
			#	       AND T.CPF_CGC=' . $_SESSION['cpf_cnpj'];
            #$sql = 'UPDATE TISS_TRANSACAO_PROTOCOLO_DIG T 
			#           SET T.NR_PROTOCOLO_RECEBIMENTO ='.$sequenciaProtocolo.','
			#		      'T.DT_ENVIO = SYSDATE,
			#			   T.VL_TOTAL = '
            #      ' WHERE T.NR_LOTE ='.$_POST['lote'].'
			#	       AND T.CPF_CGC=' . $_SESSION['cpf_cnpj'];
			#echo '<font color="red">====>>>> sql: ', $sql '</font>' ."\n";
            $stmt = $db_con->prepare($sql);            
            $stmt->execute();

            $db_con->commit();

            if ($stmt->rowCount()) {
                 print '<script>alert("Protocolo ' . $sequenciaProtocolo . ' gerado com sucesso!");</script>';
                //print '<script>PopupCenter("url", "title", 200, 200);</script>';
            } else {
                print '<script>alert("Protocolo não foi gerado.");</script>';
            }
        }
        catch (Exception $e) 
        {
            print '<script>alert("Erro ao gerar Protocolo.");</script>';
            echo '<font color="red">Exceção capturada: '.  $e->getMessage() .'</font>' ."\n";
            
        }   
    }
}
?>
<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" />
<script src="js/jquery-1.10.2.js"></script>
<script>
 $('.confirmation').on('click', function () {
        return confirm('Are you sure?');
    });
 </script>

<br>
<form  name="grid" method="post" action="index.php?pagina=gridguias_fabio&page_no=1">
    <font size="4"><b>Lote: </b></font>
    <select name="lote" id="lote" style="width:165px;font-size:small;" autofocus tabindex="1" onchange="this.form.submit()">>
        <option value="0">Selecione</option>                              
                <?php   foreach ($db_con->query("SELECT DISTINCT(NR_LOTE) FROM TISS_TRANSACAO_PROTOCOLO_DIG WHERE CPF_CGC =" .$_SESSION['cpf_cnpj'] . " ORDER BY NR_LOTE") as $row) { ?>
                        <option value="<?php print $row['NR_LOTE'] ?>" 
                        <?php 
                        $nr_lote =0;
                        if(isset($_SESSION['lote']))
                        {
                            $nr_lote= $_SESSION['lote'];
                        }
                      
                        if($nr_lote== $row['NR_LOTE']) { print('SELECTED');}?> ><?php print $row['NR_LOTE'] ?></option>
                <?php } ?>                
    </select>
    <br>
    <br>
    <table align="center" border="1" width="300px"  id="data"  class="table1">    
    <?php         
        if(isset($_SESSION['lote']))            
        {
            $nr_lote = $_SESSION['lote'];
            
            $query = 'SELECT * FROM (
                                    SELECT
                                      G.*, P.NR_PROTOCOLO_RECEBIMENTO, 
                                      row_number() over (ORDER BY G.Dt_Emissao ASC) line_number
                                    FROM TISS_GUIA_CONSULTA_DIG G, TISS_TRANSACAO_PROTOCOLO_DIG P 
                        WHERE  G.NR_SEQ_IMPORTACAO = P.NR_SEQ_IMPORTACAO
                        AND P.NR_LOTE= ' .$_SESSION['lote'].')';
            $records_per_page=20;
            $newquery = $paginate->paging($query,$records_per_page);
            $paginate->dataview($newquery);
            $paginate->paginglink($query,$records_per_page);  
        }
    ?>    
    </table>
    <br>
    <br>

    <?php 

    
    if((isset($_SESSION['lote']) && ($_SESSION['lote'] != 0)) && ($paginate->totalCount>0))
    {
        echo('<div align="center">');
        echo('<input type="submit" name="botao" value="Gerar Protocolo" style="font-size:large;" ');
       if($paginate->protocolo != '') echo('DISABLED title="Não é possivel salvar, Protocolo Já gerado!"'); 
        echo('/></div>');
    }
    ?>
</form>
<div id="footer">
</div>