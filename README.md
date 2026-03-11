# WordPress Docker Setup

## 📦 Thông tin dự án
- **WordPress**: Chạy trên port `8080`
- **MySQL 8.0**: Chạy trên port `3309`
- **Source code**: Clone từ repository chính thức WordPress

## 🚀 Cách chạy dự án

### 1. Khởi động containers
```bash
docker compose up -d
```

### 2. Kiểm tra trạng thái
```bash
docker compose ps
```

### 3. Truy cập WordPress
Mở trình duyệt và vào:
```
http://localhost:8080
```

WordPress sẽ tự động chuyển đến trang cài đặt `/wp-admin/install.php`

## 🗄️ Thông tin database

- **Host**: `localhost` (hoặc `127.0.0.1`)
- **Port**: `3309`
- **Database**: `wordpress`
- **Username**: `wordpress`
- **Password**: `wordpress`
- **Root Password**: `root`

### Kết nối MySQL từ máy local
```bash
mysql -h 127.0.0.1 -P 3309 -u wordpress -p
# Nhập password: wordpress
```

Hoặc dùng MySQL Workbench/TablePlus với thông tin trên.

## 🛠️ Các lệnh hữu ích

### Dừng containers
```bash
docker compose stop
```

### Xóa containers (giữ data)
```bash
docker compose down
```

### Xóa containers + data
```bash
docker compose down -v
```

### Xem logs
```bash
# Xem tất cả logs
docker compose logs

# Xem logs WordPress
docker compose logs wordpress -f

# Xem logs MySQL
docker compose logs db -f
```

### Khởi động lại containers
```bash
docker compose restart
```

## 📁 Cấu trúc thư mục

```
project1/
├── docker-compose.yml    # Cấu hình Docker
├── wordpress/            # Source code WordPress (mount vào container)
└── README.md            # File hướng dẫn này
```

## ⚙️ Cấu hình WordPress

Sau khi truy cập `http://localhost:8080`, làm theo wizard cài đặt:
1. Chọn ngôn ngữ
2. Nhập thông tin site (tên, username admin, password, email)
3. Hoàn tất cài đặt

## 🔧 Troubleshooting

### Container không khởi động được
```bash
# Xem chi tiết lỗi
docker compose logs

# Xóa và tạo lại
docker compose down -v
docker compose up -d
```

### Port đã được sử dụng
Nếu port 8080 hoặc 3309 đã bị chiếm, sửa trong `docker-compose.yml`:
```yaml
ports:
  - "8081:80"  # Đổi 8080 thành port khác
```

### Kết nối database bị lỗi
Đợi vài giây để MySQL khởi động hoàn tất, sau đó reload trang WordPress.
