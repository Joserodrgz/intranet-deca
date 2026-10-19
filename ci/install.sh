#!/usr/bin/env bash
set -euo pipefail # exit on error, unset variable or failed pipe
shopt -s nullglob

PATH="/usr/local/bin:${PATH}"

SELF_DIR="$(cd "$(dirname "$(readlink -f "${BASH_SOURCE[0]:-${0}}")")" && pwd -P)"

source "${SELF_DIR}/install.lib.sh"

HTDOCS="${HTDOCS:-/srv/www/htdocs}"

if [[ ${EUID} -ne 0 ]]; then # '$EUID' is 0 when running as root
  echo "ERROR: This script must be run with sudo" >&2
  exit 1
fi

## install required packages

REQUIRED_PACKAGES=(
  php5-curl
  php5-iconv
  php5-json
  php5-odbc
  php5-mbstring
  php5-gd
)

install-packages "${REQUIRED_PACKAGES[@]}"

if [[ ! -d "${HTDOCS}" ]]; then # '-d' checks that the directory exists
  echo "ERROR: ${HTDOCS} does not exist" >&2
  exit 1
fi

VERSION="$(grep '"version"' "${SELF_DIR}/release.json" | cut -d'"' -f4 || true)"
if [[ -z "${VERSION}" ]]; then
  echo "ERROR: Could not read the version from release.json" >&2
  exit 1
fi

for cmd in sharedir.sh tarhtdocs.sh; do
  if ! command -v "${cmd}" >/dev/null; then
    echo "ERROR: ${cmd} is not in the PATH" >&2
    exit 1
  fi
done


MAPPINGS=(
  "_configs:_configs"
  "fpdf:fpdf"
  "informix:informix"
  "vistas:informix/vistas"
  "img:img"
)

for map in "${MAPPINGS[@]}"; do
  src="${map%%:*}" # '%%:*' removes everything from the first ':' to the end (source)
  dst="${map#*:}" # '#*:' removes everything up to and including the first ':' (destination)

  if [[ ! -d "${SELF_DIR}/${src}" ]]; then
    echo "ERROR: The package does not include the ${src} folder" >&2
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
  echo "Copied ${src} -> ${HTDOCS}/${dst}"
done

# Config files: created only if they do not exist, so updates keep the server values
R2_CONFIG_FILE="${HTDOCS}/_configs/cloudflare_r2_deca.php"

if [[ ! -f "${R2_CONFIG_FILE}" ]]; then
  echo "Creating R2 config file (fill in the R2 keys)..."
  cp -a "${SELF_DIR}/config/cloudflare_r2_deca.php" "${R2_CONFIG_FILE}"
fi

cd "$(dirname "${HTDOCS}")"
sharedir.sh "$(basename "${HTDOCS}")"
chown -R wwwrun:www "$(basename "${HTDOCS}")"

echo "DECA v${VERSION} installed in ${HTDOCS}"
