# 📋 Database & Setup Files Reference

## Your System Now Has 3 Ways to Manage the Database

---

## 1️⃣ **Web-Based Database Manager**
**File:** `db_manager.php`  
**Access:** http://localhost/ai_lib/db_manager.php

### Features:
- ✅ View database statistics
- ✅ Setup demo data with one click
- ✅ Create database backups
- ✅ Clear database (⚠️ careful!)
- ✅ No command line needed
- ✅ Visual interface

### Best For:
- Visual learners
- Quick setup without terminal
- Seeing what's being created
- Regular monitoring

---

## 2️⃣ **Command Line Setup Script**
**File:** `setup_db_cli.php`  
**Command:** 
```bash
cd D:\xampp\htdocs\ai_lib
D:\xampp\php\php.exe setup_db_cli.php
```

### Features:
- ⚡ Lightning fast setup
- 📊 Clear console output
- 🔄 Repeatable (always clears first)
- 🤖 Perfect for automation/scripting
- No browser needed

### Best For:
- Fast setup
- Scripting/automation
- Terminal lovers
- CI/CD pipelines

---

## 3️⃣ **Traditional MySQL Dump (Advanced)**
**Method:** Use MySQL Workbench or phpMyAdmin

### Features:
- 🔧 Full database control
- 💾 Create/restore backups
- 🆘 Direct SQL access
- 📊 Advanced table management

### Best For:
- Advanced users
- Production environments
- Complex database operations
- Backup/restore workflows

---

## 📁 File Locations

All setup/management files are in the **root directory**:

```
D:\xampp\htdocs\ai_lib\
├── db_manager.php          ← Web-based manager
├── setup_db_cli.php        ← CLI setup script
├── SETUP_GUIDE.md          ← This guide! ← YOU ARE HERE
├── DB_FILES_REFERENCE.md   ← (What you're reading)
├── config/
│   └── database.php        ← Database config
└── [other project files]
```

---

## 🎯 Quick Decision Tree

**How do you want to setup the database?**

```
START
  ↓
Do you prefer web interfaces?
  ├─ YES → Use db_manager.php
  │        Open: http://localhost/ai_lib/db_manager.php
  │        Click: "Setup Demo Data"
  │        ✅ Done!
  │
  └─ NO → Use CLI setup script
           Command: D:\xampp\php\php.exe setup_db_cli.php
           ✅ Done!
```

---

## 📚 Demo Data Included

Both methods create the **same demo data:**

### Categories (10)
- Fiction, Science Fiction, Fantasy, Mystery, Romance
- Self-Help, History, Technology, Poetry, Children

### Books (33)
- Classic literature: The Great Gatsby, Pride and Prejudice
- Modern bestsellers: Game of Thrones, Harry Potter
- Sci-fi: 1984, Dune, Foundation, Neuromancer
- Mystery: Sherlock Holmes, The Da Vinci Code
- And 24 more quality books!

### Users (6)
```
Admin:
  email: admin@library.local
  password: Admin@123

Regular Users:
  john@example.com     | password: User@123
  jane@example.com     | password: User@123
  michael@example.com  | password: User@123
  sarah@example.com    | password: User@123
  david@example.com    | password: User@123
```

### Sample Data
- 3 borrow records (sample loans)
- 3 book reviews with ratings
- 2 reading history entries
- Complete fines configuration

---

## 🔧 Customization

### Want Different Demo Data?

**Option A: Edit the script**
- Edit `setup_db_cli.php` or `db_manager.php`
- Add/remove books, users, categories
- Increase quantities as needed

**Option B: Manual Entry**
- Setup database once
- Use web interface to add your own data
- Skip the demo data setup

**Option C: Import CSV**
- Use `admin/bulk_import.php` 
- Create your own CSV file
- Import after initial setup

---

## 📊 Database Structure

The setup creates these tables automatically:

```
users
├── id, name, email, password, role
├── phone, address, profile_pic, theme, created_at
└── updated_at

categories
├── id, name, description

books
├── id, title, author, isbn
├── published_year, category_id, description
├── available, cover_image, created_at, updated_at

borrow_records
├── id, user_id, book_id
├── issue_date, due_date, return_date, status

reviews
├── id, book_id, user_id, rating
├── review, review_date

reading_history
├── id, user_id, book_id
├── pages_read, read_date

fines_config
├── id, daily_fine, max_books

reservations
├── id, user_id, book_id, reserved_date
├── status, notification_sent
```

---

## ✅ Verification Checklist

After setup, verify:

- [ ] Can login with admin@library.local
- [ ] Can see 33+ books in library
- [ ] Can see 10 categories
- [ ] Can see 6 users in admin panel
- [ ] Can view sample borrow records
- [ ] Can see reviews and ratings
- [ ] Database statistics show data

---

## 🚀 Next Steps

1. **Choose your setup method** (Web or CLI)
2. **Run the setup**
3. **Login** with provided credentials
4. **Explore** the library system
5. **Test features** with demo data
6. **Add your own content** when ready

---

## 🆘 Need Help?

**Common Issues:**

| Problem | Solution |
|---------|----------|
| "Database connection failed" | Check MySQL is running in XAMPP |
| "Table already exists" | Run setup again - it clears first |
| "Login fails" | Use exact credentials (emails are case-sensitive) |
| "No books showing" | Refresh browser or run setup again |
| "PHP error" | Check PHP syntax: `php -l setup_db_cli.php` |

**Documentation:**
- 📖 [SETUP_GUIDE.md](SETUP_GUIDE.md) - Detailed setup instructions
- 📖 [QUICK_START.md](QUICK_START.md) - Feature overview
- 📖 [FEATURES.md](FEATURES.md) - Complete feature list
- 📖 [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) - Technical details

---

**Choose your method and get started! 🎉**
