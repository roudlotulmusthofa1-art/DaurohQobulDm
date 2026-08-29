#!/bin/bash

# Load configurations
if [ -f .deploy.conf ]; then
    source .deploy.conf
else
    echo "Error: .deploy.conf not found! Silakan copy dari .deploy.conf.example dan isi data server Anda."
    exit 1
fi

echo "🚀 Starting Deployment to $DEPLOY_HOST..."

# Check for sshpass (Windows fallback)
if command -v sshpass &> /dev/null; then
    SSH_CMD="sshpass -p $DEPLOY_SUDO_PASS ssh -o StrictHostKeyChecking=no"
else
    echo "⚠️ 'sshpass' tidak ditemukan (Mode Windows). Anda akan diminta mengetik password server manual."
    SSH_CMD="ssh -o StrictHostKeyChecking=no"
fi

echo "📥 Menarik (Pull) kode terbaru dari GitHub ke Server..."

# Execute docker command on server
$SSH_CMD $DEPLOY_USER@$DEPLOY_HOST "cd $DEPLOY_PATH && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S git pull origin main && \
    if [ -f .env.production ]; then cp .env.production .env; fi && \
    mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs storage/app/public && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker compose up -d --build && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S chmod -R 777 storage bootstrap/cache && \
    echo '⏳ Waiting for system to stabilize...' && \
    sleep 8 && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app npm install --ignore-scripts && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app npm run build && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan migrate --force --seed && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan config:clear && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan cache:clear && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan view:clear"

echo "✅ Deployment Finished & System Refreshed! Aplikasi dapat diakses di http://$DEPLOY_HOST:8080"
