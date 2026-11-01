#!/bin/bash

# Load configurations
if [ -f .deploy.conf ]; then
    source .deploy.conf
else
    echo "Error: .deploy.conf not found! Silakan copy dari .deploy.conf.example dan isi data server Anda."
    exit 1
fi

echo "🚀 Starting Deployment to $DEPLOY_HOST..."

# ===== Kirim commit terbaru ke GitHub (branch main) dulu =====
# Server hanya mengambil branch "main", jadi commit lokal harus sudah ada di main.
if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
    echo "⚠️  Ada perubahan yang BELUM di-commit. Perubahan itu TIDAK ikut ke server:"
    git status --short --untracked-files=no
    read -r -p "Lanjut deploy tanpa perubahan itu? (y/n) " jawab
    [ "$jawab" = "y" ] || { echo "Deploy dibatalkan. Commit dulu, lalu jalankan deploy lagi."; exit 1; }
fi

echo "📤 Mengirim commit ke GitHub (main)..."
if ! git push origin HEAD:main; then
    echo "❌ Gagal push ke main. Biasanya karena ada commit baru di GitHub yang belum Anda ambil."
    echo "   Jalankan: git pull origin main   lalu deploy lagi."
    exit 1
fi

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
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker compose up -d --build --renew-anon-volumes && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker compose restart web && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S chmod -R 777 storage bootstrap/cache && \
    echo '⏳ Waiting for system to stabilize...' && \
    sleep 8 && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app npm install --ignore-scripts && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app npm run build && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan migrate --force --seed && \
    (echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan storage:link || true) && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan config:clear && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan cache:clear && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan view:clear"

echo "✅ Deployment Finished & System Refreshed! Aplikasi dapat diakses di https://daurohqobul.com (atau http://$DEPLOY_HOST:8080)"
