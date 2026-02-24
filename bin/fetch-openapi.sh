#!/usr/bin/env bash
set -euo pipefail

: "${WAHA_OPENAPI_URL:?Set WAHA_OPENAPI_URL to your WAHA instance OpenAPI URL, e.g. https://host/swagger/openapi.json}"

OUT="resources/openapi/openapi.json"
mkdir -p "$(dirname "$OUT")"

echo "Fetching OpenAPI spec from: $WAHA_OPENAPI_URL"
curl -fsSL "$WAHA_OPENAPI_URL" -o "$OUT"
echo "Saved to $OUT"
