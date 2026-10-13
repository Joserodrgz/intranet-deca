#!/usr/bin/env bash
set -euo pipefail # seteo de gestión de fallos
shopt -s nullglob


SELF_DIR="$(cd "$(dirname "$(readlink -f "${BASH_SOURCE[0]:-${0}}")")" && pwd -P)"

HTDOCS="${HTDOCS:-/srv/www/htdocs}"

if [[ ${EUID} -ne 0 ]]; then # '$EUID' es 0 cuando eres root
  echo "ERROR: ejecuta con sudo" >&2
  exit 1
fi

if [[ ! -d "${HTDOCS}" ]]; then # '-d' comprueba que la ruta exista
  echo "ERROR: no existe ${HTDOCS}" >&2
  exit 1
fi

VERSION="$(grep '"version"' "${SELF_DIR}/release.json" | cut -d'"' -f4 || true)"
if [[ -z "${VERSION}" ]]; then
  echo "ERROR: no se pudo leer la versión de release.json" >&2
  exit 1
fi

for cmd in sharedir.sh tarhtdocs.sh; do
  if ! command -v "${cmd}" >/dev/null; then
    echo "ERROR: ${cmd} no está en el PATH" >&2
    exit 1
  fi
done


MAPPINGS=(
  "_configs:_configs"
  "fpdf:fpdf"
  "informix:informix"
  "vistas:informix/vistas"
)

for map in "${MAPPINGS[@]}"; do
  src="${map%%:*}" # 'map%%:*' quita todo lo que va desde el ':' hasta el final
  dst="${map#*:}" # 'map#*:'quita todo hasta el primer ':' incluido, te quedas solo con el destino

  if [[ ! -d "${SELF_DIR}/${src}" ]]; then
    echo "ERROR: el paquete no trae la carpeta ${src}" >&2
    exit 1
  fi
done

# htdocs backup
tarhtdocs.sh

for map in "${MAPPINGS[@]}"; do
  src="${map%%:*}"
  dst="${map#*:}"

  mkdir -p "${HTDOCS}/${dst}"
  cp -a "${SELF_DIR}/${src}/." "${HTDOCS}/${dst}/"
  echo "Copiado ${src} -> ${HTDOCS}/${dst}"
done

cd "$(dirname "${HTDOCS}")"
sharedir.sh "$(basename "${HTDOCS}")"
chown -R wwwrun:www "$(basename "${HTDOCS}")"

echo "DECA v${VERSION} instalado en ${HTDOCS}"
