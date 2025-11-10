# Project Structure

This document describes the organization of the Student Grade Management System.

## Directory Structure

```
student-grade-management/
│
├── api/                          # API endpoints
│   ├── get_departments.php       # Fetch departments by school
│   └── get_programs.php          # Fetch programs by department
│
├── config/                       # Configuration files
│   └── database.php              # Database connection with .env support
│
├── database/                     # Database files
│   ├── schema.sql                # Complete database schema
│   └── seed.sql                  # Sample data for testing
│
├── docs/                         # Documentation
│   ├── INSTALLATION.md           # Installation guide
│   └── PROJECT_STRUCTURE.md      # This file
│
├── includes/                     # Shared PHP includes
│   ├── config.php                # Application configuration
│   ├── conn.php                  # Legacy connection (redirects to config)
│   └── functions.php             # Helper functions
│
├── setup/                        # Installation scripts
│   ├── create_registrar.php      # Create registrar account
│   ├── install_complete_system.php  # Full system installer
│   ├── install_registration.php  # Registration system installer
│   ├── install_v2.php            # Enhanced installer
│   └── setup_required.php        # Setup requirements checker
│
├── uploads/                      # User uploaded files
│   ├── documents/                # Student documents
│   │   └── .gitkeep
│   ├── photos/                   # Profile photos
│   │   └── .gitkeep
│   └── .gitkeep
│
├── .env                          # Environment variables (not in git)
├── .env.example                  # Environment template
├── .gitignore                    # Git ignore rules
│
├── admin.php                     # Admin dashboard
├── classroom.php                 # Classroom management
├── grades.php                    # Grade management
├── index.php                     # Login page
├── logout.php                    # Logout handler
├── register.php                  # Student registration form
├── registrar.php                 # Registrar dashboard
├── registrar_course_approval.php # Course approval interface
├── registration_success.php      # Registration confirmation
├── student_course_registration.php # Student course registration
├── student_courses.php           # Student course view
├── students.php                  # Student dashboard
├── teacher.php                   # Teacher dashboard
│
├── CONTRIBUTING.md               # Contribution guidelines
├── LICENSE                       # MIT License
└── README.md                     # Main documentation

```

## File Descriptions

### Root Level PHP Files

| File | Purpose | Access |
|------|---------|--------|
| `index.php` | Login page and authentication | Public |
| `admin.php` | Admin dashboard and user management | Admin only |
| `teacher.php` | Teacher dashboard and grade management | Teacher only |
| `students.php` | Student dashboard and grade viewing | Student only |
| `registrar.php` | Registrar dashboard and approvals | Registrar only |
| `register.php` | Student self-registration form | Public |
| `registration_success.php` | Registration confirmation page | Public |
| `classroom.php` | Classroom creation and management | Teacher only |
| `grades.php` | Grade entry and management | Teacher only |
| `student_courses.php` | View registered courses | Student only |
| `student_course_registration.php` | Course registration interface | Student only |
| `registrar_course_approval.php` | Approve course registrations | Registrar only |
| `logout.php` | Session termination | All users |

### API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `api/get_departments.php` | GET | Fetch departments by school_id |
| `api/get_programs.php` | GET | Fetch programs by department_id |

### Configuration Files

| File | Purpose |
|------|---------|
| `config/database.php` | Database connection with environment variable support |
| `includes/config.php` | Application-wide configuration and constants |
| `includes/conn.php` | Legacy connection file (redirects to config.php) |
| `includes/functions.php` | Reusable helper functions |

### Database Files

| File | Purpose |
|------|---------|
| `database/schema.sql` | Complete database structure with all tables |
| `database/seed.sql` | Sample data for development/testing |

### Setup Scripts

| File | Purpose |
|------|---------|
| `setup/install_complete_system.php` | Full system installation wizard |
| `setup/create_registrar.php` | Create registrar account |
| `setup/install_registration.php` | Install registration system only |
| `setup/install_v2.php` | Enhanced installation script |
| `setup/setup_required.php` | Check system requirements |

## Database Tables

### Core Tables
- `users` - All system users (admin, teacher, student, registrar)
- `schools` - Academic schools
- `departments` - Departments within schools
- `programs` - Academic programs

### Registration Tables
- `registration_requests` - Pending student registrations

### Academic Tables
- `courses` - Course catalog
- `course_registrations` - Student course enrollments
- `course_schedule` - Class schedules
- `subjects` - Subject catalog
- `classrooms` - Teacher classrooms
- `classroom_students` - Classroom enrollments
- `grades` - Student grades

## Key Features by File

### Student Registration Flow
1. `register.php` - Student fills registration form
2. `registration_success.php` - Confirmation message
3. `registrar.php` - Registrar reviews and approves
4. Student account created in `users` table

### Course Registration Flow
1. `student_course_registration.php` - Student selects courses
2. `registrar_course_approval.php` - Registrar approves
3. `student_courses.php` - Student views enrolled courses

### Grade Management Flow
1. `classroom.php` - Teacher creates classroom
2. `classroom.php` - Teacher enrolls students
3. `grades.php` - Teacher enters grades
4. `students.php` - Student views grades

## Security Considerations

### Protected Files
- `.env` - Contains sensitive credentials (blocked by .gitignore)
- `config/database.php` - Database connection details
- `uploads/` - User uploaded files (validate on upload)

### Access Control
- Each dashboard file checks user role via `requireRole()` function
- Session-based authentication
- Password hashing using PHP's `password_hash()`
- Prepared statements for SQL queries

## Environment Variables

See `.env.example` for all available configuration options:
- Database credentials
- Application settings
- Upload limits
- Timezone configuration

## Adding New Features

### Adding a New Page
1. Create PHP file in root directory
2. Include `includes/config.php`
3. Add role check if needed
4. Follow existing code structure

### Adding a New API Endpoint
1. Create file in `api/` directory
2. Include `../includes/config.php`
3. Set JSON header
4. Return JSON response

### Adding a New Database Table
1. Add CREATE TABLE to `database/schema.sql`
2. Add sample data to `database/seed.sql` (optional)
3. Document in this file

## Maintenance

### Backup Strategy
- Regular database backups
- Backup `uploads/` directory
- Keep `.env` file secure

### Updates
- Pull latest code from repository
- Run any new migration scripts
- Clear caches if needed
- Test thoroughly before deploying

## Support

For questions about the project structure, see:
- [README.md](../README.md) - Main documentation
- [INSTALLATION.md](INSTALLATION.md) - Setup guide
- [CONTRIBUTING.md](../CONTRIBUTING.md) - Contribution guidelines
