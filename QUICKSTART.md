# Quick Start Guide

Get up and running with the Student Record Management System in 5 minutes!

## Prerequisites

- PHP 7.4+ with PDO extension
- MySQL 5.7+ or MariaDB 10.2+
- Apache or Nginx web server

## Installation (5 Steps)

### 1. Clone & Configure
```bash
git clone https://github.com/yourusername/student-grade-management.git
cd student-grade-management
cp .env.example .env
```

Edit `.env` with your database credentials:
```env
DB_HOST=localhost
DB_NAME=student_record_management
DB_USER=root
DB_PASSWORD=your_password
```

### 2. Create Database
```bash
mysql -u root -p -e "CREATE DATABASE student_record_management"
mysql -u root -p student_record_management < database/schema.sql
mysql -u root -p student_record_management < database/seed.sql
```

### 3. Set Permissions
```bash
chmod 755 uploads/
chmod 644 .env
```

### 4. Access System
Open: `http://localhost/student-grade-management/`

### 5. Login
**Quick Access:**
- Admin: `admin` / `password123`
- Registrar: `registrar` / `password123`
- Lecturer: `lecturer1` / `password123`
- Accountant: `accountant` / `password123`
- Student: `student1` / `password123`

📋 **See [DEMO_CREDENTIALS.md](DEMO_CREDENTIALS.md) for all 22 demo accounts!**

⚠️ **All users have password: `password123` - Change after first login!**

## What's Next?

### For Administrators
1. Change default passwords
2. Create teacher accounts
3. Set up schools, departments, and programs
4. Configure system settings

### For Students
1. Visit registration page: `http://localhost/student-grade-management/register.php`
2. Fill out the registration form
3. Wait for registrar approval
4. Login and register for courses

### For Teachers
1. Login with teacher credentials
2. Create classrooms
3. Enroll students
4. Enter grades

### For Registrars
1. Review student registrations
2. Approve/reject applications
3. Approve course registrations
4. Manage academic records

## Common Tasks

### Add a New School
1. Login as admin
2. Navigate to academic settings
3. Add school, departments, and programs

### Approve Student Registration
1. Login as registrar
2. Go to "Pending Registrations"
3. Review application
4. Approve or reject

### Enter Grades
1. Login as teacher
2. Select classroom
3. Choose student and subject
4. Enter grade

## Troubleshooting

**Can't connect to database?**
- Check `.env` file credentials
- Verify MySQL is running
- Ensure database exists

**File upload not working?**
- Check `uploads/` folder permissions
- Verify PHP upload settings

**Getting logged out?**
- Check PHP session configuration
- Increase session timeout in `.env`

## Documentation

- [Full Installation Guide](docs/INSTALLATION.md)
- [Project Structure](docs/PROJECT_STRUCTURE.md)
- [Contributing Guidelines](CONTRIBUTING.md)
- [Changelog](CHANGELOG.md)

## Support

- GitHub Issues: [Report a bug](https://github.com/yourusername/student-grade-management/issues)
- Documentation: See `docs/` folder
- Email: support@yourschool.edu

## License

MIT License - see [LICENSE](LICENSE) file for details.

---

**Ready to contribute?** See [CONTRIBUTING.md](CONTRIBUTING.md)

**Need help?** Check [docs/INSTALLATION.md](docs/INSTALLATION.md) for detailed instructions.
