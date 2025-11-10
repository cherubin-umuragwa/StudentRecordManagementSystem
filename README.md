# Student Grade Management System

A comprehensive web-based student management system built with PHP and MySQL. This system handles student registration, course management, grade tracking, and academic administration.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net/)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B-orange)](https://www.mysql.com/)

## 🚀 Quick Start

New to this project? Check out the [Quick Start Guide](QUICKSTART.md) to get up and running in 5 minutes!

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Default Credentials](#default-credentials)
- [Project Structure](#project-structure)
- [Usage](#usage)
- [Database Schema](#database-schema)
- [Security Features](#security-features)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)
- [Changelog](#changelog)

## Features

### User Roles
- **Admin**: Full system access and user management
- **Registrar**: Student registration approval and course management
- **Teacher**: Grade management and classroom administration
- **Student**: View grades, register for courses, and manage profile

### Core Functionality
- **Student Registration**: Comprehensive 7-section registration form with document upload
- **Course Management**: Course creation, enrollment, and approval workflow
- **Grade Tracking**: Record and view student grades across subjects
- **Academic Structure**: Schools, departments, and programs hierarchy
- **Classroom Management**: Create and manage classrooms with student enrollment
- **Course Registration**: Students can register for courses with registrar approval

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PDO PHP Extension

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/student-grade-management.git
cd student-grade-management
```

### 2. Configure Environment
```bash
cp .env.example .env
```

Edit `.env` file with your database credentials:
```env
DB_HOST=localhost
DB_NAME=student_grade_management
DB_USER=root
DB_PASSWORD=your_password
```

### 3. Create Database
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

Or import via phpMyAdmin:
1. Create a database named `student_grade_management`
2. Import `database/schema.sql`
3. Import `database/seed.sql` (optional - includes sample data)

### 4. Set Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/documents/
chmod 755 uploads/photos/
```

### 5. Access the System
Open your browser and navigate to:
```
http://localhost/student-grade-management/
```

## Default Credentials

### Admin Account
- Username: `admin`
- Password: `password123`

### Registrar Account
- Username: `registrar`
- Password: `password123`

**Important**: Change these passwords after first login!

## Project Structure

```
student-grade-management/
├── api/                    # API endpoints
│   ├── get_departments.php
│   └── get_programs.php
├── config/                 # Configuration files
│   └── database.php
├── database/              # Database files
│   ├── schema.sql         # Complete database schema
│   └── seed.sql           # Sample data
├── includes/              # Shared PHP files
│   ├── config.php         # Application config
│   ├── conn.php           # Legacy connection (redirects)
│   └── functions.php      # Helper functions
├── uploads/               # User uploaded files
│   ├── documents/
│   └── photos/
├── admin.php              # Admin dashboard
├── teacher.php            # Teacher dashboard
├── students.php           # Student dashboard
├── registrar.php          # Registrar dashboard
├── register_v2.php        # Student registration form
├── index.php              # Login page
├── .env                   # Environment variables (not in git)
├── .env.example           # Environment template
└── README.md              # This file
```

## Usage

### For Students
1. Register using the registration form
2. Wait for registrar approval
3. Login with provided credentials
4. Register for courses
5. View grades and academic progress

### For Teachers
1. Login with teacher credentials
2. Create and manage classrooms
3. Enroll students in classrooms
4. Record and manage grades

### For Registrar
1. Review and approve student registrations
2. Approve course registrations
3. Manage academic programs and courses
4. Generate student numbers

### For Admin
1. Manage all users (create, edit, delete)
2. Configure system settings
3. Manage schools, departments, and programs
4. Full system oversight

## Database Schema

The system uses the following main tables:
- `users` - All system users (admin, teacher, student, registrar)
- `schools` - Academic schools
- `departments` - Departments within schools
- `programs` - Academic programs
- `courses` - Course catalog
- `course_registrations` - Student course enrollments
- `registration_requests` - Pending student registrations
- `classrooms` - Teacher classrooms
- `grades` - Student grades
- `subjects` - Subject catalog

## Security Features

- Password hashing using PHP's `password_hash()`
- PDO prepared statements to prevent SQL injection
- Session management for authentication
- Role-based access control
- File upload validation
- Environment-based configuration

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For issues, questions, or contributions, please open an issue on GitHub.

## Changelog

### Version 2.0
- Added comprehensive student registration system
- Implemented course registration workflow
- Added schools, departments, and programs structure
- Enhanced user profiles with detailed information
- Improved dashboard interfaces
- Added document upload functionality

### Version 1.0
- Initial release
- Basic grade management
- User authentication
- Classroom management
