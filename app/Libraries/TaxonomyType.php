<?php

namespace App\ Libraries;

class TaxonomyType {

    // taxonomy
    const POSTS = 'category';
    const TAGS = 'tags';
    const ADS = 'ads_options';
    const BLOGS = 'blogs';
    const BLOG_TAGS = 'blog_tags';
    const OPTIONS = 'post_options';
    //const MENU = 'nav_menu';
    //const PAGE = 'page_taxonomy';

    private static $arr = array(
        self::POSTS => 'Danh mục',
        self::TAGS => 'Thẻ',
        self::ADS => 'Danh mục quảng cáo',
        self::BLOGS => 'Danh mục tin',
        self::BLOG_TAGS => 'Thẻ Blog/ Tin tức',
        self::OPTIONS => 'Thông số khác',
        //self::MENU => 'Menu',
        //self::PAGE => 'Trang tĩnh',
    );

    public static function typeList( $key = '', $first_name = false ) {
        if ( $key == '' ) {
            return self::$arr;
        }
        if ( isset( self::$arr[ $key ] ) ) {
            // lấy thêm định danh cho Danh mục (nếu có)
            $get_first_name = '';
            /*
            if ( $first_name == true ) {
                $get_first_name = self::nameList( $key );
            }
            */
            return $get_first_name . self::$arr[ $key ];
        }
        return '';
    }

    private static $arr_name = array(
        self::POSTS => 'Danh mục',
        self::TAGS => 'Danh sách',
        //self::ADS => 'Danh mục',
        //self::BLOGS => 'Danh mục',
        //self::BLOG_TAGS => 'Danh mục',
        self::OPTIONS => 'Danh sách',
        //self::MENU => 'Menu',
        //self::PAGE => 'Trang tĩnh',
    );

    public static function nameList( $key = '' ) {
        if ( $key == '' ) {
            return self::$arr_name;
        }
        if ( isset( self::$arr_name[ $key ] ) ) {
            return self::$arr_name[ $key ] . ' ';
        }
        return 'Danh mục ';
    }

    // trả về các meta mặc định dựa theo từng post_type
    public static function meta_default( $taxonomy ) {
        $arr = [];

        //
        $arr[ 'taxonomy_custom_post_size' ] = 'Tùy chỉnh tỉ lệ ảnh';
        $arr[ 'taxonomy_auto_slider' ] = 'Slider';
        if ( $taxonomy == self::ADS ) {
            $arr[ 'hide_widget_title' ] = 'Ẩn tiêu đề danh mục';
            $arr[ 'custom_cat_link' ] = 'Tùy chỉnh URL';
            $arr[ 'dynamic_tag' ] = 'HTML tag cho Tiêu đề';
            $arr[ 'dynamic_post_tag' ] = 'HTML tag cho Tên bài viết';
            $arr[ 'widget_description' ] = 'Mô tả';
            $arr[ 'post_number' ] = 'Số lượng bài để hiển thị';
            $arr[ 'num_line' ] = 'Số bài viết trên mỗi dòng';
            $arr[ 'post_cloumn' ] = 'Bố cục bài viết';
            $arr[ 'hide_title' ] = 'Ẩn tiêu đề của bài viết';
            $arr[ 'hide_description' ] = 'Ẩn tóm tắt của bài viết';
            $arr[ 'hide_info' ] = 'Ẩn ngày tháng, danh mục của bài viết';
            $arr[ 'show_post_content' ] = 'Hiển thị nội dung của bài viết';
            $arr[ 'run_slider' ] = 'Chạy slider';
            $arr[ 'max_width' ] = 'Chiều rộng tối đa';
            $arr[ 'custom_style' ] = 'Tùy chỉnh CSS';
            $arr[ 'custom_id' ] = 'Tùy chỉnh ID';
            $arr[ 'custom_size' ] = 'Tùy chỉnh size ảnh';
            $arr[ 'rel_xfn' ] = 'Quan hệ liên kết (XFN)';
            $arr[ 'open_target' ] = 'Mở liên kết trong tab mới';
            $arr[ 'text_view_more' ] = 'Hiển thị nút xem thêm';
            $arr[ 'text_view_details' ] = 'Hiển thị nút xem chi tiết';
            //$arr[ 'aaaaaaaaaaaaaaa' ] = 'aaaaaaaaaaaaa';
        }

        //
        //print_r( $arr );
        return $arr;
    }

