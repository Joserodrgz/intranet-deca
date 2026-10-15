<?php
    session_save_path('../tmp/informix');
    session_start();
    set_time_limit(0);
	include($_SERVER['DOCUMENT_ROOT'].'/_configs/_config.php' );
	include_once($_SERVER['DOCUMENT_ROOT'].'/_configs/_header_ifx.php' );  
	include_once($_SERVER['DOCUMENT_ROOT'].'/_configs/funciones.php' );  
	include($_SERVER['DOCUMENT_ROOT'].'/_configs/colores.php' );  		
	include_once($_SERVER['DOCUMENT_ROOT'].'/js/js.php' );  
	include_once($_SERVER['DOCUMENT_ROOT'].'/_configs/consultas.php' );	
	include_once($_SERVER['DOCUMENT_ROOT'].'/_clases/phpsecureurl.php' );
	$codep = new phpsecureurl ;
	if($_REQUEST[hmg]){$url12=$codep->decode($url12);}	
    global $PHP_SELF;
	global $accion;
	if (empty ($accion))     $accion = '1';

	switch ($accion)
    {
    case '1':	
	$hoy = $_SESSION["fechadetrabajo"];
	$dd = select_dia_DDMMAAAA($hoy);	
	$mm = select_mes_DDMMAAAA($hoy);	
	$mm = str_pad($mm,2,'0',STR_PAD_LEFT);	
	$aa = select_ano_aa_DDMMAAAA($hoy);
	$aaaa = select_ano_aaaa_DDMMAAAA($hoy);	
	include('../datos_y_funciones'.$alias_pdf.'.php');	
	$seriefactura = serie_factura ($numero, $aaaa, $estado, $empr, $centroventa);
    if(!$seriefactura) $seriefactura = $empr;
	$numero_pdf=str_pad($numero,6,'0',STR_PAD_LEFT);
	$numero_pdf = $seriefactura.'-'.$numero_pdf;
	$nom_cli = cliente($cliente);
	
    $consulta = " select nume_alb, clie_alb, nomb_cli, total_alb, ano_cli, email,
    stat_alb, ngra_alb, liq_alb, lib_alb, pai_cli, empr_alb, cent_alb , obs1_alb            
    from ffalbara, ffclient             
    where empr_alb = '$empr'
	and   nume_alb =  $numero
    and   cod_cli  = '$cliente'   
	and   stat_alb = '$estado'
    order by nume_alb";
	$result=odbc_exec($conexion, $consulta);
    if(!$result){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}	
    $total_alb=odbc_result($result,"total_alb");
    $liq_alb=odbc_result($result,"liq_alb");	
    $lib_alb = trim(odbc_result($result,"lib_alb"));
	if($lib_alb == 'AL') $total_alb = $liq_alb;		
    $baseimp = $total_alb;
	$total_alb=number_format($total_alb,2,",",".");
    $nomb_cli = odbc_result($result,"nomb_cli");
    $nomb_cli = limpiar ($nomb_cli);
    $stat_alb = odbc_result($result,"stat_alb");
    $stat_alb = limpiar($stat_alb);
    $pai_cli = trim(odbc_result($result,"pai_cli"));	
    $centro_venta = trim(odbc_result($result,"cent_alb"));
	$ano_cli = limpiar(odbc_result($result,"ano_cli"));
    $ema_cli = trim(odbc_result($result,"email")); 	
	$obs1_alb = trim(odbc_result($result,"obs1_alb"));
	if (trim($obs1_alb != '')) $tiene_obs = 'SI'; else $tiene_obs = 'NO';	
	odbc_free_result ($result);

	$cliente_url = rawurlencode($cliente);
	$numero_factura_pdf = $numero_pdf;
	
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

	$es_venta_historico = 'NO'; 
	$destino = '../../fpdf/'.$formato_albaran_opc_pdf.$alias_pdf.'.php';	
	$url = sprintf($destino."?numero=%s&estado=%s&cliente=%s&centroventa=%s&empr=%s&es_venta_historico=%s&numero_factura_pdf=%s",
	$numero, $estado, $cliente_url, $centro_venta, $empr,$es_venta_historico,$numero_factura_pdf);
	$url=$codep->encode($url);
	$destino = '';
	$url_tte = sprintf ("./crear_ttealbaran.php?accion=%s&numero=%s&estado=%s&cliente=%s&empr=%s", 
	1,$numero,$estado, $cliente, $empr);
	$url_tte=$codep->encode($url_tte);

	////////////////// enviado email
	$directorio_ventas = $originales.'ventas/'.$aaaa.'/'.$mm.'/enviados/'.$numero_pdf.'_email.pdf';
	if (is_file($directorio_ventas))    $enviado_email = 'SI';
	////////////////// enviado impresora
	$directorio_ventas = $originales.'ventas/'.$aaaa.'/'.$mm.'/'.$numero_pdf.'.pdf';
	if (is_file($directorio_ventas))    $enviado_impresora = 'SI';	

	$urleti = 
	sprintf ("../menu_etiquetas.php?action=%d&factura=%s&importe=%s&empr=%s", 2, 
	$numero, $baseimp, $empr);
	$urleti=$codep->encode($urleti);

	$url_obs = sprintf ("observaciones_albaran.php?numero=%s&estado=%s&empr=%s", 
	$numero,$estado, $empr);
	$url_obs=$codep->encode($url_obs);

	$generacion_masiva = 'SI';
	$destino = '../vistas/00_email'.$alias_pdf.'.php';	
	$url_ema = sprintf ($destino."?numero_pdf=%s&cliente=%s&empr=%s&numero=%s&estado=%s&fecha=%s",
	$numero_pdf, $cliente,$empr,$numero,$estado,$hoy);
	$url_ema=$codep->encode($url_ema);

	$enviado_email = 'NO';
	$directorio_ventas = $originales.'ventas/'.$aaaa.'/'.$mm.'/enviados/'.$numero_pdf.'_email.pdf';
	if (is_file($directorio_ventas))    $enviado_email = 'SI';
	
    echo"<fieldset><legend>Opciones del albarán o factura <font color=$textcolor4>$numero_pdf </font>de 
	<font color=$textcolor4>$nom_cli</font>.
	</legend>";
    echo "<br><TABLE align=center width=97% class=gris>";
    echo "<tr class=odd>";
    echo "<td align=left>  <div id=tit2>Cliente</div></td>";
    echo "<td align=left>  <div id=tit2>Nombre</div></td>";
    echo "<td align=right> <div id=tit2>Total</div></td>";
    echo "<td align=center><div id=tit2>T</div></td>";
    echo "<td align=left>  <div id=tit2>Factura A4</div></td>";
    echo "<td align=left>  <div id=tit2>Email</div></td>";	
    echo "<td align=center><div id=tit2>Etiq</div></td>";
	echo "<td align=center><div id=tit2>Obs</div></td>";	
    echo "<td align=center><div id=tit2>Tte</div></td>";		
    echo "<td align=left>  <div id=tit2>CMR</div></td>";
	echo "<td align=left>  <div id=tit2>VIES</div></td>";		
    echo "<td align=left>  <div id=tit2>TAI</div></td>";	
    echo "<td align=left>  <div id=tit2>COA</div></td>";	
    echo "</tr>";
    $a = 0;
 
 ///////////////////////////////////////////////////////
	include_once('../emilios_venta.php');
    $url_emilios = sprintf ("./emilios_cliente.php?accion=%s&cliente=%s",
    1, $cliente);
    $url_emilios=$codep->encode($url_emilios);
	
    echo "<tr "; if( $a%2 == 1 ) echo " class=odd";
    echo "><td class=firstcol align=left> <div id=tit3 class=line>
	<A HREF=javascript:ventana('$url_emilios','ventemi','720','600','yes');>
    <img src=../../img/email_add.png height=12 border=0 style='vertical-align:middle;'></A>&nbsp;
	$cliente</div></td>";	

    echo "<td align=left>  <div id=tit3>$nomb_cli</div></td>";
	echo "<td align=right> <div id=tit3>$total_alb</div></td>";
    echo "<td align=center><div id=tit3>$stat_alb</div></td>";	
 
	echo "<td align=left>  <div id=tit3>
	<A HREF=javascript:ventana('$url','ventana','800','565','yes');>$numero_pdf</A>";
	if($enviado_impresora == 'SI')
	echo "&nbsp;&nbsp;<img src=../../img/okmail.png border=0 height='10' style='vertical-align:middle;'>";	
	echo "</div></td>";

	////////////////// email
	$destino = './00_email'.$alias_pdf.'.php';		
	$numero_factura_pdf = $numero_pdf;
	$directorio_origen = $originales.'ventas/'.$aaaa.'/'.$mm.'/'.$numero_factura_pdf.'.pdf';	
	$directorio_destino = $originales.'/ventas/'.$aaaa.'/'.$mm.'/enviados/'.$numero_factura_pdf.'_email.pdf';	
	if (is_file($directorio_origen)) $directorio_pdf = '/publico/ventas/'.$aaaa.'/'.$mm.'/'.$numero_factura_pdf.'.pdf'; 	
	$url_ema = sprintf ($destino."?numero_pdf=%s&cliente=%s&empr=%s&numero=%s&estado=%s&fecha=%s&doc_email=%s&dO=%s&dD=%s",
	$numero_factura_pdf, $cliente_url,$empr,$nume_alb,$stat_alb,$hoy,'02',$directorio_origen,$directorio_destino);
	$destino = '';		
	
    echo "<td align=left><div id=tit3 class=line>";	
	if ($directorio_pdf != '')
    echo "
    <A HREF=javascript:ventana('$directorio_pdf','ventana','800','500','yes');>
    <img src=../../img/pdf.png height=16 border=0 style='vertical-align:middle;'></A>";
	else echo "&nbsp;";	
    if (trim($ema_cli) != '' && $enviado_impresora == 'SI') 
	{	
    echo "&nbsp;
	<A HREF=javascript:ventana('$url_ema','ventana','600','150','no');
	onclick='correo_enviando_$a(this)'	>
 	<img id='boton_email_$a' src=../../img/mail.png height=18 border=0 style='vertical-align:middle;'></A>&nbsp;";
	}
	else "&nbsp;";
    if ((trim($ema_cli) != '') && ($enviado_impresora == 'SI') && ($enviado_email == 'SI'))
    echo "<img src=../../img/okmail_azul.png border=0 height='10' style='vertical-align:middle;'>&nbsp;";
	else 
	echo "&nbsp;";
	echo "</div></td>";
	////////////////////////// etiqutas
    echo "<td align=center><div id=tit3>
    <A HREF=javascript:ventana('$urleti','urleti','640','530','no');>
    <img src=../../img/eti.png border=0 height='12' style='vertical-align:middle;'></A></div></td>";	
	/////////////////// observaciones
	if ($tiene_obs == 'SI')
    echo "<td align=center><div id=tit2>
	<A HREF=javascript:ventana('$url_obs','ventana','800','300','no');>
	<img src=../../img/bocadillo_fra.gif height=12 border=0  style='vertical-align:middle;'></a></div></td>";
 	if ($tiene_obs == 'NO')
    echo "<td align=center><div id=tit2>
	<A HREF=javascript:ventana('$url_obs','ventana','800','300','no');>
	<img src=../../img/bocadillo_vacio.gif height=12 border=0  style='vertical-align:middle;'></a></div></td>";	

	///////////////// transporte
	if ($cuenta_lin_ttes != 0)
    echo "<td align=center><div id=tit2>
	<A HREF=javascript:ventana('$url_tte','ventana','800','400','no');>
	<img src=../../img/camion_lleno.png height=16 border=0 style='vertical-align:middle;'></a></div></td>";
 	if ($cuenta_lin_ttes == 0)
    echo "<td align=center><div id=tit2>
	<A HREF=javascript:ventana('$url_tte','ventana','800','400','no');>
	<img src=../../img/camion_vacio.png height=16 border=0 style='vertical-align:middle;'></a></div></td>";
	
	//////////////////////// CMR ///////////////////////////////////
	$url_cmr = sprintf ("./crear_cmr.php?numero=%s&estado=%s&cliente=%s&empr=%s", 
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
	$destino = './00_email'.$alias_pdf.'.php';		
	$url_email_cmr = sprintf ($destino."?cliente=%s&numero_cmr=%s&empr=%s&dO=%s&dD=%s&doc_email=%s", 
	$cliente,$numero_cmr,$empr,$directorio_cmr,$directorio_enviado_cmr,'05');
	$url_email_cmr = $codep->encode($url_email_cmr);
	$destino = '';
    echo "<td align=left><div id=tit3 class=line>
    <A HREF=javascript:ventana('$url_cmr','ventana','800','650','yes');>
    <img src='../../img/CMR.png' height='16' border=0 style='vertical-align:middle;'>
	&nbsp;</a>";
	if ($url_duplicado_cmr != '')
	echo "	
    <A HREF=javascript:ventana('$url_duplicado_cmr','ventana','800','500','yes');>
    <img src='../../img/pdf.png' height='16' border=0 style='vertical-align:middle;'></A>";
    if ((trim($ema_cli) != '') && ($url_duplicado_cmr != ''))
    echo "
	<A HREF=javascript:ventana('$url_email_cmr','ventana','600','150','no');>
 	<img src=../../img/mail.png height=18 border=0 style='vertical-align:middle;'></A>";
    if ((trim($ema_cli) != '') && ($enviado_email_cmr == 'SI') && ($url_duplicado_cmr != ''))
    echo "&nbsp;<img src=../../img/okmail.png border=0 height='10' style='vertical-align:middle;'>";	
	echo"</div></td>";	
	

	////////////////////////////// comprobar VIES
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
	if(!$fec_vies) $fec_vies = date('Y-m-d');
	$vatid = str_replace(array(' ', '.', '-', ',', ', '), '', trim($cif_cli));
	$cc = substr($vatid, 0, 2);
	$vn = substr($vatid, 2);	
    odbc_free_result ($res_vies); 
	if(!$era_vies) $era_vies == 'ALTA';
	$url_vies = sprintf ("./vies_ver.php?accion=%d&cc=%s&vn=%s&cif_cli=%s", 
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
    <img src=../../img/compras.png height=14 border=0 style='vertical-align:middle;'></A>";		
	else
	echo"	
    <A HREF=javascript:ventana('$url_vies','ventana','800','175','yes');>
    <img src=../../img/ventas.png height=14 border=0 style='vertical-align:middle;'></A>";	
	echo"</div></td>";		
	}
	else
	{
    echo "<td align=left><div id=tit3>&nbsp;</div></td>";	
	}
	
	//////////////////////// Contratos TAI ///////////////////////////////////////
	$destino = '../../fpdf/contrato_vies'.$alias_pdf.'.php';	
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
	$destino = './00_email'.$alias_pdf.'.php';	
	$url_email_tai = sprintf ($destino."?cliente=%s&numero_cto=%s&empr=%s&dO=%s&dD=%s&doc_email=%s", 
	$cliente,$numero_cto,$empr,$directorio_cto,$directorio_enviado_tai,'06');
	$url_email_tai = $codep->encode($url_email_tai);	
	if ($pai_cli != 'ES')
	{	
    echo "<td align=".$albaranes_dia_ALIGN["TAI"]."><div id=tit3 class=line>
    <A HREF=javascript:ventana('$url_cto','ventana','780','460','yes');>
    <img src=../../img/editar_verde.png height=18 border=0 style='vertical-align:middle;'></A>";
	if ($url_duplicado_cto != '')
	echo"	
    <A HREF=javascript:ventana('$url_duplicado_cto','ventana','800','500','yes');>
    <img src=../../img/pdf_verde.png height=16 border=0 style='vertical-align:middle;'></A>";
    if ((trim($ema_cli) != '') && ($url_duplicado_cto != ''))
    echo "
	<A HREF=javascript:ventana('$url_email_tai','ventana','600','150','no');>
 	<img src=../../img/mail_verde.png height=20 border=0 style='vertical-align:middle;'></A>";	
    if ((trim($ema_cli) != '') && ($enviado_email_tai == 'SI') && ($url_duplicado_cto != ''))
    echo "&nbsp;<img src=../../img/okmail.png border=0 height='10' style='vertical-align:middle;'>";	
	echo"</div></td>";	
	}
	else		
    echo "<td align=center><div id=tit3>&nbsp;</div></td>";	

	//////////////////////// Contratos COA ///////////////////////////////////////
	$destino = '../../fpdf/contrato_vies_coa.php';	
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
	$destino = './email_vies_coa.php';
	$url_email_coa = sprintf ($destino."?cliente=%s&numero_coa=%s&numero_pdf=%s&fecha=%s&empr=%s&d_coa=%s&d_alb=%s&d_env=%s&doc_email=%s", 
	$cliente,$numero_coa,$numero_factura_pdf,$hoy,$empr,$directorio_coa,$directorio_origen,$directorio_enviado_coa,'07');
	$url_email_coa = $codep->encode($url_email_coa);
	
	if ($pai_cli != 'ES' && $url_duplicado_cto != '')
	{	
    echo "<td align=".$albaranes_dia_ALIGN["COA"]."><div id=tit3 class=line>
    <A HREF=javascript:ventana('$url_coa','ventana','780','460','yes');>
    <img src=../../img/editar_rojo.png height=18 border=0 style='vertical-align:middle;'></A>";
	if ($url_duplicado_coa != '')
	echo"	
    <A HREF=javascript:ventana('$url_duplicado_coa','ventana','800','500','yes');>
    <img src=../../img/pdf_rojo.png height=16 border=0 style='vertical-align:middle;'></A>";
	else echo "&nbsp;&nbsp;";		
    if ((trim($ema_cli) != '') && ($url_duplicado_coa != ''))
    echo "&nbsp;
	<A HREF=javascript:ventana('$url_email_coa','ventana','600','150','no');>
 	<img src=../../img/mail_rojo.png height=20 border=0 style='vertical-align:middle;'></A>&nbsp;";
	else echo "&nbsp;";
    if ((trim($ema_cli) != '') && ($enviado_email_coa == 'SI') && ($url_duplicado_coa != ''))
    echo "<img src=../../img/okmail_rojo.png border=0 height='10' style='vertical-align:middle;'>";
	else 
	echo "&nbsp;";		
	echo"</div></td>";	
	}
	else		
    echo "<td align=center><div id=tit3>&nbsp;</div></td>";	

	

    //echo "<td align=center><div id=tit3>$empr</div></td>";
    echo "</tr>"; 
    echo"</table><br>";
    echo"</div>";
    echo "</fieldset>";
	break;

    case '2':

	modifica_linea ($emp_contab,$inicio,$fin);
	?><script>
	opener.location.reload();
	self.close();
	</script><?php
	break;
}       
function modifica_linea ()
    {
	include($_SERVER['DOCUMENT_ROOT'].'/_configs/_config.php' );
	include_once($_SERVER['DOCUMENT_ROOT'].'/_configs/_header_ifx.php' );  

    global $PHP_SELF;
        
    $consulta = ("  select max(fech_logalbara) as fecha_max from logalbara
	where empr_logalbara IN $emp ");
	$resultado=odbc_exec($conexion, $consulta);
    if(!$resultado){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}	
	$fecha_max = odbc_result($resultado,"fecha_max");
    odbc_free_result ($resultado);	
	$fecha_max = reset_fechaa(substr($fecha_max,0,10));
	
    $consulta_borrar = " delete from logalbara
	where empr_logalbara IN $emp
	and   date(fech_logalbara) < '$fecha_max'	";
	$res_borrar = odbc_exec($conexion, $consulta_borrar);
    if(!$res_borrar){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}	
    odbc_free_result ($res_borrar);
}	 
?>