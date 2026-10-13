<?php
function envase($cod_env)
{
  if (is_file($_SERVER['DOCUMENT_ROOT'] . '/_configs/_config.php')) {
    include($_SERVER['DOCUMENT_ROOT'] . '/_configs/_config.php');
  }
  if (is_file('../_configs/_config.php')) {
    include('../_configs/_config.php');
  }

  $envase = "
    SELECT des_env
    FROM ffenvase
    WHERE cod_env = '$cod_env'
  ";
  $res_env = odbc_exec($conexion, $envase);
  $des_env = limpiar(odbc_result($res_env, "des_env"));
  odbc_free_result($res_env);

  return $des_env;
}
