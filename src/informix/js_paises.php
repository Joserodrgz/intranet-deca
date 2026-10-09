<?php
?>
<script type="text/javascript">
<!--
var cuenta_pais = new Array();
cuenta_pais['AF'] = 'Afganistán';
cuenta_pais['AX'] = 'Åland';
cuenta_pais['AL'] = 'Albania';
cuenta_pais['DE'] = 'Alemania';
cuenta_pais['AD'] = 'Andorra';
cuenta_pais['AO'] = 'Angola';
cuenta_pais['AI'] = 'Anguila';
cuenta_pais['AQ'] = 'Antártida';
cuenta_pais['AG'] = 'Antigua y Barbuda';
cuenta_pais['SA'] = 'Arabia Saudita';
cuenta_pais['DZ'] = 'Argelia';
cuenta_pais['AR'] = 'Argentina';
cuenta_pais['AM'] = 'Armenia';
cuenta_pais['AW'] = 'Aruba';
cuenta_pais['AU'] = 'Australia';
cuenta_pais['AT'] = 'Austria';
cuenta_pais['AZ'] = 'Azerbaiyán';
cuenta_pais['BS'] = 'Bahamas';
cuenta_pais['BH'] = 'Baréin';
cuenta_pais['BD'] = 'Bangladés';
cuenta_pais['BB'] = 'Barbados';
cuenta_pais['BY'] = 'Bielorrusia';
cuenta_pais['BE'] = 'Bélgica';
cuenta_pais['BZ'] = 'Belice';
cuenta_pais['BJ'] = 'Benín';
cuenta_pais['BM'] = 'Bermudas';
cuenta_pais['BT'] = 'Bután';
cuenta_pais['BO'] = 'Bolivia';
cuenta_pais['BA'] = 'Bosnia y Herzegovina';
cuenta_pais['BW'] = 'Botsuana';
cuenta_pais['BR'] = 'Brasil';
cuenta_pais['BN'] = 'Brunéi';
cuenta_pais['BG'] = 'Bulgaria';
cuenta_pais['BF'] = 'Burkina Faso';
cuenta_pais['BI'] = 'Burundi';
cuenta_pais['CV'] = 'Cabo Verde';
cuenta_pais['KH'] = 'Camboya';
cuenta_pais['CM'] = 'Camerún';
cuenta_pais['CA'] = 'Canadá';
cuenta_pais['CL'] = 'Chile';
cuenta_pais['CN'] = 'China';
cuenta_pais['CO'] = 'Colombia';
cuenta_pais['KM'] = 'Comoras';
cuenta_pais['CG'] = 'Congo';
cuenta_pais['CD'] = 'Congo (Rep. Dem.)';
cuenta_pais['KP'] = 'Corea del Norte';
cuenta_pais['KR'] = 'Corea del Sur';
cuenta_pais['CR'] = 'Costa Rica';
cuenta_pais['CI'] = 'Costa de Marfil';
cuenta_pais['HR'] = 'Croacia';
cuenta_pais['CU'] = 'Cuba';
cuenta_pais['CW'] = 'Curazao';
cuenta_pais['DK'] = 'Dinamarca';
cuenta_pais['DM'] = 'Dominica';
cuenta_pais['EC'] = 'Ecuador';
cuenta_pais['EG'] = 'Egipto';
cuenta_pais['SV'] = 'El Salvador';
cuenta_pais['AE'] = 'Emiratos Árabes Unidos';
cuenta_pais['ER'] = 'Eritrea';
cuenta_pais['SK'] = 'Eslovaquia';
cuenta_pais['SI'] = 'Eslovenia';
cuenta_pais['ES'] = 'España';
cuenta_pais['US'] = 'Estados Unidos';
cuenta_pais['EE'] = 'Estonia';
cuenta_pais['ET'] = 'Etiopía';
cuenta_pais['PH'] = 'Filipinas';
cuenta_pais['FI'] = 'Finlandia';
cuenta_pais['FJ'] = 'Fiyi';
cuenta_pais['FR'] = 'Francia';
cuenta_pais['GA'] = 'Gabón';
cuenta_pais['GM'] = 'Gambia';
cuenta_pais['GE'] = 'Georgia';
cuenta_pais['GH'] = 'Ghana';
cuenta_pais['GI'] = 'Gibraltar';
cuenta_pais['GD'] = 'Granada';
cuenta_pais['GR'] = 'Grecia';
cuenta_pais['GL'] = 'Groenlandia';
cuenta_pais['GP'] = 'Guadalupe';
cuenta_pais['GU'] = 'Guam';
cuenta_pais['GT'] = 'Guatemala';
cuenta_pais['GG'] = 'Guernsey';
cuenta_pais['GN'] = 'Guinea';
cuenta_pais['GW'] = 'Guinea-Bisáu';
cuenta_pais['GQ'] = 'Guinea Ecuatorial';
cuenta_pais['GY'] = 'Guyana';
cuenta_pais['HT'] = 'Haití';
cuenta_pais['HN'] = 'Honduras';
cuenta_pais['HK'] = 'Hong Kong';
cuenta_pais['HU'] = 'Hungría';
cuenta_pais['IN'] = 'India';
cuenta_pais['ID'] = 'Indonesia';
cuenta_pais['IR'] = 'Irán';
cuenta_pais['IQ'] = 'Irak';
cuenta_pais['IE'] = 'Irlanda';
cuenta_pais['IM'] = 'Isla de Man';
cuenta_pais['IS'] = 'Islandia';
cuenta_pais['IL'] = 'Israel';
cuenta_pais['IT'] = 'Italia';
cuenta_pais['JM'] = 'Jamaica';
cuenta_pais['JP'] = 'Japón';
cuenta_pais['JE'] = 'Jersey';
cuenta_pais['JO'] = 'Jordania';
cuenta_pais['KZ'] = 'Kazajistán';
cuenta_pais['KE'] = 'Kenia';
cuenta_pais['KG'] = 'Kirguistán';
cuenta_pais['KI'] = 'Kiribati';
cuenta_pais['XK'] = 'Kosovo';
cuenta_pais['KW'] = 'Kuwait';
cuenta_pais['LA'] = 'Laos';
cuenta_pais['LS'] = 'Lesoto';
cuenta_pais['LV'] = 'Letonia';
cuenta_pais['LB'] = 'Líbano';
cuenta_pais['LR'] = 'Liberia';
cuenta_pais['LY'] = 'Libia';
cuenta_pais['LI'] = 'Liechtenstein';
cuenta_pais['LT'] = 'Lituania';
cuenta_pais['LU'] = 'Luxemburgo';
cuenta_pais['MO'] = 'Macao';
cuenta_pais['MK'] = 'Macedonia del Norte';
cuenta_pais['MG'] = 'Madagascar';
cuenta_pais['MY'] = 'Malasia';
cuenta_pais['MW'] = 'Malaui';
cuenta_pais['MV'] = 'Maldivas';
cuenta_pais['ML'] = 'Mali';
cuenta_pais['MT'] = 'Malta';
cuenta_pais['MA'] = 'Marruecos';
cuenta_pais['MQ'] = 'Martinica';
cuenta_pais['MU'] = 'Mauricio';
cuenta_pais['MR'] = 'Mauritania';
cuenta_pais['YT'] = 'Mayotte';
cuenta_pais['MX'] = 'México';
cuenta_pais['FM'] = 'Micronesia';
cuenta_pais['MD'] = 'Moldavia';
cuenta_pais['MC'] = 'Mónaco';
cuenta_pais['MN'] = 'Mongolia';
cuenta_pais['ME'] = 'Montenegro';
cuenta_pais['MS'] = 'Montserrat';
cuenta_pais['MZ'] = 'Mozambique';
cuenta_pais['MM'] = 'Myanmar';
cuenta_pais['NA'] = 'Namibia';
cuenta_pais['NR'] = 'Nauru';
cuenta_pais['NP'] = 'Nepal';
cuenta_pais['NI'] = 'Nicaragua';
cuenta_pais['NE'] = 'Níger';
cuenta_pais['NG'] = 'Nigeria';
cuenta_pais['NO'] = 'Noruega';
cuenta_pais['NC'] = 'Nueva Caledonia';
cuenta_pais['NZ'] = 'Nueva Zelanda';
cuenta_pais['OM'] = 'Omán';
cuenta_pais['NL'] = 'Países Bajos';
cuenta_pais['PK'] = 'Pakistán';
cuenta_pais['PW'] = 'Palaos';
cuenta_pais['PA'] = 'Panamá';
cuenta_pais['PG'] = 'Papúa Nueva Guinea';
cuenta_pais['PY'] = 'Paraguay';
cuenta_pais['PE'] = 'Perú';
cuenta_pais['PF'] = 'Polinesia Francesa';
cuenta_pais['PL'] = 'Polonia';
cuenta_pais['PT'] = 'Portugal';
cuenta_pais['PR'] = 'Puerto Rico';
cuenta_pais['QA'] = 'Qatar';
cuenta_pais['GB'] = 'Reino Unido';
cuenta_pais['CF'] = 'República Centroafricana';
cuenta_pais['CZ'] = 'República Checa';
cuenta_pais['DO'] = 'República Dominicana';
cuenta_pais['RE'] = 'Reunión';
cuenta_pais['RW'] = 'Ruanda';
cuenta_pais['RO'] = 'Rumania';
cuenta_pais['RU'] = 'Rusia';
cuenta_pais['EH'] = 'Sahara Occidental';
cuenta_pais['WS'] = 'Samoa';
cuenta_pais['AS'] = 'Samoa Americana';
cuenta_pais['BL'] = 'San Bartolomé';
cuenta_pais['KN'] = 'San Cristóbal y Nieves';
cuenta_pais['SM'] = 'San Marino';
cuenta_pais['MF'] = 'San Martín';
cuenta_pais['PM'] = 'San Pedro y Miquelón';
cuenta_pais['VC'] = 'San Vicente y las Granadinas';
cuenta_pais['SH'] = 'Santa Elena';
cuenta_pais['LC'] = 'Santa Lucía';
cuenta_pais['ST'] = 'Santo Tomé y Príncipe';
cuenta_pais['SN'] = 'Senegal';
cuenta_pais['RS'] = 'Serbia';
cuenta_pais['SC'] = 'Seychelles';
cuenta_pais['SL'] = 'Sierra Leona';
cuenta_pais['SG'] = 'Singapur';
cuenta_pais['SX'] = 'Sint Maarten';
cuenta_pais['SY'] = 'Siria';
cuenta_pais['SO'] = 'Somalia';
cuenta_pais['LK'] = 'Sri Lanka';
cuenta_pais['SZ'] = 'Suazilandia';
cuenta_pais['ZA'] = 'Sudáfrica';
cuenta_pais['SD'] = 'Sudán';
cuenta_pais['SS'] = 'Sudán del Sur';
cuenta_pais['SE'] = 'Suecia';
cuenta_pais['CH'] = 'Suiza';
cuenta_pais['SR'] = 'Surinam';
cuenta_pais['TH'] = 'Tailandia';
cuenta_pais['TW'] = 'Taiwán';
cuenta_pais['TZ'] = 'Tanzania';
cuenta_pais['TJ'] = 'Tayikistán';
cuenta_pais['IO'] = 'Territorio Británico del Océano Índico';
cuenta_pais['TF'] = 'Territorios Australes Franceses';
cuenta_pais['TL'] = 'Timor Oriental';
cuenta_pais['TG'] = 'Togo';
cuenta_pais['TK'] = 'Tokelau';
cuenta_pais['TO'] = 'Tonga';
cuenta_pais['TT'] = 'Trinidad y Tobago';
cuenta_pais['TN'] = 'Túnez';
cuenta_pais['TM'] = 'Turkmenistán';
cuenta_pais['TR'] = 'Turquía';
cuenta_pais['TV'] = 'Tuvalu';
cuenta_pais['UA'] = 'Ucrania';
cuenta_pais['UG'] = 'Uganda';
cuenta_pais['UY'] = 'Uruguay';
cuenta_pais['UZ'] = 'Uzbekistán';
cuenta_pais['VU'] = 'Vanuatu';
cuenta_pais['VE'] = 'Venezuela';
cuenta_pais['VN'] = 'Vietnam';
cuenta_pais['WF'] = 'Wallis y Futuna';
cuenta_pais['YE'] = 'Yemen';
cuenta_pais['DJ'] = 'Yibuti';
cuenta_pais['ZM'] = 'Zambia';
cuenta_pais['ZW'] = 'Zimbabue';
/*
var lista_pais = [];
for (var clave in cuenta_pais) {
    lista_pais.push(clave + ' - ' + cuenta_pais[clave]);
}
*/
var lista_pais = Object.entries(cuenta_pais).map(([clave, valor]) => `${clave} - ${valor}`);

