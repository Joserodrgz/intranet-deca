<?php
session_save_path('../tmp/informix');
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/_configs/_config.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/_header_ifx.php');
include($_SERVER['DOCUMENT_ROOT'] . '/_configs/colores.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/js/js.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/funciones.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/consultas.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_clases/phpsecureurl.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/informix/js_tte.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/informix/js_paises.php');
$codep = new phpsecureurl();
if ($_REQUEST[hmg]) {
  $url12 = $codep->decode($url12);
}
global $PHP_SELF, $numero, $estado, $cliente, $empr;
global $accion;
if (empty($accion)) {
  $accion = '1';
}
/////////////////////// miro si existen la tablas

$datos_actuales = "
  SELECT tabname
  FROM systables
  WHERE tabname = 'phpdeca'
";
$resultado_datos_actuales = odbc_exec($conexion, $datos_actuales);
if (!$resultado_datos_actuales) {
  $err = odbc_errormsg();
  printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
}
$tabname = trim(odbc_result($resultado_datos_actuales, "tabname"));
odbc_free_result($resultado_datos_actuales);
if (!$tabname) {
  /////////// creo la tabla phpdeca
  $crear = "
    CREATE TABLE phpdeca
    (
      id_deca SERIAL NOT NULL,
      ejercicio SMALLINT NOT NULL,
      numero INTEGER NOT NULL,
      referencia CHAR(30) NOT NULL,
      referencia_php CHAR(20),
      token CHAR(64) NOT NULL,
      fecha_creacion DATETIME YEAR TO SECOND NOT NULL,
      fecha_modif DATETIME YEAR TO SECOND,
      nombre_pdf CHAR(100),
      r2_object CHAR(255),
      url_deca CHAR(500),
      estado CHAR(1)
    )
  ";
  $resultado = odbc_exec($conexion, $crear);
  if (!$resultado) {
    $err = odbc_errormsg();
    printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
  }
  odbc_free_result($resultado);

  $indice = "
    CREATE UNIQUE INDEX ux_deca_id ON phpdeca (id_deca)
  ";
  $indexar = odbc_exec($conexion, $indice);
  if (!$indexar) {
    $err = odbc_errormsg();
    printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
  }
  odbc_free_result($indexar);

  $indice = "
    CREATE UNIQUE INDEX ux_deca_numero ON phpdeca (ejercicio, numero)
  ";
  $indexar = odbc_exec($conexion, $indice);
  if (!$indexar) {
    $err = odbc_errormsg();
    printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
  }
  odbc_free_result($indexar);

  $indice = "
    CREATE UNIQUE INDEX ux_deca_referencia ON phpdeca (referencia)
  ";
  $indexar = odbc_exec($conexion, $indice);
  if (!$indexar) {
    $err = odbc_errormsg();
    printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
  }
  odbc_free_result($indexar);

  $indice = "
    CREATE UNIQUE INDEX ux_deca_token ON phpdeca (token)
  ";
  $indexar = odbc_exec($conexion, $indice);
  if (!$indexar) {
    $err = odbc_errormsg();
    printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
  }
  odbc_free_result($indexar);

  $indice = "
    CREATE INDEX idx_deca_fecha ON phpdeca (fecha_creacion)
  ";
  $indexar = odbc_exec($conexion, $indice);
  if (!$indexar) {
    $err = odbc_errormsg();
    printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
  }
  odbc_free_result($indexar);

  $indice = "
    CREATE INDEX idx_deca_estado ON phpdeca (estado)
  ";
  $indexar = odbc_exec($conexion, $indice);
  if (!$indexar) {
    $err = odbc_errormsg();
    printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
  }
  odbc_free_result($indexar);
} // fin no existe php_emilios

