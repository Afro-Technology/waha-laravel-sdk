#!/usr/bin/env bash
set -euo pipefail

SPEC="resources/openapi/openapi.json"
OUT_DIR="generated"
GEN_VERSION="${OPENAPI_GENERATOR_VERSION:-7.6.0}"

if [ ! -f "$SPEC" ]; then
  echo "OpenAPI spec not found at $SPEC. Run: WAHA_OPENAPI_URL=... composer openapi:fetch"
  exit 1
fi

mkdir -p "$OUT_DIR"

rm -rf "$OUT_DIR/lib" "$OUT_DIR/docs" "$OUT_DIR/test" "$OUT_DIR/.openapi-generator" "$OUT_DIR/composer.json" "$OUT_DIR/README.md"

echo "Generating PHP client using openapi-generator-cli:$GEN_VERSION"
docker run --rm -v "$(pwd):/work" -w /work openapitools/openapi-generator-cli:v${GEN_VERSION} \
  generate \
  -i "$SPEC" \
  -g php \
  -o "$OUT_DIR" \
  --additional-properties="invokerPackage=MediaBridge\\Waha\\Generated,packageName=MediaBridgeWahaGenerated,srcBasePath=lib" \
  --skip-validate-spec

echo "Done. Output: $OUT_DIR"
