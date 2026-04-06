<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Library System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>

:root{
--sidebar-width:260px;
}

body{
background:#f8f9fa;
overflow-x:hidden;
overflow-y:auto;
}

/* SIDEBAR */

.sidebar{
width:var(--sidebar-width);
height:100vh;
position:fixed;
left:0;
top:0;
background:linear-gradient(180deg,#212529 0%,#343a40 100%);
color:#fff;
transition:all .3s;
z-index:1000;
overflow-y:auto;
display:flex;
flex-direction:column;
}

.sidebar.collapsed{
margin-left:calc(-1 * var(--sidebar-width));
}

.sidebar-brand{
padding:1.5rem;
font-size:1.2rem;
font-weight:bold;
border-bottom:1px solid rgba(255,255,255,0.1);
display:flex;
align-items:center;
gap:10px;
}

.sidebar-menu{
flex:1;
padding:1rem 0;
}

.menu-header{
padding:.5rem 1.5rem;
font-size:.75rem;
text-transform:uppercase;
color:#6c757d;
letter-spacing:1px;
margin-top:1rem;
}

.nav-link{
color:#adb5bd;
padding:.8rem 1.5rem;
display:flex;
align-items:center;
gap:12px;
transition:.2s;
border-left:3px solid transparent;
}

.nav-link:hover{
color:#fff;
background:rgba(255,255,255,.05);
}

.nav-link.active{
color:#fff;
background:rgba(13,110,253,.1);
border-left-color:#0d6efd;
}

.nav-link i{
font-size:1.1rem;
width:24px;
text-align:center;
}

/* BOTTOM LINKS */

.sidebar-footer{
padding:10px 0;
border-top:1px solid rgba(255,255,255,0.1);
}

/* MAIN CONTENT */

.main-content{
margin-left:var(--sidebar-width);
transition:all .3s;
min-height:100vh;
}

/* TOP NAVBAR */

.top-navbar{
background:#fff;
box-shadow:0 .15rem 1.75rem rgba(58,59,69,.15);
height:60px;
position:sticky;
top:0;
z-index:999;
display:flex;
align-items:center;
padding:0 1.5rem;
}

/* CONTENT */

.content-wrapper{
padding:1.5rem;
}

/* MOBILE */

@media(max-width:768px){

.sidebar{
margin-left:calc(-1 * var(--sidebar-width));
}

.sidebar.show{
margin-left:0;
}

.main-content{
margin-left:0;
}

}

</style>
</head>

<body>

<!-- SIDEBAR -->

<nav class="sidebar" id="sidebar">

<div class="sidebar-brand">
<i class="bi bi-journal-bookmark-fill text-primary"></i>
<span>AI Library</span>
</div>

<div class="sidebar-menu">

<?php
$role=$_SESSION['role'];
$page=basename($_SERVER['PHP_SELF']);
?>

<div class="menu-header">Navigation</div>

<?php if($role=='admin'): ?>

<a href="../admin/dashboard.php" class="nav-link <?= $page=='dashboard.php'?'active':'' ?>">
<i class="bi bi-speedometer2"></i> Dashboard
</a>

<a href="../admin/manage_books.php" class="nav-link <?= $page=='manage_books.php'?'active':'' ?>">
<i class="bi bi-journal-text"></i> Manage Books
</a>

<a href="../admin/borrow_manage.php" class="nav-link <?= $page=='borrow_manage.php'?'active':'' ?>">
<i class="bi bi-arrow-left-right"></i> Borrow Control
</a>

<a href="../admin/analytics.php" class="nav-link <?= $page=='analytics.php'?'active':'' ?>">
<i class="bi bi-graph-up-arrow"></i> Analytics
</a>

<a href="../admin/manage_users.php" class="nav-link <?= $page=='manage_users.php'?'active':'' ?>">
<i class="bi bi-people"></i> Manage Users
</a>

<div class="menu-header">Features</div>

<a href="../admin/manage_covers.php" class="nav-link <?= $page=='manage_covers.php'?'active':'' ?>">
<i class="bi bi-image"></i> Book Covers
</a>

<a href="../admin/manage_categories.php" class="nav-link <?= $page=='manage_categories.php'?'active':'' ?>">
<i class="bi bi-folder"></i> Categories
</a>

<a href="../admin/bulk_import.php" class="nav-link <?= $page=='bulk_import.php'?'active':'' ?>">
<i class="bi bi-upload"></i> Bulk Import
</a>

<a href="../admin/audit_logs.php" class="nav-link <?= $page=='audit_logs.php'?'active':'' ?>">
<i class="bi bi-shield-check"></i> Audit Logs
</a>

<div class="menu-header">System</div>

<a href="../admin/settings.php" class="nav-link <?= $page=='settings.php'?'active':'' ?>">
<i class="bi bi-gear"></i> Settings
</a>

<?php else: ?>

<a href="../user/dashboard.php" class="nav-link <?= $page=='dashboard.php'?'active':'' ?>">
<i class="bi bi-house"></i> Home
</a>

<a href="../user/books.php" class="nav-link <?= $page=='books.php'?'active':'' ?>">
<i class="bi bi-search"></i> Browse Books
</a>

<a href="../user/reservations.php" class="nav-link <?= $page=='reservations.php'?'active':'' ?>">
<i class="bi bi-bookmark-plus"></i> My Reservations
</a>

<a href="../user/my_books.php" class="nav-link <?= $page=='my_books.php'?'active':'' ?>">
<i class="bi bi-book"></i> My Books
</a>

<a href="../user/fines.php" class="nav-link <?= $page=='fines.php'?'active':'' ?>">
<i class="bi bi-currency-rupee"></i> My Fines
</a>

<a href="../user/reading_history.php" class="nav-link <?= $page=='reading_history.php'?'active':'' ?>">
<i class="bi bi-clock-history"></i> Reading History
</a>

<div class="menu-header">Discover</div>

<a href="../user/trending.php" class="nav-link <?= $page=='trending.php'?'active':'' ?>">
<i class="bi bi-fire"></i> Trending
</a>

<a href="../user/recommend.php" class="nav-link <?= $page=='recommend.php'?'active':'' ?>">
<i class="bi bi-robot"></i> AI Suggest
</a>

<a href="../user/notifications.php" class="nav-link <?= $page=='notifications.php'?'active':'' ?>">
<i class="bi bi-bell"></i> Notifications
</a>

<a href="../chatbot/chatbot.php" class="nav-link <?= $page=='chatbot.php'?'active':'' ?>">
<i class="bi bi-chat-dots"></i> Chatbot
</a>

<a href="../user/ebooks.php" class="nav-link">
<i class="bi bi-book-half"></i> E-Books
</a>

<?php endif; ?>

</div>

<div class="sidebar-footer">

<a href="../user/profile.php" class="nav-link">
<i class="bi bi-person-circle"></i> Profile
</a>

<a href="../auth/logout.php" class="nav-link text-danger">
<i class="bi bi-box-arrow-left"></i> Logout
</a>

</div>

</nav>

<!-- MAIN CONTENT -->

<div class="main-content">

<div class="top-navbar">

<button class="btn btn-link text-dark me-3" id="sidebarToggle">
<i class="bi bi-list fs-4"></i>
</button>

<span class="ms-auto">
Welcome, <b><?= $_SESSION['name'] ?></b>
</span>

</div>

<div class="content-wrapper">