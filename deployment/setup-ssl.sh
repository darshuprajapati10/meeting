#!/bin/bash
###############################################################################
# SSL/HTTPS Setup Script for Yujix API
# Uses Let's Encrypt (free SSL certificates) via Certbot
#
# Run this on the production server: root@157.245.97.43
# Usage: sudo bash setup-ssl.sh
###############################################################################

set -e

echo "=========================================="
echo "SSL/HTTPS Setup with Let's Encrypt"
echo "=========================================="

# Step 1: Install Certbot
echo "Step 1: Installing Certbot..."
apt-get update -qq
apt-get install -y certbot python3-certbot-nginx

echo "✅ Certbot installed"

# Step 2: Obtain SSL certificate and configure nginx automatically
echo ""
echo "Step 2: Obtaining SSL certificate..."
echo "This will:"
echo "  - Validate domain ownership"
echo "  - Obtain free SSL certificate"
echo "  - Configure nginx for HTTPS"
echo "  - Set up HTTP → HTTPS redirect"
echo ""

certbot --nginx \
  -d yujix.com \
  -d www.yujix.com \
  --non-interactive \
  --agree-tos \
  --email admin@yujix.com \
  --redirect

echo ""
echo "✅ SSL certificate obtained and installed"

# Step 3: Verify installation
echo ""
echo "Step 3: Verifying HTTPS setup..."
sleep 2

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://yujix.com/api/ping || echo "000")
if [ "$HTTP_CODE" -eq 200 ]; then
    echo "✅ HTTPS is working! (HTTP $HTTP_CODE)"
else
    echo "⚠️  HTTPS check returned HTTP $HTTP_CODE"
    echo "Please verify manually: https://yujix.com"
fi

# Step 4: Test automatic renewal
echo ""
echo "Step 4: Testing automatic renewal..."
certbot renew --dry-run

echo ""
echo "=========================================="
echo "✅ SSL/HTTPS Setup Complete!"
echo "=========================================="
echo ""
echo "Your website is now accessible via HTTPS:"
echo "  - https://yujix.com"
echo "  - https://www.yujix.com"
echo ""
echo "HTTP requests automatically redirect to HTTPS"
echo ""
echo "Certificate details:"
certbot certificates
echo ""
echo "Next steps:"
echo "1. Test HTTPS access: https://yujix.com"
echo "2. Update GitLab CI/CD health check to use HTTPS"
echo "3. Update .env APP_URL to https://yujix.com"
echo ""
echo "Auto-renewal: Certificates renew automatically every 90 days"
echo "Check renewal timer: systemctl status certbot.timer"
echo ""
