<?php
// Theme and UI configuration

function initializeTheme() {
    // Check for saved theme preference
    if(isset($_COOKIE['ai_library_theme'])){
        return $_COOKIE['ai_library_theme'];
    }
    
    // Check for dark mode preference in database if user is logged in
    if(isset($_SESSION['user_id'])){
        include("config/database.php");
        $user_id = $_SESSION['user_id'];
        
        $result = $conn->query("SELECT theme_preference FROM users WHERE id = $user_id");
        if($result && $row = mysqli_fetch_assoc($result)){
            return $row['theme_preference'] ?? 'light';
        }
    }
    
    return 'light';
}

function setTheme($theme) {
    if(!in_array($theme, ['light', 'dark'])){
        return false;
    }
    
    // Save to cookie
    setcookie('ai_library_theme', $theme, time() + (365 * 24 * 60 * 60), '/');
    
    // Save to database if user is logged in
    if(isset($_SESSION['user_id'])){
        include("config/database.php");
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("UPDATE users SET theme_preference = ? WHERE id = ?");
        $stmt->bind_param("si", $theme, $user_id);
        return $stmt->execute();
    }
    
    return true;
}

function getThemeCSS($theme) {
    if($theme == 'dark'){
        return '
        <style>
            :root {
                --bs-primary: #0d6efd;
                --bs-body-bg: #1a1a1a;
                --bs-body-color: #e0e0e0;
                --bs-border-color: #333;
                --sidebar-dark: #0d0d0d;
                --card-dark: #1a1a1a;
            }
            
            body.dark-mode {
                background-color: #1a1a1a;
                color: #e0e0e0;
            }
            
            body.dark-mode .card {
                background-color: #1a1a1a;
                border-color: #333;
                color: #e0e0e0;
            }
            
            body.dark-mode .table {
                color: #e0e0e0;
                border-color: #333;
            }
            
            body.dark-mode .table thead {
                background-color: #2a2a2a;
                color: #e0e0e0;
            }
            
            body.dark-mode .btn-outline-primary {
                color: #0d6efd;
                border-color: #0d6efd;
            }
            
            body.dark-mode .btn-outline-primary:hover {
                background-color: #0d6efd;
                border-color: #0d6efd;
                color: #fff;
            }
            
            body.dark-mode .form-control,
            body.dark-mode .form-select {
                background-color: #2a2a2a;
                border-color: #333;
                color: #e0e0e0;
            }
            
            body.dark-mode .form-control:focus,
            body.dark-mode .form-select:focus {
                background-color: #2a2a2a;
                border-color: #0d6efd;
                color: #e0e0e0;
            }
            
            body.dark-mode .modal-content {
                background-color: #1a1a1a;
                border-color: #333;
                color: #e0e0e0;
            }
            
            body.dark-mode .modal-header {
                border-color: #333;
            }
            
            body.dark-mode .dropdown-menu {
                background-color: #1a1a1a;
                border-color: #333;
            }
            
            body.dark-mode .dropdown-item {
                color: #e0e0e0;
            }
            
            body.dark-mode .dropdown-item:hover {
                background-color: #2a2a2a;
            }
            
            body.dark-mode .alert-info {
                background-color: #1a3a4a;
                border-color: #0d6efd;
                color: #b8d4e0;
            }
            
            body.dark-mode .badge {
                background-color: #0d6efd;
            }
        </style>';
    }
    
    return '';
}
?>
