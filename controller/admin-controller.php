<?php
// FILE: controller/admin-controller.php

class AdminController {

    public function categories(){
        global $conn;
        if (!isset($conn)) {
            $conn = mysqli_connect("localhost", "root", "", "duan_1");
            if (!$conn) {
                die("Lỗi kết nối CSDL: " . mysqli_connect_error());
            }
            mysqli_set_charset($conn, "utf8mb4");
        }
        include_once 'admin/categories.php';
    }

    public function customers(){
        include_once 'admin/customers.php';
    }

    public function orders(){
        include_once 'admin/orders.php';
    }

    public function products() {
        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../models/ProductModel.php';

        // 1. Kết nối & Model
        $database = new Database();
        $db = $database->getConnection();
        $productModel = new ProductModel($db);
        
        // 2. LẤY DỮ LIỆU DANH MỤC & GIỚI TÍNH
        $categories = $productModel->getAllCategories(); 
        $genders = $productModel->getAllGenders();

        // 3. XỬ LÝ POST (Lưu dữ liệu với upload ảnh)
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_product'])) {
            $data = [
                'name'        => $_POST['name'] ?? '',
                'price'       => $_POST['price'] ?? 0,
                'storage'     => $_POST['storage'] ?? 0,
                'description' => $_POST['description'] ?? '',
                'category_id' => $_POST['category_id'] ?? 1, 
                'id_gender'   => $_POST['gender_id'] ?? 1 // Sửa từ id_gender thành gender_id
            ];

            // Xử lý upload hình ảnh
            $uploadResult = $this->handleImageUpload();
            if ($uploadResult['main_image']) {
                $data['main_image'] = $uploadResult['main_image'];
            }
            if ($uploadResult['sub_images']) {
                $data['sub_images'] = $uploadResult['sub_images'];
            }

            if (!empty($_POST['product_id'])) {
                // Nếu là update, giữ lại ảnh cũ nếu không upload ảnh mới
                $data['id'] = $_POST['product_id'];
                $currentProduct = $productModel->getProductById($data['id']);
                if (!$uploadResult['main_image'] && isset($currentProduct['img'])) {
                    $data['current_main_image'] = $currentProduct['img'];
                }
                if (!$uploadResult['sub_images'] && isset($currentProduct['img_child'])) {
                    $data['current_sub_images'] = $currentProduct['img_child'];
                }

                if ($productModel->update($data)) {
                    echo "<script>alert('Cập nhật thành công!'); window.location.href='index.php?page=products';</script>";
                } else {
                    echo "<script>alert('Có lỗi khi cập nhật!');</script>";
                }
            } else {
                if ($productModel->insert($data)) {
                    echo "<script>alert('Thêm mới thành công!'); window.location.href='index.php?page=products';</script>";
                } else {
                    echo "<script>alert('Có lỗi khi thêm mới!');</script>";
                }
            }
        }

        // Xử lý Xóa
        if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
            if ($productModel->delete($_GET['id'])) {
                echo "<script>alert('Xóa thành công!'); window.location.href='index.php?page=products';</script>";
            } else {
                echo "<script>alert('Có lỗi khi xóa!'); window.location.href='index.php?page=products';</script>";
            }
        }

        // Xử lý Edit - Lấy dữ liệu sản phẩm để chỉnh sửa
        $productToEdit = null;
       if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $productToEdit = $productModel->getProductWithImages($_GET['id']);
}

        // Lấy danh sách hiển thị
        $productList = $productModel->getAllProducts();

        include_once 'admin/admin-products.php';
    }

    // Hàm xử lý upload hình ảnh
    private function handleImageUpload() {
        $result = [
            'main_image' => '',
            'sub_images' => ''
        ];

        $uploadDir = __DIR__ . '/../uploads/';
        
        // Tạo thư mục uploads nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Upload ảnh chính
        if (!empty($_FILES['main_image']['name'])) {
            $mainImageName = $this->uploadSingleImage($_FILES['main_image'], $uploadDir);
            if ($mainImageName) {
                $result['main_image'] = $mainImageName;
            }
        }

        // Upload ảnh phụ (nhiều ảnh)
        if (!empty($_FILES['sub_images']['name'][0])) {
            $subImages = [];
            foreach ($_FILES['sub_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['sub_images']['error'][$key] === 0) {
                    $fileInfo = [
                        'name' => $_FILES['sub_images']['name'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['sub_images']['error'][$key]
                    ];
                    $subImageName = $this->uploadSingleImage($fileInfo, $uploadDir);
                    if ($subImageName) {
                        $subImages[] = $subImageName;
                    }
                }
            }
            if (!empty($subImages)) {
                $result['sub_images'] = implode(',', $subImages);
            }
        }

        return $result;
    }

    // Hàm upload single image
    private function uploadSingleImage($file, $uploadDir) {
        if ($file['error'] !== 0) {
            return false;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            return false;
        }

        // Tạo tên file duy nhất
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $fileName;
        }

        return false;
    }

    public function thongke(){
        include_once 'admin/thongke.php';
    }

    // Các hàm mock giữ nguyên (xóa khi dùng Model thật)
    private function getMockProducts() {
        return [
            ['id' => 1, 'name' => 'Áo Polo KS25FH47C-SCWK', 'price' => 350000, 'category_name' => 'Áo', 'gender_name' => 'Nam', 'category_id' => 1, 'id_gender' => 1,
             'img' => 'ao-nam-Áo Polo KS25FH47C-SCWK-hình19.jpg', 'img_child' => 'ao-nam-Áo Polo KS25FH47C-SCWK-hình20.jpg',
             'description' => 'Mẫu áo Polo nam với chất liệu vải thoáng mát...', 'variants' => [
                 ['color_name' => 'Đen', 'size_name' => 'S', 'quantity' => 50],
                 ['color_name' => 'Trắng', 'size_name' => 'M', 'quantity' => 45],
                 ['color_name' => 'Xám', 'size_name' => 'L', 'quantity' => 30],
             ]],
            ['id' => 2, 'name' => 'Quần Tây Nam Form Ôm Vừa', 'price' => 480000, 'category_name' => 'Quần', 'gender_name' => 'Nam', 'category_id' => 2, 'id_gender' => 1,
             'img' => 'quan-tay-nam.jpg', 'img_child' => 'quan-tay-nam-2.jpg', 'description' => 'Quần tây công sở form ôm vừa, hiện đại.', 'variants' => [
                 ['color_name' => 'Xanh dương', 'size_name' => 'M', 'quantity' => 55],
                 ['color_name' => 'Đen', 'size_name' => 'L', 'quantity' => 40],
             ]],
        ];
    }
    private function getMockCategories() { return [['id' => 1, 'name' => 'Áo'], ['id' => 2, 'name' => 'Quần']]; }
    private function getMockGenders() { return [['id' => 1, 'name' => 'Nam'], ['id' => 2, 'name' => 'Nữ']]; }
    private function getMockColors() { return [['id' => 4, 'name' => 'Đen'], ['id' => 5, 'name' => 'Trắng']]; }
    private function getMockSizes() { return [['id' => 1, 'name' => 'S'], ['id' => 2, 'name' => 'M'], ['id' => 3, 'name' => 'L']]; }
}