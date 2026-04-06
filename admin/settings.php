<?php
session_start();
include("../config/database.php");
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') header("Location: ../auth/login.php");

if(isset($_POST['update'])){
    $borrow_days = $_POST['borrow_days'];
    $extend_days = $_POST['extend_days'];
    $daily_fine = $_POST['daily_fine'];

    mysqli_query($conn, "UPDATE settings SET borrow_days='$borrow_days', extend_days='$extend_days' WHERE id=1");
    mysqli_query($conn, "UPDATE fines_config SET daily_fine='$daily_fine' WHERE id=1");
    
    $success = "Settings Updated Successfully";
}

 $current_settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM settings WHERE id=1"));
 $fine_config = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM fines_config WHERE id=1"));

include("../includes/layout.php");
?>

<h2>System Configuration</h2>

<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header">Library Rules</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Borrow Duration (Days)</label>
                        <input type="number" name="borrow_days" value="<?= $current_settings['borrow_days'] ?>" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Extension Duration (Days)</label>
                        <input type="number" name="extend_days" value="<?= $current_settings['extend_days'] ?>" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Daily Fine Amount (₹)</label>
                        <input type="number" step="0.01" name="daily_fine" value="<?= $fine_config['daily_fine'] ?>" class="form-control">
                    </div>
                    <button name="update" class="btn btn-primary w-100">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-light">
            <div class="card-body">
                <h5>Info</h5>
                <p>Changes here will immediately affect new borrow records. Existing records will not be retroactively updated.</p>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>