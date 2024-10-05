<?php
session_start();
require_once 'config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'login';

// Routing logic
switch ($page) {
    case 'admin_dashboard':
        if (isAdmin()) {
            require 'admin/dashboard.php';
        } else {
            header('Location: index.php?page=login');
            exit;
        }
        break;
        
    case 'admin_events':
        if (isAdmin()) {
            require 'admin/events.php';
        } else {
            header('Location: index.php?page=login');
            exit;
        }
        break;

    case 'user_dashboard':
        if (isLoggedIn()) {
            require 'user/dashboard.php';
        } else {
            header('Location: index.php?page=login');
            exit;
        }
        break;

    case 'event_details':
        if (isLoggedIn()) {
            require 'user/event_details.php';
        } else {
            header('Location: index.php?page=login');
            exit;
        }
        break;

    case 'login':
        require 'user/login.php';
        break;

    case 'register':
        require 'user/register.php';
        break;
        case 'logout':
            session_destroy();
            header('Location: index.php?page=login');
            exit;
        
    default:
        require 'user/login.php';
        break;
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}