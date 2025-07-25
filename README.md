# Tez Capital

A comprehensive Laravel application for capital management with modern Vue.js frontend, role-based access control, and comprehensive audit logging.

## Features

- **User Management**: Complete user lifecycle management with role-based permissions
- **Role & Permission System**: Granular access control using Spatie Laravel Permission
- **Audit Logging**: Track all user activities and system changes
- **Modern UI**: Built with Vue.js 3, TypeScript, and Tailwind CSS
- **Responsive Design**: Mobile-first responsive interface
- **Real-time Updates**: Dynamic content updates without page refreshes
- **Docker Support**: Containerized development and deployment

## Tech Stack

### Backend
- **Laravel 12**: Modern PHP framework
- **PHP 8.2+**: Latest PHP features and performance
- **PostgreSQL**: Robust relational database
- **Spatie Laravel Permission**: Role and permission management
- **Laravel Auditing**: Comprehensive activity logging

### Frontend
- **Vue.js 3**: Progressive JavaScript framework
- **TypeScript**: Type-safe JavaScript development
- **Inertia.js**: SPA-like experience without API complexity
- **Tailwind CSS**: Utility-first CSS framework
- **Vite**: Fast build tool and development server

### DevOps
- **Docker**: Containerized development environment
- **Docker Compose**: Multi-container orchestration
- **Supervisor**: Process management
- **Traefik**: Reverse proxy and load balancer

## Project Structure

```
tez-capital/
├── docker-compose.yml          # Docker services configuration
├── tez-capital.dockerfile      # Main application container
├── www/                        # Laravel application root
│   ├── app/                    # Laravel application logic
│   │   ├── Http/Controllers/   # API and web controllers
│   │   ├── Models/             # Eloquent models
│   │   └── Requests/           # Form request validation
│   ├── database/               # Migrations and seeders
│   ├── resources/              # Frontend assets
│   │   ├── js/                 # Vue.js application
│   │   │   ├── components/     # Reusable Vue components
│   │   │   ├── pages/          # Page components
│   │   │   └── layouts/        # Layout components
│   │   └── css/                # Stylesheets
│   └── routes/                 # Application routes
├── start.sh                    # Development startup script
├── stop-dev.sh                 # Development stop script
└── README.md                   # This file
```

## Quick Start

### Prerequisites

- Docker and Docker Compose
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd tez-capital
   ```

2. **Start the development environment**
   ```bash
   ./start.sh
   ```

3. **Access the application**
   - Web Interface: http://tez-capital.web.local:8983
   - Development Server: http://localhost:5173

### First Time Setup

1. **Run database migrations and seeders**
   ```bash
   docker-compose exec tez-capital php artisan migrate --seed
   ```

2. **Create default admin user** (if not created by seeder)
   ```bash
   docker-compose exec tez-capital php artisan tinker
   ```
   ```php
   $user = User::create([
       'name' => 'Admin',
       'email' => 'admin@example.com',
       'password' => Hash::make('password'),
       'status' => true
   ]);
   $user->assignRole('Super Admin');
   ```

## Development

### Available Scripts

- `./start.sh` - Start development environment
- `./stop-dev.sh` - Stop development environment
- `./quick-rebuild.sh` - Quick container rebuild
- `./rebuild-assets.sh` - Rebuild frontend assets
- `./dev-hot-reload.sh` - Start with hot reload

### Frontend Development

```bash
# Install dependencies
docker-compose exec tez-capital npm install

# Development with hot reload
docker-compose exec tez-capital npm run dev

# Build for production
docker-compose exec tez-capital npm run build

# Linting
docker-compose exec tez-capital npm run lint
```

### Backend Development

```bash
# Run migrations
docker-compose exec tez-capital php artisan migrate

# Run seeders
docker-compose exec tez-capital php artisan db:seed

# Clear caches
docker-compose exec tez-capital php artisan optimize:clear

# Run tests
docker-compose exec tez-capital php artisan test
```

## Configuration

### Environment Variables

Key environment variables in `www/.env`:

```env
APP_NAME="Tez Capital"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://tez-capital.web.local

DB_CONNECTION=pgsql
DB_HOST=postgres_slave
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Docker Configuration

The application uses external networks:
- `traefik-public`: For reverse proxy
- `postgres-network`: For database connectivity

Ensure these networks exist before starting:
```bash
docker network create traefik-public
docker network create pgsql_postgres-network
```

## Key Features

### User Management
- Create, read, update, delete users
- Bulk operations (activate, deactivate, delete, assign roles)
- User status management
- Last login tracking

### Role & Permission System
- Dynamic role creation and management
- Granular permission assignments
- Role-based route protection
- Permission-based UI component visibility

### Audit Logging
- Comprehensive activity tracking
- User action logging
- Data change history
- Searchable audit trails

### Modern UI Components
- Responsive data tables
- Modal dialogs for forms
- Interactive dashboards
- Real-time notifications

## API Endpoints

### Users
- `GET /api/system/users` - List users
- `POST /api/system/users` - Create user
- `PUT /api/system/users/{id}` - Update user
- `DELETE /api/system/users/{id}` - Delete user
- `POST /api/system/users/bulk-action` - Bulk operations

### Roles & Permissions
- `GET /api/system/roles` - List roles
- `POST /api/system/roles` - Create role
- `PUT /api/system/roles/{id}` - Update role
- `DELETE /api/system/roles/{id}` - Delete role

## Security

- CSRF protection on all forms
- XSS protection with Vue.js
- SQL injection prevention with Eloquent ORM
- Role-based access control
- Password hashing with bcrypt
- Session-based authentication

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and linting
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and questions, please contact the development team or create an issue in the repository.

## Changelog

### v1.0.0
- Initial release
- User management system
- Role and permission management
- Audit logging
- Modern Vue.js frontend
- Docker containerization