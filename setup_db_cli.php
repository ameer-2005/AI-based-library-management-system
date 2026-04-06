<?php
// CLI Database Setup Script - Run from command line
// Usage: php setup_db_cli.php

include("config/database.php");

echo "\n=================================================\n";
echo "  📊 AI Library Database Setup\n";
echo "=================================================\n\n";

// Check database connection
if(!$conn) {
    echo "❌ Database connection failed!\n";
    exit(1);
}

echo "✅ Database connection successful\n\n";

// Clear existing data
echo "Clearing existing data...\n";
$tables = ['reservations', 'reviews', 'reading_history', 'borrow_records', 'books', 'categories', 'users', 'fines_config'];
foreach($tables as $table) {
    $conn->query("DELETE FROM $table");
    echo "  ✓ Cleared $table\n";
}

echo "\n1️⃣  Adding Categories...\n";
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
    $stmt->execute();
    $cat_ids[$cat['name']] = $conn->insert_id;
    echo "  ✓ {$cat['name']}\n";
    $stmt->close();
}

echo "\n2️⃣  Adding Users...\n";
$users = [
    ['name' => 'Admin User', 'email' => 'admin@library.local', 'password' => password_hash('Admin@123', PASSWORD_BCRYPT), 'role' => 'admin', 'email_verified' => 1],
    ['name' => 'John Doe', 'email' => 'john@example.com', 'password' => password_hash('User@123', PASSWORD_BCRYPT), 'role' => 'user', 'email_verified' => 1],
    ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'password' => password_hash('User@123', PASSWORD_BCRYPT), 'role' => 'user', 'email_verified' => 1],
    ['name' => 'Michael Johnson', 'email' => 'michael@example.com', 'password' => password_hash('User@123', PASSWORD_BCRYPT), 'role' => 'user', 'email_verified' => 1],
    ['name' => 'Sarah Williams', 'email' => 'sarah@example.com', 'password' => password_hash('User@123', PASSWORD_BCRYPT), 'role' => 'user', 'email_verified' => 1],
    ['name' => 'David Brown', 'email' => 'david@example.com', 'password' => password_hash('User@123', PASSWORD_BCRYPT), 'role' => 'user', 'email_verified' => 1],
];

$user_ids = [];
foreach($users as $user) {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, email_verified) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $user['name'], $user['email'], $user['password'], $user['role'], $user['email_verified']);
    $stmt->execute();
    $user_ids[$user['email']] = $conn->insert_id;
    echo "  ✓ {$user['name']} ({$user['role']})\n";
    $stmt->close();
}

