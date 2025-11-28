<?php
// File: models/CartModel.php (Đã sửa lỗi logic/cú pháp và xử lý trả về null)

class CartModel {
    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection; 
    }
    
    /**
     * Lấy ID giỏ hàng (cart_id) của người dùng. Nếu chưa có, tạo mới.
     * @param int $user_id ID người dùng
     * @return int cart_id (Trả về 0 nếu thất bại)
     */
    public function getOrCreateCartId($user_id): int { // Khai báo kiểu trả về
        try {
            // Lấy giỏ hàng hiện có
            $sql = "SELECT id FROM cart WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); 
            $stmt->execute();
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart) {
                return (int)$cart['id'];
            } else {
                // Tạo giỏ hàng mới
                $sql = "INSERT INTO cart (user_id) VALUES (:user_id)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    return (int)$this->db->lastInsertId(); // Trả về INT
                }
                // Nếu execute() thất bại hoặc lastInsertId() trả về 0/false (hiếm)
                return 0; // Thay vì null, trả về 0 để đồng bộ kiểu trả về INT
            }
        } catch (PDOException $e) {
            // Xử lý lỗi CSDL (ví dụ: ghi log)
            // echo "Lỗi CSDL khi lấy/tạo giỏ hàng: " . $e->getMessage();
            return 0; // Trả về 0 khi có lỗi CSDL
        }
    }

    /**
     * Thêm hoặc cập nhật mặt hàng vào bảng cartdetail SQL của người dùng.
     * @param int $cart_id ID giỏ hàng
     * @param int $variant_id ID biến thể sản phẩm (product_variant_id)
     * @param int $quantity Số lượng sản phẩm
     */
    public function saveCartDetail($cart_id, $variant_id, $quantity): bool {
        // 1. Kiểm tra xem mặt hàng đã tồn tại trong cartdetail chưa
        $sql_check = "SELECT id, quantity FROM cartdetail WHERE cart_id = :cart_id AND productVariant_id = :variant_id";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
        $stmt_check->bindParam(':variant_id', $variant_id, PDO::PARAM_INT);
        $stmt_check->execute();
        $item = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            // Cập nhật số lượng
            $new_quantity = $item['quantity'] + $quantity;
            $sql_update = "UPDATE cartdetail SET quantity = :quantity WHERE id = :id";
            $stmt_update = $this->db->prepare($sql_update);
            
            $stmt_update->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
            $stmt_update->bindParam(':id', $item['id'], PDO::PARAM_INT);
            return $stmt_update->execute();
        } else {
            // Thêm mới
            $sql_insert = "INSERT INTO cartdetail (cart_id, productVariant_id, quantity) 
                            VALUES (:cart_id, :variant_id, :quantity)";
            $stmt_insert = $this->db->prepare($sql_insert);
            $stmt_insert->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':variant_id', $variant_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            return $stmt_insert->execute();
        }
    }

    /**
     * Lấy tất cả mặt hàng trong giỏ hàng SQL của người dùng.
     */
    public function getCartItemsByUserId($user_id) {
        $sql = "SELECT 
                    p.id AS product_id,
                    p.name, 
                    p.img AS image, 
                    p.category_id,
                    p.price AS price_original,
                    p.sale_price,
                    -- Sử dụng COALESCE để lấy sale_price nếu có, nếu không lấy price
                    COALESCE(p.sale_price, p.price) AS price_final, 
                    cd.quantity,
                    cd.productVariant_id AS variant_id,
                    s.name AS size_name,
                    c.name AS color_name
                FROM cartdetail cd
                JOIN cart ca ON cd.cart_id = ca.id
                JOIN product_variant pv ON cd.productVariant_id = pv.id
                JOIN products p ON pv.product_id = p.id 
                JOIN size s ON pv.size_id = s.id
                JOIN color c ON pv.color_id = c.id
                WHERE ca.user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Bạn có thể thêm các hàm khác như removeCartItem, updateCartItemQuantity ở đây.
}