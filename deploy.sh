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

# ===== Token WhatsApp (Fonnte) =====
# File .env di server tidak ikut git, jadi token Fonnte diambil dari .env laptop ini
# lalu ditulis ke .env.production di server setiap deploy (supaya chat otomatis jalan).
WA_TOKEN=$(grep -E '^WA_API_TOKEN=' .env 2>/dev/null | head -1 | cut -d= -f2- | tr -d '\r"'"'")
WA_URL=$(grep -E '^WA_API_URL=' .env 2>/dev/null | head -1 | cut -d= -f2- | tr -d '\r"'"'")
[ -z "$WA_URL" ] && WA_URL="https://api.fonnte.com/send"
if [ -z "$WA_TOKEN" ]; then
    echo "⚠️  WA_API_TOKEN di .env laptop kosong: chat WhatsApp otomatis di server tidak akan jalan."
fi

echo "📥 Menarik (Pull) kode terbaru dari GitHub ke Server..."

# Execute docker command on server
# package-lock.json di server bisa berubah sendiri karena "npm install" (langkah di bawah),
# jadi dikembalikan dulu ke versi GitHub supaya "git pull" tidak gagal.
if ! $SSH_CMD $DEPLOY_USER@$DEPLOY_HOST "cd $DEPLOY_PATH && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S git checkout -- package-lock.json && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S git pull origin main && \
    ENVF=.env; if [ -f .env.production ]; then ENVF=.env.production; fi && \
    if [ -n '$WA_TOKEN' ]; then echo '$DEPLOY_SUDO_PASS' | sudo -S sh -c \"sed -i '/^WA_API_URL=/d;/^WA_API_TOKEN=/d;/^[[:space:]]*$/d' \$ENVF && printf '\\nWA_API_URL=%s\\nWA_API_TOKEN=%s\\n' '$WA_URL' '$WA_TOKEN' >> \$ENVF\" && echo '✅ Token WhatsApp (Fonnte) dipasang di server'; fi && \
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
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan sesi:migrate && \
    (echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan storage:link || true) && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan config:clear && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan cache:clear && \
    echo '$DEPLOY_SUDO_PASS' | sudo -S docker exec daurohqobuldm-app php artisan view:clear"; then
    echo "❌ Deploy GAGAL di server. Baca pesan error di atas (kode di server belum diperbarui sepenuhnya)."
    exit 1
fi

echo "✅ Deployment Finished & System Refreshed! Aplikasi dapat diakses di https://daurohqobul.com (atau http://$DEPLOY_HOST:8080)"