    // trả về định dạng của từng post type (nếu có) -> mặc định type = text
    public static function meta_type( $key ) {
        $arr = [
            'hide_widget_title' => 'checkbox',
            'dynamic_tag' => 'select',
            'dynamic_post_tag' => 'select',
            'widget_description' => 'textarea',
            'post_number' => 'number',
            'num_line' => 'select',
            'post_cloumn' => 'select',
            'hide_title' => 'checkbox',
            'hide_description' => 'checkbox',
            'hide_info' => 'checkbox',
            'show_post_content' => 'checkbox',
            'run_slider' => 'checkbox',
            'open_target' => 'checkbox',
            'taxonomy_auto_slider' => 'checkbox',
            'max_width' => 'select',
        ];
        if ( isset( $arr[ $key ] ) ) {
            return $arr[ $key ];
        }

        //
        return 'text';
    }

    // description của từng meta nếu có
    public static function meta_desc( $key ) {
        $arr = [
            'custom_size' => 'Kích thước hình ảnh liên quan đến việc đảm bảo khung hình không bị vỡ, kích thước mặc định sẽ được sử dụng nếu bạn bỏ qua trường dữ liệu tương ứng. <br> Từ kích thước mong muốn mà bạn nhập vào, hệ thống sẽ tính toán tỉ lệ phù hợp nhất, cách tính tỉ lệ sẽ lấy chiều cao/ chiều rộng. <br> Ví dụ, bạn có hình ảnh có kích thước chiều rộng là 1366px, chiều cao là 400px, bạn sẽ nhập vào ô tương ứng là: <strong>400/1366</strong>. <br> * Vui lòng chỉ nhập số và dấu chéo.',
            'custom_cat_link' => '* Mặc định URL sẽ được tạo theo URL của phân nhóm hoặc để trống nếu không có nhóm. Bạn muốn thiết lập cứng URL cho phần này thì có thể thiết lập tại đây, hoặc hủy URL thì nhập <strong>#</strong>.',
            'custom_style' => file_get_contents( dirname( __DIR__ ) . '/Views/html/custom_style.html', 1 ),
            'custom_id' => '* Tương tự như CSS -> gán ID để xử lý cho tiện.',
            'rel_xfn' => '<strong>rel</strong>: noreferrer, nofollow...',
            'text_view_more' => 'Nhập nội dung cho nút xem thêm (Danh mục), khi trường này có dữ liệu, nút xem thêm sẽ xuất hiện trong widget',
            'text_view_details' => 'Nhập nội dung cho nút xem chi tiết bài viết, khi trường này có dữ liệu, nút xem chi tiết sẽ xuất hiện, liên kết của nó chính là liên kết của bài viết hoặc link gắn ngoài của bài viết',
            'taxonomy_custom_post_size' => 'Mặc định, tỉ lệ ảnh sẽ được dùng theo cấu hình chung của hệ thống. Trường hợp cần cấu hình riêng cho từng danh mục thì bạn có thể thiết lập tại đây. Ví dụ: 4/3',
            'taxonomy_auto_slider' => 'Khi chế độ này được kích hoạt, một slider sẽ tự động được khởi tạo, sau đó bạn chỉ việc thêm ảnh cho slider để nó có thể hoạt động',
        ];
        if ( isset( $arr[ $key ] ) ) {
            echo '<p class="controls-text-note">' . $arr[ $key ] . '</p>';
        }
    }

    // mảng chứa giá trị của các select
    public static function meta_select( $key ) {
        $arr_dynamic_tag = [
            'div' => 'DIV',
            'p' => 'P',
            'li' => 'LI',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6'
        ];

        //
        $arr = [
            'dynamic_tag' => $arr_dynamic_tag,
            'dynamic_post_tag' => $arr_dynamic_tag,
            'num_line' => [
                '' => 'Mặc định',
                'thread-list100' => 1,
                'thread-list50' => 2,
                'thread-list33' => 3,
                'thread-list25' => 4,
                'thread-list20' => 5,
                'thread-list16' => 6,
                'thread-list14' => 7,
                'thread-list12' => 8,
                'thread-list11' => 9,
                'thread-list10' => 10,
                'thread-list9' => 11,
                'thread-list8' => 12,
            ],
            'post_cloumn' => [
                '' => 'Mặc định',
                'chu_anh' => 'Chữ trái - ảnh phải',
                'anhtren_chuduoi' => 'Ảnh trên - chữ dưới',
                'chutren_anhduoi' => 'Chữ trên - ảnh dưới',
                'chi_chu' => 'Chỉ tiêu đề (title only)',
                'chi_anh' => 'Chỉ ảnh (image only)',
                'text_only' => 'Tiêu đề + nội dung (text only)',
                'chi_anh_chu' => 'Chỉ ảnh + tiêu đề (title + image)'
            ],
            'max_width' => [
                '' => 'Không giới hạn chiều rộng',
                'w99' => 'Rộng tối đa 999px',
                'w90' => 'Rộng tối đa 1366px',
                'w96' => 'Rộng tối đa 1666px',
            ]
        ];
        if ( isset( $arr[ $key ] ) ) {
            return $arr[ $key ];
        }

        //
        return [];
    }

}
