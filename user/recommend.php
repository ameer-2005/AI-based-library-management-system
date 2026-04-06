<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: ../auth/login.php"); exit(); }

include("../config/database.php");
include("../includes/layout.php");

 $user_id = $_SESSION['user_id'];

// Step 1: Find user's favorite category based on history
 $cat_query = "
    SELECT books.category_id, COUNT(*) as total 
    FROM borrow_records 
    JOIN books ON borrow_records.book_id = books.id 
    WHERE borrow_records.user_id = '$user_id' 
    GROUP BY books.category_id 
    ORDER BY total DESC 
    LIMIT 1
";

 $cat_result = mysqli_query($conn, $cat_query);
 $recommendations = [];

if(mysqli_num_rows($cat_result) > 0){
    $fav_cat = mysqli_fetch_assoc($cat_result)['category_id'];

    // Step 2: Recommend books from that category that user hasn't borrowed yet
    $rec_query = "
        SELECT * FROM books 
        WHERE category_id = '$fav_cat' 
        AND id NOT IN (
            SELECT book_id FROM borrow_records WHERE user_id = '$user_id'
        )
        LIMIT 5
    ";
    
    $recommendations = mysqli_query($conn, $rec_query);
}
?>

<h2><i class="bi bi-magic text-primary"></i> Recommended For You</h2>
<p class="text-muted">Based on your reading history</p>

<style>
/* RECOMMENDATIONS PAGE */

body {
  background: #f5f7fa;
}

.content-wrapper {
  background: #f5f7fa;
}

h2 {
  color: #2c3e50;
  font-weight: 700;
  letter-spacing: -0.5px;
  margin-bottom: 0.5rem;
  font-size: 2rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

h2 i {
  font-size: 2.2rem;
}

.text-muted {
  color: #7f8c8d !important;
  margin-bottom: 2rem;
  font-size: 15px;
}

/* RECOMMENDATION CARDS */

.rec-card {
  background: white;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: none;
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
}

.rec-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  opacity: 0;
  transition: opacity 0.3s;
  z-index: 0;
  border-radius: 14px;
}

.rec-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.rec-card:hover::before {
  opacity: 1;
}

.rec-icon-area {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 40px;
  position: relative;
  z-index: 1;
}

.rec-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  position: relative;
  z-index: 1;
}

.rec-title {
  font-weight: 700;
  color: #2c3e50;
  font-size: 15px;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.rec-author {
  color: #7f8c8d;
  font-size: 12px;
  margin: 0 0 15px 0;
}

.rec-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 11px 16px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 13px;
  text-decoration: none;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.rec-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
  color: white;
}

/* EMPTY STATE */

.empty-rec {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.empty-rec i {
  font-size: 64px;
  color: #bdc3c7;
  margin-bottom: 20px;
  display: block;
}

.empty-rec h3 {
  color: #2c3e50;
  font-weight: 700;
  margin-bottom: 10px;
}

.empty-rec p {
  color: #7f8c8d;
  margin: 0;
}

.empty-rec a {
  display: inline-block;
  margin-top: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s;
}

.empty-rec a:hover {
  transform: translateY(-2px);
  color: white;
}

</style>

<div class="row mt-4">
    <?php if(!empty($recommendations) && mysqli_num_rows($recommendations) > 0): ?>
        <?php while($book = mysqli_fetch_assoc($recommendations)): ?>
        <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
            <div class="rec-card">
                <div class="rec-icon-area">
                    <i class="bi bi-book-half"></i>
                </div>
                <div class="rec-content">
                    <h5 class="rec-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                    <p class="rec-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                    <a href="../borrow/issue.php?book_id=<?php echo $book['id']; ?>" class="rec-btn">
                        <i class="bi bi-book"></i> Borrow Now
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-md-12">
            <div class="empty-rec">
                <i class="bi bi-star"></i>
                <h3>No Recommendations Yet</h3>
                <p>We need more borrowing history to generate personalized recommendations.</p>
                <p>Start borrowing books from different categories!</p>
                <a href="books.php"><i class="bi bi-book"></i> Browse Books</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include("../includes/layout_footer.php"); ?>