<?php

namespace App\Libraries;

//
use App\Language\admin\AdminTranslate;

//
class PostType
{
    // post_type
    const POST = 'post';
    const ADS = 'ads';
    //const BLOG = 'blog';
    const PAGE = 'page';
    const PROD = 'product';
    const MENU = 'nav_menu';
    const HTML_MENU = 'html_menu'; // kiểu menu soạn thảo trực tiếp trong khung nội dung của post
    const ORDER = 'shop_order';

    // định dạng media từ codeigniter
    const MEDIA = 'file_upload';
    const MEDIA_URI = 'upload/';
    const MEDIA_PATH = 'public/upload/'; // path upload có thể khác với URI do nó có thể nằm trong thư mục public
    // định dạng media từ wordpress
    const WP_MEDIA = 'attachment';
    const WP_MEDIA_URI = 'wp/wp-content/uploads/';

    // post_status
    //const PUBLIC = 'publish'; // daidq (2022-04-24): bỏ chữ sẽ bỏ tham số PUBLIC do trùng với mặc định của PHP
    const PUBLICITY = 'publish';
    const PRIVATELY = 'private'; // riêng tư -> chỉ dành cho tài khoản đã đăng nhập
    const PENDING = 'pending';
    const DRAFT = 'draft';
    const DELETED = 'trash';
    const INHERIT = 'inherit';
    const REMOVED = 'remove'; // ẩn hoàn toàn khỏi hệ thống admin

    // các loại thumbnail của media
    const MEDIA_MEDIUM = 'medium';
    const MEDIA_LARGE = 'large';
    const MEDIA_THUMBNAIL = 'thumbnail';
    const MEDIA_MEDIUM_LARGE = 'medium_large';

    //
    public function __construct()
    {
        //
    }

    protected static $arr = array(
        self::POST => AdminTranslate::POST,
        self::ADS => 'Quảng cáo',
        //self::BLOG => 'Blog/ Tin tức',
        self::PAGE => 'Trang tĩnh',
        self::PROD => AdminTranslate::PROD,
        self::MENU => 'Menu',
        self::HTML_MENU => 'HTML Menu',
        self::MEDIA => 'Media',
        self::ORDER => 'Đơn hàng',
    );

    public static function arrStatus()
    {
        return array(
            self::PUBLICITY => 'Hiển thị',
            self::PRIVATELY => 'Riêng tư',
            self::PENDING => 'Chờ duyệt',
            self::DRAFT => 'Bản nháp',
            self::DELETED => 'XÓA',
            //self::INHERIT => '',

        );
    }

    public static function typeList($key = '')
    {
        //echo $key . '<br>' . PHP_EOL;
        if ($key == '') {
            return self::$arr;
        }
        if (isset(self::$arr[$key])) {
            return self::$arr[$key];
        }
        return '';
    }

    public static function imageSizes()
    {
        return [
            'image_medium' => 'Ảnh đại diện (medium)',
            'image_thumbnail' => 'Ảnh đại diện (thumbnail)',
            'image_webp' => 'Ảnh đại diện (webp)',
            'image_medium_large' => 'Ảnh đại diện (medium large)',
            'image_large' => 'Ảnh đại diện (large)',
        ];
    }

    // trả về các meta mặc định dựa theo từng post_type
    public static function meta_default($post_type)
    {
        $arr = self::imageSizes();
        $arr['image'] = 'Ảnh đại diện'; // fullsize
        $arr['image_size'] = 'Kích cỡ'; // lựa chọn size ảnh
        // SEO
        $arr['meta_title'] = 'Meta title';
        $arr['meta_description'] = 'Meta description';
        $arr['meta_keyword'] = 'Meta keyword';

        //
        if (
            $post_type == self::POST ||
            //$post_type == self::BLOG ||
            $post_type == self::ADS
        ) {
            //$arr[ 'post_relationships' ] = 'Danh sách term ID';
            $arr['post_category'] = 'Danh mục';
            $arr['post_tags'] = 'Thẻ';
        }

        //
        if ($post_type == self::ADS) {
            $arr['url_video'] = 'URL video';
            $arr['url_redirect'] = 'Đường dẫn';
        }
        //
        else if ($post_type == self::PAGE) {
            //$arr[ 'second_content' ] = 'Nội dung phụ';
            $arr['post_auto_slider'] = 'Slider';
            $arr['page_template'] = 'Giao diện';
        }

        //
        //print_r( $arr );
        return $arr;
    }

