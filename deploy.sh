#!/bin/bash
# Redeploy HRFlow on the VPS: pulls latest code, updates deps, rebuilds the
# frontend and restarts the service. Run this ON THE SERVER, from anywhere,
# e.g.: bash /home/ubuntu/hrflow/deploy.sh
set -e

cd "$(dirname "$0")"

echo "==> Pulling latest code..."
git pull origin main

echo "==> Updating backend dependencies..."
cd backend
.venv/bin/pip install --quiet -r requirements.txt
cd ..

echo "==> Building frontend..."
cd frontend
npm install --silent
npm run build
cd ..

echo "==> Restarting service..."
sudo systemctl restart hrflow.service
sleep 2
sudo systemctl status hrflow.service --no-pager | head -8

echo "==> Done."
