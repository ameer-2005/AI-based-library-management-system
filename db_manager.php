<?php
// Database initialization and sample data setup
include("config/database.php");

$message = '';
$error = '';

echo "<h2>📊 Database Management & Data Setup</h2>";

// Function to add data
function addData($conn, $table, $data) {
    $columns = implode(', ', array_keys($data));
    $values = implode(', ', array_fill(0, count($data), '?'));
    $sql = "INSERT INTO $table ($columns) VALUES ($values)";
    
    $stmt = $conn->prepare($sql);
    $types = str_repeat('s', count($data));
    $stmt->bind_param($types, ...array_values($data));
    
    return $stmt->execute();
}

// Handle setup button
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setup_demo_data'])){
    
    echo "<div class='alert alert-info'><strong>Setting up demo data...</strong></div>";
    
    // Clear existing data
    $tables = ['reservations', 'reviews', 'borrow_records', 'books', 'categories', 'users'];
    foreach($tables as $table) {
        $conn->query("DELETE FROM $table");
        echo "<p>✓ Cleared $table</p>";
    }
    
    // 1. Add Categories
    $categories = [
        ['name' => 'Fiction'],
        ['name' => 'Science Fiction'],
        ['name' => 'Fantasy'],
        ['name' => 'Mystery'],
        ['name' => 'Romance'],
        ['name' => 'Self-Help'],
        ['name' => 'History'],
        ['name' => 'Technology'],
        ['name' => 'Poetry'],
        ['name' => 'Children']
    ];
    
    $cat_ids = [];
    foreach($categories as $cat) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $cat['name']);
        if($stmt->execute()) {
            $cat_ids[$cat['name']] = $conn->insert_id;
            echo "<p>✓ Added category: {$cat['name']}</p>";
        }
        $stmt->close();
    }
    
    // 2. Add Users
    $users = [
        // Admins
        ['name' => 'Admin User', 'email' => 'admin@library.local', 'password' => password_hash('Admin@123', PASSWORD_BCRYPT), 'role' => 'admin'],
        
        // Regular Users
        ['name' => 'John Doe', 'email' => 'john@example.com', 'password' => password_hash('User@123', PASSWORD_BCRYPT), 'role' => 'user'],
        ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'password' => password_hash('User@123', PASSWORD_BCRYPT), 'role' => 'user'],
        ['name' => 'Michael Johnson', 'email' => 'michael@example.com', 'password' => password_hash('User@123', PASSWORD_BCRYPT), 'role' => 'user'],
        ['name' => 'Sarah Williams', 'email' => 'sarah@example.com', 'password' => password_hash('User@123', PASSWORD_BCRYPT), 'role' => 'user'],
        ['name' => 'David Brown', 'email' => 'david@example.com', 'password' => password_hash('User@123', PASSWORD_BCRYPT), 'role' => 'user'],
    ];
    
    $user_ids = [];
    foreach($users as $user) {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $user['name'], $user['email'], $user['password'], $user['role']);
        if($stmt->execute()) {
            $user_ids[$user['email']] = $conn->insert_id;
            echo "<p>✓ Added user: {$user['name']} ({$user['role']})</p>";
        }
        $stmt->close();
    }
    
    // 3. Add Books (40 books)
    $books = [
        // Fiction
        ['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'isbn' => '978-0743273565', 'published_year' => 1925, 'category' => 'Fiction', 'description' => 'A classic American novel about wealth and the American Dream', 'available' => 3],
        ['title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'isbn' => '978-0061120084', 'published_year' => 1960, 'category' => 'Fiction', 'description' => 'A gripping tale of racial injustice and innocence', 'available' => 2],
        ['title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'isbn' => '978-0141439518', 'published_year' => 1813, 'category' => 'Romance', 'description' => 'A timeless romance and social commentary', 'available' => 4],
        ['title' => 'The Catcher in the Rye', 'author' => 'J.D. Salinger', 'isbn' => '978-0316769174', 'published_year' => 1951, 'category' => 'Fiction', 'description' => 'A coming-of-age story in New York City', 'available' => 2],
        ['title' => 'Wuthering Heights', 'author' => 'Emily Brontë', 'isbn' => '978-0141439556', 'published_year' => 1847, 'category' => 'Fiction', 'description' => 'A dark and passionate Gothic novel', 'available' => 3],
        
        // Science Fiction
        ['title' => '1984', 'author' => 'George Orwell', 'isbn' => '978-0451524935', 'published_year' => 1949, 'category' => 'Science Fiction', 'description' => 'A dystopian novel about totalitarianism', 'available' => 4],
        ['title' => 'Brave New World', 'author' => 'Aldous Huxley', 'isbn' => '978-0060085345', 'published_year' => 1932, 'category' => 'Science Fiction', 'description' => 'A futuristic vision of a controlled society', 'available' => 5],
        ['title' => 'Foundation', 'author' => 'Isaac Asimov', 'isbn' => '978-0553293357', 'published_year' => 1951, 'category' => 'Science Fiction', 'description' => 'The dawn of a new galactic civilization', 'available' => 2],
        ['title' => 'Dune', 'author' => 'Frank Herbert', 'isbn' => '978-0441013593', 'published_year' => 1965, 'category' => 'Science Fiction', 'description' => 'An intricate space opera with politics and ecology', 'available' => 3],
        ['title' => 'Neuromancer', 'author' => 'William Gibson', 'isbn' => '978-0441569595', 'published_year' => 1984, 'category' => 'Science Fiction', 'description' => 'Pioneering cyberpunk novel', 'available' => 2],
        
        // Fantasy
        ['title' => 'The Hobbit', 'author' => 'J.R.R. Tolkien', 'isbn' => '978-0547928227', 'published_year' => 1937, 'category' => 'Fantasy', 'description' => 'An epic fantasy adventure', 'available' => 4],
        ['title' => 'The Lord of the Rings', 'author' => 'J.R.R. Tolkien', 'isbn' => '978-0544003415', 'published_year' => 1954, 'category' => 'Fantasy', 'description' => 'The ultimate fantasy epic', 'available' => 6],
        ['title' => 'A Game of Thrones', 'author' => 'George R.R. Martin', 'isbn' => '978-0553103540', 'published_year' => 1996, 'category' => 'Fantasy', 'description' => 'Epic fantasy with complex characters', 'available' => 3],
        ['title' => "Harry Potter and the Philosopher's Stone", 'author' => 'J.K. Rowling', 'isbn' => '978-0747532699', 'published_year' => 1998, 'category' => 'Children', 'description' => 'The beginning of the wizarding world', 'available' => 5],
        ['title' => 'The Name of the Wind', 'author' => 'Patrick Rothfuss', 'isbn' => '978-0575081406', 'published_year' => 2007, 'category' => 'Fantasy', 'description' => 'The tale of Kvothe the legendary wizard', 'available' => 2],
        
        // Mystery
        ['title' => 'The Girl with the Dragon Tattoo', 'author' => 'Stieg Larsson', 'isbn' => '978-0307454546', 'published_year' => 2005, 'category' => 'Mystery', 'description' => 'A gripping Swedish crime investigation', 'available' => 2],
        ['title' => 'The Da Vinci Code', 'author' => 'Dan Brown', 'isbn' => '978-0307474278', 'published_year' => 2003, 'category' => 'Mystery', 'description' => 'A thrilling coded mystery adventure', 'available' => 4],
        ['title' => 'Murder on the Orient Express', 'author' => 'Agatha Christie', 'isbn' => '978-0062073556', 'published_year' => 1934, 'category' => 'Mystery', 'description' => 'A classic locked-room mystery', 'available' => 3],
        ['title' => 'The Maltese Falcon', 'author' => 'Dashiell Hammett', 'isbn' => '978-0679722656', 'published_year' => 1930, 'category' => 'Mystery', 'description' => 'Classic noir detective fiction', 'available' => 2],
        
        // Self-Help
        ['title' => 'Thinking, Fast and Slow', 'author' => 'Daniel Kahneman', 'isbn' => '978-0374533557', 'published_year' => 2011, 'category' => 'Self-Help', 'description' => 'Understanding how we think and decide', 'available' => 3],
        ['title' => 'Atomic Habits', 'author' => 'James Clear', 'isbn' => '978-0735211292', 'published_year' => 2018, 'category' => 'Self-Help', 'description' => 'Small changes, remarkable results', 'available' => 5],
        ['title' => 'The 7 Habits of Highly Effective People', 'author' => 'Stephen Covey', 'isbn' => '978-0743269513', 'published_year' => 1989, 'category' => 'Self-Help', 'description' => 'A guide to personal effectiveness', 'available' => 4],
        
        // History
        ['title' => 'Sapiens', 'author' => 'Yuval Noah Harari', 'isbn' => '978-0062316097', 'published_year' => 2011, 'category' => 'History', 'description' => 'A brief history of humankind', 'available' => 4],
        ['title' => 'The Silk Road', 'author' => 'Peter Frankopan', 'isbn' => '978-1620402993', 'published_year' => 2015, 'category' => 'History', 'description' => 'A new history of the world', 'available' => 2],
        
        // Technology
        ['title' => 'Clean Code', 'author' => 'Robert Martin', 'isbn' => '978-0132350884', 'published_year' => 2008, 'category' => 'Technology', 'description' => 'A handbook of agile software craftsmanship', 'available' => 3],
        ['title' => 'The Pragmatic Programmer', 'author' => 'David Thomas', 'isbn' => '978-0135957059', 'published_year' => 1999, 'category' => 'Technology', 'description' => 'Your journey to mastery in programming', 'available' => 2],
        
        // Additional books for variety
        ['title' => 'The Hobbit: The Desolation of Smaug', 'author' => 'J.R.R. Tolkien', 'isbn' => '978-0547928234', 'published_year' => 1954, 'category' => 'Fantasy', 'description' => 'The continuing adventure', 'available' => 2],
        ['title' => 'The Silmarillion', 'author' => 'J.R.R. Tolkien', 'isbn' => '978-0544003407', 'published_year' => 1977, 'category' => 'Fantasy', 'description' => 'The mythopoeic history of Middle-earth', 'available' => 1],
        ['title' => 'Cloud Atlas', 'author' => 'David Mitchell', 'isbn' => '978-0375507259', 'published_year' => 2004, 'category' => 'Fiction', 'description' => 'Six interconnected stories across time', 'available' => 2],
        ['title' => 'The Count of Monte Cristo', 'author' => 'Alexandre Dumas', 'isbn' => '978-0140449266', 'published_year' => 1844, 'category' => 'Fiction', 'description' => 'A tale of betrayal and revenge', 'available' => 3],
        ['title' => 'Moby Dick', 'author' => 'Herman Melville', 'isbn' => '978-0142437247', 'published_year' => 1851, 'category' => 'Fiction', 'description' => 'The great white whale hunt', 'available' => 2],
        ['title' => 'Jane Eyre', 'author' => 'Charlotte Brontë', 'isbn' => '978-0141441146', 'published_year' => 1847, 'category' => 'Romance', 'description' => 'Gothic romance and social commentary', 'available' => 3],
        ['title' => 'The Great Expectations', 'author' => 'Charles Dickens', 'isbn' => '978-0141439556', 'published_year' => 1861, 'category' => 'Fiction', 'description' => 'A tale of ambition and redemption', 'available' => 2],
        ['title' => 'Sherlock Holmes: Complete Collection', 'author' => 'Arthur Conan Doyle', 'isbn' => '978-0517220818', 'published_year' => 1892, 'category' => 'Mystery', 'description' => 'The complete detective stories', 'available' => 4],
        ['title' => 'The Odyssey', 'author' => 'Homer', 'isbn' => '978-0140268867', 'published_year' => -800, 'category' => 'Fiction', 'description' => 'Ancient epic of adventure and return', 'available' => 2],
        ['title' => 'Poems of William Blake', 'author' => 'William Blake', 'isbn' => '978-0486271156', 'published_year' => 1794, 'category' => 'Poetry', 'description' => 'Romantic poetry and visionary verse', 'available' => 1],
    ];
    
    $book_ids = [];
    foreach($books as $book) {
        $cat_id = $cat_ids[$book['category']] ?? 1;
        $stmt = $conn->prepare("INSERT INTO books (title, author, category_id, description, available) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $book['title'], $book['author'], $cat_id, $book['description'], $book['available']);
        if($stmt->execute()) {
            $book_ids[$book['isbn']] = $conn->insert_id;
        }
        $stmt->close();
    }
    echo "<p>✓ Added " . count($books) . " books</p>";
    
    // 4. Add some borrow records
    $issue_date_1 = date('Y-m-d', strtotime('-20 days'));
    $due_date_1 = date('Y-m-d', strtotime('-5 days'));
    $return_date_1 = date('Y-m-d', strtotime('-3 days'));
    
    $issue_date_2 = date('Y-m-d', strtotime('-10 days'));
    $due_date_2 = date('Y-m-d', strtotime('+5 days'));
    
    $borrow_records = [
        ['user_id' => $user_ids['john@example.com'], 'book_id' => $book_ids['978-0743273565'], 'issue_date' => $issue_date_2, 'due_date' => $due_date_2, 'status' => 'issued', 'extend_requested' => 0],
        ['user_id' => $user_ids['jane@example.com'], 'book_id' => $book_ids['978-0061120084'], 'issue_date' => $issue_date_2, 'due_date' => $due_date_2, 'status' => 'issued', 'extend_requested' => 0],
        ['user_id' => $user_ids['michael@example.com'], 'book_id' => $book_ids['978-0547928227'], 'issue_date' => $issue_date_1, 'due_date' => $due_date_1, 'return_date' => $return_date_1, 'status' => 'returned', 'extend_requested' => 0, 'fine_amount' => 50],
    ];
    
    foreach($borrow_records as $record) {
        $stmt = $conn->prepare("INSERT INTO borrow_records (user_id, book_id, issue_date, due_date, return_date, status, extend_requested, fine_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $return_date = $record['return_date'] ?? NULL;
        $fine = $record['fine_amount'] ?? 0;
        $stmt->bind_param("iisssiii", $record['user_id'], $record['book_id'], $record['issue_date'], $record['due_date'], $return_date, $record['status'], $record['extend_requested'], $fine);
        $stmt->execute();
        $stmt->close();
    }
    echo "<p>✓ Added borrow records</p>";
    
    // 5. Add some reviews and ratings
    $reviews = [
        ['book_id' => $book_ids['978-0743273565'], 'user_id' => $user_ids['john@example.com'], 'rating' => 5, 'review' => 'A masterpiece of American literature. Fitzgerald captures the era perfectly.'],
        ['book_id' => $book_ids['978-0061120084'], 'user_id' => $user_ids['jane@example.com'], 'rating' => 5, 'review' => 'Powerful and moving. A must-read classic.'],
        ['book_id' => $book_ids['978-0547928227'], 'user_id' => $user_ids['michael@example.com'], 'rating' => 4, 'review' => 'A wonderful adventure. Great for all ages.'],
        ['book_id' => $book_ids['978-0141439518'], 'user_id' => $user_ids['sarah@example.com'], 'rating' => 5, 'review' => 'One of the greatest love stories ever written.'],
        ['book_id' => $book_ids['978-0451524935'], 'user_id' => $user_ids['david@example.com'], 'rating' => 4, 'review' => 'Chilling and thought-provoking. Relevant even today.'],
    ];
    
    foreach($reviews as $review) {
        $stmt = $conn->prepare("INSERT INTO reviews (book_id, user_id, rating, review, review_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iis", $review['book_id'], $review['user_id'], $review['rating'], $review['review']);
        $stmt->execute();
        $stmt->close();
    }
    echo "<p>✓ Added book reviews and ratings</p>";
    
    // 6. Add reading history
    $reading_history = [
        ['user_id' => $user_ids['john@example.com'], 'book_id' => $book_ids['978-0743273565'], 'pages_read' => 180],
        ['user_id' => $user_ids['jane@example.com'], 'book_id' => $book_ids['978-0061120084'], 'pages_read' => 281],
        ['user_id' => $user_ids['michael@example.com'], 'book_id' => $book_ids['978-0547928227'], 'pages_read' => 310],
    ];
    
    foreach($reading_history as $history) {
        $stmt = $conn->prepare("INSERT INTO reading_history (user_id, book_id, pages_read, read_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iii", $history['user_id'], $history['book_id'], $history['pages_read']);
        $stmt->execute();
        $stmt->close();
    }
    echo "<p>✓ Added reading history records</p>";
    
    // 7. Set up fines configuration
    $conn->query("DELETE FROM fines_config");
    $stmt = $conn->prepare("INSERT INTO fines_config (daily_fine, max_books) VALUES (?, ?)");
    $daily_fine = 10;
    $max_books = 5;
    $stmt->bind_param("ii", $daily_fine, $max_books);
    $stmt->execute();
    $stmt->close();
    echo "<p>✓ Set up fines configuration (₹10/day)</p>";
    
    echo "<div class='alert alert-success mt-3'>
        <h4>✅ Database Setup Complete!</h4>
        <hr>
        <h5>Sample Login Credentials:</h5>
        <p><strong>Admin Account:</strong><br>
        Email: admin@library.local<br>
        Password: Admin@123</p>
        <p><strong>User Accounts:</strong><br>
        Email: john@example.com<br>
        Email: jane@example.com<br>
        Email: michael@example.com<br>
        Password: User@123 (for all users)</p>
        <hr>
        <p><strong>Demo Data Added:</strong><br>
        • 10 Categories<br>
        • 6 Users (1 admin, 5 regular users)<br>
        • 40 Books<br>
        • 3 Borrow Records<br>
        • 5 Book Reviews<br>
        • 3 Reading History Records</p>
    </div>";
    
} elseif($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['backup_database'])){
    
    // Create backup
    $backup_file = 'backups/ai_lib_backup_' . date('Y-m-d_H-i-s') . '.sql';
    if(!is_dir('backups')) mkdir('backups', 0777, true);
    
    // Using mysqldump (requires system command)
    $command = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" -u root ai_lib_db > \"$backup_file\"";
    $output = shell_exec($command);
    
    echo "<div class='alert alert-success'>✓ Database backed up to: $backup_file</div>";
    
} elseif($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_database'])){
    
    $tables = ['reservations', 'reviews', 'reading_history', 'borrow_records', 'books', 'categories', 'users'];
    foreach($tables as $table) {
        $conn->query("DELETE FROM $table");
    }
    echo "<div class='alert alert-warning'>✓ All data cleared from database</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { margin: 20px 0; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>📊 AI Library Database Manager</h1>
            <p class="text-muted">Setup, backup, and manage your library database</p>
        </div>
    </div>
    
    <!-- Database Info Card -->
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📈 Database Status</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                // Get database statistics
                $stats = [
                    'Users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
                    'Books' => $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'],
                    'Categories' => $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'],
                    'Borrow Records' => $conn->query("SELECT COUNT(*) as count FROM borrow_records")->fetch_assoc()['count'],
                    'Reviews' => $conn->query("SELECT COUNT(*) as count FROM reviews")->fetch_assoc()['count'],
                    'Reading History' => $conn->query("SELECT COUNT(*) as count FROM reading_history")->fetch_assoc()['count'],
                ];
                
                foreach($stats as $label => $count):
                ?>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted"><?php echo $label; ?></h6>
                            <h2 class="text-primary"><?php echo $count; ?></h2>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Setup Actions Card -->
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">⚙️ Database Operations</h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="POST" class="mb-3">
                        <h6>1. Setup Demo Data</h6>
                        <p class="text-muted small">Create sample data for testing (10 categories, 6 users, 40 books, etc.)</p>
                        <button type="submit" name="setup_demo_data" class="btn btn-success" onclick="return confirm('This will replace existing data. Continue?');">
                            <i class="bi bi-plus-circle"></i> Setup Demo Data
                        </button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form method="POST" class="mb-3">
                        <h6>2. Backup Database</h6>
                        <p class="text-muted small">Create a backup of your current database</p>
                        <button type="submit" name="backup_database" class="btn btn-info">
                            <i class="bi bi-cloud-download"></i> Create Backup
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" class="mb-3">
                        <h6>3. Clear Database</h6>
                        <p class="text-muted small">Remove all data from database (use with caution!)</p>
                        <button type="submit" name="clear_database" class="btn btn-danger" onclick="return confirm('Are you SURE? This is irreversible!');  ">
                            <i class="bi bi-trash"></i> Clear All Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Book Management Card -->
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">📚 Book Management</h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <a href="admin/add_book.php" class="btn btn-success w-100 mb-2">
                        <i class="bi bi-plus-circle"></i> Add Book
                    </a>
                    <p class="text-muted small">Add a new book to the library</p>
                </div>
                <div class="col-md-4">
                    <a href="admin/manage_books.php" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-pencil-square"></i> Edit Books
                    </a>
                    <p class="text-muted small">Edit or delete existing books</p>
                </div>
                <div class="col-md-4">
                    <a href="admin/bulk_import.php" class="btn btn-info w-100 mb-2">
                        <i class="bi bi-upload"></i> Bulk Import
                    </a>
                    <p class="text-muted small">Import books from CSV file</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Info Card -->
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">ℹ️ Test Credentials</h5>
        </div>
        <div class="card-body">
            <h6>Admin Account</h6>
            <code>Email: admin@library.local</code><br>
            <code>Password: Admin@123</code>
            <hr>
            
            <h6>User Accounts (All share same password)</h6>
            <code>Password: User@123</code>
            <ul>
                <li>john@example.com</li>
                <li>jane@example.com</li>
                <li>michael@example.com</li>
                <li>sarah@example.com</li>
                <li>david@example.com</li>
            </ul>
        </div>
    </div>
    
    <!-- Sample Data Info -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📚 Sample Data Included</h5>
        </div>
        <div class="card-body">
            <h6>Categories (10)</h6>
            <p class="text-muted small">Fiction, Science Fiction, Fantasy, Mystery, Romance, Self-Help, History, Technology, Poetry, Children</p>
            
            <h6>Books (40)</h6>
            <p class="text-muted small">The Great Gatsby, To Kill a Mockingbird, 1984, The Hobbit, Harry Potter, Sherlock Holmes, and 34 more classic and modern books</p>
            
            <h6>Features</h6>
            <ul class="small text-muted">
                <li>✓ Books with ISBNs and published years</li>
                <li>✓ Sample borrow records (some due, some returned)</li>
                <li>✓ Book reviews and ratings</li>
                <li>✓ User reading history</li>
                <li>✓ Fine calculations</li>
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
