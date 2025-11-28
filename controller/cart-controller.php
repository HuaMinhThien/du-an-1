<?php
// File: /controllers/CartController.php (CHỈ DÙNG SESSION VÀ ĐÃ THÊM SẢN PHẨM GỢI Ý)

require_once('models/ProductModel.php'); 
// BỎ: require_once('models/CartModel.php'); 
require_once('config/Database.php'); 

class CartController {
	private $productModel;
	private $db;

	public function __construct() {
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		
		// Khởi tạo ProductModel
		$this->db = (new Database())->getConnection(); 
		$this->productModel = new ProductModel($this->db);
	}

	/**
	 * Hiển thị trang giỏ hàng (pages/cart.php)
	 */
	public function index() {
		$success_message = $_SESSION['success_message'] ?? null;
		$error_message = $_SESSION['error_message'] ?? null;
		unset($_SESSION['success_message']); 
		unset($_SESSION['error_message']); 
		
		// CHỈ LẤY GIỎ HÀNG TỪ SESSION
		$cart_items = $_SESSION['cart'] ?? [];
		
		// =========================================================
		// 🚨 BỔ SUNG: LẤY SẢN PHẨM GỢI Ý
		// Lấy 4 sản phẩm ngẫu nhiên để gợi ý, sử dụng hàm đã sửa trong ProductModel
		$suggested_products = $this->productModel->getFeaturedProductsRandom(4);
		// =========================================================

		// Các biến: $success_message, $error_message, $cart_items, $suggested_products 
		// sẽ được truyền vào view pages/cart.php
		include_once 'pages/cart.php';
	}

	/**
	 * Xử lý hành động Thêm vào Giỏ (Add to Cart)
	 */
	public function add() {
		if (!isset($_SESSION['cart'])) {
			$_SESSION['cart'] = [];
		}

		// 1. Lấy dữ liệu từ POST
		$product_id = $_POST['product_id'] ?? null;
		$quantity = (int)($_POST['quantity'] ?? 1);
		$size_id = $_POST['size_id'] ?? null; 
		$color_id = $_POST['color_id'] ?? null; 
		$action_type = $_POST['action'] ?? 'add_to_cart';
		
		// Kiểm tra tính hợp lệ cơ bản
		if (!is_numeric($product_id) || !is_numeric($size_id) || !is_numeric($color_id) || $quantity <= 0) {
			$_SESSION['error_message'] = 'Lỗi: Thông tin sản phẩm không hợp lệ.';
			header('Location: ' . $_SERVER['HTTP_REFERER']); 
			exit();
		}

		// 2. Lấy thông tin sản phẩm và Variant ID
		$product_details = $this->productModel->getProductDetails((int)$product_id);
		$variant_id = $this->productModel->getVariantId((int)$product_id, (int)$color_id, (int)$size_id);
		$variant_details = $this->productModel->getVariantDetails($variant_id);

		if (!$product_details || !$variant_id || !$variant_details) {
			$_SESSION['error_message'] = 'Lỗi: Sản phẩm hoặc biến thể (Size/Color) không tồn tại.';
			header('Location: ' . $_SERVER['HTTP_REFERER']); 
			exit();
		}

		$size_name = $variant_details['size_name'];
		$color_name = $variant_details['color_name'];
		$final_price = $product_details['sale_price'] ?? $product_details['price'];
		
		// =========================================================
		// 3. LOGIC LƯU TRỮ (CHỈ SESSION)
		// =========================================================
		$cart_item_key = $variant_id; // Dùng variant_id làm key
		
		if (isset($_SESSION['cart'][$cart_item_key])) {
			$_SESSION['cart'][$cart_item_key]['quantity'] += $quantity;
		} else {
			$_SESSION['cart'][$cart_item_key] = [
				'product_id' => $product_details['id'],
				'name' => $product_details['name'],
				'price_final' => $final_price, 
				'size_name' => $size_name,
				'color_name' => $color_name,
				'image' => $product_details['image'],
				// 🚨 ĐÃ THÊM: Lưu category_id để tạo đường dẫn ảnh chính xác
				'category_id' => $product_details['category_id'],
				'quantity' => $quantity,
				'variant_id' => $variant_id,
			];
		}
		
		// 4. Thiết lập thông báo thành công
		$_SESSION['success_message'] = '🎉 Đã thêm sản phẩm "' . $product_details['name'] . ' - Màu: ' . $color_name . ' - Size: ' . $size_name . '" vào giỏ hàng thành công!';

		// 5. Chuyển hướng sau khi xử lý
		if ($action_type === 'buy_now') {
			header('Location: index.php?page=checkout'); 
		} else {
			header('Location: index.php?page=cart'); 
		}
		exit();
	}
	
	/**
	 * Xóa mặt hàng khỏi Session
	 */
	public function remove() {
		$variant_id = $_GET['variant_id'] ?? null;

		if (!is_numeric($variant_id) || $variant_id <= 0) {
			$_SESSION['error_message'] = 'Lỗi: Sản phẩm cần xóa không hợp lệ.';
			header('Location: index.php?page=cart');
			exit();
		}

		$cart_item_key = (int)$variant_id; 
		if (isset($_SESSION['cart'][$cart_item_key])) {
			unset($_SESSION['cart'][$cart_item_key]);
			$_SESSION['success_message'] = '✅ Đã xóa sản phẩm khỏi giỏ hàng.';
		}

		header('Location: index.php?page=cart');
		exit();
	}

	/**
	 * Cập nhật số lượng trong Session
	 */
	public function update_quantity() {
		$variant_id = $_POST['variant_id'] ?? null;
		$new_quantity = (int)($_POST['quantity'] ?? 1); 

		if (!is_numeric($variant_id) || $new_quantity <= 0) {
			$_SESSION['error_message'] = 'Lỗi: Thông tin cập nhật không hợp lệ.';
			header('Location: index.php?page=cart');
			exit();
		}

		$cart_item_key = (int)$variant_id;
		if (isset($_SESSION['cart'][$cart_item_key])) {
			$_SESSION['cart'][$cart_item_key]['quantity'] = $new_quantity;
			$_SESSION['success_message'] = '🔄 Đã cập nhật số lượng sản phẩm.';
		}

		header('Location: index.php?page=cart');
		exit();
	}
}