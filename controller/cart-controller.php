<?php
// File: /controller/CartController.php

$root_path = dirname(__DIR__); 

// SỬ DỤNG $root_path ĐỂ INCLUDE CÁC FILE KHÁC
require_once($root_path . '/models/ProductModel.php'); 
require_once($root_path . '/models/CartModels.php'); // Dùng tên file CartModels.php
require_once($root_path . '/config/Database.php'); // Giả định file config nằm trong /config/
class CartController {
    private $productModel;
    private $cartModel; 
    private $db;
    private $userId; // ID người dùng hiện tại

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = (new Database())->getConnection(); 

        $this->productModel = new ProductModel($this->db);
        $this->cartModel = new CartModel($this->db); // Tên Class là CartModel

        $this->userId = $_SESSION['user_id'] ?? 2; 
    // Nếu bạn muốn test với ID 2, bạn nên chuyển nó về mặc định này trước khi deploy.
    // $this->userId = 2; // XÓA HOẶC COMMENT DÒNG NÀY    
    }

    /**
     * Hiển thị trang giỏ hàng (pages/cart.php)
     */
    public function index() {
        // Giữ nguyên việc lấy thông báo để hiển thị trên trang giỏ hàng nếu cần
        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        // KHÔNG unset ở đây nếu muốn Toast hiển thị. Đã unset trong header.php.
        // unset($_SESSION['success_message']); 
        // unset($_SESSION['error_message']); 
        unset($_SESSION['success_message'], $_SESSION['error_message']); 
        
        // 2. LẤY DỮ LIỆU GIỎ HÀNG DỰA TRÊN USER ID
        $cart_items = $this->cartModel->getCartItemsByUserId($this->userId);
        
        // 3. Tính toán tổng tiền
        $total_amount = 0;
        foreach ($cart_items as $item) {
            // Lưu ý: p.price là giá gốc của sản phẩm, cần tính thành tiền
            $total_amount += $item['price'] * $item['quantity'];
            
            // Thêm trường 'sub_total' cho View dễ sử dụng
            $item['sub_total'] = $item['price'] * $item['quantity'];
        }

        // LẤY SẢN PHẨM GỢI Ý
        $suggested_products = $this->productModel->getFeaturedProductsRandom(4);

        include_once 'pages/cart.php';
        // Bạn sẽ cần truyền các biến này đến View (ví dụ: $data['cart_items'] = $cart_items)
        return ['cart_items' => $cart_items, 'total_amount' => $total_amount, 'user_id' => $this->userId, 'success_message' => $success_message, 'error_message' => $error_message];
    }

    /**
     * Xử lý hành động Thêm vào Giỏ (Add to Cart)
     */

    public function handleRequest() {
        $action = $_GET['action'] ?? 'index';

        switch ($action) {
            case 'index':
                return $this->index(); // Trả về dữ liệu để router tải View
            case 'add':
                $this->add_to_cart();
                break;
            case 'remove':
                $this->remove();
                break;
            case 'update':
                $this->update_quantity();
                break;
            default:
                // Xử lý lỗi hoặc gọi index
                return $this->index(); 
        }
        return []; // Tránh lỗi nếu các action chuyển hướng
    }
    public function add_to_cart() {
        // 1. Lấy dữ liệu từ POST
        $product_id = $_POST['product_id'] ?? null;
        $color_id = $_POST['color_id'] ?? 1; // Mặc định color_id=1 nếu không chọn
        $size_id = $_POST['size_id'] ?? 1;   // Mặc định size_id=1 nếu không chọn
        $quantity = (int)($_POST['quantity'] ?? 1); 

        // Lấy trang trước đó để chuyển hướng
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=products';

        if (!is_numeric($product_id) || $quantity <= 0) {
            $_SESSION['error_message'] = 'Lỗi: Thông tin sản phẩm không hợp lệ.';
            header('Location: ' . $referer);
            exit();
        }

        // 2. Tìm Variant ID (ID của phiên bản sản phẩm)
        // Đây là bước quan trọng để biết chính xác biến thể nào được mua
        $variant_id = $this->productModel->getVariantId(
            (int)$product_id, 
            (int)$color_id, 
            (int)$size_id
        );

        if (!$variant_id) {
            $_SESSION['error_message'] = 'Lỗi: Không tìm thấy biến thể sản phẩm này trong kho.';
            header('Location: ' . $referer);
            exit();
        }

        // 3. Thêm/Cập nhật sản phẩm vào giỏ hàng qua CartModel
        $add_result = $this->cartModel->addItem($this->userId, (int)$variant_id, $quantity);

        if ($add_result) {
            $_SESSION['success_message'] = '✅ Đã thêm sản phẩm vào giỏ hàng thành công.';
        } else {
            $_SESSION['error_message'] = 'Lỗi: Không thể thêm sản phẩm vào giỏ hàng (Lỗi SQL).';
        }

        // 4. Chuyển hướng về trang giỏ hàng
        header('Location: index.php?page=cart');
        exit();
    }
    
    /**
     * Xóa mặt hàng khỏi SQL
     */
    public function remove() {
        $variant_id = $_GET['key'] ?? null; 
        
        // Lấy trang trước đó để chuyển hướng (thường là trang giỏ hàng)
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=cart';
        
        if (!is_numeric($variant_id) || $variant_id <= 0) {
            $_SESSION['error_message'] = 'Lỗi: Sản phẩm cần xóa không hợp lệ.';
            header('Location: ' . $referer);
            exit();
        }

        // 🚨 XÓA TỪ SQL
        $remove_result = $this->cartModel->removeItem($this->userId, (int)$variant_id);

        if ($remove_result) {
            $_SESSION['success_message'] = '✅ Đã xóa sản phẩm khỏi giỏ hàng.';
        } else {
            $_SESSION['error_message'] = 'Lỗi: Không thể xóa sản phẩm khỏi giỏ hàng (Lỗi SQL).';
        }

        // 🚨 SỬA: Chuyển hướng quay lại trang cũ (cart)
        header('Location: ' . $referer);
        exit();
    }

    /**
     * Cập nhật số lượng trong SQL
     */
    public function update_quantity() {
        $variant_id = $_POST['variant_id'] ?? null;
        $new_quantity = (int)($_POST['quantity'] ?? 1); 
        
        // Lấy trang trước đó để chuyển hướng (thường là trang giỏ hàng)
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=cart';

        if (!is_numeric($variant_id) || $new_quantity <= 0) {
            $_SESSION['error_message'] = 'Lỗi: Thông tin cập nhật không hợp lệ.';
            header('Location: ' . $referer);
            exit();
        }

        // 🚨 CẬP NHẬT TRONG SQL
        $update_result = $this->cartModel->updateQuantity($this->userId, (int)$variant_id, $new_quantity);

        if ($update_result) {
            $_SESSION['success_message'] = '🔄 Đã cập nhật số lượng sản phẩm.';
        } else {
            $_SESSION['error_message'] = 'Lỗi: Không thể cập nhật số lượng (Lỗi SQL).';
        }

        // 🚨 SỬA: Chuyển hướng quay lại trang cũ (cart)
        header('Location: ' . $referer);
        exit();
    }
}