# GitHub Repository Setup Guide

After pushing your code to GitHub, follow these steps to make your repository professional and discoverable.

## 1. Repository Settings

### Basic Information
1. Go to your repository on GitHub
2. Click "Settings"
3. Update the following:

**Repository name:** `student-grade-management`

**Description:**
```
A comprehensive web-based student management system built with PHP and MySQL. Features include student registration, course management, grade tracking, and academic administration.
```

**Website:** (Your deployment URL if available)

**Topics/Tags:**
```
php
mysql
education
student-management
grade-management
academic-system
school-management
course-registration
pdo
bootstrap
```

### Features to Enable
- ✅ Issues
- ✅ Projects (optional)
- ✅ Wiki (optional)
- ✅ Discussions (optional)
- ❌ Sponsorships (unless you want donations)

## 2. Repository Social Preview

Create a social preview image (1280x640px) with:
- Project name: "Student Grade Management System"
- Tagline: "Complete Academic Management Solution"
- Tech stack icons: PHP, MySQL, Bootstrap
- Background: Professional education-themed

Upload at: Settings → Social preview → Upload an image

## 3. About Section

Click the gear icon next to "About" and add:

**Description:**
```
Complete student management system with registration, course enrollment, and grade tracking
```

**Website:** Your deployment URL

**Topics:** (Add the tags listed above)

**Releases:** ✅ Check this box

## 4. Create First Release

1. Go to "Releases" → "Create a new release"
2. Tag version: `v2.0.0`
3. Release title: `Version 2.0.0 - Complete Reorganization`
4. Description:
```markdown
## 🎉 Version 2.0.0 - Major Update

### What's New
- Complete project reorganization for better maintainability
- Environment-based configuration with .env support
- Unified database schema
- Comprehensive documentation
- GitHub-ready structure

### Features
- ✅ Student self-registration with approval workflow
- ✅ Course registration and management
- ✅ Grade tracking and reporting
- ✅ Multi-role access (Admin, Teacher, Student, Registrar)
- ✅ Academic structure (Schools, Departments, Programs)

### Installation
See [QUICKSTART.md](QUICKSTART.md) for quick installation or [docs/INSTALLATION.md](docs/INSTALLATION.md) for detailed instructions.

### Default Credentials
- Admin: `admin` / `password123`
- Registrar: `registrar` / `password123`

⚠️ Change these passwords immediately after installation!

### Documentation
- [README.md](README.md) - Main documentation
- [QUICKSTART.md](QUICKSTART.md) - Quick start guide
- [CHANGELOG.md](CHANGELOG.md) - Version history
- [CONTRIBUTING.md](CONTRIBUTING.md) - How to contribute

### Requirements
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx

### License
MIT License - See [LICENSE](LICENSE) file
```

5. Click "Publish release"

## 5. README Badges

Add these badges to your README.md (already included):

```markdown
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net/)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B-orange)](https://www.mysql.com/)
[![GitHub release](https://img.shields.io/github/release/yourusername/student-grade-management.svg)](https://github.com/yourusername/student-grade-management/releases)
[![GitHub issues](https://img.shields.io/github/issues/yourusername/student-grade-management.svg)](https://github.com/yourusername/student-grade-management/issues)
[![GitHub stars](https://img.shields.io/github/stars/yourusername/student-grade-management.svg)](https://github.com/yourusername/student-grade-management/stargazers)
```

## 6. Issue Templates

Create `.github/ISSUE_TEMPLATE/` directory with these templates:

### Bug Report Template
`.github/ISSUE_TEMPLATE/bug_report.md`:
```markdown
---
name: Bug Report
about: Create a report to help us improve
title: '[BUG] '
labels: bug
assignees: ''
---

**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce:
1. Go to '...'
2. Click on '...'
3. See error

**Expected behavior**
What you expected to happen.

**Screenshots**
If applicable, add screenshots.

**Environment:**
- OS: [e.g. Windows 10]
- PHP Version: [e.g. 7.4]
- MySQL Version: [e.g. 5.7]
- Browser: [e.g. Chrome 95]

**Additional context**
Any other context about the problem.
```

