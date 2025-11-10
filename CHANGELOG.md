# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2025-11-10

### Major Reorganization
- Complete project restructuring for GitHub deployment
- Consolidated all database files into unified schema
- Implemented environment-based configuration
- Organized files into logical directories

### Added
- `.env` and `.env.example` for environment configuration
- `.gitignore` for proper version control
- `config/database.php` - Environment-aware database connection
- `api/` directory for API endpoints
- `database/` directory with consolidated schema and seeds
- `docs/` directory for documentation
- `setup/` directory for installation scripts
- `uploads/` directory structure with .gitkeep files
- `LICENSE` file (MIT License)
- `CONTRIBUTING.md` - Contribution guidelines
- `CHANGELOG.md` - This file
- Comprehensive `README.md`
- `docs/INSTALLATION.md` - Quick installation guide
- `docs/PROJECT_STRUCTURE.md` - Project organization documentation

### Changed
- Database connection now uses `.env` file for credentials
- API endpoints moved to `api/` directory
- Installation scripts moved to `setup/` directory
- Updated all file paths to reflect new structure
- Single registration file: `register.php`
- Improved security with environment-based configuration
- No version-specific files (clean, single versions only)

### Consolidated
- `database/schema.sql` - Single unified database schema
  - Merged: `student_record_management.sql`
  - Merged: `database_updates.sql`
  - Merged: `database_course_registration.sql`
- `database/seed.sql` - Sample data for testing

### Removed
- Duplicate database files
- All version-specific files (v2, v3, etc.)
- Redundant documentation files:
  - `COURSE_DISPLAY_FIX.md`
  - `COURSE_FILTERING_UPDATE.md`
  - `FIX_REGISTRAR_LOGIN.md`
  - `IMPLEMENTATION_SUMMARY.md`
  - `PROGRAM_ENROLLMENT_FIX.md`
  - `QUICK_REFERENCE.md`
  - `QUICK_START_GUIDE.md`
  - `REGISTRAR_DASHBOARD_UPDATE.md`
  - `REGISTRATION_FEATURE.md`
  - `START_HERE.md`
  - `STUDENT_DASHBOARD_COURSES_UPDATE.md`
  - `WORKFLOW_DIAGRAM.txt`
- Old API files from root (moved to `api/` directory)

### Security
- Credentials moved to `.env` file (not tracked by git)
- Added `.env` to `.gitignore`
- Improved file upload security
- Enhanced session management

## [1.0.0] - 2025-09-30

### Initial Release
- Basic grade management system
- User authentication (admin, teacher, student)
- Classroom management
- Grade entry and viewing
- Subject management
- Student enrollment

### Features
- Admin dashboard
- Teacher dashboard
- Student dashboard
- Grade tracking
- Classroom creation
- Student-classroom assignment

---

## Version Numbering

This project follows [Semantic Versioning](https://semver.org/):
- MAJOR version for incompatible API changes
- MINOR version for new functionality in a backward compatible manner
- PATCH version for backward compatible bug fixes

## Upgrade Notes

### Upgrading from 1.x to 2.0

1. **Backup your database and files**
2. **Create `.env` file** from `.env.example`
3. **Update database connection** - credentials now in `.env`
4. **Run database updates** if needed
5. **Update file paths** in any custom code
6. **Test thoroughly** before going live

### Database Migration

If upgrading from version 1.x:
```sql
-- Your existing database will work with the new schema
-- No migration needed if you have all tables
-- Run schema.sql on a fresh database for new installations
```

## Future Plans

### Version 2.1 (Planned)
- Email notifications for registration approvals
- PDF report generation
- Advanced grade analytics
- Bulk student import
- Academic calendar integration

### Version 2.2 (Planned)
- REST API for mobile apps
- Student portal enhancements
- Parent portal
- SMS notifications
- Payment integration

### Version 3.0 (Future)
- Multi-tenant support
- Advanced reporting dashboard
- Learning management system integration
- Mobile applications (iOS/Android)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for how to contribute to this project.

## Support

For issues and questions:
- GitHub Issues: [Create an issue](https://github.com/yourusername/student-grade-management/issues)
- Documentation: See `docs/` directory
- Email: support@yourschool.edu
