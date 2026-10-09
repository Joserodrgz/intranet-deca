#!/usr/bin/env bash
set -euo pipefail
shopt -s nullglob

# ------
# config
# ------

BASE_BRANCH="develop"
REGISTRY_BRANCH="main"

NEW_VERSION="${1:-}"

RELEASE_BRANCH="release/v${NEW_VERSION}"
RELEASE_TAG="v${NEW_VERSION}" # npm config get tag-version-prefix

# ----
# init
# ----

## check params

if ! [[ "${NEW_VERSION}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "ERROR: Invalid version format (expected x.y.z)" >&2
  exit 1
fi

## check repository is clean

if [[ -n "$(git status --porcelain)" ]]; then
  echo "ERROR: Repository not clean" >&2
  exit 1
fi

## check current branch is BASE_BRANCH

CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

if [[ "${CURRENT_BRANCH}" != "${BASE_BRANCH}" ]]; then
  echo "ERROR: Releases are only allowed from '${BASE_BRANCH}'" >&2
  exit 1
fi

## check local BASE_BRANCH is in sync with origin/BASE_BRANCH

git fetch --quiet origin

LOCAL_COMMIT=$(git rev-parse HEAD)
REMOTE_COMMIT=$(git rev-parse "origin/${BASE_BRANCH}")

if [[ "${LOCAL_COMMIT}" != "${REMOTE_COMMIT}" ]]; then
  echo "ERROR: Local '${BASE_BRANCH}' is not in sync with 'origin/${BASE_BRANCH}'" >&2
  exit 1
fi

## check RELEASE_BRANCH does not exist

git show-ref --verify --quiet "refs/heads/${RELEASE_BRANCH}" && {
  echo "ERROR: Branch '${RELEASE_BRANCH}' already exists" >&2
  exit 1
}

# -------
# release
# -------

echo "Release branch: ${RELEASE_BRANCH}"
echo "Release tag: ${RELEASE_TAG}"

## create RELEASE_BRANCH from BASE_BRANCH

git checkout -b "${RELEASE_BRANCH}"

## increment version and create release tag

npm version "${NEW_VERSION}" -m "released v%s"

## update origin remote with local repository

git push origin "${RELEASE_BRANCH}"
git push origin --tags

## merge RELEASE_BRANCH into BASE_BRANCH

git checkout "${BASE_BRANCH}"
git merge "${RELEASE_BRANCH}"
git push

## merge RELEASE_BRANCH into REGISTRY_BRANCH

git checkout "${REGISTRY_BRANCH}"
git merge "${RELEASE_BRANCH}"
git push

# -------
# cleanup
# -------

git checkout "${BASE_BRANCH}"