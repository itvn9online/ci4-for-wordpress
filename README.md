# CodeIgniter4 for Wordpress:

## Create website with Codeigniter4 and Wordpress database

Code chạy database wordpress trên nền tảng CodeIgniter4. Về cơ bản code này viết bằng CodeIgniter4 dựa theo cấu trúc database của Wordpress để cùng một database có thể điều khiển bằng cả Wordpress.

## Ưu điểm:

- Do chạy chung database với Wordpress nên thi thoảng có những thao tác cần xử lý đột xuất với database thì có thể cái code Wordpress vào sub-domain, kết nối vào database và dùng Wordpress để xử lý nó thay vì ngồi code mới -> tận dụng kho plugin khổng lồ của Wordpress.
- Số lượng website sử dụng Wordpress hiện tại rất nhiều, nên nếu một ngày nào đó kiếm được khách có nhu cầu chuyển đổi từ Wordpress sang framework để tăng tốc độ website thì việc cần làm duy nhất là lắp code này vào và chạy nó.
- Cấu trúc database của Wordpress khá là mở và linh động. Hạn chế tối đa việc phải thêm cột vào bảng mỗi khi có trường dữ liệu mới.

## Nhược điểm:

- Tính bảo mật phụ thuộc vào trình độ của code nên cần nghiên cứu việc bảo mật đường dẫn cho admin, tránh việc sử dụng các đường dẫn phổ biến như: /admin
- Tài khoản admin cũng cần bảo mật nghiêm ngặt hơn. Riêng phần mật khẩu sẽ sử dụng cột riêng với Wordpress.

## Hướng dẫn cài đặt:

- Giải nén file `system` (bản gốc của CodeIgniter4, cập nhật ngày 2021-09-14) hoặc có thể tải CodeIgniter4 mới nhất tại đây: https://codeigniter.com/download rồi lấy thư mục system là được.
- Tạo database và import database mẫu từ file database.zip
  - Thiết lập các tham số kết nối database `username`, `password`, `database` tại file này: `/app/Config/Database.php`. \* nếu chưa có file `/app/Config/Database-sample.php` thì copy file `Database-sample.php` và đổi tên thành `Database.php`.
  - Truy cập vào phpmyadmin tìm đến bảng `wp_users` và update lại cột `user_login`, `user_email` và `ci_pass` để sử dụng (password update thông qua hàm `md5` của phpmyadmin). Update trực tiếp vào tài khoản có ID = 1 hoặc sao chép từ tài khoản đó ra.
- Liên kết vào admin: `/wgr-wp-admin`
- Liên kết tự động giải nén vendor và đồng bộ database nếu chưa được đồng bộ: `/sync/vendor_sync` (lưu ý: việc chạy link đồng bộ này là bắt buộc để đảm bảo website có đủ cấu trúc database cũng như các code của phần outsource)
- Các phần hướng dẫn khác (nếu có) sẽ bổ sung dần dần...

## Quy tắc đặt tên function:

- Do bản code này được re-build từ bản CodeIgniter2 có sẵn của công ty trước đó nên có nhiều function vẫn giữ lại và chưa thay đổi (có nghĩa là nó không tuân theo quy tắc này).
- Các function mới đều được đặt tên theo quy tắc tương tự như của Wordpress hoặc đặc giống như Wordpress luôn. Ví dụ:
  - `the_text()` và `get_the_text()` (phân biệt bởi chữ `get_`):
    - `get_the_text()` là hàm lấy và xử lý dữ liệu sau đó `return` dữ liệu đã lấy được.
    - `the_text()` là hàm sẽ gọi tới `get_the_text()` để lấy dữ liệu và thực hiện `echo` luôn (đỡ phải viết echo).

```
function get_the_text ( $prams1, $prams2, $prams3 = '' ) {
	return md5( $prams1 );
}

function the_text ( $prams1, $prams2, $prams3 = '' ) {
	echo get_the_text( $prams1, $prams2, $prams3 );
}
```

