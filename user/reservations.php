<?php
session_start();
include("../config/database.php");
include("../includes/layout.php");
include("../includes/reservations.php");

$user_id = $_SESSION['user_id'];

// Handle reservation actions
if(isset($_POST['reserve_book']) && isset($_POST['book_id'])) {
    $result = createReservation($conn, $user_id, $_POST['book_id']);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

if(isset($_POST['cancel_reservation']) && isset($_POST['reservation_id'])) {
    if(cancelReservation($conn, $_POST['reservation_id'], $user_id)) {
        $message = "Reservation cancelled successfully.";
        $message_type = "success";
    } else {
        $message = "Failed to cancel reservation.";
        $message_type = "danger";
    }
}

// Get user's reservations
$reservations = getUserReservations($conn, $user_id);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-bookmark-plus"></i> My Reservations</h2>
    <a href="books.php" class="btn btn-primary">
        <i class="bi bi-plus"></i> Reserve a Book
    </a>
</div>

<?php if(isset($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0">Active Reservations</h6>
            </div>
            <div class="card-body">
                <?php
                $active_found = false;
                mysqli_data_seek($reservations, 0);
                while($reservation = mysqli_fetch_assoc($reservations)):
                    if($reservation['status'] == 'active'):
                        $active_found = true;
                        $days_left = ceil((strtotime($reservation['expiry_date']) - time()) / (60*60*24));
                ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                        <div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($reservation['title']); ?></h6>
                            <p class="mb-1 text-muted">by <?php echo htmlspecialchars($reservation['author']); ?></p>
                            <small class="text-muted">
                                Reserved on: <?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?>
                                <?php if($days_left > 0): ?>
                                    | Expires in: <?php echo $days_left; ?> days
                                <?php else: ?>
                                    | <span class="text-danger">Expired</span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark me-2">Active</span>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                <button type="submit" name="cancel_reservation" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Cancel this reservation?')">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                <?php
                    endif;
                endwhile;

                if(!$active_found):
                ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bookmark-x fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No Active Reservations</h5>
                        <p class="text-muted">Reserve books that are currently unavailable.</p>
                        <a href="books.php" class="btn btn-primary">Browse Books</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0">Reservation History</h6>
            </div>
            <div class="card-body">
                <?php
                $history_found = false;
                mysqli_data_seek($reservations, 0);
                while($reservation = mysqli_fetch_assoc($reservations)):
                    if($reservation['status'] != 'active'):
                        $history_found = true;
                        $status_badge = $reservation['status'] == 'fulfilled' ? 'success' : 'secondary';
                        $status_text = $reservation['status'] == 'fulfilled' ? 'Fulfilled' : 'Cancelled';
                ?>
                    <div class="border-bottom py-2">
                        <small class="fw-bold"><?php echo htmlspecialchars($reservation['title']); ?></small><br>
                        <small class="text-muted">
                            <?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?>
                            <span class="badge bg-<?php echo $status_badge; ?> ms-1"><?php echo $status_text; ?></span>
                        </small>
                    </div>
                <?php
                    endif;
                endwhile;

                if(!$history_found):
                ?>
                    <div class="text-center py-3">
                        <small class="text-muted">No reservation history</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow mt-3">
            <div class="card-header">
                <h6 class="m-0">Reservation Rules</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Reserve unavailable books</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Maximum 3 active reservations</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Reservation expires in 7 days</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Get notified when book is available</li>
                    <li class="mb-0"><i class="bi bi-check-circle text-success me-1"></i> Cancel anytime before expiry</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>