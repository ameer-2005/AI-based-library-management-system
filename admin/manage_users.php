<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit();
}

include("../config/database.php");
include("../includes/layout.php");

if(isset($_POST['update_role'])){
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    mysqli_query($conn, "UPDATE users SET role='$new_role' WHERE id='$user_id'");
    $success = "User role updated successfully";
}

$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Users</h2>
</div>

<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td>
                        <span class="badge bg-<?php echo $row['role'] == 'admin' ? 'danger' : 'secondary'; ?>">
                            <?php echo ucfirst($row['role']); ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <select name="role" class="form-select form-select-sm d-inline" style="width: auto;">
                                <option value="user" <?php echo $row['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <button type="submit" name="update_role" class="btn btn-sm btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>