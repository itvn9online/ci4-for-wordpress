<?php

namespace App\ Libraries;

class ConfigType {

    // post_type
    const CONFIG = 'config';
    const CATEGORY = 'product_list';
    const POST = 'product';
    const BLOGS = 'blog_list';
    const BLOG = 'blog';
    const TRANS = 'translate';
    const SMTP = 'smtp';

    private static $arr = array(
        self::CONFIG => 'Cấu hình',
        self::CATEGORY => 'Danh sách sản phẩm',
        self::POST => 'Chi tiết sản phẩm',
        self::BLOGS => 'Danh sách Blog/ Tin tức',
        self::BLOG => 'Chi tiết Blog/ Tin tức',
        self::TRANS => 'Bản dịch',
        self::SMTP => 'Cấu hình gửi mail',
    );

    public static function list( $key = '' ) {
        if ( $key == '' ) {
            return self::$arr;
        }
        if ( isset( self::$arr[ $key ] ) ) {
            return self::$arr[ $key ];
        }
        return '';
    }

    // trả về các meta mặc định dựa theo từng post_type
    public static function meta_default( $config_type ) {
        if ( $config_type == self::CONFIG ) {
            $arr = [
                'name' => 'Tên website',
                'company_name' => 'Tên công ty',
                'h1' => 'Thẻ H1',
                'title' => 'Thẻ Title',
                'solugan' => 'Câu slogan',
                'keyword' => 'Meta Keyword',
                'description' => 'Meta Description',
                'logo' => 'Logo chính',
                'logo_main_height' => 'Chiều cao logo',
                'web_favicon' => 'Web favicon',
                'logofooter' => 'Logo footer',
                'logo_footer_height' => 'Chiều cao logo (footer)',
                'logo_mobile' => 'Logo mobile',
                'logo_mobile_height' => 'Chiều cao logo (mobile)',
                'image' => 'Ảnh share Facebook',
                'phone' => 'Số điện thoại',
                'fax' => 'Fax',
                'website' => 'Website',
                'address' => 'Địa chỉ',
                'emailsend' => '',
                'passemailsend' => '',
                'emailcart' => '',
                'emailcontact' => 'Email liên hệ',
                'registeronline' => 'Link đăng ký BCT',
                'min_price' => '',
                'max_price' => '',
                'min_price_english' => '',
                'max_price_english' => '',
                'altlogo' => '',
                'facebook' => 'Facebook',
                'google' => 'Google+',
                'linkin' => 'Linkin',
                'skype' => 'Skype',
                'youtube' => 'Youtube',
                'facebook_iframe' => '',
                'map' => '',
                'chat' => '',
                'google_analytics' => 'Google Analytics ID',
                'html_header' => 'HTML đầu trang',
                'html_body' => 'HTML chân trang',
                'robots' => 'Robots.txt',
                'webmaster_tool' => '',
                'NL_receiver_email' => '',
                'NL_url_api' => '',
                'NL_merchant_id' => '',
                'NL_merchant_pass' => '',
                'Paypal_username' => '',
                'Paypal_password' => '',
                'Paypal_signature' => '',
                'list_slide' => '',
                'site_max_width' => 'Chiều rộng trang',
                'site_full_width' => 'Chiều rộng tối đa',
                'main_banner_size' => 'Kích thước banner chính',
                'second_banner_size' => 'Kích thước banner phụ',
            ];
        } else if ( $config_type == self::CATEGORY ) {
            $arr = [
                'eb_posts_per_page' => 'Số sản phẩm trên mỗi trang',
                'eb_posts_per_line' => 'Số sản phẩm trên mỗi dòng',
                'cf_product_size' => 'Tỉ lệ ảnh sản phẩm',
                'show_child_category' => 'Hiển thị nhóm sản phẩm con',
            ];
        } else if ( $config_type == self::POST ) {
            $arr = [
                'eb_post_per_page' => 'Số sản phẩm cùng nhóm',
            ];
        } else if ( $config_type == self::BLOGS ) {
            $arr = [
                'eb_blogs_per_page' => 'Số bài viết trên mỗi trang',
                'eb_blogs_per_line' => 'Số sản phẩm trên mỗi dòng',
                'cf_blog_description_length' => 'Số lượng chữ cho phần tóm tắt bài viết',
                'cf_blog_size' => 'Tỉ lệ ảnh tin tức',
                'show_child_blogs' => 'Hiển thị nhóm tin tức con',
            ];
        } else if ( $config_type == self::BLOG ) {
            $arr = [
                'eb_blog_per_page' => 'Số bài cùng nhóm',
            ];
        } else if ( $config_type == self::TRANS ) {
            $arr_tmp = [];
            $arr_tmp[ 'main_slider_slug' ] = 'Slug slider chính';
            for ( $i = 0; $i < 10; $i++ ) {
                $arr_tmp[ 'custom_text' . $i ] = 'Custom text ' . $i;
            }

            // thêm prefix vào đầu mỗi key
            $arr = [];
            foreach ( $arr_tmp as $k => $v ) {
                $arr[ 'lang_' . $k ] = $v;
            }
        } else if ( $config_type == self::SMTP ) {
            $arr = [
                'smtp_host_name' => 'Hostname',
                'smtp_host_port' => 'Port',
                // Công nghệ bảo mật
                'smtp_secure' => 'Bảo mật',
                'smtp_host_user' => 'Username',
                'smtp_host_pass' => 'Password',
                'smtp_from' => 'From',
                'smtp_from_name' => 'From name',
                'smtp_test_email' => 'Test email',
                'smtp_test_bcc_email' => 'Test BCC email',
                'smtp_test_cc_email' => 'Test CC email',
            ];
        } else {
            $arr = [];
        }

        //
        //print_r( $arr );
        return $arr;
    }

