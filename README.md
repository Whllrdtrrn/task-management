# Task Management System

A modern, full-stack task management application built with Laravel (backend) and Vue.js (frontend), featuring real-time updates, drag-and-drop functionality, and comprehensive admin controls.

## Features

- **Authentication**: Secure user registration/login with Laravel Sanctum
- **Task Management**: Full CRUD operations with priority levels and status tracking
- **Drag & Drop**: Intuitive task reordering with real-time persistence
- **Real-time Updates**: Live synchronization using WebSockets (Laravel Echo + Pusher)
- **Admin Dashboard**: User management, statistics, and system overview
- **Responsive Design**: Mobile-first approach with TailwindCSS
- **Advanced Filtering**: Search, filter by status, priority, and more
- **Analytics**: Task statistics and user performance metrics
- **Soft Deletes**: Safe task deletion with recovery options
- **Security**: XSS/CSRF protection, input sanitization, proper access controls

## Tech Stack

### Backend
- **Laravel 10.x** - PHP framework
- **MySQL** - Database
- **Laravel Sanctum** - API authentication
- **Laravel Echo** - Real-time broadcasting
- **Pusher** - WebSocket service
- **PHPUnit** - Testing framework

### Frontend
- **Vue 3** - JavaScript framework (Composition API)
- **Pinia** - State management
- **Vue Router** - Client-side routing
- **TailwindCSS** - Utility-first CSS framework
- **SortableJS** - Drag and drop functionality
- **Axios** - HTTP client
- **Vite** - Build tool

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.1+** with extensions: mysql, mbstring, xml, curl, zip, gd
- **Composer** - PHP dependency manager
- **Node.js 16+** and **npm** - JavaScript runtime and package manager
- **MySQL 8.0+** - Database server
- **Git** - Version control

## Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/task-management-system.git
cd task-management-system
```

### 2. Backend Setup (Laravel)

```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your .env file
nano .env
```

#### Environment Configuration (.env)

```env
APP_NAME="Task Management System"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password

# Broadcasting Configuration
BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000,localhost:5173,127.0.0.1:5173

# Pusher Configuration (for real-time features)
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@taskmanager.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE task_management;
EXIT;

# Install Sanctum
composer require laravel/sanctum

# Publish Sanctum configuration
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Start the development server
php artisan serve
```

Your Laravel backend will be available at: `http://localhost:8000`

### 3. Frontend Setup (Vue.js)

```bash
# Navigate to frontend directory (from project root)
cd frontend

# Install dependencies
npm install

# Configure environment variables
cp .env.example .env
```

#### Frontend Environment Configuration (.env)

```env
VITE_API_URL=http://localhost:8000/api
VITE_PUSHER_APP_KEY=your_pusher_app_key
VITE_PUSHER_APP_CLUSTER=mt1
```

#### Start the frontend development server

```bash
npm run dev
```

Your Vue.js frontend will be available at: `http://localhost:5173`

## Usage

### Default Login Credentials

After running the seeders, you can use these default accounts:

**Admin User:**
- Email: `admin@example.com`
- Password: `password`

**Regular User:**
- Email: `user@example.com`
- Password: `password`

### Creating Your Own Account

1. Visit `http://localhost:5173/register`
2. Fill in your details
3. Login and start managing tasks!

## Testing

### Backend Tests

```bash
cd backend

# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/TaskControllerTest.php
```

### Test Database Setup

For testing, create a separate database:

```bash
# Create test database
mysql -u root -p
CREATE DATABASE task_management_test;
EXIT;

# Configure .env.testing
cp .env .env.testing
```

Update `.env.testing`:
```env
DB_DATABASE=task_management_test
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {your-token-here}
```

### API Endpoints

#### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/register` | User registration | No |
| POST | `/login` | User login | No |
| POST | `/logout` | User logout | Yes |
| GET | `/user` | Get current user | Yes |

#### Task Management Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/tasks` | Get user tasks | Yes |
| POST | `/tasks` | Create new task | Yes |
| GET | `/tasks/{id}` | Get specific task | Yes |
| PUT | `/tasks/{id}` | Update task | Yes |
| DELETE | `/tasks/{id}` | Delete task (soft delete) | Yes |
| POST | `/tasks/reorder` | Reorder tasks | Yes |

#### Admin Endpoints

| Method | Endpoint | Description | Auth Required | Admin Only |
|--------|----------|-------------|---------------|------------|
| GET | `/admin/dashboard` | Admin dashboard | Yes | Yes |
| GET | `/admin/users/{id}/tasks` | Get user's tasks | Yes | Yes |
| GET | `/admin/tasks` | Get all tasks | Yes | Yes |

### Request/Response Examples