- Ngoài ra, các function có chức năng tương tự với Wordpress sẽ được đặt tên giống với function của Wordpress luôn. Điều này nhằm mục đích giảm bớt thời gian tìm hiểu xem function này dùng để làm gì, hữu ích với những ai đã làm qua code Wordpress. Hoặc khi làm code này mà chuyển sang Wordpress thì cũng đỡ bỡ ngỡ hơn.

## Một số function thường dùng:

#### Lấy chi tiết một danh mục theo ID:

```
$get_data = $this->term_model->get_term_by_id( 1, 'taxonomy' );
```

- Trong đó:
  - Đầu vào là ID của danh mục cần lấy dữ liệu.
    - Một mảng chứa thông tin chi tiết của danh sẽ được trả về nếu có dữ liệu tương ứng.
    - Một mảng trống sẽ được trả về nếu không có giá trị nào được tìm thấy.

#### Lấy chi tiết một danh mục theo slug:

```
$get_data = $this->term_model->get_term_by_slug( 'ten-danh-muc', 'taxonomy' );
```

- Trong đó:
  - Đầu vào là slug của danh mục cần lấy dữ liệu.
    - Một mảng chứa thông tin chi tiết của danh sẽ được trả về nếu có dữ liệu tương ứng.
    - Một mảng trống sẽ được trả về nếu không có giá trị nào được tìm thấy.

#### Function lấy danh sách Sản phẩm theo danh mục:

```
$term_data = [
	'term_id' => 1,
	'slug' => 'san-pham',
];

$get_data = $this->post_model->get_posts_by( $term_data, [
	'limit' => 10,
	'offset' => 0,
	'order_by' => [
		'ID' => 'DESC',
		'post_name' => 'ASC',
	],
] );
```

- Trong đó:
  - `$term_data`: là thông tin danh mục sản phẩm hoặc có thể tự tạo một mảng có chứa `term_id` (ưu tiên) hoặc `slug` của danh mục đó là được.
  - `$ops` - tùy chọn bổ sung (không bắt buộc):
    - `limit`: giới hạn số lượng bản ghi muốn lấy.
    - `offset`: điểm bắt đầu lấy bản ghi trong mysql.
    - `order_by`: muốn sắp xếp theo cột nào trong bảng thì truyền cột đó và kiểu sắp xếp `DESC` hoặc `ASC` vào đây.

##### Lấy `10` sản phẩm mới nhất của nhóm có ID là `1`:

```
$get_data = $this->post_model->get_posts_by( 1, 10 );
```

##### Lấy `10` sản phẩm mới nhất của nhóm có slug là `san-pham`:

```
$get_data = $this->post_model->get_posts_by( 'san-pham', 10 );
```

#### Function đếm tổng số Sản phẩm có trong danh mục:

```
$term_data = [
	'term_id' => 1,
	'slug' => 'san-pham',
];

$count_data = $this->post_model->count_posts_by( $term_data );
```

- Trong đó:
  - `$term_data`: là thông tin danh mục sản phẩm hoặc có thể tự tạo một mảng có chứa `term_id` (ưu tiên) hoặc `slug` của danh mục đó là được.

##### Tính tổng số sản phẩm của nhóm có ID là `1`:

```
$get_data = $this->post_model->count_posts_by( 1 );
```

##### Tính tổng số sản phẩm của nhóm có slug là `san-pham`:

```
$get_data = $this->post_model->count_posts_by( 'san-pham' );
```

---

#### Function lấy danh sách Bài viết theo danh mục Blog/ Tin tức:

```
$get_data = $this->post_model->get_products_by( $term_data, [
	'limit' => 10,
	'offset' => 0,
	'order_by' => [
		'ID' => 'DESC',
		'post_name' => 'ASC',
	],
] );
```

- Tham số và cách sử dụng tương tự với function `get_posts_by`, khác biệt duy nhất là cái tên function.

##### Lấy `10` bài viết mới nhất của nhóm có ID là `2`:

```
$get_data = $this->post_model->get_products_by( 2, 10 );
```

##### Lấy `10` bài viết mới nhất của nhóm có slug là `tin-tuc`:

```
$get_data = $this->post_model->get_products_by( 'tin-tuc', 10 );
```

