<?php

namespace App\Libraries;

//
use App\Language\admin\AdminTranslate;

class TaxonomyType
{
    // taxonomy
    const POSTS = 'category';
    const TAGS = 'tags';
    //const OPTIONS = 'post_options';
    const ADS = 'ads_options';
    //const BLOGS = 'blogs';
    //const BLOG_TAGS = 'blog_tags';
    const PROD_CATS = 'product_cat';
    const PROD_OTPS = 'product_opt';
    const PROD_TAGS = 'product_tag';
    //const MENU = 'nav_menu';
    //const PAGE = 'page_taxonomy';

    // term_status
    const VISIBLE = '0';
    const HIDDEN = '1';

    private static $arr = array(
        self::POSTS => 'Danh mục ' . AdminTranslate::POST,
        self::TAGS => 'Thẻ ' . AdminTranslate::POST,
        //self::OPTIONS => 'Thông số khác',
        self::ADS => 'Danh mục quảng cáo',
        //self::BLOGS => 'Danh mục tin',
        //self::BLOG_TAGS => 'Thẻ Blog/ Tin tức',
        self::PROD_CATS => 'Danh mục ' . AdminTranslate::PROD,
        self::PROD_OTPS => 'Thông số khác',
        self::PROD_TAGS => 'Thẻ ' . AdminTranslate::PROD,
        //self::MENU => 'Menu',
        //self::PAGE => 'Trang tĩnh',
    );

    public static function typeList($key = '', $first_name = false)
    {
        if ($key == '') {
            return self::$arr;
        }
        if (isset(self::$arr[$key])) {
            // lấy thêm định danh cho Danh mục (nếu có)
            $get_first_name = '';
            /*
            if ( $first_name == true ) {
            $get_first_name = self::nameList( $key );
            }
            */
            return $get_first_name . self::$arr[$key];
        }
        return '';
    }

    private static $arr_name = array(
        self::POSTS => 'Danh mục',
        self::TAGS => 'Danh sách',
        //self::ADS => 'Danh mục',
        //self::BLOGS => 'Danh mục',
        //self::BLOG_TAGS => 'Danh mục',
        //self::OPTIONS => 'Danh sách',
        self::PROD_OTPS => 'Danh sách',
        //self::MENU => 'Menu',
        //self::PAGE => 'Trang tĩnh',
    );

    public static function nameList($key = '')
    {
        if ($key == '') {
            return self::$arr_name;
        }
        if (isset(self::$arr_name[$key])) {
            return self::$arr_name[$key] . ' ';
        }
        return 'Danh mục ';
    }

    // trả về các meta mặc định dựa theo từng post_type
    public static function meta_default($taxonomy)
    {
        $arr = [];

        //
        //$arr['taxonomy_avatar'] = 'Ảnh đại diện';
        $arr['taxonomy_custom_post_size'] = 'Tùy chỉnh tỉ lệ ảnh';
        $arr['taxonomy_auto_slider'] = 'Slider';
        if ($taxonomy == self::ADS) {
            $arr['hide_widget_title'] = 'Ẩn tiêu đề danh mục';
            $arr['custom_cat_link'] = 'Tùy chỉnh URL';
            $arr['dynamic_tag'] = 'HTML tag cho Tiêu đề';
            $arr['dynamic_post_tag'] = 'HTML tag cho Tên bài viết';
            $arr['widget_description'] = 'Mô tả';
            $arr['post_number'] = 'Số lượng bản ghi hiển thị';
            $arr['num_line'] = 'Số cột trên mỗi dòng';
            $arr['num_medium_line'] = 'Số cột trên mỗi dòng (table)';
            $arr['num_small_line'] = 'Số cột trên mỗi dòng (mobile)';
            $arr['column_spacing'] = 'Khoảng cách giữa các cột';
            $arr['row_align'] = 'Căn chỉnh (align)';
            $arr['post_cloumn'] = 'Bố cục bài viết';
            $arr['post_custom_cloumn'] = 'Bố cục tùy chỉnh bài viết';
            $arr['hide_title'] = 'Ẩn tiêu đề của bài viết';
            $arr['hide_description'] = 'Ẩn tóm tắt của bài viết';
            $arr['hide_info'] = 'Ẩn ngày tháng, danh mục của bài viết';
            $arr['show_short_title'] = 'Hiển thị tiêu đề ngắn bài viết';
            $arr['show_post_content'] = 'Hiển thị nội dung của bài viết';
            $arr['run_slider'] = 'Chạy slider (banner sẽ được hiển thị dưới dạng slider của flatsome)';
            $arr['max_width'] = 'Chiều rộng tối đa';
            $arr['custom_style'] = 'Tùy chỉnh CSS';
            $arr['custom_id'] = 'Tùy chỉnh ID';
            $arr['custom_size'] = 'Tùy chỉnh size ảnh';
            $arr['rel_xfn'] = 'Quan hệ liên kết (XFN)';
            $arr['open_target'] = 'Mở liên kết trong tab mới';
            $arr['text_view_more'] = 'Hiển thị nút xem thêm';
            $arr['text_view_details'] = 'Hiển thị nút xem chi tiết';
        } else {
            // SEO
            $arr['meta_title'] = 'Meta title';
            $arr['meta_description'] = 'Meta description';
            $arr['meta_keyword'] = 'Meta keyword';
            $arr['term_template'] = 'Giao diện';
            //$arr['term_status'] = 'Trạng thái hiển thị';
        }
        $arr['term_col_templates'] = 'Col HTML';

        //
        //print_r( $arr );
        return $arr;
    }

