# Intranet-Deca

## Contexto

El Documento Electrónico de Control Administrativo (DECA) es la versión
digital del documento que acredita el transporte público de mercancías por
carretera.

Se presenta mediante un `QR` para que la inspección pueda verificar los datos
del servicio: cargador, transportista, origen, destino, mercancía y matrícula.
Es obligatorio para el transporte interior y el cabotaje en España.

## Requisitos previos

### Extensión GD de PHP

Necesaria para generar la imagen del QR. Instala con YaST el paquete:

```text
php5-gd
```

## Instalación

1. Clona el repositorio y genera el paquete desde `main`:

    ```sh
    git clone https://github.com/Joserodrgz/intranet-deca.git
    cd intranet-deca
    git checkout main
    npm run build
    ```

    El build deja el paquete en `dist/intranet-deca-v<versión>.tgz`.

2. Copia el paquete al servidor del cliente, en `/tmp`, y descomprímelo:

    ```sh
    cd /tmp
    tar -xzf intranet-deca-v<versión>.tgz
    ```

3. Copia cada carpeta a su destino:

    ```sh
    sudo cp /tmp/deca/_configs/* /srv/www/htdocs/_configs
    sudo cp /tmp/deca/fpdf/*     /srv/www/htdocs/fpdf
    sudo cp /tmp/deca/informix/* /srv/www/htdocs/informix
    sudo cp /tmp/deca/vistas/*   /srv/www/htdocs/informix/vistas
    ```

4. Vuelve a dar permisos a `/srv/www/htdocs`:

    ```sh
    cd /srv/www
    sudo sharedir.sh htdocs
    sudo chown -R wwwrun:www htdocs
    ```

## Integración en la intranet

### Envases en el DECA

`fpdf/deca.php` rellena la columna Envase llamando a `envase()` con el código
de envase de cada línea. La función es opcional:

| Cliente                                         | Resultado                            |
| ----------------------------------------------- | ------------------------------------ |
| Tiene `envase()` en su `_configs/consultas.php` | Se muestra la descripción del envase |
| No tiene `envase()`                             | La columna queda en blanco           |

`fpdf/deca.php` incluye primero `_configs/consultas.php` y después
`_configs/consultas_deca.php`. Este último define una `envase()` que devuelve
una cadena vacía, pero solo si el cliente no tiene la suya:

```php
if (!function_exists('envase')) {
  // Cliente sin envases: la columna "Envase" del DECA queda en blanco
  function envase($codigo)
  {
    return '';
  }
}
```

Si el cliente ya tiene `envase()`, el `function_exists()` evita el error de
función duplicada y se usa la del cliente.

Para mostrar los envases en un cliente sin `envase()`, copia la función de
[snippets/consultas.php](snippets/consultas.php) en su
`_configs/consultas.php`, sin la etiqueta `<?php` del principio:

```php
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
```

### Columna CMR en albaranes_dia.php

En `vistas/albaranes_dia.php`, la columna CMR va al final de cada fila, dentro
del bucle `while (odbc_fetch_row($result))`.

Elimina el condicional que la envuelve. Siempre se cumple, porque el país es
`ES` o no lo es:

```php
// Eliminar
if ($pai_cli <> 'ES' || $pai_cli == 'ES') {
    // ...
}
```

Deja la celda sin condición:

```php
    // Cliente sin FacturaE -> celda vacía
    if ($es_facturae == 'NO') {
        echo "<td align=center><div id=tit3>&nbsp;</div></td>";
    }
}

// Columna CMR: se muestra para todos los países
echo "<td align=center>
    <div id=tit3>
        <a href=$url_opciones target=_blank>
            <img src=../img/CMR.png border=0 height='16'>
        </a>
    </div>
</td>";

echo "</tr>";
$a++;
} // fin while (odbc_fetch_row($result))
```

## Comprobación

1. Abre el listado de albaranes del día y verifica que aparece el icono CMR.
2. Rellena los datos de transporte de un albarán y genera el DECA.
3. Verifica que:

    - El PDF se muestra en el navegador con el QR.
    - Al escanear el QR se abre el mismo PDF desde R2.
    - La columna Envase muestra la descripción si el cliente tiene `envase()`,
      o queda en blanco si no la tiene.