switch ($accion) {
  ////////////////////////////////////////////////////////////////
  case '1':
    echo "<body onload='document.forms.libro.transportista.focus()'>";
    $nom_cli = cliente($cliente);
    $hoy = $_SESSION["fechadetrabajo"];
    $aaaa = select_ano_($hoy);
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
    $cia_satalbtte = limpiar(odbc_result($res_tte, "cia_satalbtte"));
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
        cpo_cli,
        pob_cli,
        pro_cli,
        pai_cli
      FROM ffclient
      WHERE cod_cli = '$cliente'
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
      $dest2_satalbtte = $ro_cpo_cli . ' ' . $ro_pob_cli;
    }
    //$dest2_satalbtte = $dest2_satalbtte.' ('.$pai_satalbtte.')';

    if (($estado == 'R') || ($estado == 'C')) {
      echo "<fieldset><legend>Insertar cuadro de firma del transportista para
	&nbsp; la factura $numero de $nom_cli &nbsp;&nbsp;&nbsp;</legend>";
    }
    if ($estado == 'A') {
      echo "<fieldset><legend>Insertar cuadro de firma del transportista para 
    &nbsp;el albar&aacute;n $numero de $nom_cli &nbsp;</legend>";
    }

    echo "<br><TABLE align=center width=65% class=gris>";
    echo "<tr class=azul>";
    echo "<td align=left><div id=tit2>
    Si el transportista es el cliente, dejar el dato en blanco";
    echo "</div></td></tr></table>";
    $a = 0;
    echo "<FORM METHOD=post ACTION='$_SERVER[PHP_SELF]' id='cmr_id' name=libro>";
    echo "<br><TABLE align=center width=75% class=gris name='previo'>";
    echo "<tr ";
    if ($a % 2 == 1) {
      echo " class=odd";
    }
    echo "><td class=firstcol align=left width=30%><div id=tit2>Transportista</div></td>";
    echo "<td align=left width=10%><input STYLE='text-align:left' type='text' name='transportista' id='cuentaText' 
    size='6'  maxlength='5' value='$cod_satalbtte' tabindex='1'
 	onkeyup='this.value=this.value.toUpperCase()'        
    onBlur='muestra_cuenta(this.value)'
    ></td><td align=left width=5%><a href='javascript: cargarLista_cuenta();'>
    <img src=../../img/magnifier.png  height='16' class='middle'></a></td>";
    echo "<td align=left width=55%>&nbsp;&nbsp;<input type=text name=desc_cta id='desc_cta' 
	size='35'  maxlength='35' value='' ></td>";

    echo "</tr>";
    $a++;
    echo "<tr ";
    if ($a % 2 == 1) {
      echo " class=odd";
    }
    echo "><td class=firstcol align=left><div id=tit2>Conductor</div></td>";
    echo "<td align=left colspan=3>
	<input type=text style='border:1px solid black; background:#f6edc6;text-align:left; padding:3px'  
	type='text' name='chofer' id='chofer_id'
	size='40'  maxlength='35' value='$cond_satalbtte' tabindex='2'
 	onkeyup='this.value=this.value.toUpperCase()'
  	></div></td>";

    echo "</tr>";
    $a++;
    echo "<tr ";
    if ($a % 2 == 1) {
      echo " class=odd";
    }
    echo "><td class=firstcol align=left><div id=tit2>Conductor NIF</div></td>";
    echo "<td align=left colspan=3>
	<input type=text style='border:1px solid black; background:#f6edc6;text-align:left; padding:3px'
	type='text' name='chofer_nif' id='chofer_nif_id'
  	size='15'  maxlength='15' value='$nif_satalbtte' tabindex='3'
   	onkeyup='this.value=this.value.toUpperCase()'
	></div></td>";

    echo "</tr>";
    $a++;
    echo "<tr ";
    if ($a % 2 == 1) {
      echo " class=odd";
    }
    echo iconv("UTF-8", "ISO-8859-1", "><td class=firstcol align=left><div id=tit2>Matrículas</div></td>");
    echo "<td align=left colspan=3>
	<input type=text style='border:1px solid black; background:#f6edc6;text-align:left; padding:3px'
	type='text' name='matricula' id='matricula_id'
  	size='25'  maxlength='20' value='$mat_satalbtte' tabindex='4'
   	onkeyup='this.value=this.value.toUpperCase()'
	></div><td>";

    echo "</tr>";
    $a++;
    echo "<tr ";
    if ($a % 2 == 1) {
      echo " class=odd";
    }
    echo "><td class=firstcol align=left><div id=tit2>Destino</div></td>";
    echo "<td align=left colspan=3>
	<input type=text style='border:1px solid black; background:#f6edc6;text-align:left; padding:3px'
	type='text' name='dest1' id='dest1'	
  	size='50'  maxlength='49' value='$dest1_satalbtte' tabindex='5'
   	onkeyup='this.value=this.value.toUpperCase()'
	></div><td>";

    echo "</tr>";
    $a++;
    echo "<tr ";
    if ($a % 2 == 1) {
      echo " class=odd";
    }
    echo "><td class=firstcol align=left><div id=tit2>En</div></td>";
    echo "<td align=left colspan=3>
	<input type=text style='border:1px solid black; background:#f6edc6;text-align:left; padding:3px'
	type='text' name='dest2' id='dest2'		
  	size='50'  maxlength='49' value='$dest2_satalbtte' tabindex='6'
   	onkeyup='this.value=this.value.toUpperCase()'
	></div></td>";

    if (!$pai_satalbtte) {
      $pai_satalbtte = $ro_pai_cli;
    }
    echo "</tr>";
    $a++;
    echo "<tr ";
    if ($a % 2 == 1) {
      echo " class=odd";
    }
    echo iconv("UTF-8", "ISO-8859-1", "><td class=firstcol align=left><div id=tit2>País destino</div></td>");
    echo "<td align=left width=10%><input STYLE='text-align:left' type='text' name='paisdestino' id='paisText' 
    size='6'  maxlength='5' value='$pai_satalbtte' tabindex='5'
 	onkeyup='this.value=this.value.toUpperCase()'        
    onBlur='muestra_pais(this.value)'
    ></td><td align=left width=5%><a href='javascript: cargarLista_pais();'>
    <img src=../../img/magnifier.png  height='16' class='middle'></a></td>";
    echo "<td align=left width=55%>&nbsp;&nbsp;<input type=text name=desc_pais id='desc_pais' 
	size='35'  maxlength='35' value='' ></td>";

    echo "</tr>";
    $a++;
    echo "<tr ";
    if ($a % 2 == 1) {
      echo " class=odd";
    }
    echo "><td align=left><div id=tit2>Pagar transporte";
    echo "<td align=left colspan=3><div id=tit3><input type=radio name=en_destino value='S' ";
    if ($en_destino == 'S') {
      echo "checked";
    }
    echo ">&nbsp;&nbsp;Destino&nbsp;&nbsp;&nbsp;&nbsp;";
    echo " <input type=radio name=en_destino value='N' ";
    if ($en_destino == 'N') {
      echo "checked";
    }
    echo ">&nbsp;&nbsp;Origen&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "</div></td>";

    echo "<INPUT TYPE=hidden NAME=accion VALUE=2>";
    echo "<INPUT TYPE=hidden NAME=numero VALUE=$numero>";
    echo "<INPUT TYPE=hidden NAME=estado VALUE=$estado>";
    echo "<INPUT TYPE=hidden NAME=cliente VALUE=$cliente>";
    echo "<INPUT TYPE=hidden NAME=empr VALUE=$empr>";
    echo "<INPUT TYPE=hidden NAME=aaaa VALUE=$aaaa>";

    echo "</tr></table>";
    echo "<TABLE align=center width=50 class=acceso id='buscardor_cta' STYLE='display:none';>";
    echo "<tr class=azul>";
    echo "<td align=left><div id=tit2>Buscar cuenta por descripci&oacute;n</div></td></tr><tr>";
    echo "<td align=left> <input type=text name=busc_cta id='busc_cta' size='50'  maxlength='35' value=''
   	onKeyUp='buscar_cuenta()'></td></tr><tr>";
    echo "<td align=left width=50>
   	<select style='width:99%; border:1px solid #04467E;color:#2D4167;' id='miCombocta' name='miCombocta' size=5
   	onclick=muestra_cuenta_combo(this.value)></select></td>";
    echo "</tr></table>";

    echo "<TABLE align=center width=50 class=acceso id='buscador_pais' STYLE='display:none';>";
    echo "<tr class=azul>";
    echo iconv("UTF-8", "ISO-8859-1", "<td align=left><div id=tit2>Buscar país destino por nombre</div></td></tr><tr>");
    echo "<td align=left> <input type=text name=busc_pais id='busc_pais' size='50'  maxlength='35' value=''
   	onKeyUp='buscar_pais()'></td></tr><tr>";
    echo "<td align=left width=50>
   	<select style='width:99%; border:1px solid #04467E;color:#2D4167;' id='miCombopais' name='miCombopais' size=5
   	onclick=muestra_pais_combo(this.value)></select></td>";
    echo "</tr></table>";

    ?><script>
			function muestradata(form) {
				checkSubmit();
				form.submit();
			}
		</script><?php

    echo "<TABLE align=center width=50% id='Tabla_guardar'>";
    echo "<td align=right><div id=tit2>
 	<input type=button value='Enviar'
  	name=enviar tabindex='7'
   	onClick=muestradata(this.form)
	class=search id='btsubmit'></div>";
    echo "<td align=left><div id=tit1>
  	<input type=reset value=Salir onClick=self.close() class=search></div></td>";
    echo "</FORM>";
    echo "</tr></table>";
    echo "</div>";
    echo "</fieldset>";
    break;

  ////////////////////////////////////////////////////////////////
  case '2':
    modifica_linea(
      $numero,
      $estado,
      $cliente,
      $empr,
      $aaaa,
      $transportista,
      $chofer,
      $chofer_nif,
      $matricula,
      $dest1,
      $dest2,
      $paisdestino,
      $en_destino
    );
    ?><script>
			opener.location.reload();
			self.close();
		</script><?php
    break;
}
//////////////////////////////////////////////////////////////////
function modifica_linea(
  $numero,
  $estado,
  $cliente,
  $empr,
  $aaaa,
  $transportista,
  $chofer,
  $chofer_nif,
  $matricula,
  $dest1,
  $dest2,
  $paisdestino,
  $en_destino
) {
  include($_SERVER['DOCUMENT_ROOT'] . '/_configs/_config.php');
  include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/_header_ifx.php');
  include_once($_SERVER['DOCUMENT_ROOT'] . '/_clases/phpsecureurl.php');
  $codep = new phpsecureurl();
  global $PHP_SELF;

  if (($paisdestino == '') && ($en_destino == '')) {
    echo "<TABLE align=center width=97% class=gris>";
    echo "<tr class=amarillo>";
    echo iconv("UTF-8", "ISO-8859-1", "<td align=center><div id=tit1>Falta el país destino o quién paga el transporte</div>");
    echo "</td></tr></table>";
    exit();
  }

  $user_log = $_SESSION["usuario"];
  $quien_tte = "
    SELECT COUNT(*) cuenta_lin
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
  $cuenta_lin = trim(odbc_result($res_tte, "cuenta_lin"));
  odbc_free_result($res_tte);

  if (trim($transportista != '')) {
    $t_nom_cli = acreedor($transportista);
  } else {
    $t_nom_cli = cliente($cliente);
  }

  if ($cuenta_lin == 0) {
    $sql = "
      INSERT INTO satalbtte
      (
        emp_satalbtte,
        alb_satalbtte,
        sta_satalbtte,
        cod_satalbtte,
        cia_satalbtte,
        mat_satalbtte,
        cond_satalbtte,
        nif_satalbtte,
        usr_satalbtte,
        ano_satalbtte,
        dest1_satalbtte,
        dest2_satalbtte,
        pai_satalbtte,
        en_destino
      )
      VALUES
      (
        '$empr',
        $numero,
        '$estado',
        '$transportista',
        '$t_nom_cli',
        '$matricula',
        '$chofer',
        '$chofer_nif',
        '$user_log',
        '$aaaa',
        '$dest1',
        '$dest2',
        '$paisdestino',
        '$en_destino'
      )
    ";
    $inserta = odbc_exec($conexion, $sql);
    if (!$inserta) {
      $err = odbc_errormsg();
      printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
    }
    odbc_free_result($inserta);
  } else {
    $consulta = "
      UPDATE satalbtte
      SET
        cod_satalbtte = '$transportista',
        cia_satalbtte = '$t_nom_cli',
        mat_satalbtte = '$matricula',
        cond_satalbtte = '$chofer',
        nif_satalbtte = '$chofer_nif',
        usr_satalbtte = '$user_log',
        dest1_satalbtte = '$dest1',
        dest2_satalbtte = '$dest2',
        pai_satalbtte = '$paisdestino',
        en_destino = '$en_destino'
      WHERE emp_satalbtte = '$empr'
        AND alb_satalbtte = $numero
        AND sta_satalbtte = '$estado'
        AND ano_satalbtte = '$aaaa'
    ";
    $res_cta = odbc_exec($conexion, $consulta);
    if (!$res_cta) {
      $err = odbc_errormsg();
      printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
    }
    odbc_free_result($res_cta);

    $accion_log = 'MODIFCAR';
    $tabla_log = 'TTE ALBARAN';
    $dato_anterior = '';
    $dato_nuevo =
      'Albaran ' . $numero .
      ' Cliente ' . $cliente .
      ' Ejercicio ' . $aaaa .
      ' Transportista ' . $t_nom_cli .
      ' Conductor ' . $chofer .
      ' NIF ' . $chofer_nif .
      ' Martricula ' . $matricula .
      ' Pais dest ' . $paisdestino;
    inserta_log($user_log, $accion_log, $tabla_log, $dato_anterior, $dato_nuevo);
  }

  $gasto_tte = "
    SELECT COUNT(*) cuenta_lin
    FROM ffgasven
    WHERE emp_gasven = '$empr'
      AND nfa_gasven = $numero
      AND YEAR(fec_gasven) = '$aaaa'
  ";
  $res_tte = odbc_exec($conexion, $gasto_tte);
  if (!$res_tte) {
    $err = odbc_errormsg();
    printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
  }
  $cuenta_lin = trim(odbc_result($res_tte, "cuenta_lin"));
  if (!$cuenta_lin) {
    $cuenta_lin = 0;
  }
  odbc_free_result($res_tte);
  $hoy = (date("m/d/Y"));
  if ($cuenta_lin == 0) {
    $sql = "
      INSERT INTO ffgasven
      (
        emp_gasven,
        nfa_gasven,
        fec_gasven,
        de1_gasven,
        im1_gasven,
        tip_gasven
      )
      VALUES
      (
        '$empr',
        $numero,
        '$hoy',
        '$t_nom_cli',
        0,
        'G'
      )
    ";
    $inserta = odbc_exec($conexion, $sql);
    if (!$inserta) {
      $err = odbc_errormsg();
      printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
    }
    odbc_free_result($inserta);
  } else {
    $consulta = "
      UPDATE ffgasven
      SET de1_gasven = '$t_nom_cli'
      WHERE emp_gasven = '$empr'
        AND nfa_gasven = $numero
        AND YEAR(fec_gasven) = '$aaaa'
    ";
    $res_cta = odbc_exec($conexion, $consulta);
    if (!$res_cta) {
      $err = odbc_errormsg();
      printf('Error en ' . __FILE__ . ' linea ' . __LINE__ . ', motivo --->    %s ', $err);
    }
    odbc_free_result($res_cta);
  }
}
?>