echo "\n3️⃣  Adding Books (40 books)...\n";
$books = [
    ['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'isbn' => '978-0743273565', 'published_year' => 1925, 'category' => 'Fiction', 'description' => 'A classic American novel about wealth and the American Dream', 'available' => 3],
    ['title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'isbn' => '978-0061120084', 'published_year' => 1960, 'category' => 'Fiction', 'description' => 'A gripping tale of racial injustice and innocence', 'available' => 2],
    ['title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'isbn' => '978-0141439518', 'published_year' => 1813, 'category' => 'Romance', 'description' => 'A timeless romance and social commentary', 'available' => 4],
    ['title' => 'The Catcher in the Rye', 'author' => 'J.D. Salinger', 'isbn' => '978-0316769174', 'published_year' => 1951, 'category' => 'Fiction', 'description' => 'A coming-of-age story in New York City', 'available' => 2],
    ['title' => 'Wuthering Heights', 'author' => 'Emily Brontë', 'isbn' => '978-0141439556', 'published_year' => 1847, 'category' => 'Fiction', 'description' => 'A dark and passionate Gothic novel', 'available' => 3],
    ['title' => '1984', 'author' => 'George Orwell', 'isbn' => '978-0451524935', 'published_year' => 1949, 'category' => 'Science Fiction', 'description' => 'A dystopian novel about totalitarianism', 'available' => 4],
    ['title' => 'Brave New World', 'author' => 'Aldous Huxley', 'isbn' => '978-0060085345', 'published_year' => 1932, 'category' => 'Science Fiction', 'description' => 'A futuristic vision of a controlled society', 'available' => 5],
    ['title' => 'Foundation', 'author' => 'Isaac Asimov', 'isbn' => '978-0553293357', 'published_year' => 1951, 'category' => 'Science Fiction', 'description' => 'The dawn of a new galactic civilization', 'available' => 2],
    ['title' => 'Dune', 'author' => 'Frank Herbert', 'isbn' => '978-0441013593', 'published_year' => 1965, 'category' => 'Science Fiction', 'description' => 'An intricate space opera with politics and ecology', 'available' => 3],
    ['title' => 'Neuromancer', 'author' => 'William Gibson', 'isbn' => '978-0441569595', 'published_year' => 1984, 'category' => 'Science Fiction', 'description' => 'Pioneering cyberpunk novel', 'available' => 2],
    ['title' => 'The Hobbit', 'author' => 'J.R.R. Tolkien', 'isbn' => '978-0547928227', 'published_year' => 1937, 'category' => 'Fantasy', 'description' => 'An epic fantasy adventure', 'available' => 4],
    ['title' => 'The Lord of the Rings', 'author' => 'J.R.R. Tolkien', 'isbn' => '978-0544003415', 'published_year' => 1954, 'category' => 'Fantasy', 'description' => 'The ultimate fantasy epic', 'available' => 6],
    ['title' => 'A Game of Thrones', 'author' => 'George R.R. Martin', 'isbn' => '978-0553103540', 'published_year' => 1996, 'category' => 'Fantasy', 'description' => 'Epic fantasy with complex characters', 'available' => 3],
    ['title' => "Harry Potter and the Philosopher's Stone", 'author' => 'J.K. Rowling', 'isbn' => '978-0747532699', 'published_year' => 1998, 'category' => 'Children', 'description' => 'The beginning of the wizarding world', 'available' => 5],
    ['title' => 'The Name of the Wind', 'author' => 'Patrick Rothfuss', 'isbn' => '978-0575081406', 'published_year' => 2007, 'category' => 'Fantasy', 'description' => 'The tale of Kvothe the legendary wizard', 'available' => 2],
    ['title' => 'The Girl with the Dragon Tattoo', 'author' => 'Stieg Larsson', 'isbn' => '978-0307454546', 'published_year' => 2005, 'category' => 'Mystery', 'description' => 'A gripping Swedish crime investigation', 'available' => 2],
    ['title' => 'The Da Vinci Code', 'author' => 'Dan Brown', 'isbn' => '978-0307474278', 'published_year' => 2003, 'category' => 'Mystery', 'description' => 'A thrilling coded mystery adventure', 'available' => 4],
    ['title' => 'Murder on the Orient Express', 'author' => 'Agatha Christie', 'isbn' => '978-0062073556', 'published_year' => 1934, 'category' => 'Mystery', 'description' => 'A classic locked-room mystery', 'available' => 3],
    ['title' => 'The Maltese Falcon', 'author' => 'Dashiell Hammett', 'isbn' => '978-0679722656', 'published_year' => 1930, 'category' => 'Mystery', 'description' => 'Classic noir detective fiction', 'available' => 2],
    ['title' => 'Thinking, Fast and Slow', 'author' => 'Daniel Kahneman', 'isbn' => '978-0374533557', 'published_year' => 2011, 'category' => 'Self-Help', 'description' => 'Understanding how we think and decide', 'available' => 3],
    ['title' => 'Atomic Habits', 'author' => 'James Clear', 'isbn' => '978-0735211292', 'published_year' => 2018, 'category' => 'Self-Help', 'description' => 'Small changes, remarkable results', 'available' => 5],
    ['title' => 'The 7 Habits of Highly Effective People', 'author' => 'Stephen Covey', 'isbn' => '978-0743269513', 'published_year' => 1989, 'category' => 'Self-Help', 'description' => 'A guide to personal effectiveness', 'available' => 4],
    ['title' => 'Sapiens', 'author' => 'Yuval Noah Harari', 'isbn' => '978-0062316097', 'published_year' => 2011, 'category' => 'History', 'description' => 'A brief history of humankind', 'available' => 4],
    ['title' => 'The Silk Road', 'author' => 'Peter Frankopan', 'isbn' => '978-1620402993', 'published_year' => 2015, 'category' => 'History', 'description' => 'A new history of the world', 'available' => 2],
    ['title' => 'Clean Code', 'author' => 'Robert Martin', 'isbn' => '978-0132350884', 'published_year' => 2008, 'category' => 'Technology', 'description' => 'A handbook of agile software craftsmanship', 'available' => 3],
    ['title' => 'The Pragmatic Programmer', 'author' => 'David Thomas', 'isbn' => '978-0135957059', 'published_year' => 1999, 'category' => 'Technology', 'description' => 'Your journey to mastery in programming', 'available' => 2],
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
$bookCount = 0;
foreach($books as $book) {
    $cat_id = $cat_ids[$book['category']] ?? 1;
    $stmt = $conn->prepare("INSERT INTO books (title, author, category_id, description, available) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $book['title'], $book['author'], $cat_id, $book['description'], $book['available']);
    if($stmt->execute()) {
        $book_ids[$book['isbn']] = $conn->insert_id;
        $bookCount++;
    }
    $stmt->close();
}
echo "  ✓ Added $bookCount books\n";

echo "\n4️⃣  Adding Borrow Records...\n";
$issue_date_2 = date('Y-m-d', strtotime('-10 days'));
$due_date_2 = date('Y-m-d', strtotime('+5 days'));

$borrow_records = [
    ['user_id' => $user_ids['john@example.com'], 'book_id' => $book_ids['978-0743273565'], 'issue_date' => $issue_date_2, 'due_date' => $due_date_2, 'status' => 'issued'],
    ['user_id' => $user_ids['jane@example.com'], 'book_id' => $book_ids['978-0061120084'], 'issue_date' => $issue_date_2, 'due_date' => $due_date_2, 'status' => 'issued'],
];

foreach($borrow_records as $record) {
    $stmt = $conn->prepare("INSERT INTO borrow_records (user_id, book_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $record['user_id'], $record['book_id'], $record['issue_date'], $record['due_date'], $record['status']);
    $stmt->execute();
    $stmt->close();
    echo "  ✓ Borrow record created\n";
}

echo "\n5️⃣  Adding Reviews and Ratings...\n";
$reviews = [
    ['book_id' => $book_ids['978-0743273565'], 'user_id' => $user_ids['john@example.com'], 'rating' => 5, 'review' => 'A masterpiece of American literature.'],
    ['book_id' => $book_ids['978-0061120084'], 'user_id' => $user_ids['jane@example.com'], 'rating' => 5, 'review' => 'Powerful and moving. A must-read classic.'],
    ['book_id' => $book_ids['978-0547928227'], 'user_id' => $user_ids['michael@example.com'], 'rating' => 4, 'review' => 'A wonderful adventure. Great for all ages.'],
];

foreach($reviews as $review) {
    $stmt = $conn->prepare("INSERT INTO reviews (book_id, user_id, rating, review, review_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiss", $review['book_id'], $review['user_id'], $review['rating'], $review['review']);
    $stmt->execute();
    $stmt->close();
    echo "  ✓ Review added\n";
}

echo "\n6️⃣  Adding Reading History...\n";
$reading_history = [
    ['user_id' => $user_ids['john@example.com'], 'book_id' => $book_ids['978-0743273565'], 'pages_read' => 180],
    ['user_id' => $user_ids['jane@example.com'], 'book_id' => $book_ids['978-0061120084'], 'pages_read' => 281],
];

foreach($reading_history as $history) {
    $stmt = $conn->prepare("INSERT INTO reading_history (user_id, book_id, pages_read, read_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iii", $history['user_id'], $history['book_id'], $history['pages_read']);
    $stmt->execute();
    $stmt->close();
    echo "  ✓ Reading history added\n";
}

echo "\n7️⃣  Setting up Fines Configuration...\n";
$stmt = $conn->prepare("INSERT INTO fines_config (daily_fine, max_books) VALUES (?, ?)");
$daily_fine = 10;
$max_books = 5;
$stmt->bind_param("ii", $daily_fine, $max_books);
$stmt->execute();
$stmt->close();
echo "  ✓ Daily fine set to ₹$daily_fine\n";
echo "  ✓ Max books per user: $max_books\n";

echo "\n=================================================\n";
echo "  ✅ DATABASE SETUP COMPLETE!\n";
echo "=================================================\n\n";

echo "📚 Sample Data Added:\n";
echo "  • Categories: " . count($categories) . "\n";
echo "  • Users: " . count($users) . " (1 admin, " . (count($users)-1) . " regular users)\n";
echo "  • Books: " . $bookCount . "\n";
echo "  • Borrow Records: " . count($borrow_records) . "\n";
echo "  • Reviews: " . count($reviews) . "\n";
echo "  • Reading History: " . count($reading_history) . "\n";

echo "\n🔐 Test Login Credentials:\n";
echo "  Admin:  admin@library.local | Admin@123\n";
echo "  Users:  john@example.com   | User@123\n";
echo "          jane@example.com    | User@123\n";
echo "          michael@example.com | User@123\n";
echo "          sarah@example.com   | User@123\n";
echo "          david@example.com   | User@123\n";

echo "\n✨ Ready to use! Login at: http://localhost/ai_lib/auth/login.php\n\n";
?>
