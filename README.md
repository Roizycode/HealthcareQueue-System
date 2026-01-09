# HealthQueue - Healthcare Queue Management System

A modern healthcare queue management system built with Laravel 12. Allows patients to join queues online, track their position in real-time, and receive SMS notifications when it's their turn.

## Features

- 🏥 **Multiple Services**: Consultation, Laboratory, Pharmacy, Radiology
- 🔢 **Priority Queue**: Emergency, Senior Citizen, PWD, Regular priorities
- 📱 **Virtual Queue**: Join queues online from anywhere
- 📲 **SMS Notifications**: Get notified via Twilio when it's your turn
- 📊 **Real-time Dashboard**: Live queue display and management
- 👥 **Role-based Access**: Admin, Staff, and Patient roles
- 📈 **Analytics**: Queue statistics and performance metrics

## Requirements

- PHP 8.2+
- Composer
- MySQL / PostgreSQL / SQLite
- Node.js & NPM (for frontend assets)
- Redis (optional, for queue processing)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd HCS
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database

Edit `.env` file and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=healthqueue
DB_USERNAME=root
DB_PASSWORD=
```

Or use SQLite (default):

```env
DB_CONNECTION=sqlite
```

### 5. Run Migrations & Seed Database

```bash
php artisan migrate:fresh --seed
```

### 6. Start the Application

```bash
php artisan serve
```

Visit: http://127.0.0.1:8000

## Demo Credentials

| Role  | Email                      | Password |
|-------|----------------------------|----------|
| Admin | admin@healthqueue.com      | password |
| Staff | staff@healthqueue.com      | password |

## SMS Configuration (Twilio)

To enable SMS notifications, configure Twilio in your `.env`:

```env
TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_PHONE_NUMBER=+1234567890
TWILIO_ENABLED=true
```

## Queue Worker

For background job processing (SMS notifications):

```bash
php artisan queue:work
```

## Scheduler

For automatic queue escalation checks:

```bash
php artisan schedule:run
```

Or add to crontab:
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AdminDashboardController.php
│   │   ├── StaffDashboardController.php
│   │   ├── QueueController.php
│   │   ├── PatientController.php
│   │   └── HomeController.php
│   └── Middleware/
│       └── CheckRole.php
├── Models/
│   ├── User.php
│   ├── Service.php
│   ├── Priority.php
│   ├── Patient.php
│   ├── Queue.php
│   ├── Counter.php
│   └── NotificationLog.php
├── Services/
│   ├── QueueService.php
│   └── SmsService.php
└── Jobs/
    ├── NotifyPatientJob.php
    └── CheckEscalationsJob.php
```

## Key Routes

| Route | Description |
|-------|-------------|
| `/` | Landing page |
| `/join-queue` | Virtual queue registration |
| `/check-status` | Check queue status |
| `/display` | Live queue display screen |
| `/admin` | Admin dashboard |
| `/staff` | Staff dashboard |
| `/login` | Authentication |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/services` | List services |
| GET | `/api/v1/queue/{queue}/status` | Get queue status |
| POST | `/api/v1/queue/join` | Join queue |
| POST | `/staff/queue/service/{id}/call-next` | Call next patient |
| POST | `/staff/queue/{id}/complete` | Complete queue |

## License

MIT License

## Support

For issues and questions, please open a GitHub issue.
