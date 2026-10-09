#!/usr/bin/env bash
set -euo pipefail
shopt -s nullglob

project-name() {
  npm pkg get name | tr -d '"'
}

project-version() {
  npm pkg get version | tr -d '"'
}

project-url() {
  npm pkg get repository.url | tr -d '"'
}

git-commit() {
  git rev-parse --short HEAD
}

build-date() {
  TZ=Europe/Madrid date '+%F %T %:z'
}

output-json() {
cat << EOF
{
  "name": "$(project-name)",
  "version": "$(project-version)",
  "url": "$(project-url)",
  "gitCommit": "$(git-commit)",
  "buildDate": "$(build-date)"
}
EOF
}

SCRIPT_NAME="$(basename "$0")"
COMMAND="${1:-}"

case "${COMMAND}" in
  "")
    output-json
    ;;
  name)
    project-name
    ;;

  version)
    project-version
    ;;

  url)
    project-url
    ;;

  git-commit)
    git-commit
    ;;

  build-date)
    build-date
    ;;

  *)
    echo "ERROR: Unknown command: ${COMMAND}" >&2
    echo "Usage: ${SCRIPT_NAME} [name|version|url|git-commit|build-date]" >&2
    exit 1
    ;;
esac