# 🚀 Database Setup Guide

Your AI Library System has **two ways** to setup the database with demo data:

---

## **Option 1: Web-Based Setup (Easy)** 🌐

### Steps:
1. **Open in Browser:**
   ```
   http://localhost/ai_lib/db_manager.php
   ```

2. **Click "Setup Demo Data"** button

3. **View statistics** to confirm everything is populated

4. **Login** with test credentials below

### ✅ Advantages:
- Visual interface
- See progress in real-time
- Can backup before setup
- Easy to understand

---

## **Option 2: CLI Setup (Fast)** ⚡

### Steps:

1. **Open Command Prompt** or **PowerShell**

2. **Navigate to project:**
   ```cmd
   cd D:\xampp\htdocs\ai_lib
   ```

3. **Run setup script:**
   ```cmd
   D:\xampp\php\php.exe setup_db_cli.php
   ```

4. **Wait for completion** - You'll see a summary like:
   ```
   ✅ DATABASE SETUP COMPLETE!
   
   📚 Sample Data Added:
     • Categories: 10
     • Users: 6
     • Books: 33
     • Borrow Records: 2
     • Reviews: 3
     • Reading History: 2
   ```

### ✅ Advantages:
- Runs instantly in terminal
- No web browser needed
- Perfect for automation/scripting
- Shows clear output

---

## 🔐 Login Credentials (Both Methods)

### Admin Account:
```
Email:    admin@library.local
Password: Admin@123
```

### User Accounts (All use password: User@123):
- john@example.com
- jane@example.com
- michael@example.com
- sarah@example.com
- david@example.com

---

## 📊 What Gets Added

### Categories (10):
Fiction, Science Fiction, Fantasy, Mystery, Romance, Self-Help, History, Technology, Poetry, Children

### Books (33):
The Great Gatsby, 1984, Pride and Prejudice, Dune, The Hobbit, Harry Potter, Game of Thrones, The Da Vinci Code, Sherlock Holmes, and 24 more classics!

### Users (6):
1 admin account + 5 regular user accounts

### Sample Borrow Records:
3-5 realistic borrow records with due dates

### Reviews & Ratings:
Sample reviews with ratings from 4-5 stars

### Reading History:
User reading progress entries

---

## 🔄 More Options

### See Database Stats:
- **Web:** Visit `db_manager.php` and view the statistics section
- **CLI:** No CLI option, use web interface

### Create Backups:
- **Web:** Click "Create Backup" button in `db_manager.php`
- **CLI:** Manually backup MySQL database using MySQL Workbench

### Clear All Data:
- **Web:** Click "Clear Database" button (⚠️ careful!)
- **CLI:** Edit `setup_db_cli.php` line 13-18 - script always clears before setup

---

## 🎯 Recommended Workflow

1. **First Time Setup:**
   - Use either method - both work great
   - **Recommendation:** CLI for speed, Web for visibility

2. **Testing Features:**
   - Login with admin account
   - Browse books, make borrows, write reviews
   - Test all features

3. **Fresh Start:**
   - Run setup again (clears old data first)
   - Both methods automatically clear and repopulate

4. **Production Use:**
   - Create your own books via web interface
   - **Don't use demo data** in production
   - Delete `db_manager.php` and `setup_db_cli.php` before deploying

---

## ✨ Next Steps

**Choose your setup method and run it:**
- **Web Method:** Open `http://localhost/ai_lib/db_manager.php`
- **CLI Method:** Run the command above in terminal

**Then:**
1. Login with provided credentials
2. Explore the system
3. Test all features
4. Configure email if needed (see config/database.php)

---

## 🆘 Troubleshooting

**Error: "Database connection failed"**
- Ensure XAMPP MySQL is running
- Check `config/database.php` settings

**Table not found error**
- Run the setup script again - it creates tables automatically

**Still need help?**
- Check `QUICK_START.md` for detailed instructions
- Review `IMPLEMENTATION_SUMMARY.md` for feature overview

---

**Happy Library Managing! 📚**
