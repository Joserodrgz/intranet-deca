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

### Tabla satalbtte

Comprueba si la tabla existe en Informix.

**Si no existe**, créala ejecutando `informix/_crear_tablas.php`, que ya
incluye los campos nuevos.

**Si ya existe**, añade los campos nuevos:

```sql
-- Campo pai_satalbtte
ALTER TABLE satalbtte
ADD (pai_satalbtte CHAR(3));

-- Campo en_destino
ALTER TABLE satalbtte
ADD (en_destino CHAR(3));
```

Comprueba también que la columna `emp_satalbtte` es `CHAR(2)`:

```text
Column name      Type       Nulls
emp_satalbtte    char(2)    no
```

### Tabla phpdeca

No requiere acción manual: `vistas/crear_ttealbaran.php` la crea, con sus
índices, la primera vez que se ejecuta.

## Instalación

1. Clona el repositorio y genera el paquete desde `main`:

    ```sh
    git clone https://github.com/Joserodrgz/intranet-deca.git
    cd intranet-deca
    git checkout main
    npm run build
    ```

    El paquete queda en `dist/intranet-deca-v<versión>.tgz`.

2. Copia el paquete al servidor del cliente y descomprímelo en `/tmp`:

    ```sh
    tar -xzf intranet-deca-v<versión>.tgz -C /tmp
    ```

3. Ejecuta el script de instalación con `sudo`:

    ```sh
    sudo bash /tmp/deca/install.sh
    ```

El script `install.sh`:

- Comprueba que se ejecuta como root y que existen `/srv/www/htdocs`,
  `release.json`, `sharedir.sh` y `tarhtdocs.sh`
- Hace una copia de seguridad de `htdocs` con `tarhtdocs.sh`
- Copia cada carpeta del paquete a su destino:

    | Paquete    | Destino                           |
    | ---------- | --------------------------------- |
    | `_configs` | `/srv/www/htdocs/_configs`        |
    | `fpdf`     | `/srv/www/htdocs/fpdf`            |
    | `informix` | `/srv/www/htdocs/informix`        |
    | `vistas`   | `/srv/www/htdocs/informix/vistas` |
    | `img`      | `/srv/www/htdocs/img`             |

- Crea `_configs/cloudflare_r2_deca.php` con las claves vacías solo si no
  existe, así que las actualizaciones conservan las claves del servidor
- Restaura los permisos con `sharedir.sh` y `chown -R wwwrun:www`
- Muestra la versión instalada

**Nota:** el paquete incluye `vistas/opciones_albaran.php`, que sustituye al
del cliente. Si el cliente tiene cambios propios en ese fichero, revísalos
antes de instalar.

**Nota:** para instalar en otra ruta, indícala con la variable `HTDOCS`:

```sh
sudo HTDOCS=/ruta/htdocs bash /tmp/deca/install.sh
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

`informix/albaranes_dia.php` no va en el paquete: se modifica a mano en cada
cliente. La columna CMR muestra `DeCA.png` en los albaranes de clientes de
España (`pai_cli = 'ES'`) y `CMR.png` en el resto. La cabecera de la columna
sigue siendo `Cmr`.

Dentro del bucle `while (odbc_fetch_row($result))`, sustituye la celda CMR
por el fragmento de [snippets/albaranes_dia.php](snippets/albaranes_dia.php).
`$pai_cli` ya viene de la consulta principal del listado.

Elimina también el condicional que envuelve la celda, porque siempre se
cumple:

```php
// Eliminar
if ($pai_cli <> 'ES' || $pai_cli == 'ES') {
    // ...
}
```

## Comprobación

1. Abre el listado de albaranes del día y verifica que aparece el icono CMR.
2. Rellena los datos de transporte de un albarán con destino `ES`, marca
   **Pagar transporte** y genera el DECA.
3. Verifica que:

    - El PDF se muestra en el navegador con el QR.
    - Al escanear el QR se abre el mismo PDF desde R2.
    - La columna Envase muestra la descripción si el cliente tiene `envase()`,
      o queda en blanco si no la tiene.
    - En el listado y en las opciones del albarán aparece `DeCA.png`, y en las
      opciones la cabecera de la columna es `DECA`.

4. Vacía los datos de transporte del albarán y verifica que vuelve a aparecer
   `CMR.png` y que **Enviar** en `crear_cmr.php` genera un CMR.
