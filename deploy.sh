#!/bin/bash

# Load configurations
if [ -f .deploy.conf ]; then
    source .deploy.conf
else
    echo "Error: .deploy.conf not found! Silakan copy dari .deploy.conf.example dan isi data server Anda."
    exit 1
fi

echo "🚀 Starting Deployment to $DEPLOY_HOST..."

# Ensure target directory exists on server
ssh $DEPLOY_USER@$DEPLOY_HOST "mkdir -p $DEPLOY_PATH"

# Sync files to server
# --exclude: don't send these files
rsync -avz --progress \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='.deploy.conf' \
    --exclude='storage' \
    --exclude='docker/db/data' \
    ./ $DEPLOY_USER@$DEPLOY_HOST:$DEPLOY_PATH

echo "🐳 Restarting Docker Containers on Server..."

# Execute docker command on server
ssh $DEPLOY_USER@$DEPLOY_HOST "cd $DEPLOY_PATH && \
    if [ -f .env.production ]; then cp .env.production .env; fi && \
    docker compose up -d --build && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S chmod -R 777 storage bootstrap/cache && \
    echo '⏳ Waiting for system to stabilize...' && \
    sleep 8 && \
    docker exec daurohqobuldm-app php artisan migrate --force && \
    docker exec daurohqobuldm-app php artisan config:clear && \
    docker exec daurohqobuldm-app php artisan cache:clear"

echo "✅ Deployment Finished & System Refreshed! Aplikasi dapat diakses di http://$DEPLOY_HOST:8080"
