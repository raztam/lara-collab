#!/usr/bin/env bash
set -e

# Check ngrok is installed
if ! command -v ngrok &> /dev/null; then
  echo "Error: ngrok is not installed. Download it from https://ngrok.com/download"
  exit 1
fi

ENV_FILE="$(dirname "$0")/.env"

# Start ngrok in background
ngrok http 80 &
NGROK_PID=$!

# Wait for ngrok API to be ready (up to 5s)
echo "Starting ngrok..."
for i in {1..10}; do
  sleep 0.5
  NGROK_URL=$(curl -s http://localhost:4040/api/tunnels \
    | grep -o '"public_url":"https://[^"]*"' \
    | head -1 \
    | cut -d'"' -f4)
  if [ -n "$NGROK_URL" ]; then
    break
  fi
done

if [ -z "$NGROK_URL" ]; then
  echo "Error: Could not get ngrok URL. Is ngrok running?"
  kill $NGROK_PID 2>/dev/null
  exit 1
fi

# Update APP_URL in .env
sed -i '' "s|^APP_URL=.*|APP_URL=$NGROK_URL|" "$ENV_FILE"

# Clear Laravel config cache inside the Sail container
docker exec lara-collab-laravel.test-1 php artisan config:clear 2>/dev/null \
  && echo "✓ Config cache cleared" \
  || echo "⚠ Could not clear config cache (is Sail running?)"

echo ""
echo "✓ ngrok tunnel: $NGROK_URL"
echo "✓ APP_URL updated in .env"
echo ""
echo "Press Ctrl+C to stop ngrok"

# Bring ngrok to foreground
wait $NGROK_PID
