<?php 
$formato_albaran_dia_pdf = 'B5_albaran_VF';
$formato_proforma_dia_pdf = 'B5_albaran';
//$formato_albaran_dia_pdf = 'B5_albaran_VF';
$formato_albaran_opc_pdf = 'A4_albaran_VF';
$formato_nota_entrega_pdf = 'B5_nota_entrega';

$nif_simplificado = '00000000T';
$estados_facturas_todas = array("R","C","Q","F","r","c","q","f");
$estados_facturas_negativas = array("r", "c", "q", "f");
$sql_EFT = "('R','C','Q','F','r','c','q','f')";	

	$directorio_ventas = $originales.'/ventas/';   
	if (!is_dir($directorio_ventas))        mkdir($directorio_ventas, 0777);
	$directorio_ventas = $originales.'/ventas/'.$aaaa;
	if (!is_dir($directorio_ventas))        mkdir($directorio_ventas, 0777);
	$directorio_ventas = $originales.'/ventas/'.$aaaa.'/'.$mm.'/';
	if (!is_dir($directorio_ventas))        mkdir($directorio_ventas, 0777);
	$directorio_ventas = $originales.'/ventas/'.$aaaa.'/'.$mm.'/enviados/';
	if (!is_dir($directorio_ventas))        mkdir($directorio_ventas, 0777);
	$directorio_ventas = $originales.'/cmr/';   
	if (!is_dir($directorio_ventas))        mkdir($directorio_ventas, 0777);	
	$directorio_ventas = $originales.'/cmr/enviados/';   
	if (!is_dir($directorio_ventas))        mkdir($directorio_ventas, 0777);
	
$albaranes_dia_TH = array (	
			"Cliente"  		=>  'SI' ,
			"Nombre"  		=>  'SI' ,
			"Merc"  		=>  'SI' ,
			"Env"  			=>  'SI' ,
			"Total"  		=>  'SI' ,
			"T"  			=>  'SI' ,	
			"Número"  		=>  'SI' ,
			"Email"  		=>  'SI' ,
			"Nota"  		=>  'SI' ,
			"Opc"  			=>  'SI' ,			
			"Etiq"  		=>  'NO' ,
			"Obs"  			=>  'SI' ,
			"FaE"  			=>  'NO' ,			
			"Orden"			=>  'SI' ,
			"Tte"  			=>  'NO' ,
			"CMR" 			=>  'NO' ,					
			"VIES" 			=>  'NO' ,	
			"TAI"			=>  'NO' ,
			"COA"			=>  'NO' ,			
			"Pre"  			=>  'NO' 
			);

if (is_dir($directorio_facturae) && $facturaE_dia == "SI")  $albaranes_dia_TH["FaE"] = 'SI';       
			
$albaranes_dia_ALIGN = array (	
			"Cliente"  		=>  'left' ,
			"Nombre"  		=>  'left' ,
			"Merc"  		=>  'right' ,
			"Env"  			=>  'right' ,
			"Total"  		=>  'right' ,
			"T"  			=>  'center' ,	
			"Número"  		=>  'left' ,
			"Email"  		=>  'left' ,
			"Nota"  		=>  'center' ,
			"Opc"  			=>  'center' ,			
			"Etiq"  		=>  'center' ,
			"Obs"  			=>  'center' ,
			"FaE"  			=>  'center' ,
			"Orden"			=>  'left' ,
			"Tte"  			=>  'center' ,
			"CMR" 			=>  'left' ,					
			"VIES" 			=>  'center' ,	
			"TAI"			=>  'left' ,
			"COA"			=>  'left' ,			
			"Pre"  			=>  'center' 
			);			
?>