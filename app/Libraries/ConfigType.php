<?php

namespace App\Libraries;

use App\Language\admin\AdminTranslate;

class ConfigType
{
    // config type
    const CONFIG = 'config';
    const DISPLAY = 'display';
    const SOCIAL = 'social';
    const CATEGORY = 'product_list';
    const POST = 'product';
    const BLOGS = 'blog_list';
    const BLOG = 'blog';
    const TRANS = 'translate';
    const SMTP = 'smtp';
    const CONSTANTS = 'constants';
    const CHECKOUT = 'checkout';
    const CHECKBOX = 'checkbox';
    const NUM_MON = 'num_mon'; // number and money -> loại cấu hình dùng để định giá hoặc tạo số theo ý muốn
    const FIREBASE = 'firebase';

    private static $arr_posts_per_line = [
        '' => 'Theo thiết kế mặc định của tác giả',
        'row-5' => '1',
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

    private static $eb_column_spacing = [
        '' => 'Mặc định',
        'row-small' => 'Nhỏ',
        'row-large' => 'Lớn',
        'row-collapse' => 'Không có khoảng cách',
    ];

    private static $eb_row_align = [
        '' => 'Mặc định',
        'align-equal' => 'equal',
        'align-middle' => 'middle',
        'align-bottom' => 'bottom',
    ];

    private static $arr = array(
        self::CONFIG => 'Cấu hình',
        self::DISPLAY => 'Cài đặt hiển thị',
        self::SOCIAL => 'Mạng xã hội',
        self::CATEGORY => 'Danh sách ' . AdminTranslate::POST,
        self::POST => 'Chi tiết ' . AdminTranslate::POST,
        self::BLOGS => 'Danh sách Blog/ Tin tức',
        self::BLOG => 'Chi tiết Blog/ Tin tức',
        self::TRANS => 'Bản dịch',
        self::SMTP => 'Cấu hình Mail/ Telegram',
        self::CONSTANTS => 'Constants',
        self::CHECKOUT => 'Thanh toán',
        self::CHECKBOX => 'Bật/ Tắt',
        self::NUM_MON => 'Số',
        self::FIREBASE => 'Firebase',
    );

    // các loại config chính sẽ được auto load để sử dụng khi cần thiết
    public static function mainType()
    {
        return [
            self::CONFIG,
            self::DISPLAY,
            self::SOCIAL,
            self::CATEGORY,
            self::POST,
            self::BLOGS,
            self::BLOG,
            self::CHECKOUT,
            self::FIREBASE,
            // một số config sử dụng method riêng rồi thì bỏ ở đây đi
            //self::CHECKBOX,
            //self::NUM_MON,
        ];
    }

    public static function typeList($key = '')
    {
        if ($key == '') {
            return self::$arr;
        }
        if (isset(self::$arr[$key])) {
            return self::$arr[$key];
        }
        return '';
    }

    // trả về các meta mặc định dựa theo từng config type
    public static function meta_default($config_type)
    {
        if ($config_type == self::CONFIG) {
            $arr = [
                'name' => 'Tên website',
                'company_name' => 'Tên công ty',
                'h1' => 'Thẻ H1',
                'solugan' => 'Câu slogan',
                'title' => 'Meta Title',
                'keyword' => 'Meta Keyword',
                'description' => 'Meta Description',
                'logo' => 'Logo chính',
                'logo_main_height' => 'Chiều cao logo',
                'logo_width_img' => 'Chiều rộng thật của logo',
                'logo_height_img' => 'Chiều cao thật của logo',
                'web_favicon' => 'Web favicon',
                'logofooter' => 'Logo footer',
                'logo_footer_height' => 'Chiều cao logo (footer)',
                'logo_mobile' => 'Logo mobile',
                'logo_mobile_height' => 'Chiều cao logo (mobile)',
                'phone' => 'Số điện thoại',
                'fax' => 'Fax',
                'website' => 'Website',
                'address' => 'Địa chỉ',
                'address2' => 'Địa chỉ 2',
                'emailcontact' => 'Email liên hệ',
                'html_header' => 'HTML đầu trang',
                'html_body' => 'HTML chân trang',
                'robots' => 'Robots.txt',
                'blog_private' => 'Ngăn chặn các công cụ tìm kiếm đánh chỉ mục website này',
                'enable_vue_js' => 'Sử dụng VueJS',
                'enable_hotlink_protection' => 'HotLink protection',
                'enable_device_protection' => 'Device logged protection',
                'disable_register_member' => 'Dừng đăng ký tài khoản mới',
            ];
        } else if ($config_type == self::DISPLAY) {
            $arr = [
                'body_font_size' => 'Cỡ chữ mặc định',
                'bodym_font_size' => 'Cỡ chữ mặc định',
                'default_bg' => 'Màu nền mặc định',
                'sub_bg' => 'Màu nền thứ cấp',
                'default_color' => 'Màu chữ mặc định',
                'a_color' => 'Màu liên kết',
                'site_max_width' => 'Chiều rộng trang',
                'site_full_width' => 'Chiều rộng tối đa',
                'main_banner_size' => 'Kích thước banner chính',
                'second_banner_size' => 'Kích thước banner phụ',
            ];
        } else if ($config_type == self::SOCIAL) {
            $arr = [
                'google_analytics' => 'Google Analytics ID',
                'fb_app_id' => 'Facebook App ID',
                'facebook' => 'Facebook',
                'google' => 'Google+',
                'linkin' => 'Linkin',
                'skype' => 'Skype',
                'youtube' => 'Youtube',
                'tiktok' => 'TikTok',
                'zalo' => 'Số Zalo',
                'zalo_me' => 'Link Zalo',
                'image' => 'Ảnh share Facebook',
                'registeronline' => 'Link đăng ký BCT',
                'notificationbct' => 'Link thông báo BCT',
            ];
        } else if ($config_type == self::CATEGORY) {
            $arr = [
                'eb_posts_per_page' => 'Số ' . AdminTranslate::POST . ' trên mỗi trang',
                'eb_posts_per_line' => 'Số cột trên mỗi dòng',
                'eb_posts_medium_per_line' => 'Số cột trên mỗi dòng (table)',
                'eb_posts_small_per_line' => 'Số cột trên mỗi dòng (mobile)',
                'eb_posts_column_spacing' => 'Khoảng cách giữa các cột',
                'eb_posts_row_align' => 'Căn chỉnh (align)',
                'eb_posts_sidebar' => 'Sidebar',
                'cf_product_size' => 'Tỉ lệ ảnh ' . AdminTranslate::POST,
                'cf_thumbnail_size' => 'Chất lượng hình ảnh',
                'show_child_category' => 'Hiển thị nhóm ' . AdminTranslate::POST . ' con',
            ];
        } else if ($config_type == self::POST) {
            $arr = [
                'eb_post_per_page' => 'Số ' . AdminTranslate::POST . ' cùng nhóm',
                'eb_post_per_line' => 'Số cột trên mỗi dòng',
                'eb_post_medium_per_line' => 'Số cột trên mỗi dòng (table)',
                'eb_post_small_per_line' => 'Số cột trên mỗi dòng (mobile)',
                'eb_post_column_spacing' => 'Khoảng cách giữa các cột',
                'eb_post_row_align' => 'Căn chỉnh (align)',
                'eb_post_sidebar' => 'Sidebar',
            ];
        } else if ($config_type == self::BLOGS) {
            $arr = [
                'eb_blogs_per_page' => 'Số bài viết trên mỗi trang',
                'eb_blogs_per_line' => 'Số cột trên mỗi dòng',
                'eb_blogs_medium_per_line' => 'Số cột trên mỗi dòng (table)',
                'eb_blogs_small_per_line' => 'Số cột trên mỗi dòng (mobile)',
                'eb_blogs_column_spacing' => 'Khoảng cách giữa các cột',
                'eb_blogs_row_align' => 'Căn chỉnh (align)',
                'eb_blogs_sidebar' => 'Sidebar',
                'cf_blog_description_length' => 'Độ dài tóm tắt bài viết',
                'cf_blog_size' => 'Tỉ lệ ảnh tin tức',
                'show_child_blogs' => 'Hiển thị nhóm tin tức con',
            ];
        } else if ($config_type == self::BLOG) {
            $arr = [
                'eb_blog_per_page' => 'Số bài cùng nhóm',
                'eb_blog_per_line' => 'Số cột trên mỗi dòng',
                'eb_blog_medium_per_line' => 'Số cột trên mỗi dòng (table)',
                'eb_blog_small_per_line' => 'Số cột trên mỗi dòng (mobile)',
                'eb_blog_column_spacing' => 'Khoảng cách giữa các cột',
                'eb_blog_row_align' => 'Căn chỉnh (align)',
                'eb_blog_sidebar' => 'Sidebar',
            ];
        } else if ($config_type == self::NUM_MON) {
            $arr = [
                'custom_num_mon0' => 'Custom number 0'
            ];
        } else if ($config_type == self::FIREBASE) {
            $arr = [
                'g_recaptcha_site_key' => 'G recaptcha site key',
                'g_recaptcha_secret_key' => 'G recaptcha secret key',
                'g_firebase_config' => 'SDK setup and configuration',
                'g_firebase_privacy_policy_url' => 'Privacy policy URL',
                'g_firebase_terms_service_url' => 'Terms of service URL',
                'firebase_sign_in_redirect_to' => 'Sign-in success URL',
                'g_firebase_default_country' => 'Default country',
                'g_firebase_login_hint' => 'Login hint',
                'g_firebase_language_code' => 'Language code',
                'firebase_auth_google' => 'Google Auth Provider',
                'firebase_auth_facebook' => 'Facebook Auth Provider',
                'firebase_auth_twitter' => 'Twitter Auth Provider',
                'firebase_auth_github' => 'Github Auth Provider',
                'firebase_auth_email' => 'Email Auth Provider',
                'firebase_auth_anonymous' => 'Anonymous Auth Provider',
                'firebase_auth_phone' => 'Phone Auth Provider',
                'firebase_verify_phone' => 'Phone Verify Provider',
            ];
        } else if ($config_type == self::CHECKBOX) {
            $arr = [];
            for ($i = 0; $i < NUMBER_CHECKBOXS_INPUT; $i++) {
                $arr['custom_checkbox' . $i] = 'Custom checkbox ' . $i;
            }
        } else if ($config_type == self::TRANS) {
            $arr_tmp = [];
            $arr_tmp['main_slider_slug'] = 'Slug slider chính';
            $arr_tmp['copy_right_first'] = 'Bản quyền (trước)';
            $arr_tmp['copy_right_last'] = 'Bản quyền (sau)';
            $arr_tmp['powered_by_eb'] = 'Cung cấp bởi';

            // thêm prefix vào đầu mỗi key
            $arr = [];
            foreach ($arr_tmp as $k => $v) {
                $arr['lang_' . $k] = $v;
            }
        } else if ($config_type == self::SMTP) {
            $arr = [
                // Công nghệ bảo mật
                'smtp_host_user' => 'Email hoặc Username',
                'smtp_host_pass' => 'Mật khẩu',
                'smtp_host_show_pass' => 'Mật khẩu',
                'smtp_host_name' => 'IP hoặc Hostname',
                'smtp_secure' => 'Bảo mật',
                'smtp_host_port' => 'Port',
                //'smtp_from' => 'From',
                'smtp_from_name' => 'From name',
                'smtp_no_reply' => 'No-reply',
                //
                'smtp_heading_test_email' => 'Thử nghiệm chức năng gửi email',
                'smtp_test_email' => 'Test email',
                'smtp_test_bcc_email' => 'Test BCC email',
                'smtp_test_cc_email' => 'Test CC email',
                // cấu hình dự phòng
                'smtp2_heading_host_user' => 'Cấu hình mail dự phòng',
                'smtp2_host_user' => 'Email hoặc Username',
                'smtp2_host_pass' => 'Mật khẩu',
                'smtp2_host_show_pass' => 'Mật khẩu',
                'smtp2_host_name' => 'IP hoặc Hostname',
                'smtp2_secure' => 'Bảo mật',
                'smtp2_host_port' => 'Port',
                'telegram_begin_block' => 'Cài đặt Telegram',
                'telegram_bot_token' => 'Bot token',
                'telegram_chat_id' => 'Chat ID',
            ];
        } else if ($config_type == self::CONSTANTS) {
            $arr = [
                'FTP_HOST' => 'FTP host',
                'FTP_USER' => 'FTP user',
                'FTP_PASS' => 'FTP pass',
                'PARTNER_WEBSITE' => 'Website đối tác',
                'PARTNER_BRAND_NAME' => 'Tên đối tác',
                'PARTNER2_WEBSITE' => 'Website đối tác 2',
                'PARTNER2_BRAND_NAME' => 'Tên đối tác 2',
                'MY_DB_DRIVER' => 'DB Driver',
                'BASE_PROTOCOL' => 'Giao thức cơ sở',
                'CUSTOM_MD5_HASH_CODE' => 'MD5 hash code',
                'MY_CACHE_HANDLER' => 'Kiểu cache',
                'CDN_BASE_URL' => 'CDN base URL',
                'ALLOW_USING_MYSQL_DELETE' => 'Using MySQL DELETE',
                'WGR_CSP_ENABLE' => 'CSP header',
                'WGR_CSP_DEFAULT_SRC' => 'CSP default src',
                'WGR_CSP_SCRIPT_SRC' => 'CSP script src',
                'WGR_CSP_STYLE_SRC' => 'CSP style src',
                'WGR_CSP_IMG_SRC' => 'CSP img src',
                'WGR_CSP_CONNECT_SRC' => 'CSP connect src',
                'WGR_CSP_CHILD_SRC' => 'CSP child src',
                'NUMBER_CHECKBOXS_INPUT' => 'Số lượng bản ghi dạng số nguyên',
                'SITE_LANGUAGE_SUB_FOLDER' => 'Kiểu hiển thị đa ngôn ngữ',
                'SITE_LANGUAGE_DEFAULT' => 'Ngôn ngữ mặc định',
                'WGR_TABLE_PREFIX' => 'Database table prefix',
                'HTACCESSS_ALLOW' => 'Htaccess allow',
                //
                'WGR_CATEGORY_PREFIX' => 'Tiền tố cho danh mục sản phẩm',
                'WGR_PAGES_PREFIX' => 'Tiền tố cho trang tĩnh',
                //
                'WGR_CATEGORY_PERMALINK' => 'Category permalink',
                'WGR_BLOGS_PERMALINK' => 'Blogs permalink',
                'WGR_PRODS_PERMALINK' => 'Product category permalink',
                'WGR_TAXONOMY_PERMALINK' => 'Other taxonomy permalink',
                //
                'WGR_POST_PERMALINK' => 'Post permalink',
                'WGR_BLOG_PERMALINK' => 'Blog permalink',
                'WGR_PAGE_PERMALINK' => 'Page permalink',
                'WGR_POSTS_PERMALINK' => 'Other post permalink',
            ];
        } else if ($config_type == self::CHECKOUT) {
            $arr = [
                // Số tiền mặc định -> dùng cho các website dịch vụ đồng giá
                'min_product_price' => 'Giá trị tối thiểu của đơn hàng',
                'period_price' => 'Các bước giá',
                'period_discount' => 'Giảm giá',
                'period_bonus' => 'Tặng thêm',
                'bank_number' => 'Số tài khoản',
                'bank_card_name' => 'Chủ tài khoản',
                'bank_bin_code' => 'ID ngân hàng',
                'bank_reg_in' => 'Nơi mở tài khoản',
                'bank_logo' => 'Logo ngân hàng',
                'bank_swift_code' => 'Mã số ngân hàng',
                'bank_name' => 'Tên ngân hàng',
                'bank_short_name' => 'Tên rút gọn ngân hàng',
                'bank_code' => 'Mã ngân hàng',
                'autobank_token' => 'Autobank webhook token',
            ];
        } else {
            $arr = [];
        }

        //
        //print_r( $arr );
        return $arr;
    }

    // trả về định dạng của từng post type (nếu có) -> mặc định type = text
    public static function meta_type($key)
    {
        $arr = [
            'body_font_size' => 'number',
            'bodym_font_size' => 'number',
            'default_bg' => 'color',
            'sub_bg' => 'color',
            'default_color' => 'color',
            'a_color' => 'color',
            'min_product_price' => 'number',
            //'period_price' => 'textarea',
            'period_discount' => 'hidden',
            'period_bonus' => 'hidden',
            'bank_number' => 'number',
            'bank_bin_code' => 'select',
            'bank_logo' => 'hidden',
            'bank_swift_code' => 'hidden',
            'bank_name' => 'hidden',
            'bank_short_name' => 'hidden',
            'bank_code' => 'hidden',
            'smtp_host_port' => 'number',
            'smtp2_host_port' => 'number',
            'smtp_host_pass' => 'hidden',
            'smtp2_host_pass' => 'hidden',
            'smtp_secure' => 'select',
            'smtp2_secure' => 'select',
            'cf_thumbnail_size' => 'select',
            //
            'eb_posts_per_page' => 'number',
            'eb_posts_per_line' => 'select',
            'eb_posts_medium_per_line' => 'select',
            'eb_posts_small_per_line' => 'select',
            'eb_posts_column_spacing' => 'select',
            'eb_posts_row_align' => 'select',
            'eb_posts_sidebar' => 'select',
            //
            'eb_post_per_page' => 'number',
            'eb_post_per_line' => 'select',
            'eb_post_medium_per_line' => 'select',
            'eb_post_small_per_line' => 'select',
            'eb_post_column_spacing' => 'select',
            'eb_post_row_align' => 'select',
            'eb_post_sidebar' => 'select',
            //
            'eb_blogs_per_page' => 'number',
            'eb_blogs_per_line' => 'select',
            'eb_blogs_medium_per_line' => 'select',
            'eb_blogs_small_per_line' => 'select',
            'eb_blogs_column_spacing' => 'select',
            'eb_blogs_row_align' => 'select',
            'eb_blogs_sidebar' => 'select',
            //
            'eb_blog_per_page' => 'number',
            'eb_blog_per_line' => 'select',
            'eb_blog_medium_per_line' => 'select',
            'eb_blog_small_per_line' => 'select',
            'eb_blog_column_spacing' => 'select',
            'eb_blog_row_align' => 'select',
            'eb_blog_sidebar' => 'select',
            //
            'enable_vue_js' => 'checkbox',
            'enable_hotlink_protection' => 'checkbox',
            'enable_device_protection' => 'checkbox',
            'disable_register_member' => 'checkbox',
            'blog_private' => 'checkbox',
            'smtp_no_reply' => 'checkbox',
            'show_child_category' => 'checkbox',
            'show_child_blogs' => 'checkbox',
            'logo_main_height' => 'number',
            'logo_width_img' => 'number',
            'logo_height_img' => 'number',
            'logo_footer_height' => 'number',
            'logo_mobile_height' => 'number',
            'address' => 'textarea',
            'address2' => 'textarea',
            'emailcontact' => 'email',
            'html_header' => 'textarea',
            'html_body' => 'textarea',
            'robots' => 'textarea',
            'site_max_width' => 'number',
            'site_full_width' => 'number',
            'telegram_begin_block' => 'heading',
            'smtp_heading_test_email' => 'heading',
            'smtp2_heading_host_user' => 'heading',
            'zalo' => 'number',
            'zalo_me' => 'hidden',
            'fb_app_id' => 'number',
            //
            'MY_DB_DRIVER' => 'select',
            'BASE_PROTOCOL' => 'select',
            'MY_CACHE_HANDLER' => 'select',
            'ALLOW_USING_MYSQL_DELETE' => 'select',
            'WGR_CSP_ENABLE' => 'select',
            'NUMBER_CHECKBOXS_INPUT' => 'number',
            'SITE_LANGUAGE_SUB_FOLDER' => 'select',
            'SITE_LANGUAGE_DEFAULT' => 'select',
            'g_firebase_config' => 'textarea',
            'firebase_auth_google' => 'checkbox',
            'firebase_auth_facebook' => 'checkbox',
            'firebase_auth_twitter' => 'checkbox',
            'firebase_auth_github' => 'checkbox',
            'firebase_auth_email' => 'checkbox',
            'firebase_auth_anonymous' => 'checkbox',
            'firebase_auth_phone' => 'checkbox',
            'firebase_verify_phone' => 'checkbox',
        ];
        //print_r( $arr );
        if (isset($arr[$key])) {
            return $arr[$key];
        }

        //
        return 'text';
    }

    // description của từng meta nếu có
    public static function meta_desc($key)
    {
        $firebase_note_phone = ' * Chức năng này dùng nhiều sẽ mất phí nếu đăng ký gói trả phí. Vui lòng xem bảng giá trước khi kích hoạt nó. Bảng giá chung: https://firebase.google.com/pricing?hl=en&authuser=0 Bảng giá chi tiết: https://cloud.google.com/identity-platform/pricing?authuser=0#pricing_table ';

        //
        $arr = [
            'body_font_size' => 'Cỡ chữ được thiết lập cho body.',
            'bodym_font_size' => 'Cỡ chữ được thiết lập cho body bản mobile.',
            'default_bg' => 'Là màu nền đặc trưng cho các menu, nút bấm trên toàn bộ website.',
            'sub_bg' => 'Màu nền cho các module khác, tạo sự khác biệt với màu nền chính ở trên.',
            'default_color' => 'Màu mặc định cho mọi font chữ trên website nếu module đó không được thiết lập màu riêng.',

            'enable_vue_js' => 'Khi chế độ này được kích hoạt, thư viện VueJS sẽ được nhúng vào frontend để sử dụng',
            'show_child_category' => 'Khi chế độ này được kích hoạt, và khi truy cập vào danh mục ' . AdminTranslate::POST . ', nếu trong danh mục đó có các nhóm con thì các nhóm con sẽ được hiển thị thay vì hiển thị trực tiếp danh sách ' . AdminTranslate::POST,
            'show_child_blogs' => 'Khi chế độ này được kích hoạt, và khi truy cập vào danh mục tin tức, nếu trong danh mục đó có các nhóm con thì các nhóm con sẽ được hiển thị thay vì hiển thị trực tiếp danh sách tin tức',
            'eb_post_per_page' => 'Khi số này lớn hơn 0, trong trang chi tiết bài viết sẽ lấy các bài cùng nhóm với bài hiện tại để giới thiệu',
            'eb_blog_per_page' => 'Khi số này lớn hơn 0, trong trang chi tiết bài viết sẽ lấy các bài cùng nhóm với bài hiện tại để giới thiệu',
            'main_slider_slug' => 'Nhập slug của slider chính vào đây, khi hàm the_slider không tìm được slider tương ứng thì nó sẽ lấy slider này để gán vào',
            'image' => 'Khi share bài viết lên mạng xã hội như Facebook, Zalo... ảnh này sẽ được hiển thị nếu link share không có ảnh đính kèm.',
            'registeronline' => 'Link đăng ký với bộ công thương. Trong file view, sử dụng hàm <strong>$option_model->the_bct( $getconfig );</strong> để in ra logo BCT màu đỏ.',
            'notificationbct' => 'Link thông báo với bộ công thương. Trong file view, sử dụng hàm <strong>$option_model->the_bct( $getconfig );</strong> để in ra logo BCT màu xanh.',
            'g_recaptcha_site_key' => 'Truy cập vào đây https://www.google.com/recaptcha/about/ -> tới bảng điều khiển site -> tìm tab Settings -> reCAPTCHA keys -> copy Site key và Secret key dán vào đây và lưu lại.',
            'g_firebase_config' => 'Trong https://console.firebase.google.com/u/0/ -> chọn Project cần kết nối -> Project settings -> General -> Your apps -> SDK setup and configuration -> Config -> Copy code ở Config và dán vào đây. Nếu website đã thiết lập chức năng đăng nhập qua firebase, khuyến nghị bạn tắt chức năng Đăng ký mặc định tại đây: ' . base_url('admin/configs') . '?support_tab=data_disable_register_member',
            'g_firebase_privacy_policy_url' => 'URL chính sách bảo mật khi sử dụng chức năng đăng nhập qua firebase',
            'g_firebase_terms_service_url' => 'URL điều khoản dịch vụ khi sử dụng chức năng đăng nhập qua firebase',
            'g_firebase_default_country' => 'Mã quốc gia sẽ select mặc định khi xác  thực số điện  thoại qua firebase. Danh sách các mã xem tại đây: https://github.com/firebase/firebaseui-web/blob/master/javascript/data/README.md',
            'firebase_sign_in_redirect_to' => 'URL sau khi đăng nhập thành công sẽ chuyển tới nếu không có tham số login_redirect. Mặc định sẽ chuyển về trang chủ.',
            'g_firebase_login_hint' => 'Mã quốc gia sẽ select mặc định khi xác  thực số điện  thoại qua firebase. Ví dụ: +84',
            'g_firebase_language_code' => 'Ngôn ngữ hiển thị Firebase. Mã ngôn ngữ xem tại đây: https://github.com/firebase/firebaseui-web/blob/master/LANGUAGES.md',
            'firebase_auth_google' => 'Đăng nhập bằng tài khoản Google',
            'firebase_auth_facebook' => 'Đăng nhập bằng tài khoản Facebook. Tạo app sau đó lấy App ID và App secret tại đây: https://developers.facebook.com/apps/',
            'firebase_auth_twitter' => 'Đăng nhập bằng tài khoản Twitter',
            'firebase_auth_github' => 'Đăng nhập bằng tài khoản Github. Tạo app sau đó lấy Client ID và Client secret tại đây: https://github.com/settings/developers',
            'firebase_auth_email' => 'Đăng nhập bằng email',
            'firebase_auth_anonymous' => 'Sử dụng với vai trò khách',
            'firebase_auth_phone' => 'Đăng nhập bằng số điện thoại ' . $firebase_note_phone,
            'firebase_verify_phone' => 'Chức năng xác thực số điện thoại qua Firebase tại đây: ' . base_url('firebases/phone_auth') . $firebase_note_phone,
            //
            'site_max_width' => 'Bạn có thể thiết lập chiều rộng cho trang tại đây. Chiều rộng tiêu chuẩn: 1024px - Chiều rộng phổ biến: 1366px',
            'site_full_width' => 'Tương tự chiều rộng trang nhưng có độ rộng nhỉnh hơn chút. Chiều rộng tiêu chuẩn: 1024px - Chiều rộng phổ biến: 1666px',
            'main_banner_size' => 'Đây là kích thước dùng chung cho các banner chính, sử dụng bằng cách nhập <strong>%main_banner_size%</strong> vào mục <strong>Tùy chỉnh size ảnh</strong> trong cấu hình banner.',
            'second_banner_size' => 'Tương tự <strong>main_banner_size</strong>, đây là kích thước dùng chung cho các banner khác (nếu có), sử dụng bằng cách nhập <strong>%second_banner_size%</strong> vào mục <strong>Tùy chỉnh size ảnh</strong> trong cấu hình banner.',
            'smtp_host_name' => 'IP hoặc host name của server mail. Gmail SMTP: <strong>smtp.gmail.com</strong>, Pepipost SMTP: <strong>smtp.pepipost.com</strong>',
            'smtp_host_port' => 'Port nếu có. Gmail SSL port: <strong>465</strong>, Gmail TLS port: <strong>587</strong>, Pepipost port <strong>2525</strong>.',
            'smtp_host_user' => 'Email hoặc tài khoản đăng nhập. Khuyên dùng Gmail.',
            'smtp_host_show_pass' => 'Mật khẩu ứng dụng Gmail hoặc mật khẩu đăng nhập email thông thường. Nên dùng gmail và mật khẩu ứng dụng để đảm bảo bảo mật.',
            'smtp_from' => 'Email người gửi. Để trống để sử dụng email đăng nhập luôn, hạn chế email gửi vào spam',
            'smtp_from_name' => 'Tên người gửi. Bạn có thể tùy biến tên người gửi tại đây. Ví dụ: Công ty ABC, Nguyên Văn A...',
            'smtp_no_reply' => 'Khi kích hoạt chế độ này, email reply sẽ được đặt là <strong>noreply@' . $_SERVER['HTTP_HOST'] . '</strong> để các hệ thống email xác nhận đây là mail không nhận phản hồi.',
            'smtp_test_email' => 'Thiết lập xong cấu hình, bạn có thể nhập thêm email người nhận và bấm vào đây để test email gửi đi: ' . base_url('admin/smtps') . '?test_mail=1',
            'smtp_test_bcc_email' => 'Thêm email để test chức năng BCC.',
            'smtp_test_cc_email' => 'Thêm email để test chức năng CC.',
            'smtp2_host_user' => 'Cấu hình mail dự phòng, khi mail chính có vấn đề thì mail này sẽ được kích hoạt để dùng tạm',
            'enable_hotlink_protection' => 'Chặn các website khác truy cập trực tiếp vào file ảnh trên host này.',
            'enable_device_protection' => 'Chặn đăng nhập trên nhiều thiết bị trong cùng một thời điểm. Nếu phát hiện, sẽ đưa ra popup cảnh báo cho người dùng.',
            'disable_register_member' => 'Khi muốn dừng việc đăng ký tài khoản trên website thì bật chức năng này lên. Admin vẫn có thể tạo tài khoản từ trang admin hoặc người dùng có thể đăng nhập thông qua firebase nếu website có thiết lập Đăng nhập qua firebase tại đây ' . base_url('admin/firebases') . '?support_tab=data_g_firebase_config',
            'blog_private' => 'Việc tuân thủ yêu cầu này hoàn toàn phụ thuộc vào các công cụ tìm kiếm.',
            'min_product_price' => 'Số tiền tối thiểu mà khách phải thanh toán cho mỗi đơn hàng.',
            'period_price' => 'Bấm [Thêm mới] để thêm các mức giá cho các gói nạp, bấm [Xóa] để loại bỏ một mức giá. <br> Hỗ trợ các đơn vị chuyển đổi: tr = triệu, k = nghìn, % = quy đổi theo giá gốc.',
            'bank_card_name' => 'Lưu ý: viết HOA không dấu',
            'autobank_token' => 'Tham số dùng để tăng độ bảo mật cho WebHook tự động xác thực quá trình thanh toán. URL WebHook mặc định: ' . base_url('cassos/confirm'),
            'bank_bin_code' => 'Chức năng tự động xác nhận tiền vào thông qua WebHook của https://casso.vn/ - Ưu tiên sử dụng tài khoản ngân hàng <strong>VietinBank</strong>.',
            'powered_by_eb' => 'Sử dụng lệnh <strong>$lang_model->the_web_license( $getconfig );</strong> để hiển thị thông điệp bản quyền mặc định.',
            'telegram_bot_token' => 'Token của bot trên Telegram. Sau khi có Token, hãy bấm vào đây để tìm Chat ID: ' . base_url('admin/smtps') . '?get_tele_chat_id=1',
            'telegram_chat_id' => 'ID nhóm chat trên Telegram. Bao gồm cả dấu - nếu có',
            //
            'FTP_HOST' => 'Thông tin FTP - dùng để điều khiển file trong trường hợp bị lỗi permission. Mặc định là: 127.0.0.1',
            'CDN_BASE_URL' => 'URL để chạy CDN cho các file tĩnh (nếu có). Ví dụ: https://cdn.' . $_SERVER['HTTP_HOST'] . '/',
            'ALLOW_USING_MYSQL_DELETE' => 'Mặc định không cho xóa hoàn toàn dữ liệu trong mysql, nếu bạn muốn xóa hẳn thì có thể kích hoạt tính năng này.',
            'WGR_CSP_ENABLE' => 'Bật/Tắt chế độ Content-Security-Policy. Nhớ điều chỉnh thông số src cho hợp lý.',
            'NUMBER_CHECKBOXS_INPUT' => 'Website nào cần dùng nhiều tăng số lượng bản ghi lên.',
            'SITE_LANGUAGE_SUB_FOLDER' => 'Nếu là sub-folder thì sẽ hỗ trợ prefix cho routes, url cũng sẽ thêm prefix vào ngay sau domain. Ví dụ: domain.com/vn hoặc domain.com/en',
            'CUSTOM_MD5_HASH_CODE' => 'Chuỗi sẽ thêm vào khi sử dụng hàm mdnam -> md5 -> tăng độ bảo mật cho chuỗi. Chỉ thay đổi khi thực sự cần thiết do thông số này sẽ có thể khiến toàn bộ chuỗi sử dụng hàm mdnam sẽ phải dựng lại.',
            'HTACCESSS_ALLOW' => 'Một số thư mục chỉ cho phép 1 số định dạng file được phép truy cập. Ví dụ: ' . HTACCESSS_ALLOW,
            //
            'WGR_CATEGORY_PREFIX' => 'Ví dụ: ' . WGR_CATEGORY_PREFIX,
            'WGR_PAGES_PREFIX' => 'Ví dụ: ' . WGR_PAGES_PREFIX,
            //
            'WGR_CATEGORY_PERMALINK' => 'Ví dụ: ' . WGR_CATEGORY_PERMALINK,
            'WGR_BLOGS_PERMALINK' => 'Ví dụ: ' . WGR_BLOGS_PERMALINK,
            'WGR_PRODS_PERMALINK' => 'Ví dụ: ' . WGR_PRODS_PERMALINK,
            'WGR_TAXONOMY_PERMALINK' => 'Ví dụ: ' . WGR_TAXONOMY_PERMALINK,
            //
            'WGR_POST_PERMALINK' => 'Các tham số đầu vào sẽ là %tên-cột-trong-bảng-posts%. Ví dụ: %ID% %post_type% %post_name%',
            'WGR_BLOG_PERMALINK' => 'Ví dụ: ' . WGR_BLOG_PERMALINK,
            'WGR_PAGE_PERMALINK' => 'Ví dụ: ' . WGR_PAGE_PERMALINK,
            'WGR_POSTS_PERMALINK' => 'Ví dụ: ' . WGR_POSTS_PERMALINK,
            //
            'WGR_CSP_DEFAULT_SRC' => 'Ví dụ: ' . WGR_CSP_DEFAULT_SRC,
            'WGR_CSP_SCRIPT_SRC' => 'Ví dụ: ' . WGR_CSP_SCRIPT_SRC,
            'WGR_CSP_STYLE_SRC' => 'Ví dụ: ' . WGR_CSP_STYLE_SRC,
            'WGR_CSP_IMG_SRC' => 'Ví dụ: ' . WGR_CSP_IMG_SRC,
            'WGR_CSP_CONNECT_SRC' => 'Ví dụ: ' . WGR_CSP_CONNECT_SRC,
            'WGR_CSP_CHILD_SRC' => 'Ví dụ: ' . WGR_CSP_CHILD_SRC,
        ];
        if (isset($arr[$key]) && $arr[$key] != '') {
            echo '<p class="controls-text-note">' . $arr[$key] . '</p>';
        }
    }

    // description của từng meta nếu có
    public static function defaultColor($key)
    {
        //echo $key . '<br>' . "\n";
        $arr = [
            'default_bg' => '#145c00',
            'sub_bg' => '#c20000',
            //'default_color' => '',
            'a_color' => '#0d6efd',
        ];
        if (isset($arr[$key]) && $arr[$key] != '') {
            return $arr[$key];
        }
        return '#000000';
    }

    // mảng chứa giá trị của các select
    public static function meta_select($key)
    {
        //
        $arr_num_medium_line = [];
        $arr_num_small_line = [];
        foreach (self::$arr_posts_per_line as $k => $v) {
            $arr_num_medium_line[str_replace('row-', 'row-medium-', $k)] = $v;
            $arr_num_small_line[str_replace('row-', 'row-small-', $k)] = $v;
        }

        //
        $show_sidebar = [
            '' => 'Không hiển thị',
            'top-sidebar' => 'Trên',
            'bottom-sidebar' => 'Dưới',
            'left-sidebar' => 'Trái',
            'right-sidebar' => 'Phải',
        ];

        //
        $arr_default_display_lang = [
            '' => 'Mặc định theo code',
        ];
        //print_r(SITE_LANGUAGE_SUPPORT);
        foreach (SITE_LANGUAGE_SUPPORT as $v) {
            $arr_default_display_lang[$v['value']] = $v['text'];
        }

        //
        $arr = [
            'cf_thumbnail_size' => [
                'medium' => 'Thu gọn (khuyên dùng)',
                'medium_large' => 'Trung bình (medium_large)',
                'large' => 'Lớn (large)',
                '' => 'Đầy đủ (bản gốc)',
                'thumbnail' => 'Hình nhỏ (thumbnail)',
            ],
            'eb_posts_per_line' => self::$arr_posts_per_line,
            'eb_posts_medium_per_line' => $arr_num_medium_line,
            'eb_posts_small_per_line' => $arr_num_small_line,
            'eb_posts_column_spacing' => self::$eb_column_spacing,
            'eb_posts_row_align' => self::$eb_row_align,
            'eb_posts_sidebar' => $show_sidebar,
            //
            'eb_post_per_line' => self::$arr_posts_per_line,
            'eb_post_medium_per_line' => $arr_num_medium_line,
            'eb_post_small_per_line' => $arr_num_small_line,
            'eb_post_column_spacing' => self::$eb_column_spacing,
            'eb_post_row_align' => self::$eb_row_align,
            'eb_post_sidebar' => $show_sidebar,
            //
            'eb_blogs_per_line' => self::$arr_posts_per_line,
            'eb_blogs_medium_per_line' => $arr_num_medium_line,
            'eb_blogs_small_per_line' => $arr_num_small_line,
            'eb_blogs_column_spacing' => self::$eb_column_spacing,
            'eb_blogs_row_align' => self::$eb_row_align,
            'eb_blogs_sidebar' => $show_sidebar,
            //
            'eb_blog_per_line' => self::$arr_posts_per_line,
            'eb_blog_medium_per_line' => $arr_num_medium_line,
            'eb_blog_small_per_line' => $arr_num_small_line,
            'eb_blog_column_spacing' => self::$eb_column_spacing,
            'eb_blog_row_align' => self::$eb_row_align,
            'eb_blog_sidebar' => $show_sidebar,
            //
            'smtp_secure' => [
                '' => 'Không bảo mật',
                'ssl' => 'SSL (port 465)',
                'tls' => 'TLS (port 587)',
            ],
            'bank_bin_code' => [
                '' => '[ Chọn ngân hàng ]'
            ],
            //
            'MY_DB_DRIVER' => [
                '' => 'Mặc định theo code',
                'MySQLi' => 'MySQLi',
                'Postgre' => 'Postgre',
                'PDO' => 'PDO',
                'Oracle' => 'Oracle',
            ],
            'BASE_PROTOCOL' => [
                '' => 'Mặc định theo code',
                'https' => 'https',
                'http' => 'http',
            ],
            'MY_CACHE_HANDLER' => [
                '' => 'Mặc định theo code',
                'file' => 'file',
                'redis' => 'redis',
                'memcached' => 'memcached',
            ],
            'ALLOW_USING_MYSQL_DELETE' => [
                '' => 'Mặc định theo code',
                '0' => 'Tắt',
                '1' => 'Bật',
            ],
            'WGR_CSP_ENABLE' => [
                '' => 'Mặc định theo code',
                '0' => 'Tắt',
                '1' => 'Bật',
            ],
            'SITE_LANGUAGE_SUB_FOLDER' => [
                '' => 'Mặc định theo code',
                '0' => 'sub-domain',
                '1' => 'sub-folder',
            ],
            'SITE_LANGUAGE_DEFAULT' => $arr_default_display_lang,
        ];
        $arr['smtp2_secure'] = $arr['smtp_secure'];
        if (isset($arr[$key])) {
            return $arr[$key];
        }

        //
        return [];
    }

    public static function placeholder($key = '', $default_value = '')
    {
        $arr = [
            'copy_right_first' => 'Bản quyền &copy; ',
            'copy_right_last' => ' - Toàn bộ phiên bản.',
            'powered_by_eb' => ' Cung cấp bởi ' . PARTNER_BRAND_NAME,
        ];
        //echo $k . '<br>' . "\n";
        //echo $default_value . '<br>' . "\n";

        //
        if ($key != '') {
            if (isset($arr[$key])) {
                return $arr[$key];
            }
            return $default_value;
        }
        return $arr;
    }
}
