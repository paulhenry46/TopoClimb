# Deployment Guide

This document explains how to set up and use the automated deployment workflow for TopoClimb.

## Overview

The GitHub Actions workflow automatically deploys your application to a remote server whenever changes are pushed to the `main` branch.

## Prerequisites

Before you can use the automated deployment, you need:

1. A server with SSH access
2. PHP 8.2+, Composer, and Node.js installed on the server
3. Web server (Apache/Nginx) configured
4. Database set up and running
5. Git installed on the server (optional, but recommended)

## Setting Up GitHub Secrets

The deployment workflow requires four secrets to be configured in your GitHub repository:

### 1. SSH_PRIVATE_KEY

This is your SSH private key used to authenticate with the server.

**To generate a new SSH key pair:**

```bash
# On your local machine
ssh-keygen -t ed25519 -C "github-actions@topoclimb" -f ~/.ssh/topoclimb_deploy
```

**Add the public key to your server:**

```bash
# Copy the public key
cat ~/.ssh/topoclimb_deploy.pub

# On your server, add it to authorized_keys
echo "your-public-key-here" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

**Add the private key to GitHub:**
1. Copy the private key: `cat ~/.ssh/topoclimb_deploy`
2. In GitHub: Settings → Secrets and variables → Actions → New repository secret
3. Name: `SSH_PRIVATE_KEY`
4. Value: Paste the entire private key (including `-----BEGIN OPENSSH PRIVATE KEY-----` and `-----END OPENSSH PRIVATE KEY-----`)

### 2. SERVER_IP

The IP address or hostname of your server.

**Example:**
- `192.168.1.100`
- `server.example.com`

### 3. SSH_USER

The username used to connect to your server via SSH.

**Example:**
- `ubuntu`
- `www-data`
- `deploy`

### 4. PROJECT_PATH

The absolute path on your server where the application will be deployed.

**Example:**
- `/var/www/topoclimb`
- `/home/deploy/topoclimb`

## Server Preparation

On your deployment server, ensure:

1. The project directory exists and is writable:
```bash
sudo mkdir -p /var/www/topoclimb
sudo chown www-data:www-data /var/www/topoclimb
```

2. Required directories have proper permissions:
```bash
cd /var/www/topoclimb
mkdir -p storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

3. The `.env` file is configured (it won't be overwritten by deployment):
```bash
cp .env.example .env
# Edit .env with your production settings
```

## Workflow Behavior

When you push to the `main` branch, the workflow will:

1. **Build Phase:**
   - Check out the code
   - Install Composer dependencies (optimized for production)
   - Install NPM dependencies and build assets
   - Create a deployment package

2. **Validation Phase:**
   - Verify all required secrets are configured
   - If any secret is missing, the workflow fails with a clear error message

3. **Deployment Phase:**
   - Set up SSH connection to your server
   - Upload files via rsync (excluding .git, node_modules, tests, .env, storage)
   - Run Laravel artisan commands:
     - `php artisan migrate --force`
     - `php artisan config:cache`
     - `php artisan route:cache`
   - Set proper permissions on storage and cache directories

## Troubleshooting

### Workflow fails with "Error: [SECRET_NAME] secret is not set"

**Solution:** The secret is not configured in GitHub. Follow the instructions above to add it.

### SSH connection fails

**Possible causes:**
- Incorrect `SERVER_IP` (check it's reachable)
- Wrong `SSH_USER` 
- Private key not matching the public key on server
- Firewall blocking SSH port (22)

**Test SSH connection manually:**
```bash
ssh -i ~/.ssh/topoclimb_deploy [SSH_USER]@[SERVER_IP]
```

### Permissions errors during deployment

**Solution:** Ensure the SSH user has write permissions to `PROJECT_PATH` and can run the Laravel artisan commands.

### Missing dependencies on server

**Solution:** Install required PHP extensions and ensure Composer and NPM are available in the PATH for the SSH user.

## Security Best Practices

1. **Use a dedicated SSH key** for deployment (don't reuse your personal key)
2. **Limit SSH user permissions** - create a dedicated deployment user if possible
3. **Protect your secrets** - never commit them to the repository
4. **Use HTTPS** - configure SSL/TLS on your web server
5. **Backup before deploying** - consider adding a backup step to the workflow
6. **Monitor deployments** - check the Actions tab for deployment status

## Manual Deployment

If you prefer to deploy manually or need to troubleshoot:

```bash
# On your local machine
rsync -avz --exclude='.git' --exclude='node_modules' \
  --exclude='tests' --exclude='.env' --exclude='storage' \
  ./ [SSH_USER]@[SERVER_IP]:[PROJECT_PATH]

# On the server
cd [PROJECT_PATH]
composer install --no-interaction --prefer-dist --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

## Additional Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [SSH Key Management](https://docs.github.com/en/authentication/connecting-to-github-with-ssh)
