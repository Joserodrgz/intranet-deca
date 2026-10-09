<?php

include($_SERVER['DOCUMENT_ROOT'] . '/_configs/_config.php');

# Crear la tabla logalbara

$crear = "
  CREATE TABLE logalbara
  (
    empr_logalbara CHAR(2),
    cent_logalbara CHAR(2),
    nume_logalbara INTEGER,
    clie_logalbara CHAR(10),
    stat_logalbara CHAR(1),
    merc_logalbara DECIMAL(12,2),
    bimp_logalbara DECIMAL(12,2),
    iiva_logalbara DECIMAL(12,2),
    ire_logalbara DECIMAL(12,2),
    liq_logalbara DECIMAL(12,2),
    env_logalbara INTEGER,
    ienv_logalbara DECIMAL(12,2),
    dev_logalbara INTEGER,
    idev_logalbara DECIMAL(12,2),
    total_logalbara DECIMAL(12,2),
    fech_logalbara DATETIME YEAR TO SECOND
  )
";
$resultado = odbc_exec($conexion, $crear);
if (!$resultado) {
  $err = odbc_errormsg();
  printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);
}
odbc_free_result($resultado);

# Crear indice de la tabla log_ffalbara

$indice = "
  CREATE INDEX logalbara001
  ON logalbara (empr_logalbara, nume_logalbara, stat_logalbara, total_logalbara)
";
$indexar = odbc_exec($conexion, $indice);
if (!$indexar) {
  $err = odbc_errormsg();
  printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);
}
odbc_free_result($indexar);

# Crear la tabla satalbtte para transporte en albarán y gastos

$crear = "
  CREATE TABLE satalbtte
  (
    emp_satalbtte CHAR(2) NOT NULL,
    alb_satalbtte INTEGER NOT NULL,
    sta_satalbtte CHAR(1) NOT NULL,
    ano_satalbtte CHAR(4),
    fec_satalbtte DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND NOT NULL,
    cod_satalbtte CHAR(8),
    cia_satalbtte CHAR(50),
    mat_satalbtte CHAR(20),
    cond_satalbtte CHAR(50),
    nif_satalbtte CHAR(15),
    dest1_satalbtte CHAR(50),
    dest2_satalbtte CHAR(50),
    usr_satalbtte CHAR(15),
    pai_satalbtte CHAR(3),
    en_destino CHAR(3)
  )
";
$resultado = odbc_exec($conexion, $crear);
if (!$resultado) {
  $err = odbc_errormsg();
  printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);
}
odbc_free_result($resultado);

# Crear indice de la tabla satalbtte

$indice = "
  CREATE INDEX satalbtte001
  ON satalbtte (emp_satalbtte, alb_satalbtte, fec_satalbtte)
";
$indexar = odbc_exec($conexion, $indice);
if (!$indexar) {
  $err = odbc_errormsg();
  printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);
}
odbc_free_result($indexar);
