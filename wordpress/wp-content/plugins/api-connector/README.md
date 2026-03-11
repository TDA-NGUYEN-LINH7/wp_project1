# API Connector Plugin - Hướng dẫn chi tiết

## 📋 Giới thiệu

Plugin WordPress để kết nối và lấy dữ liệu từ API bên ngoài sử dụng **WordPress HTTP API** (chuẩn và an toàn hơn cURL).

## 🚀 Cài đặt

### Bước 1: Kích hoạt Plugin
1. Đăng nhập WordPress Admin: `http://localhost:8080/wp-admin`
2. Vào **Plugins** > **Installed Plugins**
3. Tìm **API Connector** và click **Activate**

### Bước 2: Sử dụng Shortcode
Thêm shortcode vào bất kỳ trang/bài viết nào:

```
[api_users]
```

Hoặc giới hạn số lượng hiển thị:

```
[api_users limit="5"]
```

### Bước 3: Kiểm tra
- Vào **Pages** > **Add New**
- Nhập tiêu đề và thêm shortcode `[api_users]` vào nội dung
- Click **Publish** và xem kết quả

## 🔧 Tính năng

### 1. WordPress HTTP API
Plugin sử dụng các hàm chuẩn WordPress:
- `wp_remote_get()` - GET request
- `wp_remote_post()` - POST request  
- `wp_remote_request()` - Custom request

### 2. Cache thông minh
- Dữ liệu được cache 1 giờ (sử dụng Transients API)
- Giảm số lần gọi API
- Tự động xóa cache khi activate/deactivate

### 3. Xử lý lỗi
- Kiểm tra WP_Error
- Validate HTTP status code
- Hiển thị thông báo lỗi thân thiện

### 4. Security
- Escape output với `esc_html()`, `esc_attr()`
- Kiểm tra permissions
- Bảo vệ file khỏi direct access

## 📝 Tùy chỉnh Plugin

### Thay đổi API URL
Mở file `api-connector.php` và tìm hàm `get_users_from_api()`:

```php
public function get_users_from_api() {
    // Thay đổi URL này thành API của bạn
    $api_url = 'https://your-api.com/endpoint';
    
    // ... code tiếp
}
```

### Thêm Authentication
Trong hàm `call_api()`, thêm header Authorization:

```php
$args = array(
    'method'    => $method,
    'timeout'   => 15,
    'headers'   => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer YOUR_API_TOKEN',
    ),
);
```

### Gọi API POST
```php
// Ví dụ trong plugin
public function create_user($name, $email) {
    $data = array(
        'name'  => $name,
        'email' => $email,
    );
    
    return $this->call_api(
        'https://your-api.com/users',
        'POST',
        $data
    );
}
```

### Thay đổi thời gian Cache
```php
// Trong hàm get_users_from_api()
// Thay HOUR_IN_SECONDS thành:
set_transient($cache_key, $users, DAY_IN_SECONDS);   // 1 ngày
set_transient($cache_key, $users, MINUTE_IN_SECONDS); // 1 phút
set_transient($cache_key, $users, 300);              // 5 phút
```

## 🎨 Custom HTML Output

Để thay đổi cách hiển thị, edit hàm `display_api_users()`:

```php
// Thay thế phần HTML bên trong ob_start() và ob_get_clean()
ob_start();
?>
<div class="my-custom-layout">
    <?php foreach ($users as $user) : ?>
        <div class="user-card">
            <h4><?php echo esc_html($user['name']); ?></h4>
            <p><?php echo esc_html($user['email']); ?></p>
        </div>
    <?php endforeach; ?>
</div>
<?php
return ob_get_clean();
```

## 🧪 Test API từ Admin

1. Vào **API Connector** trong menu Admin
2. Click nút **Test Connection**
3. Xem kết quả kết nối

## 📚 API mẫu đang sử dụng

**JSONPlaceholder** - API công khai miễn phí cho testing:
- URL: `https://jsonplaceholder.typicode.com/users`
- Không cần authentication
- Trả về 10 users giả lập

## 🔐 Best Practices

### 1. Luôn validate dữ liệu
```php
$user_id = absint($_POST['user_id']); // Đảm bảo là số nguyên
$email = sanitize_email($_POST['email']); // Sanitize email
```

### 2. Kiểm tra WP_Error
```php
$response = wp_remote_get($url);
if (is_wp_error($response)) {
    error_log($response->get_error_message());
    return false;
}
```

### 3. Set timeout phù hợp
```php
'timeout' => 30, // 30 giây cho API chậm
```

### 4. Sử dụng wp_safe_remote_get()
Nếu URL không tin cậy:
```php
$response = wp_safe_remote_get($url); // Chặn local/private IPs
```

## 🛠️ Troubleshooting

### Plugin không hiển thị trong Admin?
- Kiểm tra file có đúng header plugin không
- Đảm bảo file nằm trong `wp-content/plugins/api-connector/`

### Shortcode không hoạt động?
- Kiểm tra plugin đã activate chưa
- Xem có lỗi PHP trong debug.log không

### API trả về lỗi?
- Kiểm tra URL API có đúng không
- Xem có cần authentication không
- Test API bằng Postman/curl trước

### Enable Debug Mode
Thêm vào `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Xem log tại: `wp-content/debug.log`

## 📦 Cấu trúc Files

```
api-connector/
├── api-connector.php    # File chính
└── README.md           # File này
```

## 🔄 Mở rộng

### Thêm Custom Post Type
```php
add_action('init', function() {
    register_post_type('api_data', array(
        'label'   => 'API Data',
        'public'  => true,
        'supports' => array('title', 'editor'),
    ));
});
```

### Tạo WP-CLI Command
```php
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('api sync', function() {
        delete_transient('api_connector_users');
        WP_CLI::success('Cache cleared!');
    });
}
```

### Thêm Settings Page
Sử dụng Settings API để lưu API key, URL, v.v.

## 📞 Support

- GitHub: https://github.com/TDA-NGUYEN-LINH7/wp_project1
- WordPress Codex: https://developer.wordpress.org/apis/

## 📄 License

GPL v2 or later
