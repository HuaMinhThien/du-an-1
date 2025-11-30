<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- BỐ CỤC CHUNG (LAYOUT) --- */
        .main-content {
            padding: 20px;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .recent-grid {
            display: grid;
            grid-template-columns: 70% auto;
            gap: 20px;
            margin-top: 20px;
            align-items: start;
        }
        
        /* --- CARD STYLE --- */
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
        }
        
        /* Header màu Xanh Đen */
        .card-header {
            background-color: #001f3f; 
            padding: 15px 20px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        .card-header h3 { 
            margin: 0; 
            font-size: 18px; 
            font-weight: 600; 
        }
        
        .card-body { 
            padding: 20px; 
        }

        /* --- FORM STYLE --- */
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 500; 
            font-size: 14px; 
            color: #333; 
        }
        
        input, select, textarea { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-size: 14px; 
            outline: none;
            transition: all 0.3s;
        }
        input:focus, select:focus, textarea:focus { 
            border-color: #001f3f; 
            box-shadow: 0 0 5px rgba(0, 31, 63, 0.2);
        }
        
        .form-group-row { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
        }

        /* Nút bấm Xanh Đen */
        .btn-primary { 
            background: #001f3f; 
            color: white; 
            border: none; 
            padding: 12px 20px; 
            border-radius: 6px; 
            width: 100%; 
            font-weight: 600; 
            cursor: pointer; 
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-primary:hover { 
            background: #003366; 
            transform: translateY(-2px);
        }

        /* --- BẢNG (TABLE) STYLE --- */
        .table-responsive { 
            overflow-x: auto; 
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        
        /* Header bảng */
        thead tr { 
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        th { 
            padding: 15px 12px; 
            font-size: 14px; 
            font-weight: 600; 
            color: #495057;
            text-align: left;
            white-space: nowrap;
        }
        
        /* Dữ liệu bảng */
        tbody tr { 
            border-bottom: 1px solid #dee2e6; 
            transition: background 0.2s; 
        }
        tbody tr:hover { 
            background-color: #f8f9fa; 
        }
        
        td { 
            padding: 15px 12px; 
            font-size: 14px; 
            color: #333; 
            vertical-align: middle;
        }

        /* --- CĂN CHỈNH CỘT --- */
        /* ID */
        td:nth-child(1) { 
            color: #6c757d; 
            font-weight: 500;
        }
        
        /* Hình ảnh */
        td:nth-child(2) { 
            text-align: center; 
        }
        
        /* Tên SP */
        td:nth-child(3) { 
            font-weight: 500; 
            color: #001f3f;
        }
        
        /* Danh mục & Giới tính */
        td:nth-child(4), 
        td:nth-child(5) { 
            color: #6c757d;
        }
        
        /* Giá */
        td:nth-child(6) { 
            font-weight: 600; 
            color: #dc3545;
        }
        
        /* Kho */
        td:nth-child(7) { 
            text-align: center; 
            font-weight: 500;
        }

        /* Hình ảnh sản phẩm */
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #e9ecef;
        }

        /* ICON STYLE */
        .action-buttons { 
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .action-btn { 
            border: none; 
            background: none; 
            cursor: pointer; 
            font-size: 16px; 
            padding: 8px;
            border-radius: 4px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
        }
        
        .action-btn.edit { 
            background: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeaa7;
        } 
        .action-btn.edit:hover { 
            background: #ffeaa7; 
            transform: scale(1.1);
        }
        
        .action-btn.delete { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }
        .action-btn.delete:hover { 
            background: #f5c6cb; 
            transform: scale(1.1);
        }

        /* Upload hình ảnh */
        .image-upload-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .upload-section {
            border: 2px dashed #dee2e6;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .upload-section:hover {
            border-color: #001f3f;
        }
        
        .upload-section h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #495057;
            font-weight: 500;
        }
        
        .image-preview {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }
        
        .preview-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #001f3f;
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #001f3f;
        }
        
        .user-details h4 {
            margin: 0;
            font-size: 16px;
            color: #001f3f;
        }
        
        .user-details small {
            color: #6c757d;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 1200px) { 
            .recent-grid { 
                grid-template-columns: 1fr; 
            } 
        }
        
        @media (max-width: 768px) {
            .form-group-row,
            .image-upload-container {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                justify-content: center;
            }
        }

        /* Status badges */
        .status-in-stock {
            background: #d1edff;
            color: #004085;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-low-stock {
            background: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <h1 class="page-title">Quản Lý Sản Phẩm</h1>
            <div class="user-info">
                <img src="https://via.placeholder.com/45" alt="Admin" class="user-avatar">
                <div class="user-details">
                    <h4>Admin</h4>
                    <small>Super Admin</small>
                </div>
            </div>
        </div>

        <main>
            <div class="recent-grid">
                
                <!-- Danh sách sản phẩm -->
                <div class="card">
                    <div class="card-header">
                        <h3>Danh Sách Sản Phẩm</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hình ảnh</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Danh mục</th>
                                        <th>Giới tính</th>
                                        <th>Giá</th>
                                        <th>Kho</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($productList)): ?>
                                        <?php foreach ($productList as $prod): ?>
                                            <tr>
                                                <td>#<?= $prod['id'] ?></td>
                                                <td>
                                                    <?php if (!empty($prod['img'])): ?>
                                                        <img src="../uploads/<?= $prod['img'] ?>" alt="<?= $prod['name'] ?>" class="product-img" onerror="this.src='https://via.placeholder.com/50'">
                                                    <?php else: ?>
                                                        <img src="https://via.placeholder.com/50" alt="No image" class="product-img">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($prod['name']) ?></td>
                                                <td><?= htmlspecialchars($prod['category_name'] ?? '---') ?></td>
                                                <td><?= htmlspecialchars($prod['gender_name'] ?? '---') ?></td>
                                                <td><?= number_format($prod['price']) ?>đ</td>
                                                <td>
                                                    <span class="<?= $prod['storage'] > 20 ? 'status-in-stock' : 'status-low-stock' ?>">
                                                        <?= $prod['storage'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="index.php?page=products&action=edit&id=<?= $prod['id'] ?>" class="action-btn edit" title="Sửa">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="index.php?page=products&action=delete&id=<?= $prod['id'] ?>" 
                                                           onclick="return confirm('Bạn chắc chắn muốn xóa sản phẩm <?= htmlspecialchars($prod['name']) ?>?')" 
                                                           class="action-btn delete" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" style="text-align:center; padding: 30px; color: #6c757d;">
                                                <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                                                <br>
                                                Chưa có dữ liệu sản phẩm.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Form thêm/sửa sản phẩm -->
                <div class="card">
                    <div class="card-header">
                        <h3><?= !empty($productToEdit) ? 'Cập Nhật Sản Phẩm' : 'Thêm Sản Phẩm Mới' ?></h3>
                        <?php if(!empty($productToEdit)): ?>
                            <a href="index.php?page=products" style="color: #ccc; font-size: 12px; text-decoration: none;">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="product_id" value="<?= $productToEdit['id'] ?? '' ?>">

                            <div class="form-group">
                                <label for="product_name"><i class="fas fa-tag"></i> Tên sản phẩm</label>
                                <input type="text" name="name" id="product_name" required 
                                       value="<?= htmlspecialchars($productToEdit['name'] ?? '') ?>" 
                                       placeholder="Nhập tên sản phẩm">
                            </div>

                            <div class="form-group-row">
                                <div class="form-group">
                                    <label for="product_price"><i class="fas fa-dollar-sign"></i> Giá bán (đ)</label>
                                    <input type="number" name="price" id="product_price" required 
                                           value="<?= $productToEdit['price'] ?? '' ?>" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="product_storage"><i class="fas fa-box"></i> Số lượng kho</label>
                                    <input type="number" name="storage" id="product_storage" required 
                                           value="<?= $productToEdit['storage'] ?? '' ?>" min="0">
                                </div>
                            </div>

                            <div class="form-group-row">
                                <div class="form-group">
                                    <label for="product_category"><i class="fas fa-list"></i> Danh mục</label>
                                    <select name="category_id" id="product_category">
                                        <?php foreach($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" 
                                                <?= (isset($productToEdit) && $productToEdit['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="product_gender"><i class="fas fa-venus-mars"></i> Giới tính</label>
                                    <select name="gender_id" id="product_gender">
                                        <?php foreach($genders as $gen): ?>
                                            <option value="<?= $gen['id'] ?>" 
                                                <?= (isset($productToEdit) && $productToEdit['id_gender'] == $gen['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($gen['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Upload hình ảnh -->
                            <div class="image-upload-container">
                                <div class="upload-section">
                                    <h4><i class="fas fa-image"></i> Hình ảnh chính</h4>
                                    <input type="file" name="main_image" accept="image/*">
                                    <?php if (!empty($productToEdit['img'])): ?>
                                        <div class="image-preview">
                                            <img src="../uploads/<?= $productToEdit['img'] ?>" alt="Main image" class="preview-img">
                                        </div>
                                        <small>Ảnh hiện tại</small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="upload-section">
                                    <h4><i class="fas fa-images"></i> Hình ảnh phụ (nhiều ảnh)</h4>
                                    <input type="file" name="sub_images[]" multiple accept="image/*">
                                    <?php if (!empty($productToEdit['sub_images'])): ?>
                                        <div class="image-preview">
                                            <?php foreach($productToEdit['sub_images'] as $subImg): ?>
                                                <img src="../uploads/<?= $subImg ?>" alt="Sub image" class="preview-img">
                                            <?php endforeach; ?>
                                        </div>
                                        <small>Ảnh phụ hiện tại</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="product_description"><i class="fas fa-file-alt"></i> Mô tả sản phẩm</label>
                                <textarea rows="4" name="description" id="product_description" placeholder="Nhập mô tả sản phẩm..."><?= htmlspecialchars($productToEdit['description'] ?? '') ?></textarea>
                            </div>
                            
                            <button type="submit" name="save_product" class="btn-primary">
                                <i class="fas fa-<?= !empty($productToEdit) ? 'sync' : 'plus' ?>"></i>
                                <?= !empty($productToEdit) ? 'CẬP NHẬT SẢN PHẨM' : 'THÊM SẢN PHẨM MỚI' ?>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        // Hiển thị preview ảnh khi chọn file
        document.addEventListener('DOMContentLoaded', function() {
            const mainImageInput = document.querySelector('input[name="main_image"]');
            const subImagesInput = document.querySelector('input[name="sub_images[]"]');
            
            if (mainImageInput) {
                mainImageInput.addEventListener('change', function(e) {
                    previewImage(e.target, '.upload-section:nth-child(1) .image-preview');
                });
            }
            
            if (subImagesInput) {
                subImagesInput.addEventListener('change', function(e) {
                    previewMultipleImages(e.target, '.upload-section:nth-child(2) .image-preview');
                });
            }
            
            function previewImage(input, previewSelector) {
                const preview = document.querySelector(previewSelector);
                if (!preview) return;
                
                preview.innerHTML = '';
                
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'preview-img';
                        preview.appendChild(img);
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }
            
            function previewMultipleImages(input, previewSelector) {
                const preview = document.querySelector(previewSelector);
                if (!preview) return;
                
                preview.innerHTML = '';
                
                if (input.files) {
                    for (let i = 0; i < input.files.length; i++) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'preview-img';
                            preview.appendChild(img);
                        }
                        
                        reader.readAsDataURL(input.files[i]);
                    }
                }
            }
        });
    </script>
</body>
</html>