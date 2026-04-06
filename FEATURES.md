# 📚 AI Library System - Complete Feature Documentation

## Overview
The AI Library System has been significantly enhanced with 15+ new advanced features for managing books, users, and library operations.

---

## 🔐 Security Features

### 1. **Password Reset & Recovery**
- **Files**: `auth/forgot_password.php`, `auth/reset_password.php`
- **Login Page Update**: Added "Forgot Password?" link
- **Features**:
  - Email verification for password recovery
  - Time-limited reset tokens (1 hour expiry)
  - Real-time password strength checker
  - Visual strength indicator (Weak → Very Strong)

**How to use**:
1. Click "Forgot Password?" on login page
2. Enter your email address
3. Check email for reset link
4. Create a strong new password
5. Login with new password

### 2. **Password Strength Requirements**
- Minimum 8 characters
- Mix of uppercase letters
- Mix of lowercase letters
- Contains at least one number
- Contains at least one special character (!@#$%^&*)
- Real-time feedback with visual indicator

### 3. **Session Management**
- **File**: `includes/security.php`
- **Features**:
  - 30-minute session timeout (auto-logout)
  - Secure cookie configuration
  - Session regeneration to prevent fixation attacks
  - IP address tracking
  - User agent tracking

### 4. **Account Security**
- Account locking after 5 failed login attempts
- Failed login attempt logging
- Last login tracking
- Password history validation

### 5. **Audit Logging**
- **Files**: `admin/audit_logs.php`, `includes/audit_logging.php`
- **Features**:
  - Track all admin actions
  - Record IP addresses
  - Detailed action logs
  - Action statistics dashboard
  - Pagination support

**Admin Actions Logged**:
- Book additions/deletions
- User management
- Category changes
- Borrow/return operations
- Settings modifications

---

## 📚 Book Management Enhancements

### 6. **Book Cover Image Management**
- **File**: `admin/manage_covers.php`
- **Features**:
  - Upload and display book covers
  - Multiple image format support (JPG, PNG, GIF, WebP)
  - 5MB file size limit
  - Recommended dimensions: 300x400 pixels
  - Automatic cover replacement
  - Gallery view of all books

**How to use**:
1. Go to Admin → Book Covers
2. Find the book you want to add a cover for
3. Click "Choose File" and select an image
4. Click "Upload"
5. Cover appears in all book listings and details

### 7. **Book Categories Management**
- **File**: `admin/manage_categories.php`
- **Features**:
  - Create and manage book categories
  - Add category descriptions
  - View books in each category
  - Delete unused categories
  - Count books per category

**How to use**:
1. Go to Admin → Categories
2. Enter category name and description
3. Click "Add Category"
4. Manage existing categories in the list

### 8. **Bulk Book Import (CSV)**
- **File**: `admin/bulk_import.php`
- **Features**:
  - Import multiple books from CSV file
  - Support for 7 fields: title, author, ISBN, year, category, description, quantity
  - Skip duplicate books (by ISBN)
  - Import statistics
  - Template download

**CSV Format**:
```
title,author,isbn,published_year,category_id,description,available
The Great Gatsby,F. Scott Fitzgerald,978-0743273565,1925,1,Classic novel,3
```

**How to use**:
1. Go to Admin → Bulk Import
2. Download the CSV template
3. Fill in your book data
4. Click "Import Books"
5. View import statistics

---

## 📖 Reading & User Features

### 9. **Reading History Tracking**
- **Files**: `user/reading_history.php`, `includes/reading_history.php`
- **Features**:
  - Automatic tracking when users borrow books
  - View complete reading history
  - Track pages read per book
  - Reading statistics dashboard
  - Last read date tracking
  - Total books and pages read

**Statistics Available**:
- Total books read
- Total pages read
- Last read date/time
- Average pages per book

**How to use**:
1. Go to User → Reading History
2. View all books you've borrowed
3. See detailed statistics about your reading

### 10. **Email Notifications**
- **Files**: `includes/email_functions.php`, `includes/notifications.php`
- **Features**:
  - Due date reminders
  - Password reset emails
  - HTML formatted emails
  - Customizable email templates
  - SMTP ready (mail() fallback)

**Email Types**:
- Due date reminders
- Password reset notifications
- Book reservation confirmations
- Fine notifications

---

## 🛠️ Admin Tools

### 11. **Enhanced Return Management**
- **File**: `admin/return_book.php`
- **Features**:
  - Detailed confirmation page
  - Automatic fine calculation
  - Fine preview before confirmation
  - User and book information display
  - Success feedback with auto-redirect
  - Error handling and validation

**How to use**:
1. Go to Admin → Borrow Management
2. Click "Mark Returned" on a borrowed book
3. Review the details
4. See calculated fine if applicable
5. Click "Confirm Return"

---

## 🔗 API Endpoints

### 12. **REST API for Mobile Apps**
- **File**: `api/index.php`
- **Base URL**: `http://localhost/ai_lib/api/index.php`

**Public Endpoints** (No auth required):
```
GET /books?page=1&limit=20
GET /book?id=1
GET /search?q=title&type=title
GET /categories
```

**Protected Endpoints** (Requires Bearer token):
```
GET /user_books
GET /reserves
GET /reading_history?limit=20
```

**Authentication**:
```
Authorization: Bearer YOUR_API_TOKEN
```

**Response Format**:
```json
{
    "data": [...],
    "page": 1,
    "limit": 20
}
```

---

## 🎨 UI/UX Enhancements

### 13. **Theme Management**
- **File**: `includes/theme_manager.php`
- **Features**:
  - Dark mode toggle (implementation ready)
  - Light mode (default)
  - User preference persistence
  - Database storage of theme choice
  - Cookie-based fallback

**How to enable** (in layout.php):
```php
<?php include("../includes/theme_manager.php"); 
$theme = initializeTheme();
echo getThemeCSS($theme);
?>
```

### 14. **Enhanced Navigation**
- Updated sidebar with new menu items
- Admin menu includes:
  - Book Covers
  - Categories
  - Bulk Import
  - Audit Logs
- User menu includes:
  - Reading History
- Feature-organized layout

---

## 📊 Database Schema

### New Tables Created:

1. **book_covers** - Book cover image management
2. **reading_history** - User reading history
3. **audit_logs** - Admin action logging
4. **two_factor_auth** - 2FA setup (ready for implementation)
5. **password_resets** - Password reset tokens
6. **user_sessions** - Active session management
7. **categories** - Book categories (enhanced)
8. **api_tokens** - API token management

### New Columns in Users Table:
- `cover_image` - User profile picture
- `two_factor_enabled` - 2FA status
- `password_strength` - Last password strength score
- `last_login` - Last login timestamp
- `account_locked` - Account lock status
- `theme_preference` - UI theme choice

---

## 🚀 Setup Instructions

### 1. Run Database Setup
```bash
php setup_features.php
```

### 2. Create Book Covers Directory
The system automatically creates `assests/book_covers/` directory

### 3. Update Email Configuration
Edit `includes/email_functions.php` to configure:
- SMTP settings (optional)
- Reply-to email address
- Email sender name

### 4. Generate API Tokens
After implementing use Admin panel to create API tokens for users

### 5. Test Features
- Test password reset flow
- Upload book covers
- Import sample CSV
- Check audit logs
- Verify email notifications

---

## 📋 Feature Checklist

✅ Password Reset & Recovery
✅ Password Strength Requirements
✅ Session Management
✅ Account Security
✅ Audit Logging
✅ Book Cover Management
✅ Book Categories
✅ Bulk CSV Import
✅ Reading History
✅ Email Notifications
✅ Enhanced Return Process
✅ REST API
✅ Security Manager
✅ Theme System (Ready)
✅ Updated Navigation

---

## 🔮 Future Enhancements

- Two-factor authentication (2FA via TOTP)
- Book recommendations AI
- Advanced analytics with export
- Social features (follow users, share lists)
- Mobile app integration
- Webhook support for external systems
- Advanced search filters
- Book wishlists
- Author spotlight
- Reading challenges

---

## 🆘 Support & Troubleshooting

### Common Issues:

**Password Reset Email Not Received**
- Check email configuration in `email_functions.php`
- Verify SMTP/mail() is enabled on server
- Check spam folder

**Book Covers Not Displaying**
- Ensure `assests/book_covers/` directory exists with write permissions
- Check file paths in database
- Verify image format is supported

**API Token Issues**
- Generate new token if expired
- Check Authorization header format: `Bearer YOUR_TOKEN`
- Ensure token exists in database

**Session Timeout Too Short**
- Adjust SESSION_TIMEOUT in `includes/security.php`
- Default: 30 minutes
- Use for: `define('SESSION_TIMEOUT', minutes * 60);`

---

## 📞 Contact & Support
For issues or feature requests, contact the development team.

**Last Updated**: March 2026
**Version**: 2.0
