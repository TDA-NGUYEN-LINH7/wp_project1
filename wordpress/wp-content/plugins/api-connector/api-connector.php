<?php
/**
 * Plugin Name: API Connector
 * Plugin URI: https://github.com/TDA-NGUYEN-LINH7/wp_project1
 * Description: Plugin trung gian để kết nối và lấy dữ liệu từ API bên ngoài sử dụng WordPress HTTP API
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://github.com/TDA-NGUYEN-LINH7
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: api-connector
 */

// Bảo vệ file khỏi truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class chính của Plugin
 */
class API_Connector {
    
    /**
     * Constructor - Khởi tạo plugin
     */
    public function __construct() {
        // Đăng ký shortcode [api_users] để hiển thị dữ liệu
        add_shortcode('api_users', array($this, 'display_api_users'));
        
        // Thêm menu admin (tùy chọn)
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Hàm gọi API sử dụng WordPress HTTP API
     * 
     * @param string $url URL của API cần gọi
     * @param string $method Phương thức HTTP (GET, POST, PUT, DELETE)
     * @param array $body Dữ liệu gửi lên (với POST/PUT)
     * @return array|WP_Error Kết quả từ API hoặc lỗi
     */
    private function call_api($url, $method = 'GET', $body = array()) {
        // Cấu hình request
        $args = array(
            'method'    => $method,
            'timeout'   => 15, // Timeout 15 giây
            'headers'   => array(
                'Content-Type' => 'application/json',
                // Thêm Authorization nếu cần
                // 'Authorization' => 'Bearer YOUR_TOKEN_HERE',
            ),
        );
        
        // Nếu có dữ liệu body (POST/PUT)
        if (!empty($body)) {
            $args['body'] = json_encode($body);
        }
        
        // Gọi API theo method
        if ($method === 'GET') {
            $response = wp_remote_get($url, $args);
        } else if ($method === 'POST') {
            $response = wp_remote_post($url, $args);
        } else {
            $response = wp_remote_request($url, $args);
        }
        
        // Kiểm tra lỗi
        if (is_wp_error($response)) {
            return $response; // Trả về WP_Error
        }
        
        // Lấy status code
        $status_code = wp_remote_retrieve_response_code($response);
        
        // Kiểm tra status code
        if ($status_code !== 200) {
            return new WP_Error('api_error', 'API trả về lỗi: ' . $status_code);
        }
        
        // Lấy body và parse JSON
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return $data;
    }
    
    /**
     * Hàm lấy danh sách users từ API public (ví dụ)
     * 
     * @return array|WP_Error
     */
    public function get_users_from_api() {
        // Sử dụng API public miễn phí để demo
        $api_url = 'https://jsonplaceholder.typicode.com/users';
        
        // Cache kết quả trong 1 giờ để tránh gọi API quá nhiều
        $cache_key = 'api_connector_users';
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false) {
            return $cached_data; // Trả về từ cache
        }
        
        // Gọi API
        $users = $this->call_api($api_url, 'GET');
        
        // Nếu thành công, lưu vào cache
        if (!is_wp_error($users)) {
            set_transient($cache_key, $users, HOUR_IN_SECONDS); // Cache 1 giờ
        }
        
        return $users;
    }
    
    /**
     * Shortcode để hiển thị danh sách users
     * Sử dụng: [api_users]
     * 
     * @param array $atts Attributes của shortcode
     * @return string HTML output
     */
    public function display_api_users($atts) {
        // Parse attributes với giá trị mặc định
        $atts = shortcode_atts(array(
            'limit' => 10, // Giới hạn số lượng hiển thị
        ), $atts);
        
        // Lấy dữ liệu từ API
        $users = $this->get_users_from_api();
        
        // Kiểm tra lỗi
        if (is_wp_error($users)) {
            return '<div class="api-error">Lỗi: ' . esc_html($users->get_error_message()) . '</div>';
        }
        
        // Giới hạn số lượng
        $users = array_slice($users, 0, absint($atts['limit']));
        
        // Bắt đầu output buffering
        ob_start();
        ?>
        
        <div class="api-connector-users">
            <h3>Danh sách Users từ API</h3>
            <?php if (!empty($users)) : ?>
                <table class="widefat" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 8px;">ID</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Tên</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Email</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Công ty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo esc_html($user['id']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <strong><?php echo esc_html($user['name']); ?></strong><br>
                                    <small>@<?php echo esc_html($user['username']); ?></small>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <a href="mailto:<?php echo esc_attr($user['email']); ?>">
                                        <?php echo esc_html($user['email']); ?>
                                    </a>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <?php echo esc_html($user['company']['name']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>Không có dữ liệu.</p>
            <?php endif; ?>
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Thêm menu Admin (tùy chọn)
     */
    public function add_admin_menu() {
        add_menu_page(
            'API Connector',           // Tiêu đề trang
            'API Connector',           // Tên menu
            'manage_options',          // Capability
            'api-connector',           // Menu slug
            array($this, 'admin_page'), // Callback
            'dashicons-rest-api',      // Icon
            30                         // Position
        );
    }
    
    /**
     * Trang quản trị plugin
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="card">
                <h2>Hướng dẫn sử dụng</h2>
                <p>Plugin này kết nối với API bên ngoài và hiển thị dữ liệu.</p>
                
                <h3>Cách sử dụng Shortcode:</h3>
                <ul>
                    <li>Thêm vào bài viết/trang: <code>[api_users]</code></li>
                    <li>Giới hạn số lượng: <code>[api_users limit="5"]</code></li>
                </ul>
                
                <h3>API đang sử dụng:</h3>
                <p><code>https://jsonplaceholder.typicode.com/users</code></p>
                
                <h3>Test API:</h3>
                <button type="button" class="button button-primary" onclick="testAPI()">
                    Test Connection
                </button>
                <div id="api-test-result" style="margin-top: 10px;"></div>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2>Code mẫu để gọi API riêng của bạn</h2>
                <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">
// Ví dụ gọi API POST
$api_url = 'https://your-api.com/endpoint';
$data = array(
    'name' => 'John Doe',
    'email' => 'john@example.com'
);

$response = wp_remote_post($api_url, array(
    'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer YOUR_TOKEN'
    ),
    'body' => json_encode($data),
    'timeout' => 15
));

if (!is_wp_error($response)) {
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    // Xử lý $result
}
                </pre>
            </div>
        </div>
        
        <script>
        function testAPI() {
            const resultDiv = document.getElementById('api-test-result');
            resultDiv.innerHTML = '<p>Đang kết nối...</p>';
            
            fetch('https://jsonplaceholder.typicode.com/users')
                .then(response => response.json())
                .then(data => {
                    resultDiv.innerHTML = '<p style="color: green;">✓ Kết nối thành công! Nhận được ' + data.length + ' users.</p>';
                })
                .catch(error => {
                    resultDiv.innerHTML = '<p style="color: red;">✗ Lỗi: ' + error.message + '</p>';
                });
        }
        </script>
        <?php
    }
}

// Khởi tạo plugin
function api_connector_init() {
    new API_Connector();
}
add_action('plugins_loaded', 'api_connector_init');

/**
 * Hook khi activate plugin
 */
function api_connector_activate() {
    // Xóa cache khi activate
    delete_transient('api_connector_users');
}
register_activation_hook(__FILE__, 'api_connector_activate');

/**
 * Hook khi deactivate plugin
 */
function api_connector_deactivate() {
    // Xóa cache khi deactivate
    delete_transient('api_connector_users');
}
register_deactivation_hook(__FILE__, 'api_connector_deactivate');
