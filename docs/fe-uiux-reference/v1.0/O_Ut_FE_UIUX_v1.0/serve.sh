#!/usr/bin/env sh
set -eu
cd "$(dirname "$0")"
node tools/server.mjs --host 127.0.0.1 --port 4173
