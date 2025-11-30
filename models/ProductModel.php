<?php
// File: models/ProductModel.php (Đã bổ sung đầy đủ chức năng)

class ProductModel {
    private $db; 

    public function __construct($db_connection) {
        $this->db = $db_connection; 
    }

    // Lấy tất cả danh mục
    public function getAllCategories() {
        $sql = "SELECT id, name FROM category ORDER BY id ASC"; 
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy tất cả giới tính
    public function getAllGenders() {
        $sql = "SELECT id, name FROM gender ORDER BY id ASC"; 
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hàm lấy tất cả sản phẩm với đầy đủ thông tin
    public function getAllProducts() {
        $sql = "SELECT p.id, p.name, p.price, p.storage, p.description, p.category_id, p.id_gender, p.img, p.img_child,
                       c.name AS category_name,
                       g.name AS gender_name
                FROM products p
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN gender g ON p.id_gender = g.id
                ORDER BY p.id ASC"; 
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy thông tin sản phẩm với hình ảnh
    public function getProductWithImages($id) {
        $sql = "SELECT p.*, c.name as category_name, g.name as gender_name 
                FROM products p 
                LEFT JOIN category c ON p.category_id = c.id 
                LEFT JOIN gender g ON p.id_gender = g.id 
                WHERE p.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Xử lý hình ảnh phụ (nếu có)
            if (!empty($product['img_child'])) {
                $product['sub_images'] = array_filter(explode(',', $product['img_child']));
            } else {
                $product['sub_images'] = [];
            }
        }
        
        return $product;
    }

    // Hàm lấy chi tiết một sản phẩm 
    public function getProductDetails($id) {
        $sql = "SELECT id, name, price, description, 
                 img AS image, img_child AS image_child, category_id, gender_id 
             FROM products 
             WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $product['thumbnails'] = [$product['image'], $product['image_child'], $product['image']];
            $product['sale_price'] = $product['price']; 
            $product['description_full'] = $product['description']; 
        }
        
        return $product;
    }

    public function getAvailableVariants($product_id) {
        $sql = "SELECT DISTINCT pv.color_id, c.name AS color_name, pv.size_id, s.name AS size_name
                FROM product_variant pv
                JOIN color c ON pv.color_id = c.id
                JOIN size s ON pv.size_id = s.id
                WHERE pv.product_id = :pid";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':pid', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $variants_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $colors = [];
        $sizes = [];

        foreach ($variants_raw as $row) {
            $colors[$row['color_id']] = ['id' => $row['color_id'], 'name' => $row['color_name']];
            $sizes[$row['size_id']] = ['id' => $row['size_id'], 'name' => $row['size_name']];
        }

        return [
            'colors' => array_values($colors), 
            'sizes' => array_values($sizes)
        ];
    }

    // Hàm lấy sản phẩm liên quan
    public function getRelatedProducts($category_id, $current_product_id, $limit = 4) {
        $sql = "SELECT id, name, price, img AS image, category_id
                FROM products 
                WHERE category_id = :category_id 
                AND id != :current_product_id 
                ORDER BY RAND() 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':current_product_id', $current_product_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Hàm lấy số lượng sản phẩm ngẫu nhiên
    public function getFeaturedProductsRandom($limit = 10) {
        $sql = "SELECT id, name, price, img AS image, category_id
                 FROM products 
                 ORDER BY RAND() 
                 LIMIT ?"; 
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Hàm lấy Variant ID
    public function getVariantId($product_id, $color_id, $size_id) {
        $sql = "SELECT id FROM product_variant 
             WHERE product_id = :pid AND color_id = :cid AND size_id = :sid";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':pid', $product_id);
        $stmt->bindParam(':cid', $color_id);
        $stmt->bindParam(':sid', $size_id);
        $stmt->execute();
        return $stmt->fetchColumn(); 
    }

    // Hàm lấy Variant Details
    public function getVariantDetails($variant_id) {
        $sql = "SELECT 
             pv.quantity, s.name AS size_name, c.name AS color_name
             FROM product_variant pv
             JOIN size s ON pv.size_id = s.id
             JOIN color c ON pv.color_id = c.id
             WHERE pv.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $variant_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProductById($id) {
        return $this->getProductWithImages($id);
    }

    public function getProductVariants($product_id) {
        $sql = "SELECT 
                    pv.id AS variant_id,
                    pv.size_id, s.name AS size_name,
                    pv.color_id, c.name AS color_name,
                    pv.quantity AS stock_quantity
                FROM product_variant pv
                JOIN size s ON pv.size_id = s.id
                JOIN color c ON pv.color_id = c.id
                WHERE pv.product_id = :product_id
                ORDER BY pv.size_id, pv.color_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =================================================================
    // PHẦN 2: CÁC HÀM CUD (CREATE - UPDATE - DELETE) CHO ADMIN
    // =================================================================

    // Hàm insert với upload ảnh
    public function insert($data) {
        $sql = "INSERT INTO products (name, price, storage, description, category_id, id_gender, img, img_child) 
                VALUES (:name, :price, :storage, :description, :category_id, :id_gender, :img, :img_child)";
        
        $stmt = $this->db->prepare($sql);

        $name = htmlspecialchars(strip_tags($data['name']));
        $description = htmlspecialchars(strip_tags($data['description']));
        
        // Xử lý hình ảnh
        $main_image = $data['main_image'] ?? 'default.jpg';
        $sub_images = $data['sub_images'] ?? '';

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':storage', $data['storage']);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':id_gender', $data['id_gender']);
        $stmt->bindParam(':img', $main_image);
        $stmt->bindParam(':img_child', $sub_images);

        return $stmt->execute();
    }

    // Hàm update với upload ảnh
    public function update($data) {
        $sql = "UPDATE products 
                SET name = :name, price = :price, storage = :storage, description = :description, 
                    category_id = :category_id, id_gender = :id_gender, img = :img, img_child = :img_child
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);

        $name = htmlspecialchars(strip_tags($data['name']));
        $description = htmlspecialchars(strip_tags($data['description']));

        // Xử lý hình ảnh
        $main_image = $data['main_image'] ?? ($data['current_main_image'] ?? 'default.jpg');
        $sub_images = $data['sub_images'] ?? ($data['current_sub_images'] ?? '');

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':storage', $data['storage']);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':id_gender', $data['id_gender']);
        $stmt->bindParam(':img', $main_image);
        $stmt->bindParam(':img_child', $sub_images);
        $stmt->bindParam(':id', $data['id']);

        return $stmt->execute();
    }

    // Xóa sản phẩm
    public function delete($id) {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Lấy tất cả màu sắc
    public function getAllColors() {
        $sql = "SELECT id, name FROM color ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy tất cả kích thước
    public function getAllSizes() {
        $sql = "SELECT id, name FROM size ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}