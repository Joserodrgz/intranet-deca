<?php

// --------------------------------------------------------------------------
// Dentro del while (odbc_fetch_row($result)), en lugar de la celda CMR actual
// (la que pinta ../img/CMR.png). Sustituye también el if que la envuelve.
//
// $pai_cli ya viene de la consulta principal del listado (columna pai_cli).
// --------------------------------------------------------------------------

// Icono de la columna CMR: DECA si el cliente es de España, CMR si es de otro país
$img_cmr = '../img/CMR.png';
if ($pai_cli == 'ES') {
  $img_cmr = '../img/DeCA.png';
}

echo "<td align=center> <div id=tit3>
  <A HREF=$url_opciones target=_blank>
  <img src=$img_cmr border=0 height='16'></A></div></td>";
