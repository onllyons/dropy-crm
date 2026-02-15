#!/bin/bash
set -e

echo "🔻 Oprire + stergere volume (reimport DB)..."
docker compose down -v

echo "🩹 Patch SQL dumps (MySQL compatibility)..."

SQL_DIR="./docker/mysql-init"

if [ -d "$SQL_DIR" ]; then
  find "$SQL_DIR" -type f -name "*.sql" | while read -r file; do
    echo "  → patch $file"

    # elimina DEFAULT zero-date
    sed -i '' "s/DEFAULT '0000-00-00 00:00:00'//g" "$file"

    # elimina DEFAULT CURRENT_TIMESTAMP pe DATETIME
    sed -i '' "s/DEFAULT CURRENT_TIMESTAMP//gI" "$file"

    # elimina ON UPDATE CURRENT_TIMESTAMP
    sed -i '' "s/ON UPDATE CURRENT_TIMESTAMP//gI" "$file"
  done
else
  echo "⚠️ $SQL_DIR nu exista, sar peste patch"
fi

echo "🔨 Build + pornire containere..."
docker compose up -d --build

echo "⏳ Astept MySQL..."
sleep 10

echo "✅ Gata."
echo "🌐 App: http://127.0.0.1:8055"
echo "🗄️ phpMyAdmin: http://127.0.0.1:8081"
