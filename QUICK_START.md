# 🚀 Quick Start Guide - New Features

## Welcome to AI Library 2.0!

Your library system now has 15+ advanced features. Here's how to get started:

---

## 👤 For Users

### 1️⃣ Password Reset (If You Forgot Your Password)
- Go to **Login Page**
- Click **"Forgot Password?"** link
- Enter your **email address**
- Check your **email inbox** for reset link
- Click the link and **create a strong password**
- Login with your **new password**

**Password Requirements**:
- At least 8 characters
- Mix of uppercase and lowercase
- Include numbers (0-9)
- Include special characters (!@#$%^&*)
- Watch the **strength meter** while typing

### 2️⃣ View Your Reading History
- Login to your **user account**
- Go to **Reading History** (sidebar)
- See all books you've **borrowed**
- View **reading statistics**:
  - Total books read
  - Total pages read
  - Last read date

### 3️⃣ Receive Email Notifications
- You'll get **email alerts** for:
  - 📧 **Due date reminders** - when your books are due soon
  - 🔐 **Password reset links** - when you request password reset
  - 📚 **Book reservations** - when your reserved book is ready

---

## 🔑 For Admins

### 1️⃣ Upload Book Covers
- Go to **Admin Dashboard** → **Book Covers**
- Find the **book** you want to add a cover for
- **Click "Choose File"** and select an image
- Click **"Upload"**
- The cover will appear in all listings

**Supported Formats**: JPG, PNG, GIF, WebP (Max 5MB)
**Recommended Size**: 300x400 pixels

### 2️⃣ Manage Book Categories
- Go to **Admin Dashboard** → **Categories**
- **Add new category**:
  - Enter category name
  - Add description (optional)
  - Click **"Add Category"**
- **Delete categories** (if no books use them)
- See **how many books** are in each category

### 3️⃣ Import Books in Bulk
- Go to **Admin Dashboard** → **Bulk Import**
- **Download the CSV template**
- Open template in **Excel/Google Sheets**
- Fill in your book data:
  ```
  Title | Author | ISBN | Year | Category ID | Description | Quantity
  ```
- Save as **CSV file**
- Upload the file
- See **import statistics**

**Example**:
```
The Great Gatsby,F. Scott Fitzgerald,978-0743273565,1925,1,Classic novel,3
```

### 4️⃣ View Audit Logs
- Go to **Admin Dashboard** → **Audit Logs**
- See **all admin actions** (who did what and when)
- Check **IP addresses** and timestamps
- View **action statistics**
- Helpful for **security monitoring**

### 5️⃣ Manage Book Returns
- Go to **Admin Dashboard** → **Borrow Management**
- Click **"Mark Returned"** on any borrowed book
- **Review the details** (book, user, dates)
- **Check if fine applies** (automatically calculated)
- Click **"Confirm Return"**
- See **success message** and auto-redirect

**Fine Calculation**:
- If returned **late**: Fine = Days Late × Daily Rate
- Fine amount shown **before confirming**
- User sees fine in their **Fines page**

---

## 🔗 For Developers - API Access

### Access Our REST API
**Base URL**: `http://localhost/ai_lib/api/index.php`

### Public Endpoints (No Auth Needed)
```bash
# Get all books
GET /api/index.php?endpoint=books&page=1&limit=20

# Get single book
GET /api/index.php?endpoint=book&id=5

# Search books
GET /api/index.php?endpoint=search&q=gatsby&type=title

# Get categories
GET /api/index.php?endpoint=categories
```

### Protected Endpoints (Requires Token)
```bash
# Get user's borrowed books
GET /api/index.php?endpoint=user_books
Header: Authorization: Bearer YOUR_TOKEN

# Get user's reservations
GET /api/index.php?endpoint=reserves
Header: Authorization: Bearer YOUR_TOKEN

# Get reading history
GET /api/index.php?endpoint=reading_history&limit=20
Header: Authorization: Bearer YOUR_TOKEN
```

### Response Format
```json
{
    "data": [
        {
            "id": 1,
            "title": "The Great Gatsby",
            "author": "F. Scott Fitzgerald",
            "isbn": "978-0743273565"
        }
    ],
    "page": 1,
    "limit": 20
}
```

---

## 🎯 Common Tasks

### Add a New Book (Admin)
1. Admin Dashboard → **Manage Books** → **Add New Book**
2. Fill in book details
3. Upload the **cover image** (recommended)
4. Click **"Add Book"**

### Change Your Password (User)
1. Click **"Profile"** in sidebar
2. Look for **"Change Password"** section
3. Enter **old password**
4. Enter **new password** (strong password required)
5. Click **"Update"**

### Export Audit Logs (Admin)
1. Go to **Audit Logs**
2. Copy log data to **spreadsheet**
3. Create **CSV/Excel file**
4. Use for **audit reports**

### Check System Health (Admin)
1. Go to **Admin Dashboard** → **Audit Logs**
2. Review recent **admin actions**
3. Check for **unusual activities**
4. Monitor **IP addresses**

---

## ❓ Frequently Asked Questions

**Q: How long is my login session?**
A: 30 minutes of inactivity. You'll be logged out automatically for security.

**Q: Can I change my password?**
A: Yes! Go to Profile → Change Password (need current password)

**Q: What image formats work for book covers?**
A: JPG, PNG, GIF, WebP. Maximum 5MB size.

**Q: How do I bulk upload 200 books?**
A: Use CSV import! Download template and fill in your books.

**Q: Who can see audit logs?**
A: Only admins can view audit logs.

**Q: Can I export my reading history?**
A: Yes, from Reading History page (manual export to spreadsheet).

**Q: What happens if I don't return a book on time?**
A: You'll get an email reminder. A fine is calculated automatically.

**Q: How do I reset my password if I forgot it?**
A: Click "Forgot Password" on login page. Check your email.

**Q: Can I reserve a book from the API?**
A: Yes, check the API endpoints for reservation features.

---

## 🔒 Security Tips

✅ **Use a strong password** - Mix uppercase, lowercase, numbers, special chars
✅ **Never share your password** - Library staff will never ask for it
✅ **Logout before leaving** - Click Logout in your profile menu
✅ **Report suspicious activity** - Tell admin if you see unusual logs
✅ **Keep email updated** - So you receive password reset emails

---

## 📞 Need Help?

- Check **FEATURES.md** for detailed documentation
- Read **IMPLEMENTATION_SUMMARY.md** for technical details
- Contact your **library administrator** for account issues
- Check **API documentation** in FEATURES.md for API details

---

## 🎉 That's It!

You're now ready to use all the advanced features of AI Library 2.0. Enjoy!

**Happy Reading! 📚**
