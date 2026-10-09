<?php
function comprobarRequisitosR2()
{
    $resultado = array();

    $resultado['curl'] = function_exists('curl_init');
    $resultado['hash'] = function_exists('hash');
    $resultado['hmac'] = function_exists('hash_hmac');
    $resultado['sha256'] = in_array('sha256', hash_algos());

    return $resultado;
}
function subirPdfR2($archivo, $objeto, $bucket, $endpoint, $access_key, $secret_key)
{
    if (!file_exists($archivo)) {
        return array(
            'ok'    => false,
            'error' => 'No existe el archivo: ' . $archivo
        );
    }

    $contenido = file_get_contents($archivo);

    if ($contenido === false) {
        return array(
            'ok'    => false,
            'error' => 'No se puede leer el archivo'
        );
    }

    /*
     * Configuración Cloudflare R2
     */
    $region  = 'auto';
    $service = 's3';

    $endpoint = rtrim($endpoint, '/');

    $datos_endpoint = parse_url($endpoint);

    if (!isset($datos_endpoint['host'])) {
        return array(
            'ok'    => false,
            'error' => 'Endpoint R2 incorrecto: ' . $endpoint
        );
    }

    $host = $datos_endpoint['host'];

    /*
     * Fecha UTC para la firma AWS V4
     */
    $amz_date   = gmdate('Ymd\THis\Z');
    $date_stamp = gmdate('Ymd');

    /*
     * SHA256 del PDF
     */
    $payload_hash = hash('sha256', $contenido);

    /*
     * Codificamos la ruta del objeto.
     *
     * Ejemplo:
     * 2026/DECA-2026-000004.pdf
     */
    $partes = explode('/', $objeto);
    $partes_codificadas = array();

    foreach ($partes as $parte) {
        $partes_codificadas[] = rawurlencode($parte);
    }

    $objeto_url = implode('/', $partes_codificadas);

    /*
     * R2 S3 API:
     * /bucket/objeto
     */
    $canonical_uri =
        '/' . rawurlencode($bucket) .
        '/' . $objeto_url;

    $url = $endpoint . $canonical_uri;

    /*
     * Cabeceras firmadas
     */
    $canonical_headers =
        'host:' . $host . "\n" .
        'x-amz-content-sha256:' . $payload_hash . "\n" .
        'x-amz-date:' . $amz_date . "\n";

    $signed_headers =
        'host;x-amz-content-sha256;x-amz-date';

    /*
     * Canonical Request
     */
    $canonical_request =
        "PUT\n" .
        $canonical_uri . "\n" .
        "\n" .
        $canonical_headers . "\n" .
        $signed_headers . "\n" .
        $payload_hash;

    /*
     * Credential Scope
     */
    $credential_scope =
        $date_stamp . '/' .
        $region . '/' .
        $service . '/aws4_request';

    /*
     * String To Sign
     */
    $string_to_sign =
        "AWS4-HMAC-SHA256\n" .
        $amz_date . "\n" .
        $credential_scope . "\n" .
        hash('sha256', $canonical_request);

    /*
     * Clave de firma AWS Signature V4
     */
    $k_date = hash_hmac(
        'sha256',
        $date_stamp,
        'AWS4' . $secret_key,
        true
    );

    $k_region = hash_hmac(
        'sha256',
        $region,
        $k_date,
        true
    );

    $k_service = hash_hmac(
        'sha256',
        $service,
        $k_region,
        true
    );

    $k_signing = hash_hmac(
        'sha256',
        'aws4_request',
        $k_service,
        true
    );

    $signature = hash_hmac(
        'sha256',
        $string_to_sign,
        $k_signing
    );

    /*
     * Cabecera Authorization
     */
    $authorization =
        'AWS4-HMAC-SHA256 ' .
        'Credential=' . $access_key . '/' . $credential_scope . ', ' .
        'SignedHeaders=' . $signed_headers . ', ' .
        'Signature=' . $signature;

    /*
     * Enviamos el PDF mediante PUT
     */
	$ruta_cacert = dirname(__FILE__) . '/cacert.pem';
	if (!file_exists($ruta_cacert)) {
    return array(
        'ok'    => false,
        'error' => 'No existe cacert.pem: ' . $ruta_cacert
    );
	}
	
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CAINFO, $ruta_cacert);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $contenido);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Authorization: ' . $authorization,
            'Content-Type: application/pdf',
            'x-amz-content-sha256: ' . $payload_hash,
            'x-amz-date: ' . $amz_date
        )
    );

    $respuesta = curl_exec($ch);

    if ($respuesta === false) {

        $error = curl_error($ch);

        curl_close($ch);

        return array(
            'ok'    => false,
            'error' => 'Error cURL: ' . $error
        );
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    /*
     * S3 PUT Object devuelve HTTP 200
     */
    if ($http_code == 200) {

        return array(
            'ok'     => true,
            'objeto' => $objeto
        );
    }

    return array(
        'ok'        => false,
        'http_code' => $http_code,
        'error'     => 'R2 HTTP ' . $http_code . ': ' . $respuesta
    );
}
////////////////////
function descargarPdfR2($objeto, $bucket, $endpoint, $access_key, $secret_key)
{
    $region  = 'auto';
    $service = 's3';

    $endpoint = rtrim($endpoint, '/');

    $datos_endpoint = parse_url($endpoint);

    if (!isset($datos_endpoint['host'])) {
        return array(
            'ok'    => false,
            'error' => 'Endpoint R2 incorrecto'
        );
    }

    $host = $datos_endpoint['host'];

    /*
     * Fecha UTC para AWS Signature V4
     */
    $amz_date   = gmdate('Ymd\THis\Z');
    $date_stamp = gmdate('Ymd');

    /*
     * Codificamos correctamente la ruta del objeto.
     */
    $partes = explode('/', $objeto);
    $partes_codificadas = array();

    foreach ($partes as $parte) {
        $partes_codificadas[] = rawurlencode($parte);
    }

    $objeto_url = implode('/', $partes_codificadas);

    /*
     * Ruta R2:
     * /hmg-deca/2026/DECA-2026-000004.pdf
     */
    $canonical_uri =
        '/' . rawurlencode($bucket) .
        '/' . $objeto_url;

    $url = $endpoint . $canonical_uri;

    /*
     * Para GET sin cuerpo, SHA256 de cadena vacía.
     */
    $payload_hash = hash('sha256', '');

    /*
     * Cabeceras firmadas
     */
    $canonical_headers =
        'host:' . $host . "\n" .
        'x-amz-content-sha256:' . $payload_hash . "\n" .
        'x-amz-date:' . $amz_date . "\n";

    $signed_headers =
        'host;x-amz-content-sha256;x-amz-date';

    /*
     * Canonical Request
     */
    $canonical_request =
        "GET\n" .
        $canonical_uri . "\n" .
        "\n" .
        $canonical_headers . "\n" .
        $signed_headers . "\n" .
        $payload_hash;

    /*
     * Credential Scope
     */
    $credential_scope =
        $date_stamp . '/' .
        $region . '/' .
        $service . '/aws4_request';

    /*
     * String To Sign
     */
    $string_to_sign =
        "AWS4-HMAC-SHA256\n" .
        $amz_date . "\n" .
        $credential_scope . "\n" .
        hash('sha256', $canonical_request);

    /*
     * Firma AWS V4
     */
    $k_date = hash_hmac(
        'sha256',
        $date_stamp,
        'AWS4' . $secret_key,
        true
    );

    $k_region = hash_hmac(
        'sha256',
        $region,
        $k_date,
        true
    );

    $k_service = hash_hmac(
        'sha256',
        $service,
        $k_region,
        true
    );

    $k_signing = hash_hmac(
        'sha256',
        'aws4_request',
        $k_service,
        true
    );

    $signature = hash_hmac(
        'sha256',
        $string_to_sign,
        $k_signing
    );

    /*
     * Authorization
     */
    $authorization =
        'AWS4-HMAC-SHA256 ' .
        'Credential=' . $access_key . '/' . $credential_scope . ', ' .
        'SignedHeaders=' . $signed_headers . ', ' .
        'Signature=' . $signature;

    /*
     * Certificado CA
     */
    $ruta_cacert = dirname(__FILE__) . '/cacert.pem';

    if (!file_exists($ruta_cacert)) {
        return array(
            'ok'    => false,
            'error' => 'No existe cacert.pem: ' . $ruta_cacert
        );
    }

    /*
     * Petición GET a R2
     */
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_CAINFO, $ruta_cacert);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Authorization: ' . $authorization,
            'x-amz-content-sha256: ' . $payload_hash,
            'x-amz-date: ' . $amz_date
        )
    );

    $contenido = curl_exec($ch);

    if ($contenido === false) {

        $error = curl_error($ch);

        curl_close($ch);

        return array(
            'ok'    => false,
            'error' => 'Error cURL: ' . $error
        );
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    curl_close($ch);

    if ($http_code == 200) {

        return array(
            'ok'           => true,
            'contenido'    => $contenido,
            'content_type' => $content_type
        );
    }

    return array(
        'ok'        => false,
        'http_code' => $http_code,
        'error'     => 'R2 HTTP ' . $http_code . ': ' . $contenido
    );
}
/////////////////////////////
function generarUrlFirmadaR2(
    $objeto,
    $bucket,
    $endpoint,
    $access_key,
    $secret_key,
    $segundos
) {
    $region  = 'auto';
    $service = 's3';

    /*
     * R2 admite entre 1 segundo y 7 días.
     */
    $segundos = (int)$segundos;

    if ($segundos < 1) {
        $segundos = 1;
    }

    if ($segundos > 604800) {
        $segundos = 604800;
    }

    $endpoint = rtrim($endpoint, '/');

    $datos_endpoint = parse_url($endpoint);

    if (!isset($datos_endpoint['host'])) {
        return false;
    }

    $host = $datos_endpoint['host'];

    /*
     * Fechas AWS
     */
    $amz_date   = gmdate('Ymd\THis\Z');
    $date_stamp = gmdate('Ymd');

    /*
     * Codificar ruta del objeto sin perder las /
     */
    $partes = explode('/', $objeto);
    $partes_codificadas = array();

    foreach ($partes as $parte) {
        $partes_codificadas[] = rawurlencode($parte);
    }

    $objeto_url = implode('/', $partes_codificadas);

    /*
     * Conservamos el mismo formato que ya nos funciona
     * con nuestro endpoint R2:
     *
     * /hmg-deca/2026/DECA-2026-000004.pdf
     */
    $canonical_uri =
        '/' . rawurlencode($bucket) .
        '/' . $objeto_url;

    /*
     * Credential Scope
     */
    $credential_scope =
        $date_stamp . '/' .
        $region . '/' .
        $service . '/aws4_request';

    /*
     * Parámetros que formarán parte de la URL.
     *
     * IMPORTANTE:
     * deben estar ordenados alfabéticamente.
     */
    $parametros = array(
        'X-Amz-Algorithm'     => 'AWS4-HMAC-SHA256',
        'X-Amz-Credential'    => $access_key . '/' . $credential_scope,
        'X-Amz-Date'          => $amz_date,
        'X-Amz-Expires'       => (string)$segundos,
        'X-Amz-SignedHeaders' => 'host'
    );

    ksort($parametros);

    /*
     * Canonical Query String
     */
    $query = array();

    foreach ($parametros as $clave => $valor) {
        $query[] =
            rawurlencode($clave) .
            '=' .
            rawurlencode($valor);
    }

    $canonical_query_string = implode('&', $query);

    /*
     * Para una URL GET firmada utilizamos
     * UNSIGNED-PAYLOAD.
     */
    $canonical_headers = 'host:' . $host . "\n";

    $signed_headers = 'host';

    $payload_hash = 'UNSIGNED-PAYLOAD';

    /*
     * Canonical Request
     */
    $canonical_request =
        "GET\n" .
        $canonical_uri . "\n" .
        $canonical_query_string . "\n" .
        $canonical_headers . "\n" .
        $signed_headers . "\n" .
        $payload_hash;

    /*
     * String To Sign
     */
    $string_to_sign =
        "AWS4-HMAC-SHA256\n" .
        $amz_date . "\n" .
        $credential_scope . "\n" .
        hash('sha256', $canonical_request);

    /*
     * Clave de firma AWS Signature V4
     */
    $k_date = hash_hmac(
        'sha256',
        $date_stamp,
        'AWS4' . $secret_key,
        true
    );

    $k_region = hash_hmac(
        'sha256',
        $region,
        $k_date,
        true
    );

    $k_service = hash_hmac(
        'sha256',
        $service,
        $k_region,
        true
    );

    $k_signing = hash_hmac(
        'sha256',
        'aws4_request',
        $k_service,
        true
    );

    $signature = hash_hmac(
        'sha256',
        $string_to_sign,
        $k_signing
    );

    /*
     * URL definitiva
     */
    $url =
        $endpoint .
        $canonical_uri .
        '?' .
        $canonical_query_string .
        '&X-Amz-Signature=' .
        $signature;

    return $url;
}
/////////////