### Feature Request Template
`.github/ISSUE_TEMPLATE/feature_request.md`:
```markdown
---
name: Feature Request
about: Suggest an idea for this project
title: '[FEATURE] '
labels: enhancement
assignees: ''
---

**Is your feature request related to a problem?**
A clear description of the problem.

**Describe the solution you'd like**
What you want to happen.

**Describe alternatives you've considered**
Other solutions you've thought about.

**Additional context**
Any other context or screenshots.
```

## 7. Pull Request Template

Create `.github/pull_request_template.md`:
```markdown
## Description
Brief description of changes.

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tested locally
- [ ] No PHP errors
- [ ] Database queries work
- [ ] All features functional

## Checklist
- [ ] Code follows project style
- [ ] Self-reviewed code
- [ ] Commented complex code
- [ ] Updated documentation
- [ ] No new warnings
- [ ] Added tests (if applicable)

## Screenshots (if applicable)
Add screenshots here.
```

## 8. GitHub Actions (Optional)

Create `.github/workflows/php.yml` for automated testing:
```yaml
name: PHP CI

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  build:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '7.4'
        extensions: pdo, pdo_mysql, mbstring
    
    - name: Check PHP syntax
      run: find . -name "*.php" -exec php -l {} \;
    
    - name: Run tests
      run: echo "Tests would run here"
```

## 9. Security

### Enable Security Features
1. Go to Settings → Security
2. Enable:
   - ✅ Dependency graph
   - ✅ Dependabot alerts
   - ✅ Dependabot security updates

### Add SECURITY.md
Create `SECURITY.md` in root:
```markdown
# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 2.0.x   | :white_check_mark: |
| < 2.0   | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability, please email:
security@yourschool.edu

Please do NOT create a public GitHub issue for security vulnerabilities.

We will respond within 48 hours.
```

## 10. Community Files

### CODE_OF_CONDUCT.md
```markdown
# Code of Conduct

## Our Pledge
We pledge to make participation in our project a harassment-free experience for everyone.

## Our Standards
- Be respectful
- Be collaborative
- Be professional
- Accept constructive criticism

## Enforcement
Violations can be reported to: conduct@yourschool.edu
```

## 11. GitHub Pages (Optional)

If you want to host documentation:

1. Go to Settings → Pages
2. Source: Deploy from a branch
3. Branch: `main` / `docs`
4. Save

Create `docs/index.html` for a landing page.

## 12. Protect Main Branch

1. Go to Settings → Branches
2. Add rule for `main`
3. Enable:
   - ✅ Require pull request reviews
   - ✅ Require status checks to pass
   - ✅ Require branches to be up to date

## 13. Add Collaborators

1. Go to Settings → Collaborators
2. Add team members with appropriate permissions:
   - **Admin:** Full access
   - **Write:** Can push to repository
   - **Read:** Can view and clone

## 14. Project Board (Optional)

Create a project board for task management:

1. Go to Projects → New project
2. Template: "Basic kanban"
3. Columns:
   - To Do
   - In Progress
   - Done

## 15. Wiki (Optional)

Enable and populate wiki with:
- Installation guides
- User manuals
- API documentation
- Troubleshooting guides
- FAQ

## 16. Discussions (Optional)

Enable Discussions for:
- Q&A
- Feature requests
- General discussion
- Show and tell

## Checklist

Before making repository public:

- [ ] Repository name and description set
- [ ] Topics/tags added
- [ ] Social preview image uploaded
- [ ] README.md is complete
- [ ] LICENSE file exists
- [ ] .gitignore is correct
- [ ] .env is NOT in repository
- [ ] First release created
- [ ] Issue templates added
- [ ] PR template added
- [ ] SECURITY.md added
- [ ] CODE_OF_CONDUCT.md added
- [ ] Branch protection enabled
- [ ] Security features enabled
- [ ] Collaborators added

## After Going Public

- [ ] Share on social media
- [ ] Submit to awesome lists
- [ ] Post on relevant forums
- [ ] Write blog post
- [ ] Create demo video
- [ ] Add to portfolio

## Maintenance

### Weekly
- [ ] Review and respond to issues
- [ ] Review pull requests
- [ ] Update documentation

### Monthly
- [ ] Check for security updates
- [ ] Review and close stale issues
- [ ] Update dependencies

### Quarterly
- [ ] Create new release
- [ ] Update changelog
- [ ] Review and update documentation

---

**Last Updated:** November 10, 2025  
**Status:** Ready for GitHub 🚀
