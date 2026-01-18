# Sudo Permissions Fixed

The deploy user now has passwordless sudo access for deployment operations:
- systemctl reload/restart php8.2-fpm
- systemctl reload/restart nginx  
- supervisorctl commands for yujix workers

This enables zero-downtime deployments via GitLab CI/CD.

Date: Sun Jan 11 12:54:01 IST 2026

