#!/bin/bash

# =============================================================================
# CAS Private Care Security Check Script
# =============================================================================
# Run this script periodically to verify security configurations are correct.
# 
# Usage: 
#   bash scripts/security-check.sh
#   bash scripts/security-check.sh https://staging.casprivatecare.com
#
# Requirements:
#   - curl
#   - grep
#   - awk
# =============================================================================

# Configuration
DOMAIN="${1:-https://casprivatecare.com}"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
PASSED=0
FAILED=0
WARNINGS=0

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  CAS Private Care Security Check${NC}"
    echo -e "${BLUE}  Domain: ${DOMAIN}${NC}"
    echo -e "${BLUE}  Date: ${TIMESTAMP}${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

check_pass() {
    echo -e "  ${GREEN}✅ $1${NC}"
    ((PASSED++))
}

check_fail() {
    echo -e "  ${RED}❌ $1${NC}"
    ((FAILED++))
}

check_warn() {
    echo -e "  ${YELLOW}⚠️  $1${NC}"
    ((WARNINGS++))
}

section_header() {
    echo ""
    echo -e "${YELLOW}$1${NC}"
    echo "────────────────────────────────────────────────────"
}

# =============================================================================
# Security Checks
# =============================================================================

print_header

# Fetch headers once
HEADERS=$(curl -sI "${DOMAIN}" 2>/dev/null)

# -----------------------------------------------------------------------------
# 1. SSL/TLS Certificate Check
# -----------------------------------------------------------------------------
section_header "1. SSL/TLS Certificate"

if echo | openssl s_client -connect "${DOMAIN#https://}:443" -servername "${DOMAIN#https://}" 2>/dev/null | openssl x509 -noout -dates 2>/dev/null; then
    CERT_EXPIRY=$(echo | openssl s_client -connect "${DOMAIN#https://}:443" -servername "${DOMAIN#https://}" 2>/dev/null | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
    DAYS_LEFT=$(( ($(date -d "${CERT_EXPIRY}" +%s) - $(date +%s)) / 86400 ))
    
    if [ "$DAYS_LEFT" -gt 30 ]; then
        check_pass "SSL Certificate valid for ${DAYS_LEFT} days"
    elif [ "$DAYS_LEFT" -gt 7 ]; then
        check_warn "SSL Certificate expires in ${DAYS_LEFT} days - renew soon!"
    else
        check_fail "SSL Certificate expires in ${DAYS_LEFT} days - URGENT!"
    fi
else
    check_fail "Could not verify SSL certificate"
fi

# -----------------------------------------------------------------------------
# 2. Security Headers
# -----------------------------------------------------------------------------
section_header "2. Security Headers"

check_header() {
    local header_name="$1"
    local header_display="$2"
    
    if echo "$HEADERS" | grep -qi "$header_name"; then
        local header_value=$(echo "$HEADERS" | grep -i "$header_name" | head -1)
        check_pass "${header_display}: Present"
    else
        check_fail "${header_display}: MISSING"
    fi
}

check_header "Strict-Transport-Security" "HSTS"
check_header "X-Content-Type-Options" "X-Content-Type-Options"
check_header "X-Frame-Options" "X-Frame-Options"
check_header "X-XSS-Protection" "X-XSS-Protection"
check_header "Content-Security-Policy" "Content-Security-Policy"
check_header "Referrer-Policy" "Referrer-Policy"
check_header "Permissions-Policy" "Permissions-Policy"

# -----------------------------------------------------------------------------
# 3. Exposed Files Check
# -----------------------------------------------------------------------------
section_header "3. Sensitive Files Exposure"

check_exposed() {
    local url="$1"
    local description="$2"
    
    local status=$(curl -sI "${url}" 2>/dev/null | head -1 | awk '{print $2}')
    
    if [ "$status" = "404" ] || [ "$status" = "403" ]; then
        check_pass "${description}: Protected (HTTP ${status})"
    elif [ -z "$status" ]; then
        check_warn "${description}: Could not check"
    else
        check_fail "${description}: EXPOSED (HTTP ${status})"
    fi
}

check_exposed "${DOMAIN}/.env" ".env file"
check_exposed "${DOMAIN}/.git/config" ".git directory"
check_exposed "${DOMAIN}/storage/logs/laravel.log" "Laravel logs"
check_exposed "${DOMAIN}/.htaccess" ".htaccess file"
check_exposed "${DOMAIN}/composer.json" "composer.json"
check_exposed "${DOMAIN}/artisan" "artisan file"
check_exposed "${DOMAIN}/phpinfo.php" "phpinfo.php"
check_exposed "${DOMAIN}/info.php" "info.php"
check_exposed "${DOMAIN}/storage/app" "Storage directory"
check_exposed "${DOMAIN}/vendor" "Vendor directory"

# -----------------------------------------------------------------------------
# 4. Security.txt
# -----------------------------------------------------------------------------
section_header "4. Security.txt"

SECURITY_TXT=$(curl -s "${DOMAIN}/.well-known/security.txt" 2>/dev/null)

if [ -n "$SECURITY_TXT" ] && echo "$SECURITY_TXT" | grep -qi "Contact"; then
    check_pass "security.txt exists and has Contact field"
    
    if echo "$SECURITY_TXT" | grep -qi "Expires"; then
        check_pass "security.txt has Expires field"
    else
        check_warn "security.txt missing Expires field"
    fi
else
    check_warn "security.txt not found or incomplete"
fi

# -----------------------------------------------------------------------------
# 5. Common Attack Vectors
# -----------------------------------------------------------------------------
section_header "5. Common Attack Vectors"

# Check for directory listing
DIR_STATUS=$(curl -s "${DOMAIN}/storage/" 2>/dev/null)
if echo "$DIR_STATUS" | grep -qi "Index of"; then
    check_fail "Directory listing enabled on /storage/"
else
    check_pass "Directory listing disabled"
fi

# Check for version disclosure
if echo "$HEADERS" | grep -qi "X-Powered-By"; then
    check_warn "X-Powered-By header exposes technology"
else
    check_pass "X-Powered-By header not present"
fi

if echo "$HEADERS" | grep -qi "Server:.*Apache\|Server:.*nginx"; then
    SERVER=$(echo "$HEADERS" | grep -i "Server:" | head -1)
    if echo "$SERVER" | grep -qE "[0-9]+\.[0-9]+"; then
        check_warn "Server header exposes version number"
    else
        check_pass "Server header present without version"
    fi
fi

# -----------------------------------------------------------------------------
# 6. API Security
# -----------------------------------------------------------------------------
section_header "6. API Security"

# Check if API requires authentication
API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "${DOMAIN}/api/admin/stats" 2>/dev/null)
if [ "$API_STATUS" = "401" ] || [ "$API_STATUS" = "403" ]; then
    check_pass "Admin API requires authentication (HTTP ${API_STATUS})"
