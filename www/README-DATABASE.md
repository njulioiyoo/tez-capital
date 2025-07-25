# Database Configuration Guide

## Overview
Aplikasi ini menggunakan PostgreSQL dengan setup Master-Slave untuk optimasi performa read/write operations.

## Database Setup

### Docker Containers
- **postgres_master**: Master database (Write operations) - Port 5434
- **postgres_slave**: Slave database (Read operations) - Port 5435

### Environment Configurations

#### 1. Development/CLI (.env)
Untuk development dan CLI commands dari host system:
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5434
DB_DATABASE=tez-capital
DB_USERNAME=postgres
DB_PASSWORD=password123

# Master/Slave disabled untuk CLI compatibility
```

#### 2. Docker Container (.env.docker)
Untuk aplikasi web yang berjalan dalam Docker container:
```env
DB_CONNECTION=pgsql
DB_HOST=postgres_master
DB_PORT=5432
DB_DATABASE=tez-capital
DB_USERNAME=postgres
DB_PASSWORD=password123

# Master Database (Write)
DB_WRITE_HOST=postgres_master
DB_WRITE_PORT=5432

# Slave Database (Read)  
DB_READ_HOST=postgres_slave
DB_READ_PORT=5432
```

## Usage

### For Development
```bash
# Menggunakan .env (localhost dengan port mapping)
php artisan migrate
php artisan tinker
```

### For Docker Deployment
```bash
# Copy environment Docker
cp .env.docker .env

# Atau set environment variables di Docker Compose
docker-compose up -d
```

## Features

### Laravel Auditing
Sistem audit logging telah diimplementasi untuk tracking semua perubahan data:

- **Models with Auditing**: MenuItem, User
- **Audit Log Page**: `/audit-log`
- **Features**: 
  - Statistics dashboard
  - Advanced filtering
  - Real-time activity tracking
  - User attribution
  - IP address logging

### Master-Slave Benefits
- **Write Operations**: Diarahkan ke postgres_master
- **Read Operations**: Diarahkan ke postgres_slave  
- **Load Balancing**: Distribusi beban antara read/write
- **High Availability**: Redundancy untuk data protection

## Troubleshooting

### Connection Issues
1. Pastikan Docker containers berjalan:
   ```bash
   docker ps | grep postgres
   ```

2. Test koneksi ke database:
   ```bash
   nc -zv localhost 5434  # Master
   nc -zv localhost 5435  # Slave
   ```

3. Untuk CLI development, gunakan .env dengan localhost
4. Untuk Docker deployment, gunakan .env.docker dengan hostnames

### Error Resolution
- **"could not translate host name"**: Gunakan konfigurasi environment yang tepat
- **Connection refused**: Periksa status Docker containers
- **SQLSTATE[08006]**: Validasi kredensial database dan port