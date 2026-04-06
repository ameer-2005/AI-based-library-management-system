<?php
// ensure session started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("../config/database.php");

// chat history stored in session
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

$userMessage = "";
$botResponse = "";
// default quick suggestions visible from the start
$suggestions = ['trending books','recommend books','search AI books','help'];

// helper functions for database queries
function searchBooks($conn, $term) {
    $termLike = "%" . $term . "%";
    $stmt = $conn->prepare(
        "SELECT b.*, c.name AS category_name
         FROM books b
         LEFT JOIN categories c ON b.category_id = c.id
         WHERE b.title LIKE ? OR b.author LIKE ? OR c.name LIKE ?"
    );
    $stmt->bind_param('sss', $termLike, $termLike, $termLike);
    $stmt->execute();
    return $stmt->get_result();
}

function getTrending($conn, $limit = 3) {
    $sql = "SELECT b.title, b.author, COUNT(br.id) as borrow_count
            FROM borrow_records br
            JOIN books b ON br.book_id = b.id
            GROUP BY b.id
            ORDER BY borrow_count DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result();
}

function getPersonalReco($conn, $userId) {
    $sql = "SELECT b.category_id, COUNT(*) as cnt
            FROM borrow_records br
            JOIN books b ON br.book_id=b.id
            WHERE br.user_id = ?
            GROUP BY b.category_id
            ORDER BY cnt DESC
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $cat = $row['category_id'];
        $sql2 = "SELECT * FROM books WHERE category_id = ?
                 AND id NOT IN (SELECT book_id FROM borrow_records WHERE user_id = ?)
                 LIMIT 3";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param('ii', $cat, $userId);
        $stmt2->execute();
        return $stmt2->get_result();
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message'])) {
    $userMessage = trim($_POST['message']);
    $_SESSION['last_user'] = $userMessage;

    // prepare cleaned message for pattern matching
    $cleanMessage = strtolower($userMessage);
    $cleanMessage = preg_replace("/[^a-z0-9\s]/", "", $cleanMessage);

    // default suggestions that appear as quick buttons
    $suggestions = [
        'trending books',
        'recommend books',
        'search AI books',
        'help'
    ];

    // intent detection sequence with flexible/fuzzy matching
    $matched = false;
    // greet
    if (preg_match('/\b(hi|hello|hey|good morn|good after|good even|greetings|howdy)\b/', $cleanMessage)) {
        $botResponse = "👋 Hello! I'm your friendly AI Library Assistant. Ask me about books, recommendations, availability, or library help.";
        $matched = true;
    }
    // trending or popular or top
    elseif (preg_match('/\b(trending|popular|most borrow|top books|top rated)\b/', $cleanMessage)) {
        if (preg_match('/rated/', $cleanMessage)) {
            $sql = "SELECT b.title,b.author, AVG(r.rating) as avg_rate
                    FROM reviews r
                    JOIN books b ON r.book_id=b.id
                    GROUP BY b.id
                    ORDER BY avg_rate DESC
                    LIMIT 3";
            $result = $conn->query($sql);
            if ($result && $result->num_rows) {
                $botResponse = "⭐ Top rated books:<br><ul>";
                while ($row = $result->fetch_assoc()) {
                    $botResponse .= "<li><strong>" . htmlspecialchars($row['title']) . "</strong> by " . htmlspecialchars($row['author']) .
                        " (" . number_format($row['avg_rate'],1) . "/5)</li>";
                }
                $botResponse .= "</ul>";
            } else {
                $botResponse = "No rated books found.";
            }
        } else {
            $res = getTrending($conn);
            if ($res && $res->num_rows) {
                $botResponse = "📚 Top trending books:<br><ul>";
                while ($row = $res->fetch_assoc()) {
                    $botResponse .= "<li><strong>" . htmlspecialchars($row['title']) . "</strong> by " .
                        htmlspecialchars($row['author']) . " (" . $row['borrow_count'] . " borrows)</li>";
                }
                $botResponse .= "</ul>";
            } else {
                $botResponse = "No trending books found.";
            }
        }
        $matched = true;
    }
    // availability
    elseif (preg_match('/\b(is|are) (.+?) available\b/', $cleanMessage, $m)) {
        $title = trim($m[2]);
        $res = searchBooks($conn, $title);
        if ($res && $res->num_rows) {
            $lines = [];
            while ($row = $res->fetch_assoc()) {
                $avail = ($row['available'] == 1) ? 'Yes, available 📗' : 'Not currently (borrowed) 📕';
                $lines[] = "<strong>" . htmlspecialchars($row['title']) . "</strong>: " . $avail;
            }
            $botResponse = implode('<br>', $lines);
        } else {
            $botResponse = "I couldn't find that book in our catalogue.";
        }
        $matched = true;
    }
    // similar books
    elseif (preg_match('/(?:books? )?(?:like|similar to) (.+)/', $cleanMessage, $m)) {
        $book = trim($m['1']);
        $res = searchBooks($conn, $book);
        if ($res && $row = $res->fetch_assoc()) {
            $cat = $row['category_id'];
            $stmt = $conn->prepare("SELECT title,author FROM books WHERE category_id = ? AND id != ? LIMIT 3");
            $stmt->bind_param('ii', $cat, $row['id']);
            $stmt->execute();
            $sim = $stmt->get_result();
            if ($sim && $sim->num_rows) {
                $botResponse = "Books similar to <strong>" . htmlspecialchars($row['title']) . "</strong>:<br><ul>";
                while ($r2 = $sim->fetch_assoc()) {
                    $botResponse .= "<li>" . htmlspecialchars($r2['title']) . " by " . htmlspecialchars($r2['author']) . "</li>";
                }
                $botResponse .= "</ul>";
            } else {
                $botResponse = "I couldn't find similar books.";
            }
        } else {
            $botResponse = "I don't recognize that title.";
        }
        $matched = true;
    }
    // recommendations
    elseif (preg_match('/\b(recommend|suggest|what should i read|something to read)\b/', $cleanMessage)) {
        if (isset($_SESSION['user_id'])) {
            $rec = getPersonalReco($conn, $_SESSION['user_id']);
            if ($rec && $rec->num_rows) {
                $botResponse = "Based on your history, you might like:<br><ul>";
                while ($r = $rec->fetch_assoc()) {
                    $botResponse .= "<li><strong>" . htmlspecialchars($r['title']) . "</strong> by " . htmlspecialchars($r['author']) . "</li>";
                }
                $botResponse .= "</ul>";
            } else {
                $suggestRandom = ["The Alchemist", "Sapiens", "The Hobbit", "Clean Code", "Thinking, Fast and Slow"];
                $botResponse = "📖 How about <strong>" . $suggestRandom[array_rand($suggestRandom)] . "</strong>?";
            }
        } else {
            $botResponse = "📖 I recommend reading <strong>To Kill a Mockingbird</strong>. (Log in for personalized ideas)";
        }
        $matched = true;
    }
    // library help
    elseif (preg_match('/\b(borrow|extend|return|due|how do i|how to|library|account|fine|borrow help)\b/', $cleanMessage)) {
        $botResponse = "📚 **Library Help:**<br>
            - To borrow a book go to its page and click 'Issue'.<br>
            - You can extend a loan from your dashboard before the due date.<br>
            - Return books at the front desk or via the return page.<br>
            - Check 'My Books' for due dates and fines.<br>
            - Contact admin if you have account issues.";
        $matched = true;
    }
    // explicit search
    elseif (preg_match('/\b(find|search|show|list)\b/', $cleanMessage)) {
        if (preg_match('/(?:find|search|show|list)(?: for)? (.+)/', $cleanMessage, $m2)) {
            $term = trim($m2[1]);
        } else {
            $term = '';
        }
        if ($term === '') {
            $botResponse = "Please tell me what you're looking for, e.g. 'find python books'.";
        } else {
            $res = searchBooks($conn, $term);
            if ($res && $res->num_rows) {
                $botResponse = "🔍 Here are some books about <em>" . htmlspecialchars($term) . "</em> 📚<br><ul>";
                while ($row = $res->fetch_assoc()) {
                    $botResponse .= "<li><strong>" . htmlspecialchars($row['title']) . "</strong> by " . htmlspecialchars($row['author']);
                    if ($row['category_name']) {
                        $botResponse .= " <em>(" . htmlspecialchars($row['category_name']) . ")</em>";
                    }
                    $botResponse .= "</li>";
                }
                $botResponse .= "</ul>";
            } else {
                $botResponse = "No books found matching '<strong>" . htmlspecialchars($term) . "</strong>'.";
            }
        }
        $matched = true;
    }
    // help
    elseif (preg_match('/\b(help|commands|features|what can you do)\b/', $cleanMessage)) {
        $botResponse = "🤖 You can ask me things like:<br>
            - trending books<br>
            - recommend books<br>
            - search python books<br>
            - check if a title is available<br>
            - or how to borrow books";
        $matched = true;
    }
    // fallback search attempt
    if (!$matched) {
        $res = searchBooks($conn, $cleanMessage);
        if ($res && $res->num_rows) {
            $botResponse = "🔍 Here are some results I found for '<em>" . htmlspecialchars($cleanMessage) . "</em>' 📚<br><ul>";
            while ($row = $res->fetch_assoc()) {
                $botResponse .= "<li><strong>" . htmlspecialchars($row['title']) . "</strong> by " . htmlspecialchars($row['author']) . "</li>";
            }
            $botResponse .= "</ul>";
            $matched = true;
        }
    }
    // fuzzy guess
    if (!$matched) {
        function guessIntent($text) {
            $cands = [
                'trending books' => 'trending',
                'recommend books' => 'recommend',
                'search books' => 'search',
                'help' => 'help'
            ];
            $best = null; $min = PHP_INT_MAX;
            foreach ($cands as $phrase => $name) {
                $d = levenshtein($text, $phrase);
                if ($d < $min) { $min = $d; $best = $name; }
            }
            if ($min <= 4) return $best;
            return null;
        }
        $guess = guessIntent($cleanMessage);
        if ($guess) {
            $botResponse = "I think you might be asking about $guess; try phrasing it differently.\n";
            $botResponse .= "You can ask: trending books, recommend books, search python books, or how to borrow books.";
            $matched = true;
        }
    }
    if (!$matched) {
        $botResponse = "I'm sorry, I couldn't figure that one out. You can ask me things like: trending books, recommend books, search python books, or how to borrow books.";
    }
    // record dialogue
    $_SESSION['chat_history'][] = ['user' => $userMessage, 'bot' => $botResponse];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>AI Library Assistant</title>

<style>

body{
font-family:Arial;
background:#f4f7f6;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
margin:0;
}

.chat{
width:500px;
background:white;
border-radius:10px;
box-shadow:0 10px 25px rgba(0,0,0,0.1);
display:flex;
flex-direction:column;
height:600px;
}

.header{
background:#4a90e2;
color:white;
padding:20px;
text-align:center;
}

.window{
flex:1;
padding:20px;
overflow-y:auto;
background:#f9f9f9;
}

.message{
    margin-bottom:10px;
    padding:10px;
    border-radius:10px;
    max-width:80%;
    display: inline-block;
}

.user{
    background:#4a90e2;
    color:white;
    align-self:flex-end;
    border-radius:10px 10px 0 10px;
}

.bot{
    background:#e9ecef;
    align-self:flex-start;
    border-radius:10px 10px 10px 0;
}

.suggestions{
    padding:10px;
    border-top:1px solid #eee;
    text-align:center;
    background:#ffffff;
}

.suggestion-btn{
    margin:5px;
    padding:6px 12px;
    background:#f1f1f1;
    border:none;
    border-radius:20px;
    cursor:pointer;
}

.suggestion-btn:hover{
    background:#e0e0e0;
}

.input{
display:flex;
padding:10px;
border-top:1px solid #eee;
}

.input input{
flex:1;
padding:10px;
border:1px solid #ccc;
border-radius:20px;
}

.input button{
margin-left:10px;
padding:10px 20px;
background:#4a90e2;
color:white;
border:none;
border-radius:20px;
}

</style>
</head>

<body>

<div class="chat">

<div class="header">
<h2>AI Library Assistant</h2>
</div>

<div class="window" id="chatWindow">
    <?php if (empty($_SESSION['chat_history'])): ?>
        <div class="message bot">Hello! Ask me about books.</div>
    <?php endif; ?>

    <?php foreach ($_SESSION['chat_history'] as $entry): ?>
        <div class="message user"><?php echo htmlspecialchars($entry['user']); ?></div>
        <div class="message bot"><?php echo $entry['bot']; ?></div>
    <?php endforeach; ?>
</div>

<div class="suggestions">
    <?php foreach ($suggestions as $s): ?>
        <button type="button" class="suggestion-btn"><?php echo htmlspecialchars($s); ?></button>
    <?php endforeach; ?>
</div>

<div class="input">

<form method="POST" id="chatForm">

<input type="text" name="message" id="chatInput" placeholder="Type a message..." required>

<button type="submit">Send</button>

</form>

</div>

</div>

<script>
const chatWindow = document.getElementById('chatWindow');
function scrollBottom() {
    if (chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;
}
// scroll on load
scrollBottom();

// ensure scroll after sending
const form = document.getElementById('chatForm');
if (form) {
    form.addEventListener('submit', () => setTimeout(scrollBottom, 50));
}

// suggestion button click behaviour
document.querySelectorAll('.suggestion-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById('chatInput');
        if (input) {
            input.value = btn.textContent;
            form.submit();
        }
    });
});
</script>

</body>
</html>