<?php
session_save_path('../tmp/informix');
session_start();
	include($_SERVER['DOCUMENT_ROOT'].'/_configs/_config.php' );
	include_once($_SERVER['DOCUMENT_ROOT'].'/_configs/_header_ifx.php' );  
	include_once($_SERVER['DOCUMENT_ROOT'].'/js/js.php' );
        include_once($_SERVER['DOCUMENT_ROOT'].'/_configs/funciones.php' );        
	include_once($_SERVER['DOCUMENT_ROOT'].'/_clases/phpsecureurl.php' );
        $codep = new phpsecureurl ;
        if($_REQUEST[hmg]){$url12=$codep->decode($url12);}

        global $PHP_SELF,$numero,$cliente, $empr, $aaaa;

global $accion;
if (empty ($accion))     $accion = '1';
switch ($accion)
    {
    case '1':	
   	echo"<body onload='document.forms.libro.transportista.focus()'>";
    $quien = (" select nom_cli from ffclient where cod_cli = '$cliente' ");
	$quien=odbc_exec($conexion, $quien);
    if(!$quien){$err = odbc_errormsg();
	printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);}
    $row = odbc_fetch_row($quien);
	$nom_cli = limpiar(odbc_result($quien,"nom_cli"));
    odbc_free_result ($quien);

    $quien_tte = (" select * from satalbtte 
	where emp_satalbtte = '$empr'
	and   alb_satalbtte =  $numero
	and   ano_satalbtte = '$aaaa' 		");
	$res_tte=odbc_exec($conexion, $quien_tte);
    if(!$res_tte){$err = odbc_errormsg();
	printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);}
	$cia_satalbtte 	= trim(odbc_result($res_tte,"cia_satalbtte"));
	$mat_satalbtte 	= trim(odbc_result($res_tte,"mat_satalbtte"));
	$cond_satalbtte = trim(odbc_result($res_tte,"cond_satalbtte"));
	$nif_satalbtte 	= trim(odbc_result($res_tte,"nif_satalbtte"));
	$fecha_tte = rotar_timestamp(odbc_result($res_tte,"fec_satalbtte"));	
	$dest1_satalbtte = trim(odbc_result($res_tte,"dest1_satalbtte"));
	$dest2_satalbtte = trim(odbc_result($res_tte,"dest2_satalbtte"));
	$pai_satalbtte 	= trim(odbc_result($res_tte,"pai_satalbtte"));		
	$en_destino 	= trim(odbc_result($res_tte,"en_destino"));		
    odbc_free_result ($res_tte);

    echo"<fieldset><legend>Datos del transporte del albar&aacute;n $numero de $nom_cli</legend>";
        
        echo "<br><TABLE align=center width=100%>"; 
 	echo"<tr class=amarillo>";    
        echo"<td align=left><div id=tit2>Transportista";
        echo"<td align=left><input STYLE='text-align:left' type='text' 
        size='40'  maxlength='35' value='$cia_satalbtte' readonly=readonly </td>";
    
 	echo"<tr class=amarillo>"; 
  	echo"<td align=left colspan=1><div id=tit2>Conductor";
   	echo"<td align=left><input STYLE='text-align:left' type='text' 
    	size='40'  maxlength='35' value='$cond_satalbtte' readonly=readonly
      	></div>
       	</tr><tr class=amarillo>";

	echo"<td align=left><div id=tit2>Conductor NIF";
 	echo"<td align=left><input STYLE='text-align:left' type='text''
  	size='15'  maxlength='15' value='$nif_satalbtte' readonly=readonly
    	></div>
     	</tr><tr class=amarillo>";

	echo"<td align=left><div id=tit2>Matr&iacute;culas";
 	echo"<td align=left><input STYLE='text-align:left' type='text' 
  	size='25'  maxlength='20' value='$mat_satalbtte' readonly=readonly
    	></div></tr><tr class=amarillo>";

	echo"<td align=left><div id=tit2>Fecha de carga";
 	echo"<td align=left><input STYLE='text-align:center' type='text'
  	size='17'  maxlength='20' value='$fecha_tte' readonly=readonly
    	></div></tr><tr class=amarillo>";

	echo"<td align=left><div id=tit2>Destino";
 	echo"<td align=left><input STYLE='text-align:left' type='text' 
  	size='50'  maxlength='50' value='$dest1_satalbtte' readonly=readonly
    	></div></tr><tr class=amarillo>";

	echo"<td align=left><div id=tit2>en";
 	echo"<td align=left><input STYLE='text-align:left' type='text' 
  	size='50'  maxlength='50' value='$dest2_satalbtte' readonly=readonly
    	></div></tr><tr class=amarillo>";

     	echo "<td align=right colspan=2><div id=tit2>
      	<input type=reset value=Cerrar onClick=self.close() class=search></div></td>";
       	echo"</tr></table>";
        echo"</div>";
        echo "</fieldset>";
	break;
}       
?>