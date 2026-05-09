#!/bin/bash

echo "=== Testing Login Flow ==="
echo ""

# Clean cookies
rm -f /tmp/login_test_cookies.txt
TEST_EMAIL="${ERP_TEST_EMAIL:-admin@example.test}"
TEST_PASS="${ERP_TEST_PASS:-Admin@1234}"

# 1. Get login page and CSRF token
echo "1. Getting login page..."
LOGIN_HTML=$(curl -s -c /tmp/login_test_cookies.txt http://127.0.0.1:8000/login)
CSRF_TOKEN=$(echo "$LOGIN_HTML" | grep -oP '(?<=name="_token" value=")[^"]+' | head -1)

if [ -z "$CSRF_TOKEN" ]; then
    echo "ERROR: Could not extract CSRF token"
    echo "Login page response length: ${#LOGIN_HTML} bytes"
    exit 1
fi

echo "✓ Got CSRF token: ${CSRF_TOKEN:0:20}..."

# 2. Attempt login
echo ""
echo "2. Attempting login with ${TEST_EMAIL}..."
LOGIN_RESPONSE=$(curl -s -b /tmp/login_test_cookies.txt -c /tmp/login_test_cookies.txt \
    -X POST http://127.0.0.1:8000/login \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -H "X-Requested-With: XMLHttpRequest" \
    -w "\nHTTP_CODE:%{http_code}" \
    -d "_token=${CSRF_TOKEN}" \
    -d "email=${TEST_EMAIL}" \
    -d "password=${TEST_PASS}" \
    -L)

HTTP_CODE=$(echo "$LOGIN_RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)
RESPONSE_BODY=$(echo "$LOGIN_RESPONSE" | sed '/HTTP_CODE:/d')

echo "HTTP Status: $HTTP_CODE"
echo ""

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    echo "✓ Login request successful!"
    echo ""
    echo "Response preview:"
    echo "$RESPONSE_BODY" | head -20
else
    echo "✗ Login failed with HTTP $HTTP_CODE"
    echo ""
    echo "Response:"
    echo "$RESPONSE_BODY"
fi

echo ""
echo "3. Checking session..."
cat /tmp/login_test_cookies.txt | grep -v "^#"
