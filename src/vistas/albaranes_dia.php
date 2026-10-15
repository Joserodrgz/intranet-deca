<?php
function albaranes_dia ()
    {
	echo "<script type='text/javascript' src='./_js/jquery.js'></script>";
    echo "<script type='text/javascript' src='./_js/tablesorter.min.js'></script>";	
	echo "<script type='text/javascript' src='./_js/tablesorter.widgets.js'></script>";		
	?><style>
	
	#tit3borroso
        {
        font-family: verdana;
        font-size: 10px;
        color: #666666;
        font-weight: regular;
        margin: 0.3ex 0.5ex ;
        clear: both;
        padding: 0.3ex;
        text-decoration: none;
        }
	</style>
    <script type="text/javascript">
	$(document).ready(function() {
	$("#sortedtable").tablesorter({ sortlist: [0,0],
	widgets: ["stickyHeaders","zebra", "math"]	});
	});
	</script><?php			
	
	include($_SERVER['DOCUMENT_ROOT'].'/_configs/_config.php');
	include($_SERVER['DOCUMENT_ROOT'].'/_configs/colores.php');
	include($_SERVER['DOCUMENT_ROOT'].'/js/js.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/_configs/consultas.php');
	include ('datos_emisor.php');  
	include_once($_SERVER['DOCUMENT_ROOT'].'/_clases/phpsecureurl.php');
	$codep = new phpsecureurl ;
    global $PHP_SELF, $nume_alb, $numero, $ordenar, $factura, $importe;
	$directorio_facturae = $_SERVER['DOCUMENT_ROOT'].'/facturae/'; 
	
	$cifes = array();	
	$filtro_efectivo = " select cif_cli cif_cliente, sum(total_alb) as importe_efec
	from ffalbara
    where empr_alb IN $emp
	and   nume_alb < 500000
	and   stat_alb not in ('N','n')
	and   cif_cli != '0000000T'
	group by cif_cli
	HAVING SUM(total_alb) > 1000 ";
 	$resultado_efec=odbc_exec($conexion, $filtro_efectivo);
    if(!$resultado_efec){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}	
	while(odbc_fetch_row($resultado_efec))
	{
	$cif_cliente = trim(odbc_result($resultado_efec,"cif_cliente"));
	$cifes[] = $cif_cliente;
	}
	odbc_free_result($resultado_efec);	
	/////////////////////////////////////  fflineas sin ffalbara
	$error_flin = 'NO';
	$error_en_flin = ''; 	
	$filtro_lin = " select distinct empr_lin, cent_lin, alba_lin, indi_lin, 
	clie_lin, iva__lin
	from fflineas
    where empr_lin IN $emp
	and   alba_lin not in (select nume_alb from ffalbara 
	where nume_alb = alba_lin and stat_alb = indi_lin)		";
 	$resultado_flin=odbc_exec($conexion, $filtro_lin);
    if(!$resultado_flin){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}	
	$empr_flin = trim(odbc_result($resultado_flin,"empr_lin"));
	$cent_flin = trim(odbc_result($resultado_flin,"cent_lin"));
	$alba_flin = odbc_result($resultado_flin,"alba_lin");
	$indi_flin = odbc_result($resultado_flin,"indi_lin");
	$clie_flin = trim(odbc_result($resultado_flin,"clie_lin"));
	$iva__flin = trim(odbc_result($resultado_flin,"iva__lin"));
	if($empr_flin)
	{
	$error_flin = 'SI';
	$error_en_flin = 'Error: Cabecera del albarán '.$alba_flin.' tipo  '.$indi_flin.' del cliente '.$clie_flin.' no existe';
	}
	odbc_free_result($resultado_flin);		
	/////////////////////////////////////////////////////

	$hoy = $_SESSION["fechadetrabajo"];
	$dd = select_dia_DDMMAAAA($hoy);	
	$mm = select_mes_DDMMAAAA($hoy);	
	$mm = str_pad($mm,2,'0',STR_PAD_LEFT);	
	$aa = select_ano_aa_DDMMAAAA($hoy);
	$aaaa = select_ano_aaaa_DDMMAAAA($hoy);	
	include('emilios_venta.php');	
	include('./datos_y_funciones'.$alias_pdf.'.php');
	/////////////////////////////////////////////////
	$dia_cerrado = 'NO'; 	
	$emp_hcaja;
	$fec_hcaja = reset_fecha($hoy);
	$filtro_dia = " 
	select emp_hcaja from ffhcaja where emp_hcaja IN $emp	and  fec_hcaja = '$fec_hcaja' ";
 	$resultado_dia=odbc_exec($conexion, $filtro_dia);
    if(!$resultado_dia){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}	
	$emp_hcaja = trim(odbc_result($resultado_dia,"emp_hcaja"));
	odbc_free_result($resultado_dia);	
	if($emp_hcaja)	$dia_cerrado = 'SI';	
	/////////////////////////////////////////////
	$eshoy = date('d/m/Y');
	$es_finde = reset_fecha_a($hoy);
	$es_findes = date('w', strtotime($es_finde));
	if((restar_fechas($eshoy, $hoy) < 0) || (restar_fechas($eshoy, $hoy) >=5)
	|| $es_findes == 0 || $es_findes == 6 || $dia_cerrado == 'SI'	)
	{	
    echo "<TABLE align=center width=75% class=gris>";
    echo"<tr class=amarillo>";
    echo"<td align=left><div id=tit1>fecha de trabajo no válida <b>$hoy</b></div>";
    echo"</td></tr></table><br>";
	exit();
	}
	///////////////////////////////////////////////////
	$enviar_hoy_vf = 'SI';
	if(restar_fechas($eshoy, $hoy) >=1)	$enviar_hoy_vf = 'NO';
		
    echo "<TABLE align=center width=97% class=gris>";
    echo"<tr class=amarillo>";
    if($enviar_hoy_vf == 'SI')
	echo'<td align=left><div id=tit1>Albaranes del día '.$hoy.' -- '.$cif_340.' -- <b>VERI*FACTU -- '.$obligado_verifactu.'</b></div>';
    if($enviar_hoy_vf == 'NO')
    echo'<td align=left><div id=tit1>Albaranes del día '.$hoy.' -- '.$cif_340.' -- <b>VERI*FACTU FECHA PROFORMA</b></div>';
    echo"</td></tr></table>";
////////////////////// esta consulta ha cambiado pal TBAI iva y recargo	

    $consulta = " select _ifNull(tbai_alb,9999999)*1 as tbai_alb, nume_alb, clie_alb, nomb_cli, ienv_alb,idev_alb,
	merc_alb, total_alb, bimp_alb, iiva_alb, ire_alb, ref_alb, fref_alb, tiv_alb as tipo_iva, rec__alb as tipo_req,
	ano_cli, email, ffalbara.cif_cli,env_cli,
    stat_alb, liq_alb, lib_alb, pai_cli, empr_alb, cent_alb, obs1_alb            
    from ffalbara, ffclient             
    where empr_alb IN $emp
	and   nume_alb < 500000
    and   cod_cli = clie_alb  
	order by tbai_alb, nume_alb ";
 	$result=odbc_exec($conexion, $consulta);
    if(!$result){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}
	
    echo "<TABLE align=center width=97% class=gris id='sortedtable'><thead>";
    echo "<tr class=odd>";
	foreach($albaranes_dia_TH as $key=>$value)
	{
	if($value == 'SI')
	echo '<th align='.$albaranes_dia_ALIGN[$key].'><div id=tit2>'.$key.'</div></th>';		
	}		
    echo "</tr></thead>";
	
    $a = 0;

	while(odbc_fetch_row($result))
	{
	
	$titis = 'tit3';		
	
	$importe  = odbc_result($result,"total_alb");
	$importe_alb  = odbc_result($result,"total_alb");	
	$tbai_alb  = odbc_result($result,"tbai_alb")*1;	
	if($tbai_alb == 9999999) $tbai_alb = 0; 
	$nume_alb = odbc_result($result,"nume_alb");	
	$bimp_alb = odbc_result($result,"bimp_alb");
	$iiva_alb = odbc_result($result,"iiva_alb");
	$ire_alb  = odbc_result($result,"ire_alb");
	$importe_iva = $iiva_alb + $ire_alb;
	$merc_alb	= odbc_result($result,"merc_alb");
	$stat_alb = odbc_result($result,"stat_alb");
    $stat_alb = trim($stat_alb);
	$estado = $stat_alb;	
	if($enviar_hoy_vf == 'NO') $stat_alb = 'P'.$stat_alb;
	if($enviar_hoy_vf == 'NO' && $merc_alb < 0) $stat_alb = 'p'.$stat_alb;	
	$merc_ordenado = str_pad($merc_alb*100,8,'0',STR_PAD_LEFT); 	
	$ienv_alb 	= odbc_result($result,"ienv_alb");
	$idev_alb  	= odbc_result($result,"idev_alb");	
	$importe_env = $ienv_alb - $idev_alb;
	$base_real 	= $merc_alb + $importe_env;
	$total_real = ($base_real + $importe_iva)*1;
	$env_ordenado = str_pad($importe_env*100,8,'0',STR_PAD_LEFT); 	
	$importe_env=number_format($importe_env,2,",",".");	
	$merc_alb=number_format($merc_alb,2,",",".");
    $total_alb=odbc_result($result,"total_alb");
    $liq_alb=odbc_result($result,"liq_alb");	
    $lib_alb = trim(odbc_result($result,"lib_alb"));
	if($lib_alb == 'AL') 
	{	
	$total_alb = $liq_alb;	
	$importe = $liq_alb;		
	}
	$total_ordenado = str_pad($total_alb*100,8,'0',STR_PAD_LEFT); 	
	$baseimp = $total_alb;
    $nomb_cli = odbc_result($result,"nomb_cli");
    $nomb_cli = limpiar ($nomb_cli);
    $cliente = odbc_result($result,"clie_alb");
    $cliente = trim($cliente);
    $clie_alb = odbc_result($result,"clie_alb");		
    $pai_cli = trim(odbc_result($result,"pai_cli"));	
    $centro_venta = trim(odbc_result($result,"cent_alb"));
    $empr = trim(odbc_result($result,"empr_alb"));	
	$ano_cli = limpiar(odbc_result($result,"ano_cli"));
    $ema_cli = trim(odbc_result($result,"email")); 	
    $cif_cli = trim(odbc_result($result,"cif_cli"));		
    $cif_cli = quitar_comas($cif_cli);
//	$cpo_cli = trim(odbc_result($result,"cpo_cli"));  
    $tipo_iva = odbc_result($result,"tipo_iva");
    $tipo_req = odbc_result($result,"tipo_req");
	$env_cli = odbc_result($result,"env_cli");
	$ref_alb = odbc_result($result,"ref_alb");
	$fref_alb = odbc_result($result,"fref_alb");
 	$rectifica1 = limpiar($ref_alb);
	$rectifica2 = limpiar ($fref_alb);	
	if($rectifica2 && strlen($rectifica2) == 10)	
	$rectifica2 = DateTime::createFromFormat('Y-m-d', $rectifica2)->format('d/m/Y');
	if($rectifica2 && strlen($rectifica2) == 8)
	$rectifica2 = DateTime::createFromFormat('y-m-d', $rectifica2)->format('d/m/Y');
	$obs1_alb = trim(odbc_result($result,"obs1_alb"));
	if (trim($obs1_alb != '')) $tiene_obs = 'SI'; else $tiene_obs = 'NO';
	$numero = $nume_alb;
	$seriefactura = serie_factura ($numero, $aaaa, $estado, $empr, $centro_venta);
 	if($enviar_hoy_vf == 'NO') $seriefactura = 'P';
	
	
 	if($estado == 'C' || $estado =='c' || $estado == 'R' || $estado =='r' || 
	   $estado == 'Q' || $estado =='q' || $estado == 'F' || $estado =='f'  )
	{
 	$error_pcero = 'NO';
	$error_en_precio = '';			
    $consulta_pcero = "   
	select distinct  arti_lin, marc_lin, prec_lin, nume_lin
	from fflineas
	where empr_lin = '$empr'
    and   alba_lin =  $nume_alb
	and   clie_lin = '$cliente'
    and   indi_lin = '$estado'
	and   prec_lin = 0 
    order by nume_lin                      ";
    $res_pcero = odbc_exec ($conexion, $consulta_pcero);
    if(!$res_pcero){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}	
	$arti_lin = trim(odbc_result($res_pcero,"arti_lin"));
	if($arti_lin)
	{	
  	$des_art = articulo($arti_lin);
	$marc_lin = trim(odbc_result($res_pcero,"marc_lin"));  
	$prec_lin = odbc_result($res_pcero,"prec_lin");  
	$nume_lin = odbc_result($res_pcero,"nume_lin");	
	$error_pcero = 'SI';
	$error_en_precio = 'Precio cero en '.$des_art.' línea '.$nume_lin.' del albarán '.$nume_alb.' del cliente '.$cliente;
	}
    odbc_free_result ($res_pcero); 
	////////////////////////////////
	$error_bimp = 'NO';		
	$error_en_bimp = '';	
	$dif_base_real = round($base_real-$bimp_alb,2);	
	if($dif_base_real != 0) 
	{
	$error_bimp = 'SI';
	$error_en_bimp = 'Error en base imponible. Base calculada '.$base_real.'  y base imponible del albarán '.$bimp_alb.' en el cliente '.$cliente;
	}
	////////////////////////////////
	$error_total = 'NO';		
	$error_en_total = '';		
	$dif_total_real = round($total_real-$importe_alb,2);
	if($dif_total_real != 0) 
	{
	$error_total = 'SI';
	$error_en_total = 'Error en el total del albarán. Total calculado '.$total_real.'  y total del albarán '.$importe_alb.' en el cliente '.$cliente;
	}	
	////////////////////
	} // if($stat_alb == 

	$cliente_url = rawurlencode($cliente);

	$url_opciones = 
	sprintf ("vistas/albaranes_dia_opc.php?accion=%d&numero=%s&estado=%s&cliente=%s&empr=%s", 1, 
	$nume_alb, $stat_alb, $cliente_url, $empr);
	$url_opciones=$codep->encode($url_opciones);

	$enviado_impresora = 'NO';
	$enviado_email = 'NO';
	$numero_pdf = str_pad($nume_alb,6,'0',STR_PAD_LEFT);
	$numero_facturaepdf = $numero_pdf;
	$numero_factura_pdf = $seriefactura.'-'.$numero_pdf;
	
	$es_venta_historico = 'NO'; 

	$destino = '../fpdf/'.$formato_nota_entrega_pdf.$alias_pdf.'.php';
	$url_entrega = sprintf($destino."?numero=%s&estado=%s&cliente=%s&centroventa=%s&empr=%s&es_venta_historico=%s&numero_factura_pdf=%s",
	$nume_alb, $stat_alb, $cliente_url, $centro_venta, $empr,$es_venta_historico,$numero_factura_pdf);
	$url_entrega=$codep->encode($url_entrega);

	if($enviar_hoy_vf == 'SI') 
	$destino = '../fpdf/'.$formato_albaran_dia_pdf.$alias_pdf.'.php';
	if($enviar_hoy_vf == 'NO') 
	$destino = '../fpdf/'.$formato_proforma_dia_pdf.$alias_pdf.'.php';

	$url = sprintf($destino."?numero=%s&estado=%s&cliente=%s&centroventa=%s&empr=%s&es_venta_historico=%s&numero_factura_pdf=%s",
	$nume_alb, $stat_alb, $cliente_url, $centro_venta, $empr,$es_venta_historico,$numero_factura_pdf);
	$url=$codep->encode($url);
	
	///////////// para evitar doble click
	?><script>
	function correo_enviando_<?php echo $a;?>(link)
	{
    document.getElementById('boton_email_<?php echo $a;?>').src = '../img/enviando_rojo.png';
	link.onclick = function(event) {
    event.preventDefault();
	}
	}
	function impresora_enviando_<?php echo $a;?>(link)
	{
    document.getElementById('numero_pdf_<?php echo $a;?>').src = '../img/tickazul.png';
	link.onclick = function(event) {
    event.preventDefault();
	}
	}	
	</script><?php

	$destino = '';
	$directorio_origen 	= 'NO';
	$directorio_destino = 'NO';
	$directorio_pdf = 'NO';		
	$generacion_masiva = 'SI';
	$destino = 'vistas/00_email'.$alias_pdf.'.php';	
	$url_ema = 'NO';
	if($estado == 'A' || $estado =='a')
	{
	$directorio_origen = $originales.'ventas/'.$aaaa.'/'.$mm.'/'.$numero_factura_pdf.'.pdf';	
	$directorio_destino = $originales.'/ventas/'.$aaaa.'/'.$mm.'/enviados/'.$numero_factura_pdf.'_email.pdf';	
	if (is_file($directorio_origen)) $directorio_pdf = '/publico/ventas/'.$aaaa.'/'.$mm.'/'.$numero_factura_pdf.'.pdf'; 	
	$url_ema = sprintf ($destino."?numero_pdf=%s&cliente=%s&empr=%s&numero=%s&estado=%s&fecha=%s&doc_email=%s&dO=%s&dD=%s",
	$numero_factura_pdf, $cliente_url,$empr,$nume_alb,$estado,$hoy,'01',$directorio_origen,$directorio_destino);
	}

	if($estado == 'P' || $estado =='p')
	{
	$directorio_origen = $originales.'ventas/'.$aaaa.'/'.$mm.'/'.$numero_factura_pdf.'.pdf';	
	$directorio_destino = $originales.'/ventas/'.$aaaa.'/'.$mm.'/enviados/'.$numero_factura_pdf.'_email.pdf';	
	if (is_file($directorio_origen)) $directorio_pdf = '/publico/ventas/'.$aaaa.'/'.$mm.'/'.$numero_factura_pdf.'.pdf'; 	
	$url_ema = sprintf ($destino."?numero_pdf=%s&cliente=%s&empr=%s&numero=%s&estado=%s&fecha=%s&doc_email=%s&dO=%s&dD=%s",
	$numero_factura_pdf, $cliente_url,$empr,$nume_alb,$stat_alb,$hoy,'03',$directorio_origen,$directorio_destino);
	}

	if($estado == 'C' || $estado =='c' || $estado == 'R' || $estado =='r')
	{
	$directorio_origen = $originales.'ventas/'.$aaaa.'/'.$mm.'/'.$numero_factura_pdf.'.pdf';	
	$directorio_destino = $originales.'/ventas/'.$aaaa.'/'.$mm.'/enviados/'.$numero_factura_pdf.'_email.pdf';	
	if (is_file($directorio_origen)) $directorio_pdf = '/publico/ventas/'.$aaaa.'/'.$mm.'/'.$numero_factura_pdf.'.pdf'; 	
	$url_ema = sprintf ($destino."?numero_pdf=%s&cliente=%s&empr=%s&numero=%s&estado=%s&fecha=%s&doc_email=%s&dO=%s&dD=%s",
	$numero_factura_pdf, $cliente_url,$empr,$nume_alb,$estado,$hoy,'02',$directorio_origen,$directorio_destino);
	}

	if($estado == 'Q' || $estado =='q' || $estado == 'F' || $estado =='f')
	{
	$directorio_origen = $originales.'ventas/'.$aaaa.'/'.$mm.'/'.$numero_factura_pdf.'.pdf';	
	$directorio_destino = $originales.'/ventas/'.$aaaa.'/'.$mm.'/enviados/'.$numero_factura_pdf.'_email.pdf';	
	if (is_file($directorio_origen)) $directorio_pdf = '/publico/ventas/'.$aaaa.'/'.$mm.'/'.$numero_factura_pdf.'.pdf'; 	
	$url_ema = sprintf ($destino."?numero_pdf=%s&cliente=%s&empr=%s&numero=%s&estado=%s&fecha=%s&doc_email=%s&dO=%s&dD=%s",
	$numero_factura_pdf, $cliente_url,$empr,$nume_alb,$estado,$hoy,'02',$directorio_origen,$directorio_destino);
	}
	
	if ($url_ema != 'NO') $url_ema=$codep->encode($url_ema);
	if (is_file(trim($directorio_destino)))	$enviado_email = 'SI';
	if (is_file(trim($directorio_origen)))  $enviado_impresora = 'SI';
	$destino = '';	
///////////////////////////////////////////////////////
	if((($stat_alb == 'Q' || $stat_alb == 'F') && $total_alb > 400) ||
	(in_array($cif_cli, $cifes) && $stat_alb == 'C')) $titis = 'tit3borroso'; 
///////////////////////////////////////////////////////	

	$error_en_nif = '';
	if($pai_cli == 'ES')
	{	
	if(COMPROBAR_DOCUMENTO($cif_cli) )
	{
	$titis = 'tit3borroso'; 
	$error_en_nif = COMPROBAR_DOCUMENTO($cif_cli);
	} 
	}

	if(	$error_pcero == 'SI' ) $titis = 'tit3borroso'; 
/////////////////////////////////// / fecha de facturacion fuera de rango
	$eshoy = date('d/m/Y');
	if(restar_fechas($eshoy, $hoy) <  0) $titis = 'tit3borroso';
	if(restar_fechas($eshoy, $hoy) >  0  && restar_fechas($eshoy, $hoy) < 6) $titis = 'tit3azul';		
	if(restar_fechas($eshoy, $hoy) >= 6) $titis = 'tit3borroso';	
	if(	$error_pcero == 'SI' ) $titis = 'tit3borroso'; 
	if(	$error_bimp  == 'SI' ) $titis = 'tit3borroso'; 
	if(	$error_total == 'SI' ) $titis = 'tit3borroso'; 
///////////////////////////////////////////////////////
    $url_emilios = sprintf ("vistas/emilios_cliente.php?accion=%s&cliente=%s",1, $cliente);
    $url_emilios=$codep->encode($url_emilios);	
//////////////////////////////////////////////////////	
	$total_alb=number_format($total_alb,2,",",".");
    echo "<tr "; if( $a%2 == 1 ) echo " class=odd";
    echo "><td class=firstcol align=left> <div id=$titis class=line>
	<A HREF=javascript:ventana('$url_emilios','ventana','720','600','yes');>
    <img src=../img/email_add.png height=12 border=0 style='vertical-align:middle;'></A>&nbsp;
	$clie_alb</div></td>";	
	
	if($albaranes_dia_TH["Nombre"] == 'SI')
    echo '<td align='.$albaranes_dia_ALIGN["Nombre"].'><div id='.$titis.' class=line>'.$nomb_cli.'</div></td>';
    if($albaranes_dia_TH["Merc"] == 'SI')
    echo '<td align='.$albaranes_dia_ALIGN["Merc"].'><div id='.$titis.'><!-- '.$merc_ordenado.'-->'.$merc_alb.'</div></td>';		
    if($albaranes_dia_TH["Env"] == 'SI')
    echo '<td align='.$albaranes_dia_ALIGN["Env"].'><div id='.$titis.'><!-- '.$env_ordenado.'-->'.$importe_env.'</div></td>';		
    if($albaranes_dia_TH["Total"] == 'SI')
    echo '<td align='.$albaranes_dia_ALIGN["Total"].'><div id='.$titis.'><!-- '.$total_ordenado.'-->'.$total_alb.'</div></td>';		
    if($albaranes_dia_TH["T"] == 'SI')
    echo '<td align='.$albaranes_dia_ALIGN["T"].'><div id='.$titis.'>'.$estado.'</div></td>';		
 
	/////////////////// numero de factura pdf
    if($albaranes_dia_TH["Número"] == 'SI')
	{	
    echo '<td '.$albaranes_dia_ALIGN["Número"].' class=line><div class=tooltip id='.$titis.' class=line>';
	if($error_en_nif)
	echo "		
	<div class='left'><ul>
    <li><h4 style='color:rgb(0,0,255);'>$cif_cli</h4></li>	
    <li><b 	style='color:rgb(255,0,0);'>$error_en_nif</b></li>
    </ul><i></i></div>";
	if($error_en_precio)
	{	
	echo "		
	<div class='left'><ul>
    <li><b style='color:rgb(255,0,0);'>$error_en_precio</b></li>
    </ul><i></i></div>";	
	}
	if($error_en_bimp)
	{	
	echo "		
	<div class='left'><ul>
    <li><b style='color:rgb(255,0,0);'>$error_en_bimp</b></li>
    </ul><i></i></div>";	
	}	
	if($error_en_total)
	{	
	echo "		
	<div class='left'><ul>
    <li><b style='color:rgb(255,0,0);'>$error_en_total</b></li>
    </ul><i></i></div>";	
	}		
	if($pai_cli == 'ES')
	if($titis != 'tit3borroso')		
	echo "<A HREF=javascript:ventana('$url','ventana_pdf','800','565','yes');
	onclick='impresora_enviando_$a(this)'>";
	if($pai_cli != 'ES')
	if($titis != 'tit3borroso')	
	echo "<A HREF=javascript:ventana('$url_opciones','ventana_opc','950','200','yes');
	onclick='impresora_enviando_$a(this)'>";	
	echo "$numero_factura_pdf&nbsp;&nbsp;";

// $memo_ant =memory_get_peak_usage();
// $memo_te = memory_get_usage();
// echo "Uso : " . ( $memo_ant -$memo_te)."\n";

	if (($enviado_impresora == 'SI') && ($stat_alb!='N' && $stat_alb!='n') )
	echo "<img src=../img/okmail.png border=0 height='10' style='vertical-align:middle;'>";
	if (($enviado_impresora == 'SI') && ($stat_alb == 'N' || $stat_alb == 'n') )
	echo "<img src=../img/blank.gif border=0 height='12'>";	
	if (($enviado_impresora == 'NO') && ($nume_alb < 500000) && ($stat_alb !='N' && $stat_alb !='n') )
	echo "<img id='numero_pdf_$a' src=../img/xross.png border=0 height='12'>";	
	if (($enviado_impresora == 'NO') && ($nume_alb < 500000) && ($stat_alb == 'N' || $stat_alb == 'n') )
	echo "<img src=../img/blank.gif border=0 height='12'>";		
	if($titis != 'tit3borroso') echo "</A>";	
	echo "&nbsp;";
	echo "</div></td>";	
	}
	/////////////////// email albaran dia
    if($albaranes_dia_TH["Email"] == 'SI')
	{	
    echo '<td align='.$albaranes_dia_ALIGN["Email"].'><div id='.$titis.' class=line>';	
	if ($directorio_pdf != 'NO')
    echo "
    <A HREF=javascript:ventana('$directorio_pdf','ventana','800','500','yes');>
    <img src=../img/pdf.png height=16 border=0 style='vertical-align:middle;'></A>";
	else echo "&nbsp;";	
    if (trim($ema_cli) != '' && $enviado_impresora == 'SI') 
	{	
    echo "&nbsp;&nbsp;&nbsp;
	<A HREF=javascript:ventana('$url_ema','ventana','600','150','no');
	onclick='correo_enviando_$a(this)'	>
 	<img id='boton_email_$a' src=../img/mail.png height=18 border=0 style='vertical-align:middle;'></A>&nbsp;";
	}
	else "&nbsp;";
    if ((trim($ema_cli) != '') && ($enviado_impresora == 'SI') && ($enviado_email == 'SI'))
    echo "<img src=../img/okmail_azul.png border=0 height='10' style='vertical-align:middle;'>&nbsp;";
	else 
	echo "&nbsp;";
	echo "</div></td>";
	}
	/////////////////// nota de entrega	
    if($albaranes_dia_TH["Nota"] == 'SI')	
	{	
	if($lib_alb != 'AL')
	{	
    echo '<td align='.$albaranes_dia_ALIGN["Nota"].'><div id='.$titis.'>';
    echo "<A HREF=javascript:ventana('$url_entrega','ventana','800','565','yes');>
    <img src=../img/nota_de_entrega.png border=0 height='16' style='vertical-align:middle;'></A></div></td>";
	}
	else echo '<td align=center><div id='.$titis.'>&nbsp;</div></td>';
	}
	/////////////////// opciones
    if($albaranes_dia_TH["Opc"] == 'SI')	
	{	
	echo "<td align=".$albaranes_dia_ALIGN["Opc"]."><div id=".$titis.">
	<A HREF=$url_opciones target=_blank>";
	echo "<img src=../img/ejecutar.png border=0 height='16' style='vertical-align:middle;'>";
	echo "</A>";
	echo "</div></td>";	
	}
	/////////////////// etiquetas origen	
    if($albaranes_dia_TH["Etiq"] == 'SI')	
	{
	$urleti = 
	sprintf ("./menu_etiquetas.php?action=%d&factura=%s&importe=%s&empr=%s", 2, 
	$numero, $baseimp, $empr);
	$urleti=$codep->encode($urleti);
	
    if ($enviado_impresora == 'SI')
	{	
    echo '<td align='.$albaranes_dia_ALIGN["Nota"].'><div id='.$titis.'>';
    echo "<A HREF=javascript:ventana('$urleti','urleti','640','530','no');>
    <img src=../img/eti.png border=0 height='12' style='vertical-align:middle;'></A></div></td>";
	}
	else echo '<td align=center><div id='.$titis.'>&nbsp;</div></td>';
	}

	/////////////////// observaciones
    if($albaranes_dia_TH["Obs"] == 'SI')	
	{		
	$url_obs = sprintf ("./vistas/observaciones_albaran.php?numero=%s&estado=%s&empr=%s", 
	$numero,$estado, $empr);
	$url_obs=$codep->encode($url_obs);
	if ($tiene_obs == 'SI')
    echo "<td align=".$albaranes_dia_ALIGN["Obs"]."><div id=tit2>
	<A HREF=javascript:ventana('$url_obs','ventana','800','300','no');>
	<img src=../img/bocadillo_fra.gif height=12 border=0  style='vertical-align:middle;'></a></div></td>";
 	if ($tiene_obs == 'NO')
    echo "<td align=".$albaranes_dia_ALIGN["Obs"]."><div id=tit2>
	<A HREF=javascript:ventana('$url_obs','ventana','800','300','no');>
	<img src=../img/bocadillo_vacio.gif height=12 border=0  style='vertical-align:middle;'></a></div></td>";		
	}	
	/////////////////// facturaE /////////////////////////////////////////
	if (is_dir($directorio_facturae) && $facturaE_dia == "SI")   
	{
	$url_facturae = 
	sprintf ("../facturae/facturae_reset_dia.php?numero=%s&estado=%s&cliente=%s&empr=%s",
	$nume_alb, $stat_alb, $cliente_url, $empr);
	$url_facturae=$codep->encode($url_facturae);
    $facturae_cli = "select distinct cli_facturae from facturae where cli_facturae = '$cliente'";
	$facturae_res = odbc_exec($conexion, $facturae_cli);     
    $facturae_cli = trim(odbc_result($facturae_res,"cli_facturae")); 
	odbc_free_result($facturae_res);
	if($facturae_cli) $es_facturae = 'SI'; else $es_facturae = 'NO';
	if($es_facturae == 'SI')
	{
	$numero_facturae = $aaaa.'_'.$cif_340.'_'.$seriefactura.'_'.$numero_facturaepdf.'.xml';
	$directorio_ventas = $originales.'facturae/'.$numero_facturae;
	if (is_file($directorio_ventas)) $enviado_facturae = 'SI'; else $enviado_facturae = 'NO';
	}
	if($es_facturae == 'SI' && $enviado_facturae == 'NO')
    echo "<td align=center> <div id=$titis>
    <A HREF=javascript:ventana('$url_facturae','ventana','800','565','yes');>
	<img src=../facturae/facturae_rojo.png border=0 height='16'></A></div></td>";
	if($es_facturae == 'SI' && $enviado_facturae == 'SI')
    echo "<td align=center> <div id=$titis>
    <A HREF=javascript:ventana('$url_facturae','ventana','800','565','yes');>
	<img src=../facturae/facturaeverde.png border=0 height='16'></A></div></td>";
    if($es_facturae == 'NO')
	echo "<td align=center><div id=$titis>&nbsp;</div></td>";	 
	}       
	/////////////////// numero de orden de emision de facturas
    if($albaranes_dia_TH["Orden"] == 'SI')	
	{
	if($error_en_precio) 	exit();
	if($error_en_bimp)		exit();
	if($error_en_total)		exit();	
    $descripcion_TBAI = '';		
	$orden_tbai = str_pad($tbai_alb,7,'0',STR_PAD_LEFT);

	if ($obligado_tbai == 'SI') 								include ('albaranes_dia_TBAI.php');
	if ($obligado_verifactu == 'SI' && $enviar_hoy_vf == 'SI') 	include ('albaranes_dia_VF.php');
	if ($obligado_tbai == 'NO' && $obligado_verifactu == 'NO' || $enviar_hoy_vf == 'NO') 
	echo "<td align=".$albaranes_dia_ALIGN["Orden"]." class=line>
	<div class='tooltip' id=".$titis.">".$orden_tbai."&nbsp;</div></td>";	
	} /////////////////// fin numero de orden de emision de facturas

	/////////////////// transporte
    if($albaranes_dia_TH["Tte"] == 'SI')	
	{	
	$quien_tte = " 
	select count(*) cuenta_lin from satalbtte 
	where emp_satalbtte = '$empr'
	and   alb_satalbtte =  $numero
	and   sta_satalbtte = '$estado' 
	and   YEAR(fec_satalbtte) = $aaaa 	";
	$res_tte=odbc_exec($conexion, $quien_tte);
    if(!$res_tte){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}	
	$cuenta_lin_ttes = trim(odbc_result($res_tte,"cuenta_lin"));
    odbc_free_result ($res_tte);

	$url_tte = sprintf ("./vistas/crear_ttealbaran.php?accion=%s&numero=%s&estado=%s&cliente=%s&empr=%s", 
	1,$numero,$estado, $cliente, $empr);
	$url_tte=$codep->encode($url_tte);

	if ($cuenta_lin_ttes != 0)
    echo "<td align=".$albaranes_dia_ALIGN["Tte"]."><div id=tit2>
	<A HREF=javascript:ventana('$url_tte','ventana','800','400','no');>
	<img src=../img/camion_lleno.png height=16 border=0 style='vertical-align:middle;'></a></div></td>";
 	if ($cuenta_lin_ttes == 0)
    echo "<td align=".$albaranes_dia_ALIGN["Tte"]."><div id=tit2>
	<A HREF=javascript:ventana('$url_tte','ventana','800','400','no');>
	<img src=../img/camion_vacio.png height=16 border=0 style='vertical-align:middle;'></a></div></td>";
	}

	//////////////////////// CMR 
    if($albaranes_dia_TH["CMR"] == 'SI')	
	{		
	$url_cmr = sprintf ("./vistas/crear_cmr.php?numero=%s&estado=%s&cliente=%s&empr=%s", 
	$numero,$estado, $cliente_url, $empr);
	$url_cmr=$codep->encode($url_cmr);	
	$numero_cmr = str_pad($numero,6,'0',STR_PAD_LEFT);
	$numero_cmr = $aaaa.$estado.$empr.$numero_cmr;
	$directorio_cmr = $originales.'cmr/'.$numero_cmr.'.pdf';
	$directorio_cmrs = '/publico/cmr/'.$numero_cmr.'.pdf';
	if (is_file($directorio_cmr))
	$url_duplicado_cmr = sprintf ("$directorio_cmrs"); else $url_duplicado_cmr = '';
	$enviado_email_cmr = 'NO';
	$directorio_enviado_cmr = $originales.'cmr/enviados/'.$numero_cmr.'.pdf';
	if (is_file($directorio_enviado_cmr)) $enviado_email_cmr = 'SI';	
	$destino = 'vistas/00_email'.$alias_pdf.'.php';		
	$url_email_cmr = sprintf ($destino."?cliente=%s&numero_cmr=%s&empr=%s&dO=%s&dD=%s&doc_email=%s", 
	$cliente,$numero_cmr,$empr,$directorio_cmr,$directorio_enviado_cmr,'05');
	$url_email_cmr = $codep->encode($url_email_cmr);
	$destino = '';
    echo "<td align=".$albaranes_dia_ALIGN["CMR"]."><div id=tit3 class=line>
    <A HREF=javascript:ventana('$url_cmr','ventana','800','650','yes');>
    <img src='../img/CMR.png' height='16' border=0 style='vertical-align:middle;'>
	&nbsp;</a>";
	if ($url_duplicado_cmr != '')
	echo "	
    <A HREF=javascript:ventana('$url_duplicado_cmr','ventana','800','500','yes');>
    <img src='../img/pdf.png' height='16' border=0 style='vertical-align:middle;'></A>";
    if ((trim($ema_cli) != '') && ($url_duplicado_cmr != ''))
    echo "
	<A HREF=javascript:ventana('$url_email_cmr','ventana','600','150','no');>
 	<img src=../img_sat/mail.png height=18 border=0 style='vertical-align:middle;'></A>";
    if ((trim($ema_cli) != '') && ($enviado_email_cmr == 'SI') && ($url_duplicado_cmr != ''))
    echo "&nbsp;<img src=../img/okmail.png border=0 height='10' style='vertical-align:middle;'>";	
	echo"</div></td>";	
	}

	////////////////////////////// comprobar VIES
    if($albaranes_dia_TH["VIES"] == 'SI')	
	{	
	$es_intracomunitario = 'N';	
	if($pai_cli != 'ES')
	{
    $consulta_ic = " select cif_cli, com_paises 
	from ffclient, ffpaises
	where cod_cli = '$cliente' 
	and   pai_cli = '$pai_cli' 
	and   pai_cli = cod_paises 		";
	$resultado_ic=odbc_exec($conexion, $consulta_ic);
    if(!$resultado_ic){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}
	$cif_cli = limpiar(odbc_result($resultado_ic,"cif_cli"));	
	$es_intracomunitario = trim(odbc_result($resultado_ic,"com_paises"));
    odbc_free_result ($resultado_ic);
	if($es_intracomunitario == 'S')
	{	
    $consulta_vies = " select es_vies, max(fec_vies) as fec_vies 
	from ffvies 
	where nif_vies = '$cif_cli' 
	group by es_vies ";
	$res_vies = odbc_exec($conexion, $consulta_vies);
    if(!$res_vies){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}
	$era_vies = trim(odbc_result($res_vies,"es_vies"));
	$fec_vies = trim(odbc_result($res_vies,"fec_vies"));
	$vatid = str_replace(array(' ', '.', '-', ',', ', '), '', trim($cif_cli));
	$cc = substr($vatid, 0, 2);
	$vn = substr($vatid, 2);	
    odbc_free_result ($res_vies); 
	if(!$era_vies) $era_vies == 'ALTA';
	$url_vies = sprintf ("./vistas/vies_ver.php?accion=%d&cc=%s&vn=%s&cif_cli=%s", 
	2,$cc,$vn,$cif_cli);;
	$url_vies=$codep->encode($url_vies);
	} // es intracomunitario == S
	} // es pais != ES	
	if($es_intracomunitario == 'S')
	{	
	if($era_vies != 'ALTA')
	{	
	$vies_fecha = rotar_fecha_AAAAMMDD($fec_vies);			
	$dias_vies  = restar_fechas_mejor($hoy,$vies_fecha);
	} else $dias_vies = 91;
    echo "<td align=".$albaranes_dia_ALIGN["VIES"]." alt='$dias_vies' title='Útltima comprobación $vies_fecha'>
	<div id=tit3 class=line>$era_vies&nbsp;";
	if ($era_vies == 'SI' && $dias_vies < 91)
	echo"	
    <A HREF=javascript:ventana('$url_vies','ventana','800','175','yes');>
    <img src=../img/compras.png height=14 border=0 style='vertical-align:middle;'></A>";		
	else
	echo"	
    <A HREF=javascript:ventana('$url_vies','ventana','800','175','yes');>
    <img src=../img/ventas.png height=14 border=0 style='vertical-align:middle;'></A>";	
	echo"</div></td>";		
	}
	else
	{
    echo "<td align=left><div id=tit3>&nbsp;</div></td>";	
	}
	} ///// fin comprobar VIES	

	////////////////////////////// contrato TAI
    if($albaranes_dia_TH["TAI"] == 'SI')	
	{		
	$destino = '../fpdf/contrato_vies'.$alias_pdf.'.php';	
	$url_cto = sprintf ($destino."?numero=%s&estado=%s&cliente=%s&empr=%s&aaaa=%s", 
	$numero,$estado,$cliente_url,$empr,$aaaa);
	$url_cto=$codep->encode($url_cto);
	$destino = '';		
	$cod_cto = 'TAI';
	$numero_cto = str_pad($numero,6,'0',STR_PAD_LEFT);
	$numero_cto = $aaaa.$estado.$cod_cto.$empr.$numero_cto;	
	$directorio_cto = $originales.'cmr/'.$numero_cto.'.pdf';
	$directorio_ctos = '/publico/cmr/'.$numero_cto.'.pdf';
	if (is_file($directorio_cto))
	$url_duplicado_cto = sprintf ("$directorio_ctos"); else $url_duplicado_cto = '';
	$enviado_email_tai = 'NO';
	$directorio_enviado_tai = $originales.'cmr/enviados/'.$numero_cto.'.pdf';
	if (is_file($directorio_enviado_tai)) $enviado_email_tai = 'SI';	
	$destino = 'vistas/00_email'.$alias_pdf.'.php';	
	$url_email_tai = sprintf ($destino."?cliente=%s&numero_cto=%s&empr=%s&dO=%s&dD=%s&doc_email=%s", 
	$cliente,$numero_cto,$empr,$directorio_cto,$directorio_enviado_tai,'06');
	$url_email_tai = $codep->encode($url_email_tai);	
	if ($pai_cli != 'ES')
	{	
    echo "<td align=".$albaranes_dia_ALIGN["TAI"]."><div id=tit3 class=line>
    <A HREF=javascript:ventana('$url_cto','ventana','780','460','yes');>
    <img src=../img/editar_verde.png height=18 border=0 style='vertical-align:middle;'></A>";
	if ($url_duplicado_cto != '')
	echo"	
    <A HREF=javascript:ventana('$url_duplicado_cto','ventana','800','500','yes');>
    <img src=../img/pdf_verde.png height=16 border=0 style='vertical-align:middle;'></A>";
    if ((trim($ema_cli) != '') && ($url_duplicado_cto != ''))
    echo "
	<A HREF=javascript:ventana('$url_email_tai','ventana','600','150','no');>
 	<img src=../img/mail_verde.png height=20 border=0 style='vertical-align:middle;'></A>";	
    if ((trim($ema_cli) != '') && ($enviado_email_tai == 'SI') && ($url_duplicado_cto != ''))
    echo "&nbsp;<img src=../img/okmail.png border=0 height='10' style='vertical-align:middle;'>";	
	echo"</div></td>";	
	}
	else		
    echo "<td align=center><div id=tit3>&nbsp;</div></td>";	
	} ///// fin contrato TAI

	//////////////////////// Contratos COA 
    if($albaranes_dia_TH["COA"] == 'SI')	
	{		
	$destino = '../fpdf/contrato_vies_coa.php';	
	$url_coa = sprintf ($destino."?numero=%s&estado=%s&cliente=%s&empr=%s&aaaa=%s", 
	$numero,$estado,$cliente,$empr,$aaaa);
	$url_coa=$codep->encode($url_coa);
	$destino = '';		
	$cod_cto = 'COA';
	$numero_coa = str_pad($numero,6,'0',STR_PAD_LEFT);
	$numero_coa = $aaaa.$estado.$cod_cto.$empr.$numero_coa;	
	$directorio_coa = $originales.'cmr/'.$numero_coa.'.pdf';
	$directorio_coas = '/publico/cmr/'.$numero_coa.'.pdf';
	if (is_file($directorio_coa))
	$url_duplicado_coa = sprintf ("$directorio_coas"); else $url_duplicado_coa = '';
	$enviado_email_coa = 'NO';
	$directorio_enviado_coa = $originales.'cmr/'.$numero_coa.'_enviado.pdf';
	if (is_file($directorio_enviado_coa)) $enviado_email_coa = 'SI';
	$destino = './vistas/email_vies_coa.php';
	$url_email_coa = sprintf ($destino."?cliente=%s&numero_coa=%s&numero_pdf=%s&fecha=%s&empr=%s&d_coa=%s&d_alb=%s&d_env=%s&doc_email=%s", 
	$cliente,$numero_coa,$numero_factura_pdf,$hoy,$empr,$directorio_coa,$directorio_origen,$directorio_enviado_coa,'07');
	$url_email_coa = $codep->encode($url_email_coa);
	
	if ($pai_cli != 'ES' && $url_duplicado_cto != '')
	{	
    echo "<td align=".$albaranes_dia_ALIGN["COA"]."><div id=tit3 class=line>
    <A HREF=javascript:ventana('$url_coa','ventana','780','460','yes');>
    <img src=../img/editar_rojo.png height=18 border=0 style='vertical-align:middle;'></A>";
	if ($url_duplicado_coa != '')
	echo"	
    <A HREF=javascript:ventana('$url_duplicado_coa','ventana','800','500','yes');>
    <img src=../img/pdf_rojo.png height=16 border=0 style='vertical-align:middle;'></A>";
	else echo "&nbsp;&nbsp;";		
    if ((trim($ema_cli) != '') && ($url_duplicado_coa != ''))
    echo "&nbsp;
	<A HREF=javascript:ventana('$url_email_coa','ventana','600','150','no');>
 	<img src=../img/mail_rojo.png height=20 border=0 style='vertical-align:middle;'></A>&nbsp;";
	else echo "&nbsp;";
    if ((trim($ema_cli) != '') && ($enviado_email_coa == 'SI') && ($url_duplicado_coa != ''))
    echo "<img src=../img/okmail_rojo.png border=0 height='10' style='vertical-align:middle;'>";
	else 
	echo "&nbsp;";		
	echo"</div></td>";	
	}
	else		
    echo "<td align=center><div id=tit3>&nbsp;</div></td>";	
	} ///// fin contrato COA


	
    echo "</tr>";
    $a++;
	if($error_en_precio) exit();
    }
	if($error_en_flin)
	{	
	$url_error_pasven = sprintf ("vistas/corregir_pasven_desde_hliven.php?accion=%s&empr_alb=%s&cent_alb=%s&nume_alb=%s&clie_alb=%s&stat_alb=%s&iva_alb=%s", 
	3,$empr_flin,$cent_flin,$alba_flin,$clie_flin,$indi_flin,$iva__flin);
	$url_error_pasven = $codep->encode($url_error_pasven);
	
    echo "<tr class=azul>";
    echo "<td class=firstcol align=left> <div id=tit2 class=line>
	<img src=../img/email_add.png height=12 border=0 style='vertical-align:middle;'>&nbsp;
	$clie_flin</div></td>";		
	$nomb_fcli = cliente($clie_flin);
    echo '<td align='.$albaranes_dia_ALIGN["Nombre"].'><div id=tit2 class=line>'.$nomb_fcli.'</div></td>';	
    echo '<td align=left colspan = 5><div id=tit2 class=line>'.$error_en_flin.'</div></td>';	
    echo "<td align=left><div id=tit2>reconstruir cabecera  ---></div></td>";		
    echo "<td align=center><div id=tit2>   
	<A HREF=javascript:ventana('$url_error_pasven','ventana','850','300','yes');>	
	<img src=../img/ventas.png height=16 border=0></a></div></td>";	 	
    echo "<td align=left colspan=3><div id=tit2><---   reconstruir cabecera</div></td>";		
    echo "</tr>";	
	}	
	echo "</TABLE><br>";
	odbc_free_result($result);
	unset($cifes);
 	}
?>