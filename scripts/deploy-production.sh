#!/usr/bin/env bash

set -Eeuo pipefail

readonly SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"

export DEPLOY_CONTEXT=production

exec "$SCRIPT_DIR/deploy-dev.sh" "$@"