    // trả về định dạng của từng post type (nếu có) -> mặc định type = text
    public static function meta_type($key)
    {
        $arr = [
            'term_template' => 'select',
            'term_col_templates' => 'select',
            'hide_widget_title' => 'checkbox',
            'dynamic_tag' => 'select',
            'dynamic_post_tag' => 'select',
            'widget_description' => 'textarea',
            'post_number' => 'number',
            'num_line' => 'select',
            'num_medium_line' => 'select',
            'num_small_line' => 'select',
            'column_spacing' => 'select',
            'row_align' => 'select',
            'post_cloumn' => 'select',
            'post_custom_cloumn' => 'select',
            'hide_title' => 'checkbox',
            'hide_description' => 'checkbox',
            'hide_info' => 'checkbox',
            'show_short_title' => 'checkbox',
            'show_post_content' => 'checkbox',
            'run_slider' => 'checkbox',
            'open_target' => 'checkbox',
            'taxonomy_auto_slider' => 'checkbox',
            'max_width' => 'select',
            //'term_status' => 'select',
        ];
        if (isset($arr[$key])) {
            return $arr[$key];
        }

        //
        return 'text';
    }

    // description của từng meta nếu có
    public static function meta_desc($key)
    {
        $arr = [
            'custom_size' => 'Kích thước hình ảnh liên quan đến việc đảm bảo khung hình không bị vỡ, kích thước mặc định sẽ được sử dụng nếu bạn bỏ qua trường dữ liệu tương ứng. <br> Từ kích thước mong muốn mà bạn nhập vào, hệ thống sẽ tính toán tỉ lệ phù hợp nhất, cách tính tỉ lệ sẽ lấy chiều cao/ chiều rộng. <br> Ví dụ, bạn có hình ảnh có kích thước chiều rộng là 1366px, chiều cao là 400px, bạn sẽ nhập vào ô tương ứng là: <strong>400/1366</strong>. <br> * Vui lòng chỉ nhập số và dấu chéo.',
            'custom_cat_link' => '* Mặc định URL sẽ được tạo theo URL của phân nhóm hoặc để trống nếu không có nhóm. Bạn muốn thiết lập cứng URL cho phần này thì có thể thiết lập tại đây, hoặc hủy URL thì nhập <strong>#</strong>.',
            //'custom_style' => file_get_contents(dirname(__DIR__) . '/Views/html/custom_style.html', 1),
            'custom_style' => 'Chọn class CSS hỗ trợ định dạng sẵn hoặc tự soạn class CSS mới sau đó thực hiện viết CSS tương ứng.',
            'custom_id' => '* Tương tự như CSS -> gán ID để xử lý cho tiện.',
            'rel_xfn' => '<strong>rel</strong>: noreferrer, nofollow...',
            'text_view_more' => 'Nhập nội dung cho nút xem thêm (Danh mục), khi trường này có dữ liệu, nút xem thêm sẽ xuất hiện trong widget',
            'text_view_details' => 'Nhập nội dung cho nút xem chi tiết bài viết, khi trường này có dữ liệu, nút xem chi tiết sẽ xuất hiện, liên kết của nó chính là liên kết của bài viết hoặc link gắn ngoài của bài viết',
            'taxonomy_custom_post_size' => 'Mặc định, tỉ lệ ảnh sẽ được dùng theo cấu hình chung của hệ thống. Trường hợp cần cấu hình riêng cho từng danh mục thì bạn có thể thiết lập tại đây. Ví dụ: 4/3',
            'taxonomy_auto_slider' => 'Khi chế độ này được kích hoạt, một slider sẽ tự động được khởi tạo, sau đó bạn chỉ việc thêm ảnh cho slider để nó có thể hoạt động',
            'term_template' => 'Sử dụng khi muốn thiết lập giao diện riêng cho từng danh mục. File mẫu là file .php được đặt trong thư mục <b>term-templates</b> của mỗi theme.',
            'term_col_templates' => 'HTML mẫu của phần col cho từng danh mục (nếu có). Mặc định sử dụng col chung của website.',
            'post_custom_cloumn' => 'Khi cần tùy chỉnh `Bố cục bài viết` cho danh mục này thì có thể thêm file .html vào đây `/' . str_replace(ROOTPATH, '', VIEWS_CUSTOM_PATH) . 'ads_node/` sau đó chọn file tương ứng cho danh mục này. HTML trong file được chọn sẽ dùng để tạo hình cho bài viết. Mẫu HTML có thể copy từ file `/app/Views/html/ads_node.html` hoặc tùy chỉnh theo tiêu chuẩn .col của bootstrap.',
            //'term_status' => 'Dùng khi cần ẩn các danh mục khỏi menu động.',
        ];
        if (isset($arr[$key])) {
            echo '<p class="controls-text-note">' . $arr[$key] . '</p>';
        }
    }

