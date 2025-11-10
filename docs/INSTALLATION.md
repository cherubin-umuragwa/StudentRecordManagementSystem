# Installation Guide

This guide will help you set up the Student Grade Management System on your local or production server.

## Prerequisites

Before you begin, ensure you have:

- **PHP 7.4 or higher** with PDO, PDO_MySQL, mbstring, fileinfo extensions
- **MySQL 5.7 or higher** (or MariaDB 10.2+)
- **Apache** or **Nginx** web server

## Quick Start

### 1. Configure Environment
```bash
cp .env.example .env
# Edit .env with your database credentials
```

### 2. Create Database
```bash
mysql -u root -p -e "CREATE DATABASE student_grade_management"
mysql -u root -p student_grade_management < database/schema.sql
mysql -u root -p student_grade_management < database/seed.sql
```

### 3. Set Permissions
```bash
chmod 755 uploads/
chmod 644 .env
```

### 4. Access System
Navigate to: `http://localhost/student-grade-management/`

**Default Login:**
- Admin: `admin` / `password123`
- Registrar: `registrar` / `password123`

⚠️ Change passwords after first login!

## Detailed Installation

See README.md for complete installation instructions.
