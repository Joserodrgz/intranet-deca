# Intranet-Deca

The DECA (Documento Electrónico de Control Administrativo) is the digital
document that proves road freight transport. It is shown as a QR code so
inspectors can check the shipper, carrier, origin, destination, goods and
plate. It is mandatory for domestic transport and cabotage in Spain.

## Requirements

- PHP GD extension to generate the QR image: install `php5-gd` with YaST

## Release

From the `develop` branch:

```sh
npm run release <version>
```

This creates the `release/v<version>` branch and the `v<version>` tag, merges
them into `develop` and `main`, and pushes everything to `origin`.

## Build

Generate the package from the `main` branch:

```sh
git checkout main
npm run build
```

The package is written to `dist/intranet-deca-v<version>.tgz`.

## Deployment

Deploy the latest release with appman:

```sh
sudo appman install intranet-deca
```

`install.sh` backs up `htdocs` with `tarhtdocs.sh`, copies each package folder
to its destination and restores permissions with `sharedir.sh` and
`chown -R wwwrun:www`:

| Package    | Destination                       |
| ---------- | --------------------------------- |
| `_configs` | `/srv/www/htdocs/_configs`        |
| `fpdf`     | `/srv/www/htdocs/fpdf`            |
| `informix` | `/srv/www/htdocs/informix`        |
| `vistas`   | `/srv/www/htdocs/informix/vistas` |
| `img`      | `/srv/www/htdocs/img`             |

It creates `_configs/cloudflare_r2_deca.php` with empty keys only if it does
not exist, so updates keep the server keys. Fill in the keys on the first
install: they are in Notion, under **Intranet > Intranet Deca**.

**Note:** the package replaces `vistas/opciones_albaran.php`. Review any
client changes to that file before installing.

### Manual installation

If appman is not available, copy the package to the server and run the
installer by hand:

1. Extract the package in `/tmp`, removing any previous version first:

    ```sh
    cd /tmp
    rm -rf /tmp/intranet-deca
    tar -xzf intranet-deca-v<version>.tgz
    ```

2. Run the installer:

    ```sh
    sudo intranet-deca/install.sh
    ```

## Intranet integration

### Packaging column in the DECA

`fpdf/deca.php` fills the Envase column by calling `envase()` from the
client's `_configs/consultas.php`. If the client has no `envase()`,
`_configs/consultas_deca.php` defines a fallback, guarded by
`function_exists()`, that returns an empty string and leaves the column blank.

To show packaging for a client without `envase()`, copy the function from
[snippets/consultas.php](snippets/consultas.php) into their
`_configs/consultas.php`, without the opening `<?php` tag.

### CMR column in albaranes_dia.php

`informix/albaranes_dia.php` is not in the package: edit it by hand on each
client. Inside the `while (odbc_fetch_row($result))` loop, replace the CMR
cell with [snippets/albaranes_dia.php](snippets/albaranes_dia.php). It shows
`DeCA.png` for Spanish clients (`pai_cli = 'ES'`) and `CMR.png` for the rest.

Also remove the condition that wraps the cell, since it is always true:

```php
if ($pai_cli <> 'ES' || $pai_cli == 'ES') {
```

## Testing

1. Open the daily delivery notes list and check that the CMR icon appears.
2. Fill in the transport data of a delivery note to `ES`, tick
   **Pagar transporte** and generate the DECA. Check that:

    - The PDF opens in the browser with the QR code.
    - Scanning the QR code opens the same PDF from R2.
    - The Envase column shows the packaging description, or is blank if the
      client has no `envase()`.
    - `DeCA.png` appears in the list and in the delivery note options, where
      the column header is `DECA`.

3. Clear the transport data and check that `CMR.png` appears again and that
   **Enviar** in `crear_cmr.php` generates a CMR.
