<?php

// Bắt đầu Session cho tất cả các trang
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kéo header vào trước
include_once 'includes/header.php';

// 1. Lấy tên trang (page)
$page = $_GET['page'] ?? 'home'; 
$action = $_GET['action'] ?? null; 

$controller = null; // Khởi tạo biến Controller
$controller_file = '';

// =========================================================
// 2. LOGIC ĐỊNH TUYẾN (Routing Logic)
// =========================================================
$controller = null; 
$method_to_call = $page; 
$controller_name = '';
$controller_file = '';


if ($page === 'cart') {
    $controller_name = 'CartController';
    $controller_file = 'controller/cart-controller.php'; 
    $method_to_call = $action ?? 'index'; 

} else {
    $controller_name = 'HomeController';
    $controller_file = 'controller/home-controller.php';
    $method_to_call = $page; 
}


require $controller_file; 
$controller = new $controller_name(); 


// 4. Gọi phương thức
if ($controller && method_exists($controller, $method_to_call)) {
    // Gọi phương thức tương ứng
    $controller->$method_to_call();
} else {
    if (!($controller instanceof HomeController)) {
        // Nếu chưa nạp HomeController, nạp nó vào
        require 'controller/home-controller.php'; 
        $controller = new HomeController();
    }
    $controller->home(); 
}

// Kéo footer vào
include_once 'includes/footer.php';
?>