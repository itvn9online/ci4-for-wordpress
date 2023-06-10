<?php
die('no money no love');
echo $a;
echo 'test format on type';


?>
<!-- các file CSS chuyển từ PHP sang sẽ cho vào đây -->
<?php
$base_model->add_css('public/css/ten_file.css', [
    'get_content' => 1,
    'preload' => 1,
    'cdn' => CDN_BASE_URL,
]);
$base_model->adds_css([
    'public/css/ten_file.css',
    'themes/' . THEMENAME . '/css/aaaaaaaaaaa.css',
], [
    'get_content' => 1,
    'preload' => 1,
    'cdn' => CDN_BASE_URL,
]);
// lấy mã CSS trả về thay vì echo luôn
$base_model->get_add_css('public/css/ten_file.css', [
    'get_content' => 1,
    'preload' => 1,
    'cdn' => CDN_BASE_URL,
]);
?>

<!-- các file JS chuyển từ PHP sang sẽ cho vào đây -->
<?php

$base_model->add_js('javascript/ten_file.js', [
    'get_content' => 1,
    'preload' => 1,
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);

$base_model->adds_js([
    'javascript/ten_file.js',
    'themes/' . THEMENAME . '/js/aaaaaaaaaaa.js',
], [
    'get_content' => 1,
    'preload' => 1,
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);

// lấy mã JS trả về thay vì echo luôn
$base_model->get_add_js('javascript/ten_file.js', [
    'get_content' => 1,
    'preload' => 1,
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);


// bộ câu lệnh git dùng để đồng bộ code từ máy này sang máy khác, đỡ bị xung đột
/*
git checkout master
git pull origin hung
git merge hung
git push
*/


// bộ lệnh đồng bộ hùng
/*
git checkout hung
git pull origin master
git push origin hung

*/

/*
ini_set('display_errors', 1);
error_reporting(E_ALL);
*/

/* * Định dạng trong INPUT HTML5 Pattern_format::EMAIL */

$this->base_model->alert('Nội dung thông báo', 'URL cần chuyển đến hoặc mã cảnh báo error');
$this->base_model->short_string('Nội dung cần cắt', 'độ dài cần cắt');
// chuyển chuỗi thành URL tiêu chuẩn (SEO) -> dùng khi cần tạo slug URL hoặc xử lý tên file upload lên host
$this->base_model->_eb_non_mark_seo('Nội dung cần xử lý');

$user_id = $this->base_model->get_ses_login()['ID'];

// cURL
$this->base_model->get('URL');

$this->base_model->_eb_number_only('fgfsd097834msdgs');
$this->base_model->_eb_float_only('fgfsd097834msdgs');


// INSERT
$result_id = $this->base_model->insert($this->table, $data, true);
//var_dump( $result_id );
//print_r( $result_id );

if ($result_id !== false) {
    //
}


// UPDATE
$result_id = $this->base_model->update_multiple('users', [
    // SET
    'member_type' => UsersType::GUEST,
], [
    // WHERE
    'member_type' => UsersType::GUEST,
], [
    'debug_backtrace' => debug_backtrace()[1]['function'],
    // trong builder CI4 lệnh UPDATE chưa hỗ trợ lệnh join
    /*
        'join' => array(
        'tbl1' => 'tbl_0.id = tbl1.id',
        'tbl2' => 'tbl_0.id = tbl2.id'
        ),
        */
    'where_in' => array(
        'ID' => array(
            1,
            2,
            3
        )
    ),
    'where_not_in' => array(
        'ID' => array(
            1,
            2,
            3
        )
    ),
    // hiển thị mã SQL để check
    'show_query' => 1,
    // trả về câu query để sử dụng cho mục đích khác
    'get_query' => 1,
    // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
    //'no_remove_field' => 1
]);

if ($result_id !== false) {
    //
}


// SELECT
$data = $this->base_model->select(
    '*',
    'users',
    array(
        // các kiểu điều kiện where
        // WHERE AND OR
        "(aaaaaaaaaa = 1 OR bbbbbbb = 2)" => null,
        // WHERE IN
        "ID IN (SELECT user_id FROM tbl_0 WHERE select_id = " . $chapter_id . ")" => null,
        // mặc định
        'date_check_in >= ' => 1,
        'date_check_in <= ' => 10,
        'member_type' => UsersType::MEMBER,
        'member_type' => UsersType::GUEST,
        'is_deleted' => DeletedStatus::FOR_DEFAULT,
        'FIND_IN_SET(\'string_to_find\', column_name)' => null,
    ),
    array(
        'where_or' => array(
            'username' => 2,
            [
                'username' => 3,
                'FIND_IN_SET(\'string_to_find\', column_name)' => null,
            ],
            'ID' => 1
        ),
        'where_in' => array(
            'ID' => array(
                1,
                2,
                3
            )
        ),
        'where_not_in' => array(
            'ID' => array(
                1,
                2,
                3
            )
        ),
        'join' => array(
            'tbl1' => 'tbl_0.id = tbl1.id',
            'tbl2' => 'tbl_0.id = tbl2.id'
        ),
        'like' => array(
            'username' => 2,
            'ID' => 1
        ),
        'like_before' => array(
            'username' => 2,
            'ID' => 1
        ),
        'like_after' => array(
            'username' => 2,
            'ID' => 1
        ),
        'not_like' => array(
            'username' => 2,
            'ID' => 1
        ),
        'or_like' => array(
            'username' => 2,
            'ID' => 1
        ),
        'or_not_like' => array(
            'username' => 2,
            'ID' => 1
        ),
        'group_by' => array(
            'username',
            'ID',
        ),
        'order_by' => array(
            'username' => 'ASC',
            'ID' => 'DESC'
        ),
        // hiển thị mã SQL để check
        'show_query' => 1,
        // trả về câu query để sử dụng cho mục đích khác
        //'get_query' => 1,
        // trả về COUNT(column_name) AS column_name
        //'selectCount' => 'ID',
        // trả về tổng số bản ghi -> tương tự mysql num row
        //'getNumRows' => 1,
        //'offset' => 0,
        'limit' => 3
    )
);

// DELETE
$this->base_model->delete_multiple('users', [
    // WHERE
    'member_type' => UsersType::GUEST,
], [
    'join' => array(
        'tbl1' => 'tbl_0.id = tbl1.id',
        'tbl2' => 'tbl_0.id = tbl2.id'
    ),
    'where_in' => array(
        'ID' => array(
            1,
            2,
            3
        )
    ),
    'where_not_in' => array(
        'ID' => array(
            1,
            2,
            3
        )
    ),
    // hiển thị mã SQL để check
    'show_query' => 1,
    // trả về câu query để sử dụng cho mục đích khác
    'get_query' => 1,
]);


//
$post_model->the_ads('ads-term-slug', $limit = 1, $ops = [
    //'post_type' => 'post_type',
    //'taxonomy' => 'taxonomy',
    //'limit' => 'limit',
    // nếu có tham số auto clone -> cho phép nhân bản dữ liệu cho các ngôn ngữ khác
    'auto_clone' => 1,
    // trả về dữ liệu ngay sau khi select xong -> bỏ qua đoạn builder HTML
    'return_object' => 1,
    // thêm class css tùy chỉnh vào
    'add_class' => 'css-class-1 cas-class-2',
], $using_cache = true, $time = MEDIUM_CACHE_TIMEOUT);


// JSON.parse
//
$base_model->JSON_parse([
    'json_data' => $data,
]);

// JSON echo
//
$base_model->JSON_echo([
    // mảng này sẽ in ra dưới dạng JSON hoặc number
    'json_data' => $data,
], [
    // mảng này sẽ in ra dưới dạng string
]);


// kiểm tra các giá trị bắt buộc trong 1 mảng
$this->base_model->isEmptyData($data, $required_data);


// khai báo URL tùy chỉnh trong application/config/routes.php