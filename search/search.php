<?php
session_start();
include("../config/database.php");
include("../includes/layout.php");

// Get categories for filter
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

// Initialize search parameters
$keyword = $_GET['keyword'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$availability = $_GET['availability'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'title';
$sort_order = $_GET['sort_order'] ?? 'ASC';

$result = null;
$total_results = 0;

if(isset($_GET['search']) && (!empty($keyword) || !empty($category_id) || !empty($availability))) {
    $keyword = mysqli_real_escape_string($conn, $keyword);
    $category_id = mysqli_real_escape_string($conn, $category_id);
    $availability = mysqli_real_escape_string($conn, $availability);

    // Build query
    $query = "
        SELECT books.*, categories.name as cat_name,
               AVG(reviews.rating) as avg_rating,
               COUNT(reviews.id) as review_count
        FROM books
        JOIN categories ON books.category_id = categories.id
        LEFT JOIN reviews ON books.id = reviews.book_id
    ";

    $where_conditions = [];

    if(!empty($keyword)) {
        $where_conditions[] = "(books.title LIKE '%$keyword%' OR books.author LIKE '%$keyword%' OR books.description LIKE '%$keyword%')";
    }

    if(!empty($category_id)) {
        $where_conditions[] = "books.category_id = '$category_id'";
    }

    if($availability === 'available') {
        $where_conditions[] = "books.available > 0";
    } elseif($availability === 'unavailable') {
        $where_conditions[] = "books.available = 0";
    }

    if(!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $query .= " GROUP BY books.id";

    // Add sorting
    $allowed_sort_fields = ['title', 'author', 'avg_rating', 'review_count', 'available'];
    $sort_by = in_array($sort_by, $allowed_sort_fields) ? $sort_by : 'title';
    $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';

    $query .= " ORDER BY $sort_by $sort_order";

    $result = mysqli_query($conn, $query);
    $total_results = mysqli_num_rows($result);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-search"></i> Advanced Book Search</h2>
    <div>
        <span class="badge bg-info"><?php echo $total_results; ?> results found</span>
    </div>
</div>

<!-- Advanced Search Form -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-funnel"></i> Search Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search Keywords</label>
                <input type="text" name="keyword" class="form-control"
                       placeholder="Title, author, or description..."
                       value="<?php echo htmlspecialchars($keyword); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control">
                    <option value="">All Categories</option>
                    <?php
                    mysqli_data_seek($categories, 0);
                    while($cat = mysqli_fetch_assoc($categories)):
                    ?>
                        <option value="<?php echo $cat['id']; ?>"
                                <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Availability</label>
                <select name="availability" class="form-control">
                    <option value="">All Books</option>
                    <option value="available" <?php echo $availability == 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="unavailable" <?php echo $availability == 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Sort By</label>
                <div class="d-flex gap-1">
                    <select name="sort_by" class="form-control">
                        <option value="title" <?php echo $sort_by == 'title' ? 'selected' : ''; ?>>Title</option>
                        <option value="author" <?php echo $sort_by == 'author' ? 'selected' : ''; ?>>Author</option>
                        <option value="avg_rating" <?php echo $sort_by == 'avg_rating' ? 'selected' : ''; ?>>Rating</option>
                        <option value="review_count" <?php echo $sort_by == 'review_count' ? 'selected' : ''; ?>>Reviews</option>
                        <option value="available" <?php echo $sort_by == 'available' ? 'selected' : ''; ?>>Availability</option>
                    </select>
                    <select name="sort_order" class="form-control" style="width: auto;">
                        <option value="ASC" <?php echo $sort_order == 'ASC' ? 'selected' : ''; ?>>↑</option>
                        <option value="DESC" <?php echo $sort_order == 'DESC' ? 'selected' : ''; ?>>↓</option>
                    </select>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" name="search" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="search.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Search Results -->
<?php if(isset($_GET['search'])): ?>
    <?php if($result && mysqli_num_rows($result) > 0): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Search Results (<?php echo $total_results; ?> books)</h5>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary active" id="gridView">
                    <i class="bi bi-grid"></i>
                </button>
                <button class="btn btn-outline-secondary" id="listView">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>

        <!-- Grid View -->
        <div id="resultsGrid" class="row">
            <?php
            mysqli_data_seek($result, 0);
            while($row = mysqli_fetch_assoc($result)):
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card shadow-sm h-100 book-card">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <h6 class="card-title mb-1">
                                    <a href="../reviews/book_details.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars(substr($row['title'], 0, 40)); ?>
                                        <?php echo strlen($row['title']) > 40 ? '...' : ''; ?>
                                    </a>
                                </h6>
                                <p class="text-muted small mb-1">by <?php echo htmlspecialchars($row['author']); ?></p>
                                <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($row['cat_name']); ?></span>
                            </div>

                            <div class="mt-auto">
                                <!-- Rating -->
                                <?php if($row['avg_rating'] > 0): ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-1">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <i class="bi bi-star<?php echo ($i <= round($row['avg_rating'])) ? '-fill' : ''; ?> text-warning" style="font-size: 0.8em;"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted"><?php echo number_format($row['avg_rating'], 1); ?> (<?php echo $row['review_count']; ?>)</small>
                                    </div>
                                <?php endif; ?>

                                <!-- Availability -->
                                <div class="mb-2">
                                    <?php if($row['available'] > 0): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> <?php echo $row['available']; ?> available
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Unavailable
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Actions -->
                                <div class="btn-group w-100" role="group">
                                    <a href="../reviews/book_details.php?id=<?php echo $row['id']; ?>"
                                       class="btn btn-outline-primary btn-sm">Details</a>
                                    <?php if($row['available'] > 0): ?>
                                        <a href="../borrow/issue.php?book_id=<?php echo $row['id']; ?>"
                                           class="btn btn-success btn-sm">Borrow</a>
                                    <?php else: ?>
                                        <form method="POST" action="../user/books.php" class="d-inline">
                                            <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="reserve_book" class="btn btn-warning btn-sm">
                                                Reserve
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- List View (Hidden by default) -->
        <div id="resultsList" class="d-none">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Rating</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($result, 0);
                        while($row = mysqli_fetch_assoc($result)):
                        ?>
                            <tr>
                                <td>
                                    <a href="../reviews/book_details.php?id=<?php echo $row['id']; ?>" class="text-decoration-none fw-bold">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($row['author']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['cat_name']); ?></span></td>
                                <td>
                                    <?php if($row['avg_rating'] > 0): ?>
                                        <?php echo number_format($row['avg_rating'], 1); ?> ⭐ (<?php echo $row['review_count']; ?>)
                                    <?php else: ?>
                                        <span class="text-muted">No reviews</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['available'] > 0): ?>
                                        <span class="text-success"><?php echo $row['available']; ?> copies</span>
                                    <?php else: ?>
                                        <span class="text-danger">Unavailable</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="../reviews/book_details.php?id=<?php echo $row['id']; ?>"
                                           class="btn btn-outline-primary">Details</a>
                                        <?php if($row['available'] > 0): ?>
                                            <a href="../borrow/issue.php?book_id=<?php echo $row['id']; ?>"
                                               class="btn btn-success">Borrow</a>
                                        <?php else: ?>
                                            <form method="POST" action="../user/books.php" class="d-inline">
                                                <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="reserve_book" class="btn btn-warning">
                                                    Reserve
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif(isset($_GET['search'])): ?>
        <div class="text-center py-5">
            <i class="bi bi-search fs-1 text-muted mb-3"></i>
            <h4 class="text-muted">No books found</h4>
            <p class="text-muted">Try adjusting your search criteria or browse all books.</p>
            <a href="../user/books.php" class="btn btn-primary">Browse All Books</a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Quick Search Suggestions -->
<?php if(!isset($_GET['search']) || empty($_GET['keyword'])): ?>
<div class="card shadow">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Quick Search Suggestions</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6>Popular Categories</h6>
                <ul class="list-unstyled">
                    <?php
                    mysqli_data_seek($categories, 0);
                    $count = 0;
                    while(($cat = mysqli_fetch_assoc($categories)) && $count < 3):
                        $count++;
                    ?>
                        <li><a href="?search=1&category_id=<?php echo $cat['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Available Books</h6>
                <ul class="list-unstyled">
                    <li><a href="?search=1&availability=available" class="text-decoration-none">Currently Available</a></li>
                    <li><a href="?search=1&sort_by=avg_rating&sort_order=DESC" class="text-decoration-none">Highest Rated</a></li>
                    <li><a href="?search=1&sort_by=review_count&sort_order=DESC" class="text-decoration-none">Most Reviewed</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Recent Additions</h6>
                <ul class="list-unstyled">
                    <?php
                    $recent = mysqli_query($conn, "SELECT title FROM books ORDER BY id DESC LIMIT 3");
                    while($book = mysqli_fetch_assoc($recent)):
                    ?>
                        <li><a href="?search=1&keyword=<?php echo urlencode($book['title']); ?>" class="text-decoration-none"><?php echo htmlspecialchars(substr($book['title'], 0, 30)); ?>...</a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// View toggle functionality
document.getElementById('gridView').addEventListener('click', function() {
    document.getElementById('resultsGrid').classList.remove('d-none');
    document.getElementById('resultsList').classList.add('d-none');
    this.classList.add('active');
    document.getElementById('listView').classList.remove('active');
});

document.getElementById('listView').addEventListener('click', function() {
    document.getElementById('resultsList').classList.remove('d-none');
    document.getElementById('resultsGrid').classList.add('d-none');
    this.classList.add('active');
    document.getElementById('gridView').classList.remove('active');
});

// Highlight search terms
<?php if(!empty($keyword)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const keyword = "<?php echo addslashes(strtolower($keyword)); ?>";
    const titles = document.querySelectorAll('.card-title a, .card-title');

    titles.forEach(title => {
        const text = title.textContent;
        const regex = new RegExp(`(${keyword})`, 'gi');
        if(regex.test(text)) {
            title.innerHTML = text.replace(regex, '<mark>$1</mark>');
        }
    });
});
<?php endif; ?>
</script>

<style>
.book-card { transition: transform 0.2s; }
.book-card:hover { transform: translateY(-2px); }
mark { background-color: #fff3cd; padding: 2px 4px; border-radius: 3px; }
</style>

<?php include("../includes/layout_footer.php"); ?>