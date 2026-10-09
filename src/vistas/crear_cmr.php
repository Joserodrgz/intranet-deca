<?php
session_save_path('../tmp/informix');
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/_configs/_config.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/_header_ifx.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/js/js.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/colores.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/funciones.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/consultas.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_clases/phpsecureurl.php');
$codep = new phpsecureurl;

if ($_REQUEST['hmg']) {
  $url12 = $codep->decode($url12);
}

global $PHP_SELF, $numero, $estado, $cliente, $empr;

echo "<body onload='document.forms.libro.transportista.focus()'>";

$nom_cli = cliente($cliente);
$hoy = $_SESSION["fechadetrabajo"];
$aaaa = select_ano_($hoy);
$ejercicio_deca = (int) date('Y');

//////////////////////////

$sql = "
  SELECT MAX(numero) AS ultimo_numero
  FROM phpdeca
  WHERE ejercicio = " . $ejercicio_deca . "
";
$rs = odbc_exec($conexion, $sql);

if (!$rs) {
  die('Error obteniendo numero DeCA: ' . odbc_errormsg($conexion));
}

$row = odbc_fetch_array($rs);

if ($row && $row['ultimo_numero'] !== null) {
  $numero_deca = (int) $row['ultimo_numero'] + 1;
} else {
  $numero_deca = 1;
}

odbc_free_result($rs);

$referencia_deca = 'DECA-' . $ejercicio_deca . '-' . str_pad($numero_deca, 6, '0', STR_PAD_LEFT);
$token_deca = bin2hex(openssl_random_pseudo_bytes(32));
$fecha_creacion_deca = date('Y-m-d H:i:s');
$referencia_deca_sql = str_replace("'", "''", $referencia_deca);
$token_deca_sql = str_replace("'", "''", $token_deca);
$nombre_pdf_deca = $referencia_deca_sql . '.pdf';
$directorio_deca = $originales . 'deca/' . date('Y') . '/' . date('m') . '/';

if (!is_dir($directorio_deca)) {
  mkdir($directorio_deca, 0777, true);
}

$ruta_pdf_deca = $directorio_deca . $nombre_pdf_deca;
$nombre_pdf_deca_sql = str_replace("'", "''", $nombre_pdf_deca);

$sql = "
  INSERT INTO phpdeca
  (
    ejercicio,
    numero,
    referencia,
    token,
    fecha_creacion,
    estado
  )
  VALUES
  (
    " . $ejercicio_deca . ",
    " . $numero_deca . ",
    '" . $referencia_deca_sql . "',
    '" . $token_deca_sql . "',
    CURRENT YEAR TO SECOND,
    'B'
  )
";
$rs = odbc_exec($conexion, $sql);

if (!$rs) {
  die('Error creando DeCA: ' . odbc_errormsg($conexion));
}

$sql = "
  SELECT id_deca
  FROM phpdeca
  WHERE token = '" . $token_deca_sql . "'
";
$rs = odbc_exec($conexion, $sql);

if (!$rs) {
  die('Error obteniendo id_deca: ' . odbc_errormsg($conexion));
}

if (odbc_fetch_row($rs)) {
  $id_deca = (int) odbc_result($rs, 1);
} else {
  die('Error: no se encuentra el DeCA recién creado');
}
odbc_free_result($rs);

$sql = "
  UPDATE phpdeca
  SET nombre_pdf = '" . $nombre_pdf_deca_sql . "'
  WHERE id_deca = " . $id_deca . "
";
$rs = odbc_exec($conexion, $sql);

if (!$rs) {
  die('Error actualizando PDF DeCA: ' . odbc_errormsg($conexion));
}

$quien_tte = "
  SELECT *
  FROM satalbtte
  WHERE emp_satalbtte = '$empr'
    AND alb_satalbtte = $numero
    AND sta_satalbtte = '$estado'
    AND ano_satalbtte = '$aaaa'
";
$res_tte = odbc_exec($conexion, $quien_tte);

if (!$res_tte) {
  $err = odbc_errormsg();
  printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
}

