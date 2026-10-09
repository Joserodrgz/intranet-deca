#!/usr/bin/env bash
set -euo pipefail
shopt -s nullglob

SELF_DIR="$(cd "$(dirname "$(readlink -f "${BASH_SOURCE[0]:-$0}")")" && pwd -P)"

source "${SELF_DIR}/build.lib.sh"

# ------
# config
# ------

BASE_BRANCH="main"

SRC_DIR="deca"
DIST_DIR="dist"
BUILD_DIR="${DIST_DIR}/deca"

# ----
# init
# ----

## set CWD to the root of the project

cd "$(git rev-parse --show-toplevel)"

## check build requirements

check-build-requirements "${BASE_BRANCH}"

## declare project variables

PRJ_NAME=$(ci/build-info.sh name)
PRJ_VERSION=$(ci/build-info.sh version)

## cleanup previous build

rm -rf "${DIST_DIR}"
mkdir -p "${BUILD_DIR}"

# -----
# build
# -----

## copy runtime files

cp -a "${SRC_DIR}/." "${BUILD_DIR}/"

## generate version file

ci/build-info.sh > "${BUILD_DIR}/release.json"

# ----
# dist
# ----

BUILD_DIRNAME="${BUILD_DIR#"${DIST_DIR}"/}"
DIST_OUTPUT="${DIST_DIR}/${PRJ_NAME}-v${PRJ_VERSION}.tgz"

tar -C "${DIST_DIR}" -czf "${DIST_OUTPUT}" "${BUILD_DIRNAME}"

# -------
# summary
# -------

echo "Build completed: ${DIST_OUTPUT}"