else
    check_warn "Admin API returned HTTP ${API_STATUS} - verify authentication"
fi

# Check CSRF cookie
CSRF_STATUS=$(curl -sI "${DOMAIN}/sanctum/csrf-cookie" 2>/dev/null | head -1 | awk '{print $2}')
if [ "$CSRF_STATUS" = "204" ] || [ "$CSRF_STATUS" = "200" ]; then
    check_pass "CSRF cookie endpoint accessible"
else
    check_warn "CSRF cookie endpoint returned HTTP ${CSRF_STATUS}"
fi

# -----------------------------------------------------------------------------
# 7. Cookie Security
# -----------------------------------------------------------------------------
section_header "7. Cookie Security"

COOKIES=$(curl -sI "${DOMAIN}" 2>/dev/null | grep -i "Set-Cookie")

if echo "$COOKIES" | grep -qi "Secure"; then
    check_pass "Cookies have Secure flag"
else
    check_warn "Some cookies may not have Secure flag"
fi

if echo "$COOKIES" | grep -qi "HttpOnly"; then
    check_pass "Cookies have HttpOnly flag"
else
    check_warn "Some cookies may not have HttpOnly flag"
fi

if echo "$COOKIES" | grep -qi "SameSite"; then
    check_pass "Cookies have SameSite attribute"
else
    check_warn "Some cookies may not have SameSite attribute"
fi

# -----------------------------------------------------------------------------
# Summary
# -----------------------------------------------------------------------------
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}  SUMMARY${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "  ${GREEN}Passed:${NC}   ${PASSED}"
echo -e "  ${YELLOW}Warnings:${NC} ${WARNINGS}"
echo -e "  ${RED}Failed:${NC}   ${FAILED}"
echo ""

TOTAL=$((PASSED + WARNINGS + FAILED))
SCORE=$((PASSED * 100 / TOTAL))

if [ "$FAILED" -eq 0 ] && [ "$WARNINGS" -eq 0 ]; then
    echo -e "  ${GREEN}Security Score: ${SCORE}% - EXCELLENT${NC}"
elif [ "$FAILED" -eq 0 ]; then
    echo -e "  ${YELLOW}Security Score: ${SCORE}% - GOOD (Review warnings)${NC}"
else
    echo -e "  ${RED}Security Score: ${SCORE}% - NEEDS ATTENTION${NC}"
fi

echo ""
echo "  Report generated: ${TIMESTAMP}"
echo ""

# Exit with error code if any failures
if [ "$FAILED" -gt 0 ]; then
    exit 1
fi

exit 0
