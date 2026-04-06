<?php
session_start();
include("../config/database.php");
include("../includes/layout.php");

$book_id = $_GET['id'];

// Get Book Info
$book = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT books.*, categories.name as cat_name
    FROM books
    JOIN categories ON books.category_id = categories.id
    WHERE books.id = '$book_id'
"));

// Get Reviews and calculate average rating
$reviews = mysqli_query($conn, "
    SELECT reviews.*, users.name
    FROM reviews
    JOIN users ON reviews.user_id = users.id
    WHERE reviews.book_id = '$book_id'
    ORDER BY reviews.created_at DESC
");

// Calculate average rating and review stats
$avg_rating = 0;
$rating_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$total_reviews = mysqli_num_rows($reviews);

if($total_reviews > 0) {
    mysqli_data_seek($reviews, 0);
    $total_rating = 0;
    while($r = mysqli_fetch_assoc($reviews)) {
        $total_rating += $r['rating'];
        $rating_counts[$r['rating']]++;
    }
    $avg_rating = $total_rating / $total_reviews;
    mysqli_data_seek($reviews, 0); // Reset pointer
}

// Check if user already reviewed this book
$user_reviewed = false;
if(isset($_SESSION['user_id'])) {
    $user_check = mysqli_query($conn, "SELECT id FROM reviews WHERE user_id='{$_SESSION['user_id']}' AND book_id='$book_id'");
    $user_reviewed = mysqli_num_rows($user_check) > 0;
}

// Handle Review Submit
if(isset($_POST['submit_review']) && !$user_reviewed){
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['review']);
    $user = $_SESSION['user_id'];

    mysqli_query($conn, "INSERT INTO reviews (user_id, book_id, rating, review) VALUES ('$user', '$book_id', '$rating', '$comment')");
    header("Location: book_details.php?id=$book_id");
    exit();
}
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Book Info -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 class="h2 mb-1"><?php echo htmlspecialchars($book['title']); ?></h1>
                        <p class="text-muted mb-2">by <?php echo htmlspecialchars($book['author']); ?></p>
                        <span class="badge bg-info"><?php echo htmlspecialchars($book['cat_name']); ?></span>
                    </div>
                    <div class="text-end">
                        <?php if($avg_rating > 0): ?>
                            <div class="d-flex align-items-center mb-1">
                                <div class="me-2">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="bi bi-star<?php echo ($i <= round($avg_rating)) ? '-fill' : ''; ?> text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="fw-bold"><?php echo number_format($avg_rating, 1); ?></span>
                            </div>
                            <small class="text-muted"><?php echo $total_reviews; ?> review<?php echo $total_reviews != 1 ? 's' : ''; ?></small>
                        <?php else: ?>
                            <small class="text-muted">No reviews yet</small>
                        <?php endif; ?>
                    </div>
                </div>

                <hr>
                <div class="row">
                    <div class="col-md-8">
                        <h5>Description</h5>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="mb-3">
                                <?php if($book['available'] > 0): ?>
                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        <i class="bi bi-check-circle"></i> <?php echo $book['available']; ?> Available
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger fs-6 px-3 py-2">
                                        <i class="bi bi-x-circle"></i> Out of Stock
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if($book['available'] > 0): ?>
                                <a href="../borrow/issue.php?book_id=<?php echo $book['id']; ?>" class="btn btn-primary btn-lg">
                                    <i class="bi bi-book"></i> Borrow This Book
                                </a>
                            <?php else: ?>
                                <form method="POST" action="../user/books.php" class="d-inline">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" name="reserve_book" class="btn btn-warning btn-lg">
                                        <i class="bi bi-bookmark-plus"></i> Reserve Book
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rating Breakdown -->
        <?php if($total_reviews > 0): ?>
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Rating Breakdown</h5>
            </div>
            <div class="card-body">
                <?php for($i=5; $i>=1; $i--): ?>
                    <div class="d-flex align-items-center mb-2">
                        <div class="me-2" style="width: 60px;">
                            <small><?php echo $i; ?> <i class="bi bi-star-fill text-warning"></i></small>
                        </div>
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: <?php echo $total_reviews > 0 ? ($rating_counts[$i] / $total_reviews * 100) : 0; ?>%"></div>
                        </div>
                        <div class="ms-2" style="width: 40px;">
                            <small class="text-muted"><?php echo $rating_counts[$i]; ?></small>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reviews Section -->
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-chat-quote"></i> Community Reviews (<?php echo $total_reviews; ?>)</h5>
                <?php if(!$user_reviewed && isset($_SESSION['user_id'])): ?>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        <i class="bi bi-plus"></i> Write Review
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if($total_reviews > 0): ?>
                    <div class="reviews-container">
                        <?php while($r = mysqli_fetch_assoc($reviews)): ?>
                            <div class="review-item border-bottom py-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($r['name']); ?></h6>
                                        <div class="rating-stars mb-1">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <i class="bi bi-star<?php echo ($i <= $r['rating']) ? '-fill' : ''; ?> text-warning"></i>
                                            <?php endfor; ?>
                                            <small class="text-muted ms-2">
                                                <?php echo date('M d, Y', strtotime($r['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($r['review'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat-quote fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No reviews yet</h5>
                        <p class="text-muted">Be the first to share your thoughts about this book!</p>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="bi bi-plus"></i> Write First Review
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-graph-up"></i> Book Statistics</h6>
            </div>
            <div class="card-body">
                <div class="stat-item d-flex justify-content-between py-2">
                    <span>Total Copies:</span>
                    <strong><?php echo $book['quantity']; ?></strong>
                </div>
                <div class="stat-item d-flex justify-content-between py-2">
                    <span>Available:</span>
                    <strong class="<?php echo $book['available'] > 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $book['available']; ?>
                    </strong>
                </div>
                <div class="stat-item d-flex justify-content-between py-2">
                    <span>Borrowed:</span>
                    <strong><?php echo $book['quantity'] - $book['available']; ?></strong>
                </div>
                <div class="stat-item d-flex justify-content-between py-2">
                    <span>Reviews:</span>
                    <strong><?php echo $total_reviews; ?></strong>
                </div>
                <?php if($avg_rating > 0): ?>
                <div class="stat-item d-flex justify-content-between py-2">
                    <span>Average Rating:</span>
                    <strong><?php echo number_format($avg_rating, 1); ?> ⭐</strong>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Books -->
        <?php
        $related_books = mysqli_query($conn, "
            SELECT books.id, books.title, books.author, AVG(reviews.rating) as avg_rating, COUNT(reviews.id) as review_count
            FROM books
            LEFT JOIN reviews ON books.id = reviews.book_id
            WHERE books.category_id = '{$book['category_id']}' AND books.id != '$book_id'
            GROUP BY books.id
            ORDER BY avg_rating DESC, review_count DESC
            LIMIT 3
        ");
        if(mysqli_num_rows($related_books) > 0):
        ?>
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-book"></i> Similar Books</h6>
            </div>
            <div class="card-body">
                <?php while($related = mysqli_fetch_assoc($related_books)): ?>
                    <div class="related-book mb-3 pb-3 border-bottom">
                        <h6 class="mb-1">
                            <a href="book_details.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars(substr($related['title'], 0, 40)); ?>
                                <?php echo strlen($related['title']) > 40 ? '...' : ''; ?>
                            </a>
                        </h6>
                        <p class="text-muted small mb-1"><?php echo htmlspecialchars($related['author']); ?></p>
                        <?php if($related['avg_rating'] > 0): ?>
                            <div class="d-flex align-items-center">
                                <small class="text-warning me-1">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="bi bi-star<?php echo ($i <= round($related['avg_rating'])) ? '-fill' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </small>
                                <small class="text-muted"><?php echo number_format($related['avg_rating'], 1); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Review Modal -->
<?php if(isset($_SESSION['user_id']) && !$user_reviewed): ?>
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-star"></i> Write a Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <select name="rating" class="form-control" required>
                            <option value="">Select rating...</option>
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Good</option>
                            <option value="3">⭐⭐⭐ Average</option>
                            <option value="2">⭐⭐ Poor</option>
                            <option value="1">⭐ Terrible</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Review</label>
                        <textarea name="review" class="form-control" rows="4" placeholder="Share your thoughts about this book..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.rating-stars i { font-size: 0.9em; }
.stat-item { border-bottom: 1px solid #f0f0f0; }
.stat-item:last-child { border-bottom: none; }
.related-book:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.review-item:last-child { border-bottom: none; }
</style>

<?php include("../includes/layout_footer.php"); ?>