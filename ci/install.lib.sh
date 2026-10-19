package-manager() {
  if command -v zypper >/dev/null 2>&1; then
    echo "zypper"
    return
  fi

  if command -v apt-get >/dev/null 2>&1; then
    echo "apt"
    return
  fi

  return 1
}

install-zypper-package() {
  local package="${1}"

  if ! rpm -q "${package}" >/dev/null 2>&1; then
    echo "Installing ${package}..."
    zypper -n install "${package}"
  fi
}

install-apt-package() {
  local package="${1}"

  if ! dpkg -s "${package}" >/dev/null 2>&1; then
    echo "Installing ${package}..."
    apt-get install -y "${package}"
  fi
}

install-packages() {
  local packages=("$@")
  local package

  case "$(package-manager)" in
    zypper)
      for package in "${packages[@]}"; do
        install-zypper-package "${package}"
      done
      ;;

    apt)
      apt-get update -qq

      for package in "${packages[@]}"; do
        install-apt-package "${package}"
      done
      ;;

    *)
      echo "Unsupported package manager."
      return 1
      ;;
  esac
}