$cod_satalbtte = trim(odbc_result($res_tte, "cod_satalbtte"));
$mat_satalbtte = trim(odbc_result($res_tte, "mat_satalbtte"));
$cond_satalbtte = limpiar(odbc_result($res_tte, "cond_satalbtte"));
$nif_satalbtte = limpiar(odbc_result($res_tte, "nif_satalbtte"));
$dest1_satalbtte = trim(odbc_result($res_tte, "dest1_satalbtte"));
$dest2_satalbtte = trim(odbc_result($res_tte, "dest2_satalbtte"));
$pai_satalbtte = trim(odbc_result($res_tte, "pai_satalbtte"));
$en_destino = trim(odbc_result($res_tte, "en_destino"));

odbc_free_result($res_tte);

$quien = "
  SELECT DISTINCT
    dir_cli,
    num_cli,
    cpo_cli,
    pob_cli,
    pro_cli,
    pai_cli
  FROM ffclient
  WHERE (cod_cli = '$cliente')
";
$quien = odbc_exec($conexion, $quien);

if (!$quien) {
  $err = odbc_errormsg();
  printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
}

$ro_dir_cli = limpiar(odbc_result($quien, "dir_cli"));
$ro_pob_cli = limpiar(odbc_result($quien, "pob_cli"));
$ro_pro_cli = limpiar(odbc_result($quien, "pro_cli"));
$ro_cpo_cli = limpiar(odbc_result($quien, "cpo_cli"));
$ro_pai_cli = limpiar(odbc_result($quien, "pai_cli"));

odbc_free_result($quien);

if (!$dest1_satalbtte) {
  $dest1_satalbtte = $ro_dir_cli;
}
if (!$dest2_satalbtte) {
  $dest2_satalbtte = $ro_cpo_cli . ' ' . $ro_pob_cli . ' ' . $ro_pro_cli;
}

$a = 1;

if (($estado == 'R') || ($estado == 'C')) {
  echo "<fieldset><legend>Crear &nbsp; <img src=../../img/CMR.png width='24' height='16' class='middle'> 
    &nbsp;de la factura $numero del almacén $empr de $nom_cli &nbsp;&nbsp;</legend>";	
}

if ($estado == 'A') {
  echo "<fieldset><legend>Crear &nbsp; <img src=../../img/CMR.png  width='24' height='16' class='middle'>
    &nbsp;del albarán $numero de $nom_cli &nbsp;&nbsp;</legend>";
}

$url_cmr = sprintf("../../fpdf/cmr.php");

if ($pai_satalbtte == 'ES') {
  $url_cmr = sprintf("../../fpdf/deca.php");
}

include("../js_tte.php");
include("../js_paises.php");

echo "<br><TABLE align=center width=95% class=acceso>";
echo "<tr class=amarillo>";
echo "<FORM METHOD=post ACTION='' id='cmr_id' name=libro>";
echo "<td align=left colspan=4><div id=tit3>
	Si el transportista es el cliente, dejar el dato en blanco.<br><br>";
echo "</div></td></tr><tr class=amarillo>";

// Transportista
echo "<td align=left><div id=tit2>Transportista";
echo "<td align=left><input STYLE='text-align:left' type='text' name='transportista' id='cuentaText'
	size='5' maxlength='5' value='$cod_satalbtte' tabindex='1'
	onkeyup='this.value=this.value.toUpperCase()'
	onBlur='muestra_cuenta(this.value)'
	><align=left width=5%><a href='javascript: cargarLista_cuenta();'>
	<img src=../../img/magnifier.png height='16' class='middle'></a>";
echo "<align=left width=55%>&nbsp;&nbsp;<input type=text name=desc_cta id='desc_cta'
	size='29' maxlength='35' value='' ></td>";
echo "</tr><tr class=amarillo>";

// Conductor
echo "<td align=left colspan=1><div id=tit2>Conductor";
echo "<td align=left><input STYLE='text-align:left' type='text' name='chofer' id='chofer_id'
	size='40' maxlength='35' value='$cond_satalbtte' tabindex='2'
	onkeyup='this.value=this.value.toUpperCase()'
	></div></tr><tr class=amarillo>";