    // mảng chứa giá trị của các select
    public static function meta_select($key)
    {
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
        $arr_num_line = [
            '' => 'Mặc định',
            'row-12' => 1,
            'row-6' => 2,
            'row-4' => 3,
            'row-3' => 4,
            'row-5' => 5,
            'row-2' => 6,
            'row-7' => 7,
            'row-8' => 8,
            'row-9' => 9,
            'row-10' => 10,
            'row-11' => 11,
            'row-1' => 12,
        ];
        $arr_num_medium_line = [];
        $arr_num_small_line = [];
        foreach ($arr_num_line as $k => $v) {
            $arr_num_medium_line[str_replace('row-', 'row-medium-', $k)] = $v;
            $arr_num_small_line[str_replace('row-', 'row-small-', $k)] = $v;
        }

        //
        $arr = [
            'dynamic_tag' => $arr_dynamic_tag,
            'dynamic_post_tag' => $arr_dynamic_tag,
            'num_line' => $arr_num_line,
            'num_medium_line' => $arr_num_medium_line,
            'num_small_line' => $arr_num_small_line,
            'column_spacing' => [
                '' => 'Mặc định',
                'row-small' => 'Nhỏ',
                'row-large' => 'Lớn',
                'row-collapse' => 'Không có khoảng cách',
            ],
            'row_align' => [
                '' => 'Mặc định',
                'align-equal' => 'equal',
                'align-middle' => 'middle',
                'align-bottom' => 'bottom',
            ],
            'post_cloumn' => [
                '' => 'Mặc định (Ảnh trên - chữ dưới)',
                'anh_chu' => 'Ảnh trái - chữ phải',
                'chu_anh' => 'Chữ trái - ảnh phải',
                //'anhtren_chuduoi' => 'Ảnh trên - chữ dưới',
                'chutren_anhduoi' => 'Chữ trên - ảnh dưới',
                'chi_chu' => 'Chỉ tiêu đề (title only)',
                'chi_anh' => 'Chỉ ảnh (image only)',
                'text_only' => 'Tiêu đề + nội dung (text only)',
                'chi_anh_chu' => 'Chỉ ảnh + tiêu đề (title + image)'
            ],
            'post_custom_cloumn' => [
                '' => '- Tùy chỉnh HTML -',
            ],
            'max_width' => [
                '' => 'Mặc định',
                'row-full-width' => 'Không giới hạn chiều rộng',
                /*
                '' => 'Không giới hạn chiều rộng',
                'w99' => 'Rộng tối đa 999px (w99)',
                'w90' => 'Rộng tối đa 1366px (w90)',
                'w96' => 'Rộng tối đa 1666px (w96)',
                */
            ],
            /*
            'term_status' => [
                TaxonomyType::VISIBLE => 'Hiển thị',
                TaxonomyType::HIDDEN => 'Ẩn',
            ],
            */
        ];
        if (isset($arr[$key])) {
            return $arr[$key];
        }

        //
        return [];
    }
}