#### User Registration
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "is_admin": false,
    "created_at": "2024-01-01T00:00:00.000000Z"
  },
  "token": "1|randomtokenstring..."
}
```

#### Create Task
```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer your-token-here" \
  -d '{
    "title": "Complete project documentation",
    "description": "Write comprehensive README and API docs",
    "priority": "high",
    "status": "pending"
  }'
```

#### Get Tasks with Filters
```bash
curl -X GET "http://localhost:8000/api/tasks?status=pending&priority=high&search=project" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

## Development

### Backend Development Commands

```bash
# Start Laravel development server
php artisan serve

# Start queue worker (for real-time features)
php artisan queue:work

# Watch logs
tail -f storage/logs/laravel.log

# Clear caches during development
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Create new migration
php artisan make:migration create_example_table

# Create model with migration
php artisan make:model Example -m

# Rollback migrations
php artisan migrate:rollback

# Reset database
php artisan migrate:fresh --seed
```

### Frontend Development Commands

```bash
# Start development server with hot reload
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint

# Type checking (if using TypeScript)
npm run type-check
```

### Development Workflow

1. **Start Backend Services:**
   ```bash
   # Terminal 1 - Start Laravel server
   cd backend && php artisan serve
   
   # Terminal 2 - Start queue worker (optional)
   cd backend && php artisan queue:work
   ```

2. **Start Frontend:**
   ```bash
   # Terminal 3 - Start Vue.js dev server
   cd frontend && npm run dev
   ```

3. **Development URLs:**
   - Frontend: `http://localhost:5173`
   - Backend API: `http://localhost:8000/api`
   - Laravel Admin: `http://localhost:8000`

## Security Features

- **Input Sanitization**: All user inputs are sanitized and validated
- **CSRF Protection**: Laravel's built-in CSRF protection
- **XSS Prevention**: Template escaping and content security policies
- **SQL Injection Prevention**: Eloquent ORM with parameter binding
- **Access Control**: Policy-based authorization
- **Rate Limiting**: API rate limiting to prevent abuse
- **Password Hashing**: Secure bcrypt password hashing
- **Token Authentication**: Secure API authentication with Laravel Sanctum
- **Soft Deletes**: Safe data deletion with recovery options

## Performance Features

- **Caching**: Query result caching for improved performance
- **Database Indexing**: Optimized database indexes
- **Lazy Loading**: Efficient data loading strategies
- **Asset Optimization**: Minified CSS and JavaScript with Vite
- **Real-time Updates**: Efficient WebSocket connections

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards for PHP
- Use ESLint configuration for JavaScript
- Write tests for new features
- Update documentation for API changes
- Follow conventional commit messages

## Troubleshooting

### Common Issues

1. **Database Connection Error:**
   ```bash
   # Check your .env database settings
   # Ensure MySQL is running
   sudo service mysql start
   ```

2. **Node Modules Issues:**
   ```bash
   # Clear npm cache and reinstall
   cd frontend
   rm -rf node_modules package-lock.json
   npm install
   ```

3. **Laravel Cache Issues:**
   ```bash
   # Clear all Laravel caches
   cd backend
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   php artisan view:clear
   ```

4. **Real-time Features Not Working:**
   - Check Pusher credentials in `.env`
   - Verify WebSocket connection in browser console
   - Ensure queue worker is running

5. **CORS Issues:**
   ```bash
   # Check config/cors.php settings
   # Ensure frontend URL is in SANCTUM_STATEFUL_DOMAINS
   ```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions:

- **Issues**: [GitHub Issues](https://github.com/your-username/task-management-system/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-username/task-management-system/discussions)
- **Documentation**: [Project Wiki](https://github.com/your-username/task-management-system/wiki)

## Project Status

- Backend API: Complete with authentication, CRUD operations, real-time features
- Frontend: Complete Vue.js application with modern UI/UX
- Authentication: Secure user management with Laravel Sanctum
- Real-time Features: WebSocket integration with Pusher
- Task Management: Full CRUD with drag-and-drop reordering
- Admin Panel: User management and system statistics
- Responsive Design: Mobile-friendly interface
- Testing: Comprehensive test coverage
- Documentation: Complete setup and API documentation

## Acknowledgments

- [Laravel](https://laravel.com/) - The PHP framework
- [Vue.js](https://vuejs.org/) - The progressive JavaScript framework
- [TailwindCSS](https://tailwindcss.com/) - Utility-first CSS framework
- [Pusher](https://pusher.com/) - Real-time WebSocket service
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API authentication
- [SortableJS](https://sortablejs.github.io/Sortable/) - Drag and drop functionality

---

**Happy Task Managing!**