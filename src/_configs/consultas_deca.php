<?php
// Consultas propias del DECA.
// Se incluye DESPUÉS de consultas.php y solo define las funciones que el cliente no tenga.

if (!function_exists('envase')) {
  // Cliente sin envases: la columna "Envase" del DECA queda en blanco
  function envase($codigo)
  {
    return '';
  }
}