echo "<td align=left><div id=tit2>Conductor NIF";
echo "<td align=left><input STYLE='text-align:left' type='text' name='chofer_nif' id='chofer_nif_id'
	size='15' maxlength='15' value='$nif_satalbtte' tabindex='3'
	onkeyup='this.value=this.value.toUpperCase()'
	></div></tr><tr class=amarillo>";

echo "<td align=left><div id=tit2>Matrículas";
echo "<td align=left><input STYLE='text-align:left' type='text' name='matricula' id='matricula_id'
	size='25' maxlength='20' value='$mat_satalbtte ' tabindex='4'
	onkeyup='this.value=this.value.toUpperCase()'
	></div></tr><tr class=amarillo>";

// Destino
echo "<td align=left><div id=tit2>Destino";
echo "<td align=left><input STYLE='text-align:left' type='text' name='dest1' id='dest1_id'
	size='50' maxlength='49' value='$dest1_satalbtte' tabindex='5'
	onkeyup='this.value=this.value.toUpperCase()'
	></div></tr><tr class=amarillo>";

echo "<td align=left><div id=tit2>En";
echo "<td align=left><input STYLE='text-align:left' type='text' name='dest2' id='dest2_id'
	size='50' maxlength='49' value='$dest2_satalbtte' tabindex='6'
	onkeyup='this.value=this.value.toUpperCase()'
	></div></tr><tr class=amarillo>";

if (!$pai_satalbtte) {
  $pai_satalbtte = $ro_pai_cli;
}

echo "<td align=left><div id=tit2>País destino";
echo "<td align=left width=10%><input STYLE='text-align:left' type='text' name='paisdestino' id='paisText'
	size='6' maxlength='5' value='$pai_satalbtte' tabindex='5' readonly
	onkeyup='this.value=this.value.toUpperCase()'
	onBlur='muestra_pais(this.value)'
	><align=left width=5%>
	<img src=../../img/magnifier.png height='16' class='middle'>";
echo "<align=left width=55%>&nbsp;&nbsp;<input type=text name=desc_pais id='desc_pais'
	size='35' maxlength='35' value='' readonly></td>";
echo "</tr><tr class=amarillo>";
echo "</tr>";

// Pago del transporte
$a++;
echo "<tr ";
if ($a % 2 == 1) {
  echo " class=odd";
}
echo ">";
echo "<td align=left><div id=tit2>Pagar transporte";
echo "<td align=left colspan=3><div id=tit3><input type=radio name=en_destino value='S' ";
if ($en_destino == 'S') {
  echo "checked ";
}
echo "disabled>&nbsp;&nbsp;Destino&nbsp;&nbsp;&nbsp;&nbsp;";
echo " <input type=radio name=en_destino value='N' ";
if ($en_destino == 'N') {
  echo "checked ";
}
echo "disabled>&nbsp;&nbsp;Origen&nbsp;&nbsp;&nbsp;&nbsp; ";
echo "</div></td>";
echo "</tr><tr class=amarillo>";

// Instrucciones
echo "<td align=left colspan=2><div id=tit3>
	Instrucciones al porteador, siendo la más común entrega de
	documentos contra pago (pagaré de importe)";
echo "</div></td></tr><tr class=amarillo>";
echo "<td align=left><div id=tit2>05 Instrucciones del expedidor";
echo "<td align=left><input STYLE='text-align:left' type='text' name='instr_5' id='instr_5_id'
	size='40' maxlength='40' value='' tabindex='7'
	onkeyup='this.value=this.value.toUpperCase()'
	></div></tr><tr class=amarillo>";

echo "<td align=left colspan=2><div id=tit3>
	Hace referencia a las condiciones del transporte, por ejemplo la temperatura.";
echo "</div></td></tr><tr class=amarillo>";
echo "<td align=left><div id=tit2>16 Instrucciones al transportista";
echo "<td align=left><input STYLE='text-align:left' type='text' name='instr_16' id='instr_16_id'
	size='40' maxlength='40' value='TEMPERATURA +4ºC' tabindex='8'
	onkeyup='this.value=this.value.toUpperCase()'
	></div></tr><tr class=amarillo>";

echo "<td align=left colspan=2><div id=tit3>
	Estipulaciones particulares sobre la mercancía,
	por ejemplo, la velocidad para no dañarla o el <b>número de precinto</b>.";
