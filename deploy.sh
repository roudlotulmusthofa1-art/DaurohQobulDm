#!/bin/bash

# Load configurations
if [ -f .deploy.conf ]; then
    source .deploy.conf
else
    echo "Error: .deploy.conf not found! Silakan copy dari .deploy.conf.example dan isi data server Anda."
    exit 1
fi

echo "🚀 Starting Deployment to $DEPLOY_HOST..."

# Prevent Git Bash (Windows) from translating Linux paths like /var/www/... to C:/...
export MSYS_NO_PATHCONV=1

# Check for sshpass (Windows fallback)
if command -v sshpass &> /dev/null; then
    SSH_CMD="sshpass -p $DEPLOY_SUDO_PASS ssh -o StrictHostKeyChecking=no"
    RSYNC_CMD="sshpass -p $DEPLOY_SUDO_PASS rsync"
else
    echo "⚠️ 'sshpass' tidak ditemukan (Mode Windows). Anda akan diminta mengetik password server manual."
    SSH_CMD="ssh -o StrictHostKeyChecking=no"
    RSYNC_CMD="rsync"
fi

# Ensure target directory exists on server
$SSH_CMD $DEPLOY_USER@$DEPLOY_HOST "echo '$DEPLOY_SUDO_PASS' | sudo -S mkdir -p $DEPLOY_PATH && echo '$DEPLOY_SUDO_PASS' | sudo -S chown -R $DEPLOY_USER:$DEPLOY_USER $DEPLOY_PATH"

# Sync files to server
# --exclude: don't send these files
$RSYNC_CMD -avz --progress \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='.deploy.conf' \
    --exclude='storage' \
    --exclude='docker/db/data' \
    -e "ssh -o StrictHostKeyChecking=no" \
    ./ $DEPLOY_USER@$DEPLOY_HOST:$DEPLOY_PATH

echo "🐳 Restarting Docker Containers on Server..."

# Execute docker command on server
$SSH_CMD $DEPLOY_USER@$DEPLOY_HOST "cd $DEPLOY_PATH && \
    if [ -f .env.production ]; then cp .env.production .env; fi && \
    mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs storage/app/public && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker compose up -d --build && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S chmod -R 777 storage bootstrap/cache && \
    echo '⏳ Waiting for system to stabilize...' && \
    sleep 8 && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app npm install --ignore-scripts && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app npm run build && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan migrate --force && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan config:clear && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan cache:clear && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan view:clear"

echo "✅ Deployment Finished & System Refreshed! Aplikasi dapat diakses di http://$DEPLOY_HOST:8080"
