# 🏥 Smart Healthcare Queue System

![License](https://img.shields.io/badge/license-MIT-blue.svg) ![Laravel](https://img.shields.io/badge/Laravel-12.0-red) ![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple)

A next-generation queue management system designed for modern healthcare facilities. **Smart Healthcare Queue System (HCS)** streamliness patient flow, reduces waiting times, and enhances operational efficiency through real-time tracking, SMS notifications, and intelligent priority handling.

---

## 🚀 Key Features

### ⭐ Core Modules
- **Dynamic Service Management**: Supports Consultation, Laboratory, Pharmacy, Radiology, and custom services.
- **Intelligent Priority Engine**: Automated prioritization for Emergency (EMG), Senior Citizens (SRC), Persons with Disability (PWD), and Regular (REG) patients.
- **Virtual Queueing**: Contactless, web-based check-in allowing patients to join the queue remotely.
- **Smart Notifications**: Real-time updates via SMS (Twilio Integration) and Email when a patient's turn is approaching.

### 💻 Specialized Interfaces
- **Admin Dashboard**: centralized control for services, counters, staff management, and analytics.
- **Staff Dashboard**: Efficient patient calling, servicing, and status management.
- **Live Display Board (TV Mode)**: Real-time, voice-announced queue updates designed for waiting area screens.
- **Patient Portal**: Mobile-responsive status checker and digital ticketing.

### 📈 Analytics & Reporting
- **Real-time Metrics**: Live monitoring of waiting times, service speeds, and queue lengths.
- **PDF Reports**: Generate Daily, Weekly, and Monthly performance reports.
- **Audit Logs**: Comprehensive activity tracking for security and accountability.

---

## 🛠️ System Requirements

- **PHP**: ^8.2
- **Database**: MySQL 8.0+ or PostgreSQL 13+ (SQLite supported for dev)
- **Web Server**: Nginx or Apache
- **Composer**: ^2.0
- **Node.js**: ^18.0 & NPM

---

## 📦 Installation Guide

### 1. Clone & Setup
```bash
git clone https://github.com/Roizycode/HealthcareQueue-System.git
cd HealthcareQueue-System

# Install PHP dependencies
composer install

# Install Frontend dependencies
npm install && npm run build
```

### 2. Configuration
Copy the environment file and configure your database and Twilio credentials.
```bash
cp .env.example .env
php artisan key:generate
```

**Required .env configurations:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=healthqueue

# SMS Notifications (Optional)
TWILIO_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_FROM=your_number
```

### 3. Database Initialization
```bash
php artisan migrate:fresh --seed
```

### 4. Application Launch
```bash
# Start Development Server
php artisan serve

# In a separate terminal, run Queue Worker for SMS/Email jobs
php artisan queue:work
```

Access the application at: `http://127.0.0.1:8000`

---

## 🔐 Default Credentials

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| **Administrator** | `admin@smarthealthcare.com` | `password` | Full System Access |
| **Staff** | `staff@smarthealthcare.com` | `password` | Queue & Patient Management |

> **Note:** For security, please change these credentials immediately after deployment.

---

## 📂 Project Architecture

```
app/
├── Http/Controllers/       # Request handling logic
├── Services/              # Business logic isolation (QueueService, SmsService)
├── Models/                # Eloquent ORM definitions
├── Events/ & Listeners/   # Real-time event broadcasting
└── Notifications/         # Multi-channel notification classes (SMS, Email, Database)

resources/
├── views/
│   ├── admin/             # Administration views
│   ├── staff/             # Staff operational views
│   ├── patient/           # Public facing views
│   ├── display/           # Public TV display views
│   └── layouts/           # Shared blade templates
```

---

## 🔄 Workflow

1.  **Patient Check-in**: Patient registers via Reception, Kiosk, or Mobile Web.
2.  **Assignment**: Smart algorithm assigns Queue Number (e.g., `CON-001`) based on Service & Priority.
3.  **Waiting**: Patient waits; status visible on TV Display and Mobile status page.
4.  **Notification**: System sends SMS when patient is "Next" or "Called".
5.  **Service**: Staff calls patient -> status updates to "Serving".
6.  **Completion**: Service ends -> status updates to "Completed".
7.  **Reporting**: Data logged for daily performance reports.

---

## 📄 License

This software is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

© 2026 **Smart Healthcare Systems**. All rights reserved.
