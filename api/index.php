<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include("config/database.php");

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function validateApiToken($conn) {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    
    if(empty($token)){
        sendResponse(['error' => 'Missing API token'], 401);
    }
    
    $token = str_replace('Bearer ', '', $token);
    
    $stmt = $conn->prepare("
        SELECT user_id, expires_at FROM api_tokens 
        WHERE token = ? AND (expires_at IS NULL OR expires_at > NOW())
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 0){
        sendResponse(['error' => 'Invalid or expired token'], 401);
    }
    
    $token_data = $result->fetch_assoc();
    
    // Update last_used
    $update_stmt = $conn->prepare("UPDATE api_tokens SET last_used = NOW() WHERE token = ?");
    $update_stmt->bind_param("s", $token);
    $update_stmt->execute();
    $update_stmt->close();
    
    $stmt->close();
    return $token_data['user_id'];
}

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', trim($path, '/'));

// API routes
$endpoint = end($parts);

try {
    switch($endpoint) {
        
        // Public endpoints (no auth required)
        case 'books':
            if($method == 'GET'){
                $page = $_GET['page'] ?? 1;
                $limit = min(intval($_GET['limit'] ?? 20), 100);
                $offset = ($page - 1) * $limit;
                
                $result = $conn->query("
                    SELECT b.*, bc.cover_path, c.name as category_name,
                           (SELECT AVG(rating) FROM reviews WHERE book_id = b.id) as avg_rating
                    FROM books b
                    LEFT JOIN book_covers bc ON b.id = bc.book_id
                    LEFT JOIN categories c ON b.category_id = c.id
                    LIMIT $limit OFFSET $offset
                ");
                
                $books = [];
                while($row = mysqli_fetch_assoc($result)){
                    $books[] = $row;
                }
                
                sendResponse(['data' => $books, 'page' => $page, 'limit' => $limit]);
            }
            break;
            
        case 'Book':
            if($method == 'GET'){
                $book_id = intval($_GET['id']);
                
                $stmt = $conn->prepare("
                    SELECT b.*, bc.cover_path, c.name as category_name,
                           (SELECT AVG(rating) FROM reviews WHERE book_id = b.id) as avg_rating,
                           (SELECT COUNT(*) FROM reviews WHERE book_id = b.id) as review_count
                    FROM books b
                    LEFT JOIN book_covers bc ON b.id = bc.book_id
                    LEFT JOIN categories c ON b.category_id = c.id
                    WHERE b.id = ?
                ");
                $stmt->bind_param("i", $book_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows == 0){
                    sendResponse(['error' => 'Book not found'], 404);
                }
                
                $book = $result->fetch_assoc();
                sendResponse(['data' => $book]);
            }
            break;
            
        case 'search':
            if($method == 'GET'){
                $query = $_GET['q'] ?? '';
                $type = $_GET['type'] ?? 'title';
                
                if(strlen($query) < 2){
                    sendResponse(['error' => 'Search query too short'], 400);
                }
                
                $query = '%' . $query . '%';
                $allowed_types = ['title', 'author', 'isbn'];
                $type = in_array($type, $allowed_types) ? $type : 'title';
                
                $stmt = $conn->prepare("
                    SELECT id, title, author, category_id, description
                    FROM books
                    WHERE $type LIKE ?
                    LIMIT 20
                ");
                $stmt->bind_param("s", $query);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $results = [];
                while($row = $result->fetch_assoc()){
                    $results[] = $row;
                }
                
                sendResponse(['data' => $results]);
            }
            break;
            
        case 'categories':
            if($method == 'GET'){
                $result = $conn->query("SELECT id, name FROM categories ORDER BY name");
                
                $categories = [];
                while($row = mysqli_fetch_assoc($result)){
                    $categories[] = $row;
                }
                
                sendResponse(['data' => $categories]);
            }
            break;
            
        // Protected endpoints (auth required)
        case 'user_books':
            $user_id = validateApiToken($conn);
            
            if($method == 'GET'){
                $result = $conn->query("
                    SELECT b.*, br.due_date, br.status
                    FROM books b
                    JOIN borrow_records br ON b.id = br.book_id
                    WHERE br.user_id = $user_id AND br.status != 'returned'
                ");
                
                $books = [];
                while($row = mysqli_fetch_assoc($result)){
                    $books[] = $row;
                }
                
                sendResponse(['data' => $books]);
            }
            break;
            
        case 'reserves':
            $user_id = validateApiToken($conn);
            
            if($method == 'GET'){
                $result = $conn->query("
                    SELECT r.*, b.title, b.author
                    FROM reservations r
                    JOIN books b ON r.book_id = b.id
                    WHERE r.user_id = $user_id AND r.status = 'active'
                ");
                
                $reserves = [];
                while($row = mysqli_fetch_assoc($result)){
                    $reserves[] = $row;
                }
                
                sendResponse(['data' => $reserves]);
            }
            break;
            
        case 'reading_history':
            $user_id = validateApiToken($conn);
            
            if($method == 'GET'){
                $limit = min(intval($_GET['limit'] ?? 20), 100);
                
                $result = $conn->query("
                    SELECT rh.*, b.title, b.author
                    FROM reading_history rh
                    JOIN books b ON rh.book_id = b.id
                    WHERE rh.user_id = $user_id
                    ORDER BY rh.read_date DESC
                    LIMIT $limit
                ");
                
                $history = [];
                while($row = mysqli_fetch_assoc($result)){
                    $history[] = $row;
                }
                
                sendResponse(['data' => $history]);
            }
            break;
            
        default:
            sendResponse(['error' => 'Endpoint not found'], 404);
    }
    
} catch(Exception $e) {
    sendResponse(['error' => $e->getMessage()], 500);
}
?>