#### Function đếm tổng số Bài viết có trong danh mục Blog/ Tin tức:

```
$count_data = $this->post_model->count_products_by( $term_data );
```

- Tham số và cách sử dụng tương tự với function `count_posts_by`, khác biệt duy nhất là cái tên function.

##### Tính tổng số bài viết của nhóm có ID là `2`:

```
$get_data = $this->post_model->count_products_by( 2 );
```

##### Tính tổng số bài viết của nhóm có slug là `tin-tuc`:

```
$get_data = $this->post_model->count_products_by( 'tin-tuc' );
```

---

#### Function lấy danh sách Bài viết tức theo danh mục Quảng cáo:

```
$get_data = $this->post_model->get_adss_by( $term_data, [
	'limit' => 10,
	'offset' => 0,
	'order_by' => [
		'ID' => 'DESC',
		'post_name' => 'ASC',
	],
] );
```

- Tham số và cách sử dụng tương tự với function `get_posts_by`, khác biệt duy nhất là cái tên function.

##### Lấy `10` quảng cáo mới nhất của nhóm có ID là `3`:

```
$get_data = $this->post_model->get_adss_by( 3, 10 );
```

##### Lấy `10` quảng cáo mới nhất của nhóm có slug là `quang-cao`:

```
$get_data = $this->post_model->get_adss_by( 'quang-cao', 10 );
```

#### Function đếm tổng số Bài viết có trong danh mục Quảng cáo:

```
$count_data = $this->post_model->count_adss_by( $term_data );
```

- Tham số và cách sử dụng tương tự với function `count_posts_by`, khác biệt duy nhất là cái tên function.

##### Tính tổng số quảng cáo của nhóm có ID là `3`:

```
$get_data = $this->post_model->count_adss_by( 3 );
```

##### Tính tổng số quảng cáo của nhóm có slug là `quang-cao`:

```
$get_data = $this->post_model->count_adss_by( 'quang-cao' );
```

---

#### Trả về URL của bài viết (dùng chung cho Sản phẩm và Blog/ Tin tức):

```
$post_link = $this->post_model->get_post_permalink( $data );
```

- `$data`: là dữ liệu của bản ghi cần tạo URL.

---

#### Trả về URL ảnh đại diện của bài viết (dùng chung cho Sản phẩm và Blog/ Tin tức):

```
$post_thumbnail = $this->post_model->get_post_thumbnail( $data );
```

- `$data`: là dữ liệu của bản ghi cần lấy ảnh đại diện.

---

#### Menu:

##### In ra menu có slug là `slug-of-menu`, gắn vào đó 2 class css là `class-css1` và `class-css2`, nếu menu chưa tồn tại, hệ thống sẽ tự động tạo.

```
$menu_model->the_menu( 'slug-of-menu', 'class-css1 class-css2' );
```

---

#### Banner Quảng cáo:

##### In ra `10` banner quảng cáo của nhóm có slug là `slug-of-ads-group`, nếu nhóm quảng cáo chưa tồn tại, hệ thống sẽ tự động tạo.

```
$this->post_model->the_ads( 'slug-of-ads-group', 10 );
```

---

#### Website logo:

##### In ra logo mobile nếu có, nếu không sẽ in ra logo chính (để trống các tham số phía sau sẽ in ra logo chính).

```
$option_model->the_logo( $getconfig, 'logo_mobile', 'logo_mobile_height' );
```

---

#### Mã HTML hiển thị nút sửa các block khi đăng nhập tài khoản có quyền admin:

##### Đối với Sản phẩm/ bài viết:

```
<div data-id="1" data-type="post" class="custom-bootstrap-post_type"></div>
```

- Trong đó:
  - `data-id` là `ID` của bài viết.
  - `data-type` là `post_type` của bài viết. Ví dụ: `post`, `ads`, `blog`...

##### Đối với Danh mục:

```
<div data-id="1" data-type="category" class="custom-bootstrap-taxonomy"></div>
```

- Trong đó:
  - `data-id` là `ID` của danh mục.
  - `data-type` là `taxonomy` của danh mục. Ví dụ: `category`, `blogs`...
