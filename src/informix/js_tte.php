<?php
include($_SERVER['DOCUMENT_ROOT'].'/_configs/_config.php' );
global $PHP_SELF, $cuenta;
?>
<script type="text/javascript">
<!--
var cuenta = new Array();
var  lista_cuenta = new Array();
<?php
        $consulta = " select cod_rem, nom_rem from ffremite WHERE acreoprove = 'A' ORDER BY cod_rem";
	$resultado=odbc_exec($conexion, $consulta);
        if(!$resultado)
	{$err = odbc_errormsg();printf("Imposible ejecutar la consulta a Informix, motivo --->    %s ", $err);}
        while ($row = odbc_fetch_row($resultado)){
	$des_cta=limpiar(odbc_result($resultado,"nom_rem"));
	$cod_cta=trim(odbc_result($resultado,"cod_rem"));
        echo "cuenta['$cod_cta'] = '$des_cta';\n";
        $cod_cta = str_pad($cod_cta,10,' ',STR_PAD_RIGHT);        
        echo "lista_cuenta.push('$cod_cta'+'  -   '+'$des_cta');";
        }
        odbc_free_result ($resultado);
?>
function cargarLista_cuenta() {
        document.getElementById('buscardor_cta').style.display = 'table';
        document.getElementById('busc_cta').focus();
        for (x=0;x<lista_cuenta.length;x++)
        document.libro.miCombocta[x] = new Option(lista_cuenta[x]);
}

function buscar_cuenta() {

        limpiarLista_cuenta();
        texto = document.getElementById("busc_cta").value;
        expr = new RegExp(texto,"i");
        y = 0;

        for (x=0;x<lista_cuenta.length;x++) {
        if (expr.test(lista_cuenta[x])) {
        document.libro.miCombocta[y] = new Option(lista_cuenta[x]);
        y++;
        }
        }
}

function limpiarLista_cuenta() {
        for (x=document.libro.miCombocta.length;x>=0;x--)
        document.libro.miCombocta[x] = null;
}

function muestra_cuenta_combo(nombre){
        var dato = nombre.substring(0, nombre.search("-"));
	dato1 = dato.replace(/[- ]/gi,'');
        document.getElementById('cuentaText').value = dato1;
        document.getElementById('cuentaText').focus();
        document.getElementById('buscardor_cta').style.display = 'none';
}

function muestra_cuenta(nombre){
        var dato = nombre;
        var descripcion = cuenta[dato];
        if (!descripcion) {
        var campocuenta = document.getElementById('cuentaText');
        campocuenta.value = "";
        document.libro["desc_cta"].value = descripcion;
        }
        document.libro["desc_cta"].value = descripcion;
}
// -->
</script>
<?php