function cargarLista_pais() {
    document.getElementById('buscador_pais').style.display = 'table';
    document.getElementById('busc_pais').focus();
    for (x=0;x<lista_pais.length;x++)
    document.libro.miCombopais[x] = new Option(lista_pais[x]);
}
function buscar_pais() {
    limpiarLista_pais();
    texto = document.getElementById("busc_pais").value;
    expr = new RegExp(texto,"i");
    y = 0;
    for (x=0;x<lista_pais.length;x++) {
    if (expr.test(lista_pais[x])) {
    document.libro.miCombopais[y] = new Option(lista_pais[x]);
    y++;
    }
    }
}
function limpiarLista_pais() {
    for (x=document.libro.miCombopais.length;x>=0;x--)
    document.libro.miCombopais[x] = null;
}
function muestra_pais_combo(nombre){
    var dato = nombre.substring(0,3);
	dato1 = dato.replace(/[- ]/gi,'');
    document.getElementById('paisText').value = dato1;
    document.getElementById('paisText').focus();
    document.getElementById('buscador_pais').style.display = 'none';
}
function muestra_pais(nombre){
    var dato = nombre;
    var descripcion = cuenta_pais[dato];
    if (!descripcion) {
    var campopais = document.getElementById('paisText');
    campopais.value = "";
    document.libro["desc_pais"].value = descripcion;
    }
    document.libro["desc_pais"].value = descripcion;
}
// -->
</script>
<?php 