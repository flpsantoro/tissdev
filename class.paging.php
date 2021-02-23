<?php
class paginate
{
     private $db;
     public $totalCount;
     public $protocolo='';
     public $nrProtocoloRecebimento='';
     public $nrSeqImportacao='';

     function __construct($db_con)
     {
         $this->db = $db_con;
         $this->totalCount =0;
         $this->protocolo='';
     }
 
     public function dataview($query)
     {
        $this->protocolo=0;
        $stmt = $this->db->prepare($query);

        $stmt->execute();

        $stmt2Count = $this->db->query($query)->fetchAll();        

        $this->totalCount = count($stmt2Count);        

         if($this->totalCount>0)
         {
          ?>
          <thead >
          <tr>
                <th align='center'>Guia</th>
                <th align='center'>Guia Prestador</th>
                <th align='center'>Nº Carteira</th>
                <th align='center'>Data Emissão</th>
                <th align='center'>Valor</th>
                <th align='center'>Protocolo</th>
                <th></th>                                
          </tr>
          </thead>
                <?php
                while($row=$stmt->fetch(PDO::FETCH_ASSOC))
                {
                   $this->protocolo = $row['NR_PROTOCOLO_RECEBIMENTO'];
                   $date = date_create($row['DT_EMISSAO']);                                      
                   ?>
                   <tr>
                   <td align='center'><?php echo $row['NR_GUIA']; ?></td>
                   <td align='center'><?php echo $row['NR_GUIA_PRESTADOR']; ?></td>
                   <td align='center'><?php echo $row['NR_CARTEIRA']; ?></td>
                   <td align='center'><?php echo date_format($date, 'd/m/Y'); ?></td>
                   <td align='center'>R$ <?php echo number_format($row['VL_CONSULTA'], 2, ',', ''); ?></td>
                   <td align='center'><b><?php echo $row['NR_PROTOCOLO_RECEBIMENTO']; ?></b></td>
                   <td align='center'>
                   <?php if($row['NR_PROTOCOLO_RECEBIMENTO'] == '')
                   {
                    $vRef = '<a href=index.php?pagina=guiaconsulta&guia=';
                    $vRef = $vRef.$row['NR_GUIA'];
                    $vRef = $vRef."&seqimportacao=";
                    $vRef = $vRef.$row['NR_SEQ_IMPORTACAO'];
                    $vRef = $vRef.'>Editar Guia</a></b>';
                    echo($vRef);
                   }
                    ?>
                   </td> 
                   <td>
                   <?php if($row['NR_PROTOCOLO_RECEBIMENTO'] == '')
                   {
                    echo("<a href='index.php?pagina=gridguia&guia=".$row['NR_GUIA']."&acao=apagarguia&seqimportacao=".$row['NR_SEQ_IMPORTACAO']."' onclick='return confirm(\"Tem certeza que possui excluir esta guia ?\")'><img src='imgs/delete.gif' alt='Apagar Guia' title='Apagar Guia'/></a>");
                   }
                   ?>
                   </td>                  
                  </tr>
                   <?php
                } 

                if($this->protocolo != '')
                {
                  echo("<tr>
                    <td colspan='8'>&nbsp;</td>
                    </tr>
                    <tr>
                      <td align='center' colspan='8'>
                      <b><button><a href=guias_dig.php?acao=ver&nr_protocolo=".$this->protocolo." target='_blank'>Relação Guias</a></button></b>
                      &nbsp; &nbsp; &nbsp;
                    <b><button><a href=protocolo_dig.php?nr_protocolo=".$this->protocolo." target='_blank'>Protocolo Recebimento</a></button></b>
                    </td>
                    </tr>");
                }
         }
         else
         {
          ?>
              <tr>
              <td colspan='8'>Sem dados...</td>
              </tr>
          <?php
         }  
 }
 
 public function paging($query,$records_per_page)
 {
        $starting_position=0;

        if(isset($_GET["page_no"]))
        {
             if($_GET["page_no"] == 1)
             {
              $starting_position=1;
             }
             else
             {
              $starting_position=(($_GET["page_no"]-1)*$records_per_page)+1;
              $records_per_page =($records_per_page + $starting_position)-1;
            }
        }        
        
        $query2=$query."  WHERE line_number BETWEEN $starting_position and $records_per_page  ORDER BY line_number";               
               
        return $query2;
 }
 
 public function paginglink($query,$records_per_page)
 {  
         //$self = $_SERVER['PHP_SELF']; 
         $self = 'index.php?pagina=gridguia';
        
         $stmt = $this->db->prepare($query);
         $stmt->execute();
         $rows = $stmt->fetchAll();
 
         if($stmt) 
         {
            $total_no_of_records = count($rows);
              
         } else 
         {
              echo('Error database');
              exit();
         }          

        if($total_no_of_records > 0)
        {
            ?><tr><td colspan="8"><?php

            $nr_lote  = $_SESSION['lote'];

            $total_no_of_pages=ceil($total_no_of_records/$records_per_page);
            $current_page=1;

            if(isset($_GET["page_no"]))
            {
               $current_page=$_GET["page_no"];
            }
            if($current_page!=1)
            {
               $previous =$current_page-1;
               echo "<a href='".$self."&page_no=1'>|<<<</a>&nbsp;&nbsp;";
               echo "<a href='".$self."&page_no=".$previous."'><<</a>&nbsp;&nbsp;";
            }
            for($i=1;$i<=$total_no_of_pages;$i++)
            {
            if($i==$current_page)
            {
                echo "<strong><a href='".$self."&page_no=".$i."' style='color:red;text-decoration:none'>".$i."</a></strong>&nbsp;&nbsp;";
            }
            else
            {
                echo "<a href='".$self."&page_no=".$i."'>".$i."</a>&nbsp;&nbsp;";
            }
   }
   if($current_page!=$total_no_of_pages)
   {
        $next=$current_page+1;
        echo "<a href='".$self."&page_no=".$next."'>>></a>&nbsp;&nbsp;";
        echo "<a href='".$self."&page_no=".$total_no_of_pages."'>>>>|</a>&nbsp;&nbsp;";
   }
   ?></td></tr><?php
  }
 }
}
?>