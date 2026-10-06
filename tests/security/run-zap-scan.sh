#!/bin/bash
# OWASP ZAP Baseline Scanner Runner for Taallum BD
set -e

TARGET_URL="${1:-http://127.0.0.1:8000}"
REPORT_DIR="$(pwd)/tests/security/reports"

mkdir -p "$REPORT_DIR"

echo "=========================================================="
echo " Starting OWASP ZAP Baseline Security Scan"
echo " Target URL: $TARGET_URL"
echo " Report Dir: $REPORT_DIR"
echo "=========================================================="

# Run ZAP baseline scan using official container
if command -v docker &> /dev/null; then
    docker run --rm -v "$(pwd):/zap/wrk/:rw" -t ghcr.io/zaproxy/zaproxy:stable \
        zap-baseline.py \
        -t "$TARGET_URL" \
        -c /zap/wrk/tests/security/zap-baseline.conf \
        -r /zap/wrk/tests/security/reports/zap-report.html \
        -J /zap/wrk/tests/security/reports/zap-report.json \
        -I || true
    echo "ZAP scan finished. HTML report saved to tests/security/reports/zap-report.html"
else
    echo "Docker not detected. Please install Docker to run the containerized OWASP ZAP baseline scanner."
    echo "Scan config available at: tests/security/zap-baseline.conf"
fi
