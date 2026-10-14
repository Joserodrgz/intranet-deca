<?php

// --------------------------------------------------------------------------
// 1. Después de la consulta principal del listado:
//    $result=odbc_exec($conexion, $consulta) or die ("no se ha podido realizar la consulta");
// --------------------------------------------------------------------------

$aaaa_deca = select_ano_(reset_fecha(date("d/m/Y")));
$consulta_deca = "
  SELECT
    emp_satalbtte,
    alb_satalbtte,
    sta_satalbtte
  FROM satalbtte
  WHERE emp_satalbtte IN $emp
    AND pai_satalbtte = 'ES'
    AND en_destino <> ''
    AND YEAR(fec_satalbtte) = $aaaa_deca
    AND alb_satalbtte IN (
      SELECT nume_alb
      FROM ffalbara
      WHERE empr_alb IN $emp
        AND nume_alb < 500000
    )
";
$res_deca = odbc_exec($conexion, $consulta_deca);
if (!$res_deca) {
  $err = odbc_errormsg();
  printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);
}
$albaranes_deca = array();
while (odbc_fetch_row($res_deca)) {
  $clave_deca = trim(odbc_result($res_deca, "emp_satalbtte")) . '|'
    . trim(odbc_result($res_deca, "alb_satalbtte")) . '|'
    . trim(odbc_result($res_deca, "sta_satalbtte"));
  $albaranes_deca[$clave_deca] = true;
}
odbc_free_result($res_deca);

// --------------------------------------------------------------------------
// 2. Dentro del while (odbc_fetch_row($result)), en lugar de la celda CMR actual
//    (la que pinta ../img/CMR.png). Sustituye también el if que la envuelve.
// --------------------------------------------------------------------------

// Icono de la columna CMR: DECA si el albarán está en $albaranes_deca
$img_cmr = '../img/CMR.png';
if (isset($albaranes_deca[trim($empr) . '|' . trim($nume_alb) . '|' . trim($stat_alb)])) {
  $img_cmr = '../img/DeCA.png';
}

echo "<td align=center> <div id=tit3>
  <A HREF=$url_opciones target=_blank>
  <img src=$img_cmr border=0 height='16'></A></div></td>";
