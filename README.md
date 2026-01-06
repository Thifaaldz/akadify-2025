# Akadify - Automated Certificate Verification System

<p align="center">
  <img src="aritektur/arsitektur_dev.png" alt="System Architecture" width="800"/>
</p>

<p align="center">
  <img src="aritektur/acitivitydiagram.png" alt="Activity Diagram" width="800"/>
</p>

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Technology Stack](#technology-stack)
4. [Database Schema](#database-schema)
5. [System Workflow](#system-workflow)
6. [Project Structure](#project-structure)
7. [Installation & Setup](#installation--setup)
8. [API Endpoints](#api-endpoints)
9. [Configuration](#configuration)
10. [Features](#features)
11. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

**Akadify** is an automated certificate (Ijazah) verification system designed to streamline the process of validating student certificates. The system uses OCR (Optical Character Recognition) technology to extract data from uploaded certificates and automatically compares it against the student database, sending real-time notifications via WhatsApp.

### Key Features:
- ✅ Automated OCR data extraction from ijazah documents
- ✅ Automatic verification by comparing OCR results with database records
- ✅ Real-time WhatsApp notifications for students
- ✅ Admin panel for monitoring and manual verification
- ✅ Workflow automation with n8n
- ✅ Support for multiple document formats (JPG, PNG, PDF)

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              USER LAYER                                      │
│  ┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐        │
│  │   Student       │     │   Admin         │     │   System        │        │
│  │   (Upload Form) │     │   (Filament)    │     │   (n8n, OCR)    │        │
│  └────────┬────────┘     └────────┬────────┘     └────────┬────────┘        │
└───────────┼──────────────────────┼──────────────────────┼───────────────────┘
            │                      │                      │
            ▼                      ▼                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         APPLICATION LAYER                                    │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                     Laravel + Livewire                                │   │
│  │   - UploadIjazah Livewire Component                                  │   │
│  │   - VerificationController                                           │   │
│  │   - Student, Verification, OcrResult Models                          │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
│                                    │                                         │
│                                    ▼                                         │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                         Nginx Web Server                              │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          EXTERNAL SERVICES                                   │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐             │
│  │   n8n           │  │   OCR Service   │  │   WAHA          │             │
│  │   (Workflow)    │  │   (FastAPI)     │  │   (WhatsApp)    │             │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘             │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           DATA LAYER                                         │
│  ┌─────────────────┐  ┌─────────────────┐                                  │
│  │   MariaDB       │  │   File Storage  │                                  │
│  │   (Database)    │  │   (Ijazah)      │                                  │
│  └─────────────────┘  └─────────────────┘                                  │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Technology Stack

### Backend & Framework
| Component | Technology | Purpose |
|-----------|------------|---------|
| **Main Framework** | Laravel 11 | PHP MVC framework |
| **Admin Panel** | Filament Admin | Admin interface |
| **Livewire** | Livewire | Dynamic form components |
| **OCR Service** | FastAPI + Python | Document text extraction |

### Infrastructure
| Component | Technology | Purpose |
|-----------|------------|---------|
| **Web Server** | Nginx | Reverse proxy & web server |
| **Database** | MariaDB 10.11 | Relational database |
| **Containerization** | Docker + Docker Compose | Container orchestration |

### Automation & Integration
| Component | Technology | Purpose |
|-----------|------------|---------|
| **Workflow Automation** | n8n | Process automation |
| **WhatsApp API** | WAHA (WhatsApp HTTP API) | Send WhatsApp notifications |
| **OCR Engine** | Tesseract OCR | Text recognition from images |

---

## 🗄️ Database Schema

### Students Table
```sql
CREATE TABLE students (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(255) NOT NULL,
    nisn        VARCHAR(255) NOT NULL UNIQUE,
    tahun_lulus VARCHAR(255) NOT NULL,
    sekolah     VARCHAR(255) NOT NULL,
    phone       VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
);
```

### Verifications Table
```sql
CREATE TABLE verifications (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id    BIGINT UNSIGNED NOT NULL,
    ijazah_path   VARCHAR(255) NOT NULL,
    status        ENUM('PENDING_OCR', 'PROCESSING', 'VERIFIED', 'REJECTED') 
                 DEFAULT 'PENDING_OCR',
    reason        JSON NULL,
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
```

### OcrResults Table
```sql
CREATE TABLE ocr_results (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    verification_id BIGINT UNSIGNED NOT NULL,
    raw_text      LONGTEXT NOT NULL,
    nisn          VARCHAR(255) NULL,
    nama          VARCHAR(255) NULL,
    tahun_lulus   VARCHAR(255) NULL,
    sekolah       VARCHAR(255) NULL,
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    
    FOREIGN KEY (verification_id) REFERENCES verifications(id) ON DELETE CASCADE
);
```

### Entity Relationship Diagram

```
┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│   Students  │ 1     1 │ Verifications│ 1     1 │  OcrResults │
│             │─────────│             │─────────│             │
│ - id        │         │ - id        │         │ - id        │
│ - nama      │         │ - student_id│         │ - verif_id  │
│ - nisn      │         │ - ijazah_path       │ - raw_text  │
│ - phone     │         │ - status    │         │ - nisn      │
│ - sekolah   │         │ - reason    │         │ - nama      │
└─────────────┘         └─────────────┘         │ - sekolah   │
          │                     │               └─────────────┘
          └─────────────────────┘
```

---

## 🔄 System Workflow

### 1. Upload Ijazah Process

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  Student │────▶│ Laravel  │────▶│  Store   │────▶│  Create  │
│  Uploads │     │ Livewire │     │   File   │     │  Record  │
└──────────┘     └──────────┘     └──────────┘     └──────────┘
                                                   │
                                                   ▼
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  Notify  │◀────│  Send    │◀────│ Trigger  │◀────│  N8N     │
│  Success │     │ WhatsApp │     │ Webhook  │     │ Receives │
└──────────┘     └──────────┘     └──────────┘     └──────────┘
```

### 2. OCR Processing Flow

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  N8N     │────▶│  Call    │────▶│ Extract  │────▶│  Parse   │
│  Starts  │     │  OCR API │     │   Text   │     │   Data   │
└──────────┘     └──────────┘     └──────────┘     └──────────┘
                                                      │
                                                      ▼
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  Update  │◀────│  Compare │◀────│  Match   │◀────│  Extract │
│  Status  │     │  Data    │     │  Fields  │     │   NISM   │
└──────────┘     └──────────┘     └──────────┘     │  Nama    │
                                                   │  Sekolah │
                                                   │  Tahun   │
                                                   └──────────┘
```

### 3. Verification Decision Flow

```
                    ┌─────────────────┐
                    │  OCR Data vs    │
                    │  DB Data Match? │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │                              │
              ▼                              ▼
       ┌─────────────┐                ┌─────────────┐
       │   VERIFIED  │                │  REJECTED   │
       │             │                │             │
       │ - Update    │                │ - Update    │
       │   Status    │                │   Status    │
       │ - Send WA   │                │ - Send WA   │
       │   Success   │                │   Failed    │
       └─────────────┘                └─────────────┘
```

### 4. Complete End-to-End Flow

```
  ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐
  │  Upload │───▶│  Store  │───▶│ Trigger │───▶│   N8N   │───▶│  Send   │
  │  Ijazah │    │  File   │    │ Webhook │    │ Workflow│    │   WA    │
  └─────────┘    └─────────┘    └─────────┘    └─────────┘    └────┬────┘
                                                                   │
                           ┌───────────────────────────────────────┘
                           │
                           ▼
  ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐
  │   Send  │◀───│  Final  │◀───│ Verify  │◀───│   OCR   │◀───│ Process │
  │   WA    │    │   WA    │    │  Data   │    │ Extract │    │  OCR    │
  │ Result  │    │ Success │    │  Match  │    │   Text  │    │         │
  └─────────┘    └─────────┘    └─────────┘    └─────────┘    └─────────┘
```

---

## 📁 Project Structure

```
akadify/
├── aritektur/                 # Architecture diagrams
│   ├── acitivitydiagram.png
│   └── arsitektur_dev.png
├── db/                        # Database configuration
│   ├── conf.d/
│   │   └── my.cnf
│   └── data/
├── n8n/                       # n8n workflow automation
│   └── data/
│       ├── config/
│       ├── database.sqlite
│       ├── workflows/
│       └── nodes/
├── nginx/                     # Nginx web server
│   ├── default.conf
│   ├── Dockerfile
│   └── ssl/
├── ocr/                       # OCR Service (FastAPI)
│   ├── app.py                 # Main OCR application
│   ├── Dockerfile
│   └── requirements.txt
├── php/                       # PHP/Laravel container
│   ├── Dockerfile
│   ├── docker-entrypoint.sh
│   └── local.ini
├── src/                       # Laravel Application
│   ├── app/
│   │   ├── Console/
│   │   │   └── Commands/      # CLI Commands
│   │   ├── Filament/
│   │   │   └── Admin/         # Admin resources
│   │   ├── Http/
│   │   │   └── Controllers/   # API Controllers
│   │   ├── Jobs/              # Queue jobs
│   │   ├── Livewire/          # Livewire components
│   │   │   └── UploadIjazah.php
│   │   ├── Models/            # Eloquent models
│   │   │   ├── Student.php
│   │   │   ├── Verification.php
│   │   │   └── OcrResult.php
│   │   └── Providers/         # Service providers
│   ├── bootstrap/             # Laravel bootstrap
│   ├── config/                # Configuration files
│   ├── database/
│   │   ├── migrations/        # Database migrations
│   │   └── seeders/           # Database seeders
│   ├── public/                # Public assets
│   ├── resources/             # Views and assets
│   ├── routes/                # Route definitions
│   ├── storage/               # Storage directory
│   │   └── app/
│   │       └── ijazah/        # Uploaded ijazah files
│   └── vite.config.js
├── waha/                      # WhatsApp HTTP API
│   ├── media/
│   └── sessions/
├── docker-compose.yml         # Docker Compose configuration
└── README.md                  # This file
```

---

## 🚀 Installation & Setup

### Prerequisites

- Docker & Docker Compose
- Git
- Minimum 4GB RAM
- Port availability: 80, 443, 3306, 5678, 8001, 3000

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd akadify
   ```

2. **Configure environment variables**
   ```bash
   cp src/.env.example src/.env
   # Edit src/.env with your configuration
   ```

3. **Set required environment variables**
   ```env
   PROJECT_NAME=akadify
   APP_NAME=Akadify
   APP_URL=https://akadify.test
   
   N8N_WEBHOOK_URL=http://n8n:5678/webhook/your-workflow-id
   ```

4. **Start the containers**
   ```bash
   docker-compose up -d
   ```

5. **Access services**

   | Service | URL | Credentials |
   |---------|-----|-------------|
   | **Application** | http://akadify.test | - |
   | **n8n Admin** | http://localhost:5678 | admin / admin123 |
   | **Database** | localhost:13306 | root / p455w0rd |

### Post-Installation

1. **Run Laravel migrations**
   ```bash
   docker-compose exec php php artisan migrate --seed
   ```

2. **Clear caches**
   ```bash
   docker-compose exec php php artisan optimize:clear
   ```

3. **Build frontend assets**
   ```bash
   cd src && npm install && npm run build
   ```

---

## 🔌 API Endpoints

### Verification API

#### Store Verification Result
```
POST /api/verification
Content-Type: application/json

Request Body:
{
    "verification_id": 1,           // Optional (if exists)
    "student_id": 1,                // Required if no verification_id
    "ijazah_path": "/path/to/file", // Required if no verification_id
    "valid": true,                  // Required (boolean)
    "status": "VERIFIED",           // Optional (string)
    "reason": {                     // Optional
        "error": "Data mismatch"
    }
}

Response:
{
    "message": "Verification saved successfully",
    "data": {
        "id": 1,
        "status": "VERIFIED",
        ...
    }
}
```

#### Update Verification Status
```
PUT /api/verification/{verification}
Content-Type: application/json

Request Body:
{
    "valid": false,
    "reason": {
        "error": "NISN tidak cocok"
    }
}

Response:
{
    "message": "Verification updated",
    "data": { ... }
}
```

### OCR Service API

#### Process Image
```
POST /ocr
Content-Type: application/json

{
    "phone": "6281234567890",
    "file_path": "/path/to/ijazah.jpg",
    "student_id": "1"
}

Response:
{
    "phone": "6281234567890",
    "file_path": "/path/to/ijazah.jpg",
    "student_id": "1",
    "nama_ocr": "nama siswa",
    "nisn_ocr": "1234567890",
    "sekolah_ocr": "sekolah menengah atas",
    "tahun_lulus_ocr": "2024",
    "raw_text": "..."
}
```

### Web Routes

| Route | Method | Description |
|-------|--------|-------------|
| `/upload-ijazah` | GET | Upload ijazah form (Livewire) |
| `/api/verification` | POST | Store verification result |
| `/api/verification/{id}` | PUT | Update verification status |

---

## ⚙️ Configuration

### Environment Variables

```env
# Application
PROJECT_NAME=akadify
APP_NAME=Akadify
APP_URL=https://akadify.test

# Database
DB_HOST=db
DB_DATABASE=akadify
DB_USERNAME=root
DB_PASSWORD=p455w0rd

# N8N Integration
N8N_WEBHOOK_URL=http://n8n:5678/webhook/your-workflow-id
N8N_BASIC_AUTH_USER=test
N8N_BASIC_AUTH_PASSWORD=test

# WAHA (WhatsApp API)
WAHA_URL=http://waha:3000

# Storage
FILESYSTEM_DISK=ijazah
```

### Docker Compose Services

| Service | Container Name | Port | Description |
|---------|---------------|------|-------------|
| php | akadify_php | - | PHP-FPM Laravel |
| nginx | akadify_nginx | 80, 443 | Web server |
| db | akadify_db | 13306 | MariaDB |
| n8n | akadify_n8n | 5678 | Workflow automation |
| ocr | akadify_ocr | 8001 | OCR service |
| waha | akadify_waha | 3000 | WhatsApp API |

### Networks

- **app_network**: Connects PHP, Nginx, MariaDB
- **workflow_network**: Connects n8n, OCR, WAHA

---

## ✨ Features

### For Students
- 📤 Upload ijazah documents
- 🔍 Search student by name/NISN
- 📱 Receive WhatsApp notifications
- ✅ Real-time verification status

### For Administrators
- 📊 Dashboard with verification statistics
- 📋 Manage student records
- 🔄 Manual OCR reprocessing
- ✅ Approve/reject verifications
- 📁 View uploaded documents

### System Features
- 🤖 Automated OCR data extraction
- 🔗 n8n workflow integration
- 📱 WhatsApp notifications via WAHA
- 🗃️ Complete audit trail
- 📈 JSON-based reason logging

---

## 📊 Verification Status Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  PENDING    │────▶│  PROCESSING │────▶│   VERIFIED  │     │             │
│    _OCR     │     │             │     │             │     │             │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
                           │
                           ▼
                    ┌─────────────┐
                    │  REJECTED   │
                    │             │
                    └─────────────┘
```

### Status Descriptions

| Status | Description |
|--------|-------------|
| `PENDING_OCR` | Uploaded, waiting for OCR processing |
| `PROCESSING` | OCR is currently running |
| `VERIFIED` | Data matched successfully |
| `REJECTED` | Data mismatch or OCR failed |

---

## 🔧 Troubleshooting

### Common Issues

#### 1. n8n Cannot Access Uploaded Files
**Problem**: Permission denied when n8n tries to read ijazah files

**Solution**:
```bash
# Set correct ownership
docker-compose exec php chown -R node:node /home/node/.n8n-files/ijazah
```

#### 2. OCR Service Returns 404
**Problem**: File not found at specified path

**Solution**: Ensure the file path matches the n8n-mounted path
```bash
# Inside OCR container
ls -la /data/ijazah/
```

#### 3. WhatsApp Not Sending Messages
**Problem**: WAHA session not initialized

**Solution**:
1. Access WAHA dashboard at http://localhost:3000
2. Create a new session with QR code authentication
3. Ensure session status is "WORKING"

#### 4. N8N Webhook Not Triggered
**Problem**: Webhook URL not configured

**Solution**: Set the N8N webhook URL in `.env`:
```env
N8N_WEBHOOK_URL=http://n8n:5678/webhook/your-workflow-id
```

### Log Locations

| Service | Log Location |
|---------|--------------|
| Laravel | `src/storage/logs/laravel.log` |
| n8n | `n8n/data/logs/` |
| OCR | Container stdout |
| WAHA | Container stdout |

### Useful Commands

```bash
# View Laravel logs
docker-compose exec php tail -f /var/www/html/storage/logs/laravel.log

# Restart all services
docker-compose restart

# View service logs
docker-compose logs -f php

# Run migrations
docker-compose exec php php artisan migrate

# Clear Laravel cache
docker-compose exec php php artisan optimize:clear
```

---

## 📈 Future Improvements

- [ ] Automated file permission handling
- [ ] Batch verification support
- [ ] Advanced OCR with deep learning models
- [ ] Multi-language certificate support
- [ ] SMS notification integration
- [ ] Email notification integration
- [ ] Analytics dashboard
- [ ] API rate limiting
- [ ] Unit and integration tests

---

## 📝 License

This project is proprietary software.

---

## 🤝 Support

For issues and feature requests, please contact the development team.

---

<p align="center">
  Built with ❤️ using Laravel, n8n, and OCR Technology
</p>