    // trả về bản dịch của từng post_meta dựa theo key truyền vào
    public static function meta($key, $post_type, $arr = NULL)
    {
        if ($arr === NULL) {
            $arr = self::meta_default($post_type);
        }

        if (isset($arr[$key])) {
            return $arr[$key];
        }
        return '%' . $key . '%';
    }

    // trả về định dạng của từng post type (nếu có) -> mặc định type = text
    public static function meta_type($key, $custom_arr = [])
    {
        $arr = [
            //'second_content' => 'textarea',
            'page_template' => 'select',
            'post_auto_slider' => 'checkbox',
            //'post_relationships' => 'hidden',
            'image_large' => 'hidden',
            'image_medium_large' => 'hidden',
            'image_medium' => 'hidden',
            'image_thumbnail' => 'hidden',
            'image_webp' => 'hidden',
            'image_size' => 'select',
        ];
        if (isset($arr[$key])) {
            return $arr[$key];
        }
        // dành cho custom type muốn định dạng input
        if (isset($custom_arr[$key])) {
            return $custom_arr[$key];
        }

        //
        return 'text';
    }

    // description của từng meta nếu có
    public static function meta_desc($key, $custom_arr = [])
    {
        $arr = [
            'url_video' => 'Bạn có thể nhập vào URL video trên Youtube (Ví dụ: https://youtu.be/<strong>{ID}</strong>) hoặc URL video MP4, các định dạng khác hiện chưa được hỗ trợ. <br> Ảnh đại diện theo video: http://i3.ytimg.com/vi/<strong>{ID}</strong>/hqdefault.jpg hoặc http://i3.ytimg.com/vi/<strong>{ID}</strong>/maxresdefault.jpg hoặc https://img.youtube.com/vi/<strong>{ID}</strong>/0.jpg',
            'url_redirect' => 'Nhập vào đường dẫn bạn muốn banner này trỏ tới (nếu có).',
            //'second_content' => 'Nội dung phụ để dễ xử lý giao diện cho một số trường hợp đặc biệt',
            'post_auto_slider' => 'Khi chế độ này được kích hoạt, một slider sẽ tự động được khởi tạo, sau đó bạn chỉ việc thêm ảnh cho slider để nó có thể hoạt động',
        ];
        if (isset($arr[$key])) {
            echo '<p class="controls-text-note">' . $arr[$key] . '</p>';
        }
        // dành cho custom type muốn định dạng input
        if (isset($custom_arr[$key])) {
            echo '<p class="controls-text-note">' . $custom_arr[$key] . '</p>';
        }
    }

    public static function meta_class($key)
    {
        $arr = [
            //'second_content' => 'ckeditor',

        ];
        if (isset($arr[$key])) {
            return $arr[$key];
        }

        //
        return '';
    }

    // mảng chứa giá trị của các select
    public static function meta_select($key)
    {
        $arr_image_size = self::imageSizes();
        $arr_image_size['image_origin'] = 'Ảnh đại diện (bản gốc)';

        //
        $arr = [
            //'page_template' => [],
            'image_size' => $arr_image_size,
        ];
        if (isset($arr[$key])) {
            return $arr[$key];
        }

        //
        return [];
    }

    // trả về kích cỡ resize của ảnh
    public static function media_size($key = '')
    {
        $arr = [
            self::MEDIA_THUMBNAIL => 220,
            self::MEDIA_MEDIUM => 410,
            self::MEDIA_MEDIUM_LARGE => 768,
            self::MEDIA_LARGE => 1024,
        ];
        if (isset($arr[$key])) {
            return $arr[$key];
        }
        return $arr;
    }

    public static function meta_custom_type($data_type = '')
    {
        return [];
    }

    public static function meta_custom_desc($data_type = '')
    {
        return [];
    }
}