    // trả về định dạng của từng post type (nếu có) -> mặc định type = text
    public static function meta_type( $key ) {
        $arr = [
            'smtp_host_port' => 'number',
            'smtp_host_pass' => 'password',

            'eb_posts_per_page' => 'number',
            'eb_posts_per_line' => 'select',
            'eb_post_per_page' => 'number',

            'eb_blogs_per_page' => 'number',
            'eb_blogs_per_line' => 'select',
            'eb_blog_per_page' => 'number',

            'show_child_category' => 'checkbox',
            'show_child_blogs' => 'checkbox',
            'smtp_secure' => 'select',
            'description' => '',
            'logo' => '',
            'logo_main_height' => 'number',
            'logofooter' => '',
            'logo_footer_height' => 'number',
            'logo_mobile' => '',
            'logo_mobile_height' => 'number',
            'image' => '',
            'phone' => '',
            'address' => 'textarea',
            'emailsend' => '',
            'passemailsend' => '',
            'emailcart' => '',
            'emailcontact' => 'email',
            'registeronline' => '',
            'min_price' => '',
            'max_price' => '',
            'min_price_english' => '',
            'max_price_english' => '',
            'altlogo' => '',
            'facebook' => '',
            'google' => '',
            'linkin' => '',
            'skype' => '',
            'youtube' => '',
            'facebook_iframe' => '',
            'map' => '',
            'chat' => '',
            'google_analytics' => '',
            'html_header' => 'textarea',
            'html_body' => 'textarea',
            'robots' => 'textarea',
            'webmaster_tool' => '',
            'NL_receiver_email' => '',
            'NL_url_api' => '',
            'NL_merchant_id' => '',
            'NL_merchant_pass' => '',
            'Paypal_username' => '',
            'Paypal_password' => '',
            'Paypal_signature' => '',
            'list_slide' => '',
            'site_max_width' => 'number',
            'site_full_width' => 'number',
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
            'show_child_category' => 'Khi chế độ này được kích hoạt, và khi truy cập vào danh mục sản phẩm, nếu trong danh mục đó có các nhóm con thì các nhóm con sẽ được hiển thị thay vì hiển thị trực tiếp danh sách sản phẩm',
            'show_child_blogs' => 'Khi chế độ này được kích hoạt, và khi truy cập vào danh mục tin tức, nếu trong danh mục đó có các nhóm con thì các nhóm con sẽ được hiển thị thay vì hiển thị trực tiếp danh sách tin tức',
            'eb_post_per_page' => 'Khi số này lớn hơn 0, trong trang chi tiết bài viết sẽ lấy các bài cùng nhóm với bài hiện tại để giới thiệu',
            'eb_blog_per_page' => 'Khi số này lớn hơn 0, trong trang chi tiết bài viết sẽ lấy các bài cùng nhóm với bài hiện tại để giới thiệu',
            'main_slider_slug' => 'Nhập slug của slider chính vào đây, khi hàm the_slider không tìm được slider tương ứng thì nó sẽ lấy slider này để gán vào',
            'title' => '',
            'solugan' => '',
            'keyword' => '',
            'description' => '',
            'logo' => '',
            'logofooter' => '',
            'logo_mobile' => '',
            'image' => '',
            'phone' => '',
            'address' => '',
            'emailsend' => '',
            'passemailsend' => '',
            'emailcart' => '',
            'emailcontact' => '',
            'registeronline' => 'Link đăng ký bộ công thương',
            'min_price' => '',
            'max_price' => '',
            'min_price_english' => '',
            'max_price_english' => '',
            'altlogo' => '',
            'facebook' => '',
            'google' => '',
            'linkin' => '',
            'skype' => '',
            'youtube' => '',
            'facebook_iframe' => '',
            'map' => '',
            'chat' => '',
            'google_analytics' => '',
            'html_header' => '',
            'html_body' => '',
            'robots' => '',
            'webmaster_tool' => '',
            'NL_receiver_email' => '',
            'NL_url_api' => '',
            'NL_merchant_id' => '',
            'NL_merchant_pass' => '',
            'Paypal_username' => '',
            'Paypal_password' => '',
            'Paypal_signature' => '',
            'list_slide' => '',
            'site_max_width' => 'Bạn có thể thiết lập chiều rộng cho trang tại đây. Chiều rộng tiêu chuẩn: 1024px - Chiều rộng phổ biến: 1366px',
            'site_full_width' => 'Tương tự chiều rộng trang nhưng có độ rộng nhỉnh hơn chút. Chiều rộng tiêu chuẩn: 1024px - Chiều rộng phổ biến: 1666px',
            'main_banner_size' => 'Đây là kích thước dùng chung cho các banner chính, sử dụng bằng cách nhập <strong>%main_banner_size%</strong> vào mục <strong>Tùy chỉnh size ảnh</strong> trong cấu hình banner.',
            'second_banner_size' => 'Tương tự <strong>main_banner_size</strong>, đây là kích thước dùng chung cho các banner khác (nếu có), sử dụng bằng cách nhập <strong>%second_banner_size%</strong> vào mục <strong>Tùy chỉnh size ảnh</strong> trong cấu hình banner.',

            'smtp_host_name' => 'IP hoặc host name của server mail. Gmail SMTP: <strong>smtp.gmail.com</strong>, Pepipost SMTP: <strong>smtp.pepipost.com</strong>',
            'smtp_host_port' => 'Port nếu có. Gmail SSL port: <strong>465</strong>, Gmail TLS port: <strong>587</strong>, Pepipost port <strong>2525</strong>.',
            'smtp_host_user' => 'Email hoặc tài khoản đăng nhập. Khuyên dùng Gmail.',
            'smtp_host_pass' => 'Mật khẩu ứng dụng Gmail hoặc mật khẩu đăng nhập email thông thường. Nên dùng gmail và mật khẩu ứng dụng để đảm bảo bảo mật.',
            'smtp_from' => 'Email người gửi. Để trống để sử dụng email đăng nhập luôn, hạn chế email gửi vào spam',
            'smtp_from_name' => 'Tên người gửi. Bạn có thể tùy biến tên người gửi tại đây. Ví dụ: Công ty ABC, Nguyên Văn A...',
            'smtp_test_email' => 'Thiết lập xong cấu hình, bạn có thể nhập thêm email người nhận và <a href="' . base_url( 'admin/configs' ) . '?config_type=smtp&test_mail=1" target="_blank"><strong>bấm vào đây</strong></a> để test email gửi đi.',
            'smtp_test_bbc_email' => 'Thêm email để test chức năng BCC.',
            'smtp_test_cc_email' => 'Thêm email để test chức năng CC.',
        ];
        if ( isset( $arr[ $key ] ) && $arr[ $key ] != '' ) {
            echo '<p class="controls-text-note">' . $arr[ $key ] . '</p>';
        }
    }

    // mảng chứa giá trị của các select
    public static function meta_select( $key ) {
        $eb_posts_per_line = [
            '' => 'Theo thiết kế mặc định của tác giả',
            'thread-list100' => '1',
            'thread-list50' => '2',
            'thread-list33' => '3',
            'thread-list25' => '4',
            'thread-list20' => '5',
            'thread-list16' => '6',
            'thread-list14' => '7',
            'thread-list12' => '8',
        ];
        $eb_blogs_per_line = $eb_posts_per_line;

        //
        $arr = [
            'eb_posts_per_line' => $eb_posts_per_line,
            'eb_blogs_per_line' => $eb_posts_per_line,
            'smtp_secure' => [
                '' => 'Không bảo mật',
                'ssl' => 'SSL (port 465)',
                'tls' => 'TLS (port 587)',
            ],
        ];
        if ( isset( $arr[ $key ] ) ) {
            return $arr[ $key ];
        }

        //
        return [];
    }

}