echo "</div></td></tr><tr class=amarillo>";
echo "<td align=left><div id=tit2>18 Estipulaciones particulares";
echo "<td align=left><input STYLE='text-align:left' type='text' name='instr_18' id='instr_18_id'
	size='40' maxlength='40' value='' tabindex='9'
	onkeyup='this.value=this.value.toUpperCase()'
	></div></tr>";
echo "</table>";

// Buscador de cuentas
echo "<TABLE align=center width=50 class=acceso id='buscardor_cta' STYLE='display:none';>";
echo "<tr class=azul>";
echo "<td align=left><div id=tit2>Buscar cuenta por descripción</div></td></tr><tr>";
echo "<td align=left> <input type=text name=busc_cta id='busc_cta' size='50' maxlength='35' value=''
	onKeyUp='buscar_cuenta()'></td></tr><tr>";
echo "<td align=left width=50>
	<select style='width:99%; border:1px solid #04467E;color:#2D4167;' id='miCombocta' name='miCombocta' size=5
	onclick=muestra_cuenta_combo(this.value)></select></td>";
echo "</tr></table>";

// Buscador de países
echo "<TABLE align=center width=50 class=acceso id='buscador_pais' STYLE='display:none';>";
echo "<tr class=azul>";
echo "<td align=left><div id=tit2>Buscar país destino por nombre</div></td></tr><tr>";
echo "<td align=left> <input type=text name=busc_pais id='busc_pais' size='50' maxlength='35' value=''
	onKeyUp='buscar_pais()'></td></tr><tr>";
echo "<td align=left width=50>
	<select style='width:99%; border:1px solid #04467E;color:#2D4167;' id='miCombopais' name='miCombopais' size=5
	onclick=muestra_pais_combo(this.value)></select></td>";
echo "</tr></table>";

// Campos ocultos
echo "<INPUT TYPE=hidden NAME=numero VALUE=$numero>";
echo "<INPUT TYPE=hidden NAME=estado VALUE=$estado>";
echo "<INPUT TYPE=hidden NAME=cliente VALUE=$cliente>";
echo "<INPUT TYPE=hidden NAME=empr VALUE=$empr>";
echo "<INPUT TYPE=hidden NAME=aaaa VALUE=$aaaa>";
echo "<INPUT TYPE=hidden NAME=pai_cli VALUE=$pai_satalbtte>";
echo "<INPUT TYPE=hidden NAME=en_destino VALUE=$en_destino>";
echo "<INPUT TYPE=hidden NAME=nombre_pdf_deca VALUE=$nombre_pdf_deca>";
echo "<INPUT TYPE=hidden NAME=ruta_pdf_deca VALUE=$ruta_pdf_deca>";
echo "<INPUT TYPE=hidden NAME=ejercicio_deca VALUE=$ejercicio_deca>";
echo "<INPUT TYPE=hidden NAME=referencia_deca VALUE=$referencia_deca>";
echo "<INPUT TYPE=hidden NAME=id_deca VALUE=$id_deca>";

// $url_cto = sprintf("../../fpdf/sat_contrato_vies.php");

?>
<script>
	function muestradata() {
		checkSubmit();
		document.forms['libro'].action = '<?php echo $url_cmr; ?>';
		document.forms['libro'].target = '_blank';
		document.forms['libro'].submit();
		// window.setTimeout(function() {
		// 	paintTable();
		// }, 500);
	};

	/*
	function paintTable() {
		document.forms['libro'].action = '<?php echo $url_cto; ?>';
		document.forms['libro'].target = '_blank';
		document.forms['libro'].submit();
		self.close();
		opener.location.reload();
		return true;
	};
	*/
</script>
<?php

// Botones de envío y salida
echo "<TABLE align=center width=50% id='Tabla_guardar'>";
echo "<td align=right><div id=tit2>
	<input type=button value='Enviar'
	name=enviar tabindex='10'
	onClick='return muestradata();'
	class=search id='btsubmit'></div>";
echo "<td align=left><div id=tit1>
	<input type=reset value=Salir onClick=cerrarVentana() class=search></div></td>";
echo "</FORM>";
echo "</tr></table>";
echo "</fieldset>";
?>
