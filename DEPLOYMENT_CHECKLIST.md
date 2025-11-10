# Deployment Checklist

Use this checklist before deploying to production or pushing to GitHub.

## Pre-Deployment Checklist

### Security ✅
- [ ] `.env` file is in `.gitignore`
- [ ] No hardcoded credentials in code
- [ ] Default passwords documented for changing
- [ ] File upload validation in place
- [ ] SQL injection protection (prepared statements)
- [ ] XSS protection implemented
- [ ] CSRF tokens where needed
- [ ] Session security configured

### Configuration ✅
- [ ] `.env.example` is up to date
- [ ] Database credentials are correct
- [ ] Upload directories exist and are writable
- [ ] PHP version requirements met (7.4+)
- [ ] MySQL version requirements met (5.7+)
- [ ] Required PHP extensions installed

### Database ✅
- [ ] `database/schema.sql` is complete
- [ ] `database/seed.sql` has sample data
- [ ] All foreign keys are properly set
- [ ] Indexes are optimized
- [ ] No sensitive data in seed file

### Code Quality ✅
- [ ] No PHP errors or warnings
- [ ] All file paths are correct
- [ ] API endpoints work correctly
- [ ] Forms validate properly
- [ ] Error handling is in place
- [ ] Code is commented where needed

### Documentation ✅
- [ ] README.md is complete
- [ ] QUICKSTART.md is accurate
- [ ] Installation guide is clear
- [ ] Project structure is documented
- [ ] Contributing guidelines exist
- [ ] License file is present
- [ ] Changelog is up to date

### Testing ✅
- [ ] Login works for all roles
- [ ] Student registration works
- [ ] Course registration works
- [ ] Grade entry works
- [ ] File uploads work
- [ ] API endpoints respond correctly
- [ ] Database queries are optimized
- [ ] No broken links

### GitHub Preparation ✅
- [ ] Repository name is set
- [ ] Description is written
- [ ] Topics/tags are added
- [ ] README has correct URLs
- [ ] License is appropriate
- [ ] .gitignore is complete
- [ ] No sensitive files tracked

## GitHub Push Checklist

### Before First Push
```bash
# 1. Initialize git (if not already done)
git init

# 2. Add all files
git add .

# 3. Check what will be committed
git status

# 4. Verify .env is NOT in the list
# If it is, add it to .gitignore immediately!

# 5. Commit
git commit -m "Initial commit: Complete project reorganization"

# 6. Add remote
git remote add origin https://github.com/yourusername/student-grade-management.git

# 7. Push
git push -u origin main
```

### After Push
- [ ] Verify all files are on GitHub
- [ ] Check that `.env` is NOT on GitHub
- [ ] Test clone on a different machine
- [ ] Update repository settings
- [ ] Add repository description
- [ ] Add topics (php, mysql, education, student-management)
- [ ] Enable Issues
- [ ] Add collaborators if needed

## Production Deployment Checklist

### Server Setup
- [ ] PHP 7.4+ installed
- [ ] MySQL 5.7+ installed
- [ ] Apache/Nginx configured
- [ ] SSL certificate installed (HTTPS)
- [ ] Firewall configured
- [ ] Backup system in place

### Application Setup
- [ ] Clone repository
- [ ] Copy `.env.example` to `.env`
- [ ] Configure `.env` with production values
- [ ] Create database
- [ ] Import `database/schema.sql`
- [ ] Set file permissions (755 for directories, 644 for files)
- [ ] Set upload directory permissions (755)
- [ ] Configure web server virtual host

### Security Hardening
- [ ] Change all default passwords
- [ ] Use strong database password
- [ ] Disable directory listing
- [ ] Protect `.env` file (deny access)
- [ ] Enable HTTPS only
- [ ] Set secure session cookies
- [ ] Configure PHP security settings
- [ ] Set up regular backups
- [ ] Enable error logging (disable display)
- [ ] Implement rate limiting

### Post-Deployment
- [ ] Test all functionality
- [ ] Verify email notifications (if implemented)
- [ ] Check file uploads work
- [ ] Test all user roles
- [ ] Monitor error logs
- [ ] Set up monitoring/alerts
- [ ] Document admin procedures
- [ ] Train administrators

## Maintenance Checklist

### Daily
- [ ] Check error logs
- [ ] Monitor disk space
- [ ] Verify backups completed

### Weekly
- [ ] Review user activity
- [ ] Check for security updates
- [ ] Test backup restoration

### Monthly
- [ ] Update dependencies
- [ ] Review and archive old data
- [ ] Performance optimization
- [ ] Security audit

### Quarterly
- [ ] Full system backup
- [ ] Disaster recovery test
- [ ] User access review
- [ ] Documentation update

## Rollback Plan

If something goes wrong:

1. **Stop the application**
   ```bash
   # Stop web server
   sudo systemctl stop apache2
   ```

2. **Restore database**
   ```bash
   mysql -u root -p student_grade_management < backup.sql
   ```

3. **Restore files**
   ```bash
   # Restore from backup
   cp -r backup/* /var/www/html/student-grade-management/
   ```

4. **Restart application**
   ```bash
   sudo systemctl start apache2
   ```

## Support Contacts

- **Technical Lead:** [Name] - [Email]
- **Database Admin:** [Name] - [Email]
- **System Admin:** [Name] - [Email]
- **GitHub Issues:** https://github.com/yourusername/student-grade-management/issues

## Notes

- Always test in staging before production
- Keep backups for at least 30 days
- Document all changes in CHANGELOG.md
- Communicate with users before major updates
- Have a rollback plan ready

---

**Last Updated:** November 10, 2025  
**Version:** 2.0.0  
**Status:** Ready for Deployment ✅
