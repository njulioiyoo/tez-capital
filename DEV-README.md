# 🚀 Development Setup - Tez Capital

## Quick Start

### 🔥 Hot Reload Development (Recommended)
```bash
./dev-hot-reload.sh
```
- ✅ Real-time hot module replacement
- ✅ Instant browser reload on file changes
- ✅ Frontend: http://localhost:5173
- ✅ Backend: http://localhost:8983

### 📁 File Watcher (Auto Build)
```bash
./watch-and-build.sh
```
- ✅ Auto-build on file changes
- ✅ Production-ready assets
- ✅ Works with Docker container

### 🛑 Stop All Servers
```bash
./stop-dev.sh
```

## Manual Commands

### Build Assets
```bash
# Full rebuild (with npm install)
./rebuild-assets.sh

# Quick rebuild (no npm install)
./quick-rebuild.sh
```

## Development Workflow

### Option 1: Hot Reload (Fastest)
1. Run `./dev-hot-reload.sh`
2. Edit files in `www/resources/`
3. Browser auto-refreshes instantly
4. Press Ctrl+C to stop

### Option 2: File Watcher (Docker-friendly)
1. Run `./watch-and-build.sh`
2. Edit files in `www/resources/`
3. Assets auto-build and deploy
4. Refresh browser manually

## Troubleshooting

### ❌ Port 5173 already in use
```bash
./stop-dev.sh
./dev-hot-reload.sh
```

### ❌ npm run dev fails
```bash
cd www
rm -rf node_modules package-lock.json
npm install
```

### ❌ Blank page in browser
```bash
rm -f www/public/hot
./quick-rebuild.sh
```

## Architecture Support

Package.json includes dependencies for:
- ✅ macOS ARM64 (M1/M2 Mac)
- ✅ macOS x64 (Intel Mac)
- ✅ Linux ARM64 (Docker container)
- ✅ Linux x64 (Docker container)

## File Structure

```
├── dev-hot-reload.sh    # Hot reload server
├── watch-and-build.sh   # File watcher
├── rebuild-assets.sh    # Full rebuild
├── quick-rebuild.sh     # Quick rebuild
├── stop-dev.sh          # Stop all servers
└── www/
    ├── resources/
    │   ├── js/
    │   └── css/
    └── public/
        └── build/       # Built assets
```