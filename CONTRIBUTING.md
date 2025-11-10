# Contributing to Student Record Management System

Thank you for considering contributing to this project! Here are some guidelines to help you get started.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:
- Clear description of the problem
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (if applicable)
- Your environment (PHP version, MySQL version, OS)

### Suggesting Features

Feature requests are welcome! Please:
- Check if the feature already exists
- Clearly describe the feature and its benefits
- Provide use cases

### Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Make your changes
4. Test thoroughly
5. Commit with clear messages (`git commit -m 'Add some AmazingFeature'`)
6. Push to your branch (`git push origin feature/AmazingFeature`)
7. Open a Pull Request

## Coding Standards

### PHP
- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Comment complex logic
- Use prepared statements for database queries
- Validate and sanitize all user inputs

### Database
- Use proper foreign keys and constraints
- Index frequently queried columns
- Follow naming conventions (snake_case for tables/columns)

### Security
- Never commit `.env` file
- Use password hashing (password_hash/password_verify)
- Validate file uploads
- Prevent SQL injection with prepared statements
- Implement CSRF protection where needed

## Testing

Before submitting:
- Test all functionality
- Check for PHP errors
- Verify database queries work correctly
- Test on different browsers
- Ensure mobile responsiveness

## Questions?

Feel free to open an issue for any questions or clarifications.

Thank you for contributing! 🎉
