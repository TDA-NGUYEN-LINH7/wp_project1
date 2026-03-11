# 🔌 Hướng dẫn sử dụng Plugin API Connector

Plugin đã được tạo và push lên GitHub thành công! ✅

## 📍 Vị trí Plugin
```
wordpress/wp-content/plugins/api-connector/
├── api-connector.php    # File chính (300+ dòng code)
└── README.md           # Tài liệu chi tiết
```

## 🚀 Cách kích hoạt Plugin

### Bước 1: Truy cập WordPress Admin
```
http://localhost:8080/wp-admin
```

### Bước 2: Activate Plugin
1. Vào menu **Plugins** → **Installed Plugins**
2. Tìm plugin **"API Connector"**
3. Click nút **Activate**

### Bước 3: Test Plugin
Sau khi activate, bạn sẽ thấy menu mới **"API Connector"** trong sidebar Admin.

## 📝 Sử dụng Shortcode

### Cách 1: Thêm vào Page/Post
1. Vào **Pages** → **Add New**
2. Đặt tiêu đề: "Test API"
3. Trong editor, thêm shortcode:
   ```
   [api_users]
   ```
4. Click **Publish**
5. **View Page** để xem kết quả

### Cách 2: Giới hạn số lượng hiển thị
```
[api_users limit="5"]
```

### Cách 3: Thêm trực tiếp vào Theme
Thêm vào file template (ví dụ: `page.php`):
```php
<?php echo do_shortcode('[api_users]'); ?>
```

## 🎯 Kết quả mong đợi

Plugin sẽ hiển thị **bảng danh sách Users** với các cột:
- ID
- Tên (Name + Username)
- Email (có link mailto)
- Công ty

Dữ liệu được lấy từ API: `https://jsonplaceholder.typicode.com/users`

## ⚙️ Tính năng chính

### 1. WordPress HTTP API
✅ Sử dụng `wp_remote_get()` thay vì cURL  
✅ Tự động xử lý timeout, SSL  
✅ Tương thích mọi cấu hình server  

### 2. Cache thông minh
✅ Cache dữ liệu 1 giờ (Transients API)  
✅ Giảm số lần gọi API  
✅ Tự động refresh khi cần  

### 3. Admin Dashboard
✅ Menu riêng "API Connector"  
✅ Hướng dẫn sử dụng  
✅ Nút Test Connection  
✅ Code mẫu  

### 4. Security
✅ Escape tất cả output  
✅ Validate input  
✅ Kiểm tra permissions  

## 🔧 Tùy chỉnh API riêng của bạn

### Thay đổi API URL
Mở file: `wordpress/wp-content/plugins/api-connector/api-connector.php`

Tìm hàm `get_users_from_api()` (dòng ~70):
```php
public function get_users_from_api() {
    // THAY ĐỔI URL NÀY
    $api_url = 'https://your-api.com/endpoint';
    
    // Gọi API
    $users = $this->call_api($api_url, 'GET');
    
    return $users;
}
```

### Thêm Authentication Token
Tìm hàm `call_api()` (dòng ~35), thêm header:
```php
$args = array(
    'method'    => $method,
    'timeout'   => 15,
    'headers'   => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer YOUR_API_TOKEN_HERE',
    ),
);
```

### Gọi API POST
Thêm method mới vào class:
```php
public function create_record($data) {
    $api_url = 'https://your-api.com/create';
    
    return $this->call_api($api_url, 'POST', $data);
}
```

## 🧪 Test Plugin

### Test 1: Kiểm tra Admin Menu
✅ Vào Admin → Sidebar trái → Tìm icon "API Connector"  
✅ Click vào → Xem trang hướng dẫn  
✅ Click nút "Test Connection" → Kết quả màu xanh  

### Test 2: Kiểm tra Shortcode
✅ Tạo page mới, thêm `[api_users]`  
✅ Publish và view page  
✅ Thấy bảng 10 users với đầy đủ thông tin  

### Test 3: Kiểm tra Cache
✅ Load page lần 1 → Có thể hơi chậm (gọi API)  
✅ Load lại page → Rất nhanh (dùng cache)  
✅ Cache tự động hết hạn sau 1 giờ  

## 📊 Luồng hoạt động

```
User request page
     ↓
WordPress load shortcode [api_users]
     ↓
Plugin check cache (Transient)
     ↓
   Cache có? ←────── YES ──→ Return cached data
     ↓ NO
     ↓
Call wp_remote_get(API_URL)
     ↓
Parse JSON response
     ↓
Save to cache (1 hour)
     ↓
Return data
     ↓
Render HTML table
     ↓
Display to user
```

## 🛠️ Troubleshooting

### Plugin không hiển thị?
- Kiểm tra file có ở đúng thư mục: `wp-content/plugins/api-connector/`
- Kiểm tra có lỗi PHP không (enable WP_DEBUG)

### Shortcode không hoạt động?
- Đảm bảo plugin đã **Activate**
- Thử deactivate và activate lại
- Xóa cache browser

### API trả về lỗi?
1. Vào **API Connector** menu trong Admin
2. Click nút **Test Connection**
3. Nếu lỗi → Kiểm tra internet connection
4. Thử test API bằng Postman

### Enable Debug Mode
Thêm vào `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```
Xem log tại: `wp-content/debug.log`

## 📚 Tài liệu tham khảo

- **WordPress HTTP API**: https://developer.wordpress.org/plugins/http-api/
- **Transients API**: https://developer.wordpress.org/apis/transients/
- **Shortcode API**: https://developer.wordpress.org/apis/shortcode/
- **Plugin Handbook**: https://developer.wordpress.org/plugins/

## 🎓 Code Example nâng cao

### Ví dụ 1: POST với JSON body
```php
$api_url = 'https://api.example.com/users';
$data = array(
    'name'  => 'John Doe',
    'email' => 'john@example.com'
);

$response = wp_remote_post($api_url, array(
    'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . YOUR_TOKEN
    ),
    'body' => json_encode($data),
    'timeout' => 15
));

if (!is_wp_error($response)) {
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    // Handle $result
}
```

### Ví dụ 2: PUT request
```php
$response = wp_remote_request($api_url, array(
    'method' => 'PUT',
    'headers' => array('Content-Type' => 'application/json'),
    'body' => json_encode($data)
));
```

### Ví dụ 3: DELETE request
```php
$response = wp_remote_request($api_url, array(
    'method' => 'DELETE',
    'headers' => array('Authorization' => 'Bearer ' . YOUR_TOKEN)
));
```

## ✅ Checklist hoàn thành

- [x] Tạo thư mục plugin
- [x] Viết code plugin với WordPress HTTP API
- [x] Thêm Cache (Transients)
- [x] Tạo Shortcode `[api_users]`
- [x] Tạo Admin Menu với test button
- [x] Comment chi tiết bằng tiếng Việt
- [x] Viết README.md hướng dẫn
- [x] Push lên GitHub

## 🎉 Kết luận

Plugin **API Connector** đã sẵn sàng sử dụng!

➡️ **Next steps:**
1. Activate plugin trong WordPress Admin
2. Test shortcode trên page mới
3. Tùy chỉnh API URL theo nhu cầu của bạn
4. Thêm authentication nếu cần

**Happy coding!** 🚀
