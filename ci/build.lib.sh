check-build-requirements() {
  local base_branch="${1}"
  local current_branch
  local local_commit
  local remote_commit

  if [[ -z "${base_branch}" ]]; then
    echo "ERROR: Missing required parameter 'base_branch'" >&2
    return 1
  fi

  ## check repository is clean

  if [[ -n "$(git status --porcelain)" ]]; then
    echo "ERROR: Repository not clean" >&2
    return 1
  fi

  ## check current branch is base_branch

  current_branch="$(git rev-parse --abbrev-ref HEAD)"

  if [[ "${current_branch}" != "${base_branch}" ]]; then
    echo "ERROR: Builds are only allowed from '${base_branch}'" >&2
    return 1
  fi

  ## check local base_branch is in sync with origin/base_branch

  git fetch --quiet origin

  local_commit="$(git rev-parse HEAD)"
  remote_commit="$(git rev-parse "origin/${base_branch}")"

  if [[ "${local_commit}" != "${remote_commit}" ]]; then
    echo "ERROR: Local '${base_branch}' is not in sync with 'origin/${base_branch}'" >&2
    return 1
  fi

  ## check HEAD commit is tagged

  if ! git describe --tags --exact-match >/dev/null 2>&1; then
    echo "ERROR: HEAD of '${base_branch}' is not tagged" >&2
    return 1
  fi
}