#!/bin/bash
set -e

echo "🔻 Oprire + stergere volume (reimport DB)..."
docker compose down -v

echo "🔨 Build + pornire containere..."
docker compose up -d --build

echo "⏳ Astept MySQL..."
sleep 10

echo "✅ Gata."
echo "🌐 App: http://127.0.0.1:8010"
