<?php
	session_save_path('../sat/tmp/sat');
	session_start();
	define('FPDF_FONTPATH','font/');
	require('formato_sat_cmr.php');
	include($_SERVER['DOCUMENT_ROOT'].'/_configs/_config_sat.php' );
	include_once($_SERVER['DOCUMENT_ROOT'].'/_configs/consultas_sat.php' );
	include_once($_SERVER['DOCUMENT_ROOT'].'/sat/datos_y_funciones_sat_pdf.php' );
	include_once($_SERVER['DOCUMENT_ROOT'].'/_configs/funciones.php' );
	include_once($_SERVER['DOCUMENT_ROOT'].'/_clases/phpsecureurl.php' );
	$codep = new phpsecureurl ;
	if($_REQUEST[hmg]){$url2=$codep->decode($url2);}

	require_once('../_configs/cloudflare_r2_deca.php');
	require_once('../_configs/funciones_r2_deca.php');

    global 	$PHP_SELF, $numero, $estado, $cliente, $en_destino, $transportista,$ejercicio_deca,
	$fecha, $instr_5, $instr_16, $instr_18, $chofer, $chofer_nif,$referencia_deca,$id_deca,
	$matricula, $empr, $dest1, $dest2, $paisdestino,$nombre_pdf_deca,$ruta_pdf_deca;

    $hoy = $_SESSION["fechadetrabajo"];
	if (trim($fecha !=''))	
	{$fechainicial=$fecha; $fechafinal=reset_fecha($fecha);} 
	else $fechainicial=$hoy;

	$aaaa = select_ano_ ($fechainicial);
	if ( ($estado == 'R') || ($estado == 'C') ) {$cod_cmr = 'F'; $cod_ccc = '1';}
    if ($estado == 'A') {$cod_cmr = 'A';  $cod_ccc = '3';}

	$numero_cmr = $aaaa.$cod_cmr.$empr.str_pad($numero, 6, "0", STR_PAD_LEFT);
    $numero_ccc = $aaaa.$cod_ccc.$empr.str_pad($numero, 6, "0", STR_PAD_LEFT);

    if (trim($fecha !=''))
    $consulta_total = "	select sum(bul_hliven) as total_bultos, sum(bru_hliven) as total_bruto
    from ffhliven
    where emp_hliven = '$empr'
    and   cli_hliven = '$cliente'
    and   fec_hliven = '$fechafinal'
    and   alb_hliven =  $numero
	and   ind_hliven = '$estado'        ";
	else	
    $consulta_total = " select sum(bult_lin) as total_bultos, sum(brut_lin) as total_bruto
    from fflineas, ffalbara
    where empr_lin = '$empr'
    and   empr_alb = empr_lin
    and   nume_alb = $numero
    and   alba_lin = nume_alb
    and   stat_alb = '$estado'
    and   clie_lin = clie_alb     ";
    $total = odbc_exec ($conexion, $consulta_total);
    if(!$total){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}
    $row = odbc_fetch_row($total);
	$total_bultos = odbc_result($total,"total_bultos");
	$total_bruto = odbc_result($total,"total_bruto");
    odbc_free_result ($total);

 	if(trim($transportista != ''))
	{
    $tte = " select * from ffremite where cod_rem = '$transportista' and acreoprove = 'A'   ";
    $tte = odbc_exec ($conexion, $tte);
	if(!$tte){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}
	$t_nom_cli = limpiar(odbc_result($tte,"nom_rem"));
	$t_dir_cli = utf8_encode(limpiar(odbc_result($tte,"dir_rem")));
	$t_pob_cli = limpiar(odbc_result($tte,"pob_rem"));
	$t_num_cli = limpiar(odbc_result($tte,"num_rem"));
	$t_pro_cli = limpiar(odbc_result($tte,"pro_rem"));
	$t_cpo_cli = limpiar(odbc_result($tte,"cpo_rem"));
	$t_cif_cli = limpiar(odbc_result($tte,"cif_rem"));
	$t_pai_cli = limpiar(odbc_result($tte,"pai_rem"));
    odbc_free_result ($tte);
	}

    $quien = " select * from ffclient where cod_cli = '$cliente' ";
	$quien=odbc_exec($conexion, $quien);
    if(!$quien){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}
    $row = odbc_fetch_row($quien);
 	$ro_nom_cli = limpiar(odbc_result($quien,"nom_cli"));
 	$ro_dir_cli = utf8_encode(limpiar(odbc_result($quien,"dir_cli")));
 	$ro_pob_cli = limpiar(odbc_result($quien,"pob_cli"));
 	$ro_num_cli = limpiar(odbc_result($quien,"num_cli"));
 	$ro_pro_cli = limpiar(odbc_result($quien,"pro_cli"));
 	$ro_cpo_cli = limpiar(odbc_result($quien,"cpo_cli"));
 	$ro_cif_cli = limpiar(odbc_result($quien,"cif_cli"));
 	$ro_pai_cli = limpiar(odbc_result($quien,"pai_cli"));
    odbc_free_result ($quien);

	$pdf = new INVOICE( 'P', 'mm', 'A4' );
	$pdf->Open();
	$pdf->AddPage();

	$pdf->Ellipse(0,107,7,8,5,df);
    $pdf->titulo_deca( "$numero_cmr");
    $pdf->marco_deca();
    $pdf->marco_deca_1();
	$pdf->addSociete_deca(
    "$empresa\n" ,
    "$direccion\n" .
    "$direccion1\n".
    "$direccion2\n" .
    "CIF.: $cif\n");
    $pdf->marco_deca_678();
    $pdf->marco_deca_6($matricula);
    if(trim($transportista != ''))
	{
    $pdf->addSociete_deca_6(
	"$t_nom_cli\n" ,
	"$t_dir_cli\n" .
	"$t_cpo_cli $t_pob_cli\n" .
	"$t_pro_cli ( $t_pai_cli )\n" .
	"CIF.: $t_cif_cli\n");
	}	else	{
    $pdf->addSociete_deca_6(
	"$ro_nom_cli\n" ,
    "$ro_dir_cli\n" .
	"$ro_cpo_cli $ro_pob_cli\n" .
    "$ro_pro_cli ( $ro_pai_cli )\n" .
	"CIF.: $ro_cif_cli\n");
	}
    $pdf->marco_deca_7();
    $pdf->marco_deca_2();
    $pdf->addSociete_2(
    "$ro_nom_cli\n" ,
    "$ro_dir_cli\n" .
    "$ro_cpo_cli $ro_pob_cli\n" .
    "$ro_pro_cli ( $ro_pai_cli )\n" .
    "CIF.: $ro_cif_cli\n");
    $pdf->marco_deca_3();

	$paisdestino = $paises[$paisdestino];
	$paisdestino = utf8_encode($paisdestino);
	
	if(!$dest1) $dest1 = $ro_dir_cli;
	if(!$dest2) $dest2 = $ro_cpo_cli.' '.$ro_pob_cli.' '.$ro_pro_cli;
	$dest2 = $dest2.'  ( '.$paisdestino.' )';
    $pdf->addClientAdresse_3("$dest1\n$dest2");
    $pdf->marco_deca_4();     
    $pdf->addClientAdresse_4("$direccion1, $direccion2\n$fechainicial");
    $pdf->marco_deca_5($instr_5);
    $pdf->marco_deca_9($cod_cmr);
	$centro_venta = '00';
	$numero_fra = str_pad($numero,6,'0',STR_PAD_LEFT);	
	$seriefactura = serie_factura ($numero, $aaaa, $estado, $empr, $centro_venta);	
	$numero_pdf = $seriefactura.'-'.$numero_fra;	
    $pdf->addClientAdresse_9($numero_pdf);

	$cols=array(
    "Marcas" 	   		=> 30,
    "Bultos"           	=> 15,
    "Envase"           	=> 30,
    "Mercancia"   	   	=> 80,
    "Peso bruto en kg" 	=> 25,
    "Volumen en m3"    	=> 20.2);
    $pdf->addCols_venta_deca($cols);
    $cols=array(
    "Marcas"           	=> "L",
    "Bultos"           	=> "R",
	"Envase"           	=> "L",
	"Mercancia"        	=> "L",
	"Peso bruto en kg" 	=> "R",
    "Volumen en m3"    	=> "R");
    $pdf->addLineFormat($cols);

	$y    = 113;

	if (trim($fecha !=''))
    $consulta = " select distinct des_famili, mar_hliven[1,4] as marc_lin, 
	env_hliven as enva_lin,
	sum(bul_hliven) as bult_lin, 
	sum(bru_hliven) as brut_lin
    from  ffhliven, ffarticu, fffamili
    where trim(emp_hliven) = '$empr'
    and   cli_hliven = '$cliente'
	and   fec_hliven = '$fechafinal'
    and   alb_hliven =  $numero
    and   ind_hliven = '$estado'
	and   cod_art    =  art_hliven
    and   fam_art    =  cod_famili   
    group by 1,2,3			                     
	order by 1                ";
	else   
    $consulta = " select des_famili, marc_lin[1,4] marc_lin, enva_lin, 
	sum(bult_lin) bult_lin, 
	sum(brut_lin) brut_lin
    from  ffarticu, fflineas, fffamili
    where empr_lin = '$empr'
    and   alba_lin =  $numero
    and   indi_lin = '$estado'
    and   cod_art  =  arti_lin
    and   fam_art  =  cod_famili
    group by 1,2,3
    order by 1          ";
    $resultado = odbc_exec ($conexion, $consulta);
    if(!$resultado){$err = odbc_errormsg();
	printf('Error en '.__FILE__.' linea '.__LINE__.', motivo --->    %s ', $err);}
 
	$bultos = 0;
	$bruto = 0;
	$lineas=1;
	
	while( (odbc_fetch_row($resultado)) && ($lineas<30))
	{
	$enva_lin = odbc_result($resultado,"enva_lin");	  
	$des_env = envase($enva_lin);
    if (!$des_env) $des_env = ' ';	
	$marc_lin = odbc_result($resultado,"marc_lin");	  
 	$cod_marca = substr($marc_lin,0,1);
	$des_marca = $marca_com[$cod_marca];  	
	$bult_lin = odbc_result($resultado,"bult_lin");
	$brut_lin = odbc_result($resultado,"brut_lin");
    $bultos += $bult_lin;
    $bruto += $brut_lin;
    $bult_lin=number_format($bult_lin,0);
    $brut_lin=number_format($brut_lin,2,",",".");
	$des_famili = limpiar(odbc_result($resultado,"des_famili"));        

    $line = array(
   	"Marcas" 			=> "$des_marca",
  	"Bultos"  			=> "$bult_lin",
   	"Envase"     		=> "$des_env",
  	"Mercancia"  		=> "$des_famili",
  	"Peso bruto en kg"  => "$brut_lin",
   	"Volumen en m3"  	=> " ");
	$size = $pdf->addLine($y,$line);
	$y   += $size - 0.5;
	$lineas++;
   	}
	if ($lineas == 30)
	{
	$total_bultos = $total_bultos - $bultos;
    $total_bruto = $total_bruto - $bruto;
	if(($total_bultos >0) || ($total_bruto>0))
	{
    $total_bultos=number_format($total_bultos,0);
    $total_bruto=number_format($total_bruto,2,",",".");

    $line = array(
	"10 Marcas" 			=> "...",
	"11 Bultos"  			=> "$total_bultos",
	"12 Envase"       		=> "...",
	"13 Mercancia"     		=> "Resto de mercancia en documento adjunto ",
   	"14 Peso bruto en kg"   => "$total_bruto",
   	"15 Volumen en m3"     	=> " ");
    $size = $pdf->addLine($y,$line);
    $y   += $size - 0.5;
    $lineas++;
	}
	}
    $pdf->marco_deca_16($instr_16);
    $pdf->marco_deca_17($en_destino);
    $pdf->marco_deca_18($instr_18);
    $pdf->marco_deca_19();
