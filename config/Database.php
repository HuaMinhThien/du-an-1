<?php
// File: config/Database.php

class Database {
    private $host = "localhost";
    private $db_name = "duan_1";
    private $username = "root";
    private $password = "";
    public $conn;

    // Hàm để lấy kết nối cơ sở dữ liệu
    public function getConnection() {
        $this->conn = null;
        
        try {
            // Sử dụng PDO để tạo kết nối
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            // Thiết lập chế độ lỗi để PDO ném exception khi có lỗi SQL
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        } catch(PDOException $exception) {
            echo "Lỗi kết nối CSDL: " . $exception->getMessage();
            die(); // Dừng ứng dụng nếu không kết nối được
        }

        return $this->conn;
    }
}