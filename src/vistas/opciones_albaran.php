<?php
session_save_path('../tmp/informix');
session_start();
set_time_limit(0);
include($_SERVER['DOCUMENT_ROOT'] . '/_configs/_config.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/_header_ifx.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/funciones.php');
include($_SERVER['DOCUMENT_ROOT'] . '/_configs/colores.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/js/js.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/consultas.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/_clases/phpsecureurl.php');
$codep = new phpsecureurl();
if ($_REQUEST[hmg]) {
  $url12 = $codep->decode($url12);
}

global $PHP_SELF;

global $accion;
if (empty($accion)) {
  $accion = '1';
}

switch ($accion) {
  case '1':
    $hoy = (date("d/m/Y"));
    $hoy = reset_fecha($hoy);
    $aaaa = select_ano_($hoy);
    $mm = select_mes_($hoy);
    $mm = str_pad($mm, 2, "0", STR_PAD_LEFT);
    $seriefactura = serie_factura($numero, $aaaa, $estado, $empr, $centroventa);
    if (!$seriefactura) {
      $seriefactura = $empr;
    }
    $numero_pdf = str_pad($numero, 6, '0', STR_PAD_LEFT);
    $numero_pdf = $seriefactura . '-' . $numero_pdf;
    $nom_cli = cliente($cliente);

    $consulta = "
      SELECT
        nume_alb,
        clie_alb,
        nomb_cli,
        merc_alb,
        total_alb,
        bimp_alb,
        (iiva_alb + ire_alb) AS importe_iva,
        ano_cli,
        email,
        stat_alb,
        ngra_alb,
        liq_alb,
        lib_alb,
        pai_cli,
        empr_alb,
        cent_alb,
        obs1_alb
      FROM ffalbara, ffclient
      WHERE empr_alb = '$empr'
        AND nume_alb = $numero
        AND cod_cli = '$cliente'
        AND stat_alb = '$estado'
      ORDER BY nume_alb
    ";
    $result = odbc_exec($conexion, $consulta) or die("no se ha podido realizar la consulta");
    if (!$result) {
      $err = odbc_errormsg();
      printf("Imposible Informix, motivo --->    %s ", $err);
    }
    $importe = odbc_result($result, "total_alb");
    $bimp_alb = odbc_result($result, "bimp_alb");
    $importe_iva = odbc_result($result, "importe_iva");
    $importe_iva = number_format($importe_iva, 2, ",", ".");
    $merc_alb = odbc_result($result, "merc_alb");
    $importe_env = $bimp_alb - $merc_alb;
    $importe_env = number_format($importe_env, 2, ",", ".");
    $merc_alb = number_format($merc_alb, 2, ",", ".");
    $total_alb = odbc_result($result, "total_alb");
    $baseimp = $total_alb;
    $total_alb = number_format($total_alb, 2, ",", ".");
    $nomb_cli = odbc_result($result, "nomb_cli");
    $nomb_cli = limpiar($nomb_cli);
    $stat_alb = odbc_result($result, "stat_alb");
    $stat_alb = limpiar($stat_alb);
    $pai_cli = trim(odbc_result($result, "pai_cli"));
    $centro_venta = trim(odbc_result($result, "cent_alb"));
    $ano_cli = limpiar(odbc_result($result, "ano_cli"));
    $ema_cli = trim(odbc_result($result, "email"));
    $obs1_alb = trim(odbc_result($result, "obs1_alb"));
    if (trim($obs1_alb != '')) {
      $tiene_obs = 'SI';
    } else {
      $tiene_obs = 'NO';
    }
    odbc_free_result($result);

    $cliente_url = rawurlencode($cliente);

    $quien_tte = "
      SELECT COUNT(*) cuenta_lin
      FROM satalbtte
      WHERE emp_satalbtte = '$empr'
        AND alb_satalbtte = $numero
        AND sta_satalbtte = '$estado'
        AND YEAR(fec_satalbtte) = $aaaa
    ";
    $res_tte = odbc_exec($conexion, $quien_tte);
    if (!$res_tte) {
      $err = odbc_errormsg();
      printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);
    }
    $cuenta_lin_ttes = trim(odbc_result($res_tte, "cuenta_lin"));
    odbc_free_result($res_tte);

    // Icono de la columna CMR: DECA si el destino es España y se ha indicado quién paga el transporte
    $quien_doc = "
      SELECT
        pai_satalbtte,
        en_destino
      FROM satalbtte
      WHERE emp_satalbtte = '$empr'
        AND alb_satalbtte = $numero
        AND sta_satalbtte = '$estado'
        AND YEAR(fec_satalbtte) = $aaaa
    ";
    $res_doc = odbc_exec($conexion, $quien_doc);
    if (!$res_doc) {
      $err = odbc_errormsg();
      printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);
    }
    $pai_doc = trim(odbc_result($res_doc, "pai_satalbtte"));
    $en_destino_doc = trim(odbc_result($res_doc, "en_destino"));
    odbc_free_result($res_doc);

    $img_cmr = '../../img/CMR.png';
    $titulo_cmr = 'CMR';
    if (($pai_doc == 'ES') && ($en_destino_doc != '')) {
      $img_cmr = '../../img/DeCA.png';
      $titulo_cmr = 'DECA';
    }

    if ($cuenta_lin_ttes == 0) {
      $url = sprintf(
        "../../fpdf/albaran$alias_pdf.php?numero=%s&estado=%s&cliente=%s&empr=%s",
        $numero,
        $estado,
        $cliente_url,
        $empr
      );
    } else {
      $url = sprintf(
        "../../fpdf/albaran$alias_pdf.php?numero=%s&estado=%s&cliente=%s&empr=%s",
        $numero,
        $estado,
        $cliente_url,
        $empr
      );
    }
    $url = $codep->encode($url);

    $url_tte = sprintf(
      "./crear_ttealbaran.php?accion=%s&numero=%s&estado=%s&cliente=%s&empr=%s",
      1,
      $numero,
      $estado,
      $cliente,
      $empr
    );
    $url_tte = $codep->encode($url_tte);

    $url_cmr = sprintf(
      "./crear_cmr.php?numero=%s&estado=%s&cliente=%s&empr=%s",
      $numero,
      $estado,
      $cliente_url,
      $empr
    );
    $url_cmr = $codep->encode($url_cmr);

    $urleti = sprintf(
      "../menu_etiquetas.php?action=%d&factura=%s&importe=%s&empr=%s",
      2,
      $numero,
      $baseimp,
      $empr
    );
    $urleti = $codep->encode($urleti);

    $url_obs = sprintf(
      "observaciones_albaran.php?numero=%s&estado=%s&empr=%s",
      $numero,
      $estado,
      $empr
    );
    $url_obs = $codep->encode($url_obs);

    if ($cuenta_lin_ttes == 0) {
      $url_ema = sprintf(
        "../../fpdf/albaran$alias_pdf.php?numero=%s&estado=%s&cliente=%s&empr=%s&ema_cli=%s",
        $numero,
        $estado,
        $cliente_url,
        $empr,
        $ema_cli
      );
    } else {
      $url_ema = sprintf(
        "../../fpdf/albaran$alias_pdf.php?numero=%s&estado=%s&cliente=%s&empr=%s&ema_cli=%s",
        $numero,
        $estado,
        $cliente_url,
        $empr,
        $ema_cli
      );
    }
    $url_ema = $codep->encode($url_ema);
    $enviado_email = 'NO';
    $directorio_ventas = $originales . 'ventas/' . $aaaa . '/' . $mm . '/enviados/' . $numero_pdf . '_email.pdf';
    if (is_file($directorio_ventas)) {
      $enviado_email = 'SI';
    }

    echo "<fieldset><legend>Opciones del albar&aacute;n <font color=$textcolor4>$numero_pdf </font>de 
	<font color=$textcolor4>$nom_cli</font>.
	</legend>";
    echo "<br><TABLE align=center width=97% class=gris>";
    echo "<tr class=odd>";
    echo "<td align=left>  <div id=tit2>Cliente</div></td>";
    echo "<td align=left>  <div id=tit2>Nombre</div></td>";
    echo "<td align=right> <div id=tit2>Mercanc&iacute;a</div></td>";
    echo "<td align=right> <div id=tit2>Env</div></td>";
    echo "<td align=right> <div id=tit2>Total</div></td>";
    echo "<td align=center><div id=tit2>Tipo</div></td>";
    echo "<td align=left>  <div id=tit2>N&uacute;mero</div></td>";
    echo "<td align=center><div id=tit2>Etiq</div></td>";
    echo "<td align=center><div id=tit2>$titulo_cmr</div></td>";
    echo "<td align=center><div id=tit2>Tte</div></td>";
    echo "<td align=center><div id=tit2>Obs</div></td>";
    echo "<td align=center colspan=2><div id=tit2>Email</div></td>";
    echo "<td align=center><div id=tit2>Alm</div></td>";
    echo "</tr>";
    $a = 0;

    echo "<tr ";
    if ($a % 2 == 1) {
      echo " class=odd";
    }
    echo "><td class=firstcol align=left><div id=tit3>$cliente</div></td>";
    echo "<td align=left>  <div id=tit3>$nomb_cli</div></td>";
    echo "<td align=right> <div id=tit3>$merc_alb</div></td>";
    echo "<td align=right> <div id=tit3>$importe_env</div></td>";
    echo "<td align=right> <div id=tit3>$total_alb</div></td>";
    echo "<td align=center><div id=tit3>$stat_alb</div></td>";
    echo "<td align=left>  <div id=tit3>
	<A HREF=javascript:ventana('$url','ventana','800','565','yes');>$numero_pdf</A></div></td>";
    echo "<td align=center><div id=tit3>
    <A HREF=javascript:ventana('$urleti','ventana','800','600','no');>
    <img src=../../img/eti.png border=0></A></div></td>";
    echo "<td align=center><div id=tit3>
    <A HREF=javascript:ventana('$url_cmr','ventana','700','420','yes');>
    <img src=$img_cmr border=0 height=18></A></div></td>";
    if ($cuenta_lin_ttes != 0) {
      echo "<td align=center><div id=tit2>
	<A HREF=javascript:ventana('$url_tte','ventana','800','300','no');>
	<img src=../../img/camion_lleno.png height=18 border=0></a></div></td>";
    }
    if ($cuenta_lin_ttes == 0) {
      echo "<td align=center><div id=tit2>
	<A HREF=javascript:ventana('$url_tte','ventana','800','300','no');>
	<img src=../../img/camion_vacio.png height=18 border=0></a></div></td>";
    }
    if ($tiene_obs == 'SI') {
      echo "<td align=center><div id=tit2>
	<A HREF=javascript:ventana('$url_obs','ventana','800','300','no');>
	<img src=../../img/bocadillo.gif height=18 border=0></a></div></td>";
    }
    if ($tiene_obs == 'NO') {
      echo "<td align=center><div id=tit2>
	<A HREF=javascript:ventana('$url_obs','ventana','800','300','no');>
	<img src=../../img/bocadillo_vacio.gif height=18 border=0></a></div></td>";
    }

    if (trim($ema_cli) != '') {
      echo "<td align=right><div id=tit3><A HREF=javascript:ventana('$url_ema','ventana','600','150','no');>
    <img src=../../img/properties.png border=0 height='12'></A></div></td>";
    } else {
      echo "<td align=center><div id=tit3>&nbsp;</div></td>";
    }
    if ((trim($ema_cli) != '') && ($enviado_email == 'SI')) {
      echo "<td align=left><div id=tit3><img src=../../img/tick.gif border=0 height='12'></div></td>";
    } else {
      echo "<td align=center><div id=tit3>&nbsp;</div></td>";
    }

    echo "<td align=center><div id=tit3>$empr</div></td>";
    echo "</tr>";
    echo "</table><br>";
    echo "</div>";
    echo "</fieldset>";
    break;

  case '2':
    modifica_linea($emp_contab, $inicio, $fin);
    ?><script>
	opener.location.reload();
	self.close();
	</script><?php
    break;
}

function modifica_linea()
{
  include($_SERVER['DOCUMENT_ROOT'] . '/_configs/_config.php');
  include_once($_SERVER['DOCUMENT_ROOT'] . '/_configs/_header_ifx.php');

  global $PHP_SELF;

  $consulta = "
    SELECT MAX(fech_logalbara) AS fecha_max
    FROM logalbara
    WHERE empr_logalbara IN $emp
  ";
  $resultado = odbc_exec($conexion, $consulta);
  if (!$resultado) {
    $err = odbc_errormsg();
    printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);
  }
  $fecha_max = odbc_result($resultado, "fecha_max");
  odbc_free_result($resultado);
  $fecha_max = reset_fechaa(substr($fecha_max, 0, 10));

  $consulta_borrar = "
    DELETE FROM logalbara
    WHERE empr_logalbara IN $emp
      AND DATE(fech_logalbara) < '$fecha_max'
  ";
  $res_borrar = odbc_exec($conexion, $consulta_borrar);
  if (!$res_borrar) {
    $err = odbc_errormsg();
    printf("Imposible Informix, motivo --->    %s ", $err);
  }
  odbc_free_result($res_borrar);
}
?>