//    $pdf->marco_rojo_20($pagina);
    $pdf->marco_deca_21($direccion2);
    $pdf->addClientAdresse_deca_21("$fechainicial");
    $pdf->marco_deca_22($empresa);
    $pdf->logo();
    $pdf->marco_deca_23($matricula);

	$chofer = trim ($chofer);
    $chofer_nif = trim ($chofer_nif);

    if(trim($chofer != ''))
	$addClientAdresse_23 = 	"$chofer\nN.I.F.: $chofer_nif";
	else if(trim($transportista != ''))
	$addClientAdresse_23 = 	"$t_nom_cli\nN.I.F.: $t_cif_cli";		
    else 
	$addClientAdresse_23 = 	"$ro_nom_cli\nN.I.F.: $ro_cif_cli";
	$addClientAdresse_23 = utf8_encode($addClientAdresse_23);
	$pdf->addClientAdresse_23($addClientAdresse_23);
 	$pdf->SetAutoPageBreak(true,0);                        		
	$pdf->marco_deca_24($ro_nom_cli);

    odbc_free_result ($resultado);



	$r2_object = $ejercicio_deca . '/' . $referencia_deca . '.pdf';
	
	//////////////////
	$url_deca = generarUrlFirmadaR2(
    $r2_object,
    $r2_bucket,
    $r2_endpoint,
    $r2_access_key,
    $r2_secret_key,
    604800
	);
	if ($url_deca === false) {die('Error generando URL firmada R2');}
	require_once('formato_qr_deca.php');
	$msg_deca = utf8_encode($url_deca);	
	$pdfQR = new QR();
	$qrcode = new QRcode($msg_deca, 'M');
	$qr = 'deca.png';
	$qrcode->displayPNG(1,"$qr", $background=array(255,255,255), $color=array(0,0,0));
	$pdf->imprimir_qr_deca($qr);
	/////////////////////
	$pdf->Output($ruta_pdf_deca,'F');

	$resultado_r2 = subirPdfR2(
    $ruta_pdf_deca,
    $r2_object,
    $r2_bucket,
    $r2_endpoint,
    $r2_access_key,
    $r2_secret_key
	);

	if (!$resultado_r2['ok']) {print_r($resultado_r2);die('ERROR SUBIENDO DECA A R2: ' .$resultado_r2['error']);}
	
	$r2_object_sql = str_replace("'", "''", $r2_object);
	$url_deca_sql  = str_replace("'", "''", $url_deca);
	$nombre_pdf    = $referencia_deca . '.pdf';
	$nombre_pdf_sql = str_replace("'", "''", $nombre_pdf);
	$referencia_php_sql = str_replace("'", "''", $numero_cmr);	
	
	$sql = " UPDATE phpdeca SET
        r2_object 		= '".$r2_object_sql."',
        nombre_pdf 		= '".$nombre_pdf_sql."',
        url_deca    	= '".$url_deca_sql."',
        referencia_php  = '".$referencia_php_sql."',		
        fecha_modif 	= CURRENT YEAR TO SECOND,
        estado      	= 'G'		
    WHERE id_deca 		= ".$id_deca;
	$rs = odbc_exec($conexion, $sql);
	if (!$rs) {die('Error actualizando R2 en phpdeca: ' .odbc_errormsg($conexion));}
////////////
$resultado_descarga = descargarPdfR2(
    $r2_object,
    $r2_bucket,
    $r2_endpoint,
    $r2_access_key,
    $r2_secret_key
);
	$pdf->Output();
?>