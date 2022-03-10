<?php
die( 'no money no love' );


?>
<!-- các file CSS chuyển từ PHP sang sẽ cho vào đây -->
<?php
$this->base_model->add_css( 'public/css/ten_file.css', [
    'get_content' => 1,
    'preload' => 1,
    'cdn' => CDN_BASE_URL,
] );
// lấy mã CSS trả về thay vì echo luôn
$this->base_model->get_add_css( 'public/css/ten_file.css', [
    'get_content' => 1,
    'preload' => 1,
    'cdn' => CDN_BASE_URL,
] );
?>

<!-- các file JS chuyển từ PHP sang sẽ cho vào đây -->
<?php
$this->base_model->add_js( 'public/javascript/ten_file.js', [
    'get_content' => 1,
    'preload' => 1,
    'cdn' => CDN_BASE_URL,
] );
// lấy mã JS trả về thay vì echo luôn
$this->base_model->get_add_js( 'public/javascript/ten_file.js', [
    'get_content' => 1,
    'preload' => 1,
    'cdn' => CDN_BASE_URL,
] );


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
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
*/

/*
* Định dạng trong INPUT HTML5
Pattern_format::EMAIL
*/

$this->base_model->alert( 'Nội dung thông báo', 'URL cần chuyển đến hoặc mã cảnh báo error' );
$this->base_model->short_string( 'Nội dung cần cắt', 'độ dài cần cắt' );
// chuyển chuỗi thành URL tiêu chuẩn (SEO) -> dùng khi cần tạo slug URL hoặc xử lý tên file upload lên host
$this->base_model->_eb_non_mark_seo( 'Nội dung cần xử lý' );

$user_id = $this->base_model->get_ses_login()[ 'ID' ];

// cURL
$this->base_model->get( 'URL' );

$this->base_model->_eb_number_only( 'fgfsd097834msdgs' );
$this->base_model->_eb_float_only( 'fgfsd097834msdgs' );


// INSERT
$result_id = $this->base_model->insert( $this->table, $data, true );
//var_dump( $result_id );
//print_r( $result_id );

if ( $result_id !== false ) {
    //
}


// UPDATE dữ liệu
$this->base_model->update_multiple( 'tbl_user', [
    // SET
    'is_member' => User_type::GUEST,
], [
    // WHERE
    'is_member' => User_type::GUEST,
], [
    // hiển thị mã SQL để check
    'show_query' => 1,
    // trả về câu query để sử dụng cho mục đích khác
    'get_query' => 1,
] );

// DELETE dữ liệu
$this->base_model->delete_multiple( 'tbl_user', [
    // WHERE
    'is_member' => User_type::GUEST,
] );


// SELECT dữ liệu từ 1 bảng bất kỳ
$sql = $this->base_model->select( '*', 'tbl_user', array(
    // các kiểu điều kiện where
    // WHERE AND OR
    "(aaaaaaaaaa = 1 OR bbbbbbb = 2)" => NULL,
    // WHERE IN
    "user_id IN (SELECT user_id FROM tbl_rem WHERE chapter_id = " . $chapter_id . ")" => NULL,
    // mặc định
    'date_check_in >= ' => 1,
    'date_check_in <= ' => 10,
    'is_member' => User_type::MEMBER,
    'is_member' => User_type::GUEST,
    'FIND_IN_SET(\'string_to_find\', column_name)' => NULL,
), array(
    'or_where' => array(
        'username' => 2, [
            'username' => 3,
            'FIND_IN_SET(\'string_to_find\', column_name)' => NULL,
        ],
        'user_id' => 1
    ),
    'where_in' => array(
        'user_id' => array(
            1,
            2,
            3
        )
    ),
    'where_not_in' => array(
        'user_id' => array(
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
        'user_id' => 1
    ),
    'not_like' => array(
        'username' => 2,
        'user_id' => 1
    ),
    'or_like' => array(
        'username' => 2,
        'user_id' => 1
    ),
    'or_not_like' => array(
        'username' => 2,
        'user_id' => 1
    ),
    'group_by' => array(
        'username',
        'user_id',
    ),
    'order_by' => array(
        'username' => 'ASC',
        'user_id' => 'DESC'
    ),
    // trả về COUNT(column_name) AS column_name
    //'selectCount' => 'ID',
    // hiển thị mã SQL để check
    'show_query' => 1,
    // trả về câu query để sử dụng cho mục đích khác
    //'get_query' => 1,
    // trả về tổng số bản ghi -> tương tự mysql num row
    //'getNumRows' => 1,
    //'offset' => 0,
    'limit' => 3
) );


// khai báo URL tùy chỉnh trong application/config/routes.php