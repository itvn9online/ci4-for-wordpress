<?php

namespace App\Libraries;

use App\Language\admin\AdminTranslate;

class ConfigType
{
    // config type
    const CONFIG = 'config';
    const DISPLAY = 'display';
    const SOCIAL = 'social';
    const HOME = 'home';
    const CATEGORY = 'post_list';
    const POST = 'post';
    //const BLOGS = 'blog_list';
    //const BLOG = 'blog';
    const PROD_CATS = 'product_cat';
    const PROD = 'product';
    const TRANS = 'translate';
    const SMTP = 'smtp';
    const CONSTANTS = 'constants';
    const CHECKOUT = 'checkout';
    const CHECKBOX = 'checkbox';
    const NUM_MON = 'num_mon'; // number and money -> loại cấu hình dùng để định giá hoặc tạo số theo ý muốn
    const FIREBASE = 'firebase';
    const ZALO = 'zalo';
    //
    const DISABLE_CACHE = 'disable';

    private static $arr_posts_per_line = [
        '' => 'Theo thiết kế mặc định của tác giả',
        //'row-5' => '1',
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
        'align-custom-equal' => 'custom equal',
        'align-middle' => 'middle',
        'align-bottom' => 'bottom',
    ];

    private static $arr = array(
        self::CONFIG => 'Configuration',
        self::DISPLAY => 'Display settings',
        self::SOCIAL => 'Social network',
        self::HOME => 'Home settings',
        self::CATEGORY => AdminTranslate::POST . ' lists',
        self::POST => AdminTranslate::POST . ' details',
        //self::BLOGS => 'Blog/ Tin tức lists',
        //self::BLOG => 'Blog/ Tin tức details',
        self::PROD_CATS => AdminTranslate::PROD . ' lists',
        self::PROD => AdminTranslate::PROD . ' details',
        self::TRANS => 'Translation',
        self::SMTP => 'Mail/ Telegram settings',
        self::CONSTANTS => 'Constants',
        self::CHECKOUT => 'Paygate',
        self::CHECKBOX => 'On/ Off',
        self::NUM_MON => 'Number',
        self::FIREBASE => 'Firebase',
        self::ZALO => 'Zalo OA',
    );

    // các loại config chính sẽ được auto load để sử dụng khi cần thiết
    public static function mainType()
    {
        return [
            self::CONFIG,
            self::DISPLAY,
            self::SOCIAL,
            self::HOME,
            self::CATEGORY,
            self::POST,
            //self::BLOGS,
            //self::BLOG,
            self::PROD_CATS,
            self::PROD,
            self::CHECKOUT,
            // một số config sử dụng method riêng rồi thì bỏ ở đây đi
            //self::FIREBASE,
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
                'description' => 'Meta Description',
                'keyword' => 'Meta Keyword',
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
                'emailnotice' => 'Email nhận thông báo',
                'html_header' => 'HTML đầu trang',
                'html_body' => 'HTML chân trang',
                'replace_post_content' => 'Thay thế nội dung',
                'robots' => 'Robots.txt',
                'blog_private' => 'Ngăn chặn các công cụ tìm kiếm đánh chỉ mục website này',
                'enable_vue_js' => 'Sử dụng VueJS',
                'disable_fontawesome4' => 'Không nạp font Awesome 4',
                'include_flatsome' => 'Sử dụng thư viện JS của Flatsome',
                'hide_captcha' => 'Sử dụng hide-captcha',
                'enable_hotlink_protection' => 'HotLink protection',
                'enable_device_protection' => 'Device logged protection',
                'logout_device_protection' => 'Device logout protection',
                'block_device_protection' => 'Device block protection',
                'disable_register_member' => 'Disable register new account',
                'login_rememberme' => 'Remember me login in all page',
            ];
        } else if ($config_type == self::DISPLAY) {
            $arr = [
                'body_font_size' => 'Cỡ chữ mặc định',
                'bodym_font_size' => 'Cỡ chữ mobile mặc định',
                'default_bg' => 'Màu nền mặc định',
                'sub_bg' => 'Màu nền thứ cấp',
                'default_color' => 'Màu chữ mặc định',
                'a_color' => 'Màu liên kết',
                'site_max_width' => 'Chiều rộng trang',
                'site_full_width' => 'Chiều rộng tối đa',
                'main_banner_size' => 'Kích thước banner chính',
                'second_banner_size' => 'Kích thước banner phụ',
                'custom_css' => 'CSS tùy chỉnh (all)',
                'custom_desktop_css' => 'CSS tùy chỉnh (desktop only)',
                'custom_table_css' => 'CSS tùy chỉnh (table + mobile)',
                'custom_mobile_css' => 'CSS tùy chỉnh (mobile)',
            ];
        } else if ($config_type == self::SOCIAL) {
            $arr = [
                'google_analytics' => 'Google Analytics 4 ID',
                'google_adsense' => 'Google Adsense ID',
                'google_amp_adsense' => 'Google Adsense amp ID',
                'google_ads_txt_adsense' => 'Nội dung tệp ads.txt',
                'fb_app_id' => 'Facebook App ID',
                // Chức năng tạo review ảo cho phần dữ liệu có cấu trúc ở trang chủ
                'home_fake_review' => 'Home fake review',
                'home_rating_value' => 'Home rating value',
                'home_rating_count' => 'Home rating count',
                'home_review_count' => 'Home review count',
                'off_schema_person' => 'Off schema person',
                'home_url_fanpage' => 'URL fanpage',
                'facebook' => 'Facebook',
                'google' => 'Google+',
                'linkin' => 'Linkin',
                'skype' => 'Skype',
                'youtube' => 'Youtube',
                'tiktok' => 'TikTok',
                'zalo' => 'Số Zalo',
                'zalo_me' => 'Link Zalo',
                'image' => 'Ảnh share Facebook',
                'tawk_to' => 'Tawk to ID',
                'registeronline' => 'Link đăng ký BCT',
                'notificationbct' => 'Link thông báo BCT',
            ];
        } else if ($config_type == self::HOME) {
            $arr = [
                'html_home_header' => 'HTML đầu trang',
                'html_home_body' => 'HTML chân trang',
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
                'cf_posts_size' => 'Tỉ lệ ảnh ' . AdminTranslate::POST,
                'cf_thumbnail_size' => 'Chất lượng hình ảnh',
                'show_child_category' => 'Hiển thị nhóm ' . AdminTranslate::POST . ' con',
                'max_child_category' => 'Số lượng ' . AdminTranslate::POST . ' con',
                //
                'html_posts_header' => 'HTML đầu trang',
                'html_posts_body' => 'HTML chân trang',
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
                //
                'html_post_header' => 'HTML đầu trang',
                'html_post_body' => 'HTML chân trang',
                'post_toc' => 'Sử dụng Table of content (TOC)',
                'redirect_post_404' => '404 redirect',
            ];
            /*
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
            */
        } else if ($config_type == self::PROD_CATS) {
            $arr = [
                'eb_products_per_page' => 'Số ' . AdminTranslate::PROD . ' trên mỗi trang',
                'eb_products_per_line' => 'Số cột trên mỗi dòng',
                'eb_products_medium_per_line' => 'Số cột trên mỗi dòng (table)',
                'eb_products_small_per_line' => 'Số cột trên mỗi dòng (mobile)',
                'eb_products_column_spacing' => 'Khoảng cách giữa các cột',
                'eb_products_row_align' => 'Căn chỉnh (align)',
                'eb_products_sidebar' => 'Sidebar',
                'cf_product_description_length' => 'Độ dài tóm tắt ' . AdminTranslate::PROD,
                'cf_products_size' => 'Tỉ lệ ảnh ' . AdminTranslate::PROD,
                'show_child_products' => 'Hiển thị nhóm ' . AdminTranslate::PROD . ' con',
                'max_child_products' => 'Số lượng ' . AdminTranslate::PROD . ' con',
                //
                'html_products_header' => 'HTML đầu trang',
                'html_products_body' => 'HTML chân trang',
                // định dạng tiền tệ
                'currency_format' => 'Đơn vị tiền tệ',
                'currency_sd_format' => 'Cấu trúc tiền tệ',
                'currency_after_format' => 'Tiền tệ ở phía sau',
                'currency_locales_format' => 'Locales format',
                'currency_big_format' => 'Chuyển đổi đơn vị tiền tệ',
                'currency_fraction_digits' => 'Minimum fraction digits',
            ];
        } else if ($config_type == self::PROD) {
            $arr = [
                'eb_product_per_page' => 'Số Product cùng nhóm',
                'eb_product_per_line' => 'Số cột trên mỗi dòng',
                'eb_product_medium_per_line' => 'Số cột trên mỗi dòng (table)',
                'eb_product_small_per_line' => 'Số cột trên mỗi dòng (mobile)',
                'eb_product_column_spacing' => 'Khoảng cách giữa các cột',
                'eb_product_row_align' => 'Căn chỉnh (align)',
                'eb_product_sidebar' => 'Sidebar',
                //
                'html_product_header' => 'HTML đầu trang',
                'html_product_body' => 'HTML chân trang',
                'redirect_product_404' => '404 redirect',
            ];
        } else if ($config_type == self::NUM_MON) {
            $arr = [
                'custom_num_mon0' => 'Custom number 0'
            ];
        } else if ($config_type == self::FIREBASE) {
            $arr = [
                'g_recaptcha_site_key' => 'G recaptcha site key',
                'g_recaptcha_secret_key' => 'G recaptcha secret key',
                'g_firebase_title' => 'Firebase',
                'g_firebase_config' => 'SDK setup and configuration',
                'firebase_json_config' => 'Firebase config',
                'disable_local_login' => 'Tắt chức năng đăng nhập mặc định',
                'skipverify_firebase_email' => 'Bỏ qua việc xác minh lại email',
                'save_firebase_session' => 'Giữ phiên Firebase sau mỗi lần kết nối',
                'g_firebase_privacy_policy_url' => 'Privacy policy URL',
                'g_firebase_terms_service_url' => 'Terms of service URL',
                'firebase_sign_in_redirect_to' => 'Sign-in success URL',
                'g_firebase_default_country' => 'Default country',
                'g_firebase_login_hint' => 'Login hint',
                'g_firebase_language_code' => 'Language code',
                'firebase_auth_google' => 'Google Auth Provider',
                'firebase_auth_facebook' => 'Facebook Auth Provider',
                'firebase_auth_apple' => 'Apple Auth Provider',
                'firebase_auth_microsoft' => 'Microsoft Auth Provider',
                'firebase_auth_yahoo' => 'Yahoo Auth Provider',
                'firebase_auth_twitter' => 'Twitter Auth Provider',
                'firebase_auth_github' => 'Github Auth Provider',
                'firebase_auth_email' => 'Email Auth Provider',
                'firebase_auth_anonymous' => 'Anonymous Auth Provider',
                'firebase_auth_phone' => 'Phone Auth Provider',
                'firebase_verify_phone' => 'Phone Verify Provider',
            ];
        } else if ($config_type == self::ZALO) {
            $arr = [
                'zalooa_app_id' => 'App ID',
                'zalooa_app_secret' => 'App secret',
                'zalooa_access_token' => 'Access token',
                'zalooa_refresh_token' => 'Refresh token',
                'zalooa_expires_token' => 'Thời hạn của access token',
                'zalooa_webhook' => 'Webhook',
                'zalooa_template_otp_id' => 'ID mẫu OTP ZNS',
                'zns_phone_for_test' => 'Số điện thoại test ZNS',
                'zalooa_user_id_test' => 'User ID Zalo OA test gửi tin',
                'zalooa_template_login_id' => 'ID ZNS thông báo đăng nhập',
                'zalooa_template_register_id' => 'ID ZNS T.Báo Đ.Ký thành công',
                'zalooa_template_booking_id' => 'ID ZNS thông báo đặt hàng',
                'zalooa_template_other1_id' => 'ID ZNS thông báo khác (1)',
                'zalooa_template_other2_id' => 'ID ZNS thông báo khác (2)',
                'zalooa_template_other3_id' => 'ID ZNS thông báo khác (3)',
                'zalooa_template_other4_id' => 'ID ZNS thông báo khác (4)',
                'zalooa_template_other5_id' => 'ID ZNS thông báo khác (5)',
            ];
        } else if ($config_type == self::CHECKBOX) {
            $arr = [];
            for ($i = 0; $i < NUMBER_CHECKBOXS_INPUT; $i++) {
                $arr['custom_checkbox' . $i] = 'Custom checkbox ' . $i;
            }
        } else if ($config_type == self::TRANS) {
            $arr_tmp = [];
            // $arr_tmp['main_slider_slug'] = 'Slug slider chính';
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
                'smtp_from' => 'From email',
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
                // 
                'mail_queue_begin_block' => 'Mail đặt hàng thành công',
                'mail_queue_customer' => 'Mail to customer',
                'mail_queue_admin' => 'Mail to admin',
                'mail_queue_author' => 'Mail to author',
                'mail_queue_sending_type' => 'Email sending time',
                // 
                'telegram_begin_block' => 'Cài đặt Telegram',
                'telegram_bot_token' => 'Bot token',
                'telegram_chat_id' => 'Chat ID',
            ];
        } else if ($config_type == self::CONSTANTS) {
            $arr = [
                'MY_APP_TIMEZONE' => 'App timezone',
                'FTP_HOST' => 'FTP host',
                'FTP_USER' => 'FTP user',
                'FTP_PASS' => 'FTP pass',
                'PARTNER_WEBSITE' => 'Website đối tác',
                'PARTNER_BRAND_NAME' => 'Tên đối tác',
                'PARTNER2_WEBSITE' => 'Website đối tác 2',
                'PARTNER2_BRAND_NAME' => 'Tên đối tác 2',
                'MY_DB_DRIVER' => 'DB Driver',
                'BASE_PROTOCOL' => 'Giao thức cơ sở',
                'HTTP_SYNC_HOST' => 'Host prefix',
                'CUSTOM_MD5_HASH_CODE' => 'MD5 hash code',
                'WGR_REDIS_HOSTNAME' => 'Redis hostname',
                'WGR_REDIS_PORT' => 'Redis port',
                'WGR_MEMCACHED_HOSTNAME' => 'Memcached hostname',
                'WGR_MEMCACHED_PORT' => 'Memcached port',
                'MY_CACHE_HANDLER' => 'Cache handler',
                'MY_SESSION_DRIVE' => 'Session driver',
                'CUSTOM_SESSION_PATH' => 'Session save path',
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
                'ANTI_SPAM_EXPIRED' => 'Anti spam expired',
                'CUSTOM_FAKE_POST_VIEW' => 'Fake post view',
                'ENABLE_AMP_VERSION' => 'Phiên bản AMP',
                'FAKE_WORDPRESS_VERSION' => 'Fake wordpress version',
                'SITE_LANGUAGE_SUPPORT' => 'Ngôn ngữ hỗ trợ',
                'SITE_LANGUAGE_DEFAULT' => 'Ngôn ngữ mặc định',
                'SITE_LANGUAGE_SUB_FOLDER' => 'Kiểu hiển thị đa ngôn ngữ',
                'EBE_DATE_FORMAT' => 'Date Format',
                'EBE_DATE_TEXT_FORMAT' => 'Date text Format',
                'WGR_TABLE_PREFIX' => 'Database table prefix',
                'HTACCESSS_ALLOW' => 'Htaccess allow',
                //
                //'WGR_CATEGORY_PREFIX' => 'Tiền tố cho danh mục sản phẩm',
                //'WGR_PAGES_PREFIX' => 'Tiền tố cho trang tĩnh',
                //
                'WGR_CATEGORY_PERMALINK' => 'Category permalink',
                'WGR_TAGS_PERMALINK' => 'Tag permalink (Tag base)',
                //'WGR_BLOGS_PERMALINK' => 'Blogs permalink',
                'WGR_PRODS_PERMALINK' => 'Product category permalink',
                'WGR_PROD_TAGS_PERMALINK' => 'Product tag permalink',
                'WGR_TAXONOMY_PERMALINK' => 'Other taxonomy permalink',
                //
                'WGR_POST_PERMALINK' => 'Post permalink',
                //'WGR_BLOG_PERMALINK' => 'Blog permalink',
                'WGR_PROD_PERMALINK' => 'Product permalink',
                'WGR_PAGE_PERMALINK' => 'Page permalink',
                'WGR_POSTS_PERMALINK' => 'Other post permalink',
                'DEFAULT_SELECT_POST_COL' => 'Default select post column',
                'THIS_IS_E_COMMERCE_SITE' => 'This is e-commerce website',
            ];
        } else if ($config_type == self::CHECKOUT) {
            $arr = [
                // Số tiền mặc định -> dùng cho các website dịch vụ đồng giá
                'min_product_price' => 'Giá trị đơn hàng tối thiểu',
                'shippings_fee' => 'Shippings fee',
                'deposits_money' => 'Deposits money',
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
                'autobank_token' => 'Casso webhook token',
                'paypal_client_id' => 'Paypal Client ID',
                'paypal_sdk_js' => 'Paypal JavaScript SDK',
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
            'eb_products_per_page' => 'number',
            'eb_products_per_line' => 'select',
            'eb_products_medium_per_line' => 'select',
            'eb_products_small_per_line' => 'select',
            'eb_products_column_spacing' => 'select',
            'eb_products_row_align' => 'select',
            'eb_products_sidebar' => 'select',
            //
            'eb_product_per_page' => 'number',
            'eb_product_per_line' => 'select',
            'eb_product_medium_per_line' => 'select',
            'eb_product_small_per_line' => 'select',
            'eb_product_column_spacing' => 'select',
            'eb_product_row_align' => 'select',
            'eb_product_sidebar' => 'select',
            //
            'enable_vue_js' => 'checkbox',
            'disable_fontawesome4' => 'checkbox',
            'include_flatsome' => 'checkbox',
            'hide_captcha' => 'checkbox',
            'enable_hotlink_protection' => 'checkbox',
            'enable_device_protection' => 'checkbox',
            'logout_device_protection' => 'checkbox',
            'block_device_protection' => 'checkbox',
            'disable_register_member' => 'checkbox',
            'login_rememberme' => 'checkbox',
            'blog_private' => 'checkbox',
            'smtp_no_reply' => 'checkbox',
            'show_child_category' => 'checkbox',
            'max_child_category' => 'number',
            'show_child_products' => 'checkbox',
            'max_child_products' => 'number',
            'currency_format' => 'select',
            // 'currency_sd_format' => '',
            'currency_after_format' => 'checkbox',
            // 'currency_locales_format' => '',
            'currency_big_format' => 'checkbox',
            'currency_fraction_digits' => 'number',
            // 'shippings_fee' => 'number',
            'logo_main_height' => 'number',
            'logo_width_img' => 'number',
            'logo_height_img' => 'number',
            'logo_footer_height' => 'number',
            'logo_mobile_height' => 'number',
            'address' => 'textarea',
            'address2' => 'textarea',
            'emailcontact' => 'email',
            'emailnotice' => 'email',
            //
            'html_header' => 'textarea',
            'html_body' => 'textarea',
            'replace_post_content' => 'textarea',
            'robots' => 'textarea',
            //
            'custom_css' => 'textarea',
            'custom_desktop_css' => 'textarea',
            'custom_table_css' => 'textarea',
            'custom_mobile_css' => 'textarea',
            'site_max_width' => 'number',
            'site_full_width' => 'number',
            'mail_queue_begin_block' => 'heading',
            'telegram_begin_block' => 'heading',
            'smtp_heading_test_email' => 'heading',
            'smtp2_heading_host_user' => 'heading',
            'zalo' => 'number',
            'zalo_me' => 'hidden',
            'google_ads_txt_adsense' => 'textarea',
            'fb_app_id' => 'number',
            'home_fake_review' => 'heading',
            //'home_rating_value' => 'number',
            'home_rating_count' => 'number',
            'home_review_count' => 'number',
            'off_schema_person' => 'checkbox',
            'home_url_fanpage' => 'heading',
            //
            'html_home_header' => 'textarea',
            'html_home_body' => 'textarea',
            //
            'html_products_header' => 'textarea',
            'html_products_body' => 'textarea',
            //
            'html_product_header' => 'textarea',
            'html_product_body' => 'textarea',
            //
            'html_posts_header' => 'textarea',
            'html_posts_body' => 'textarea',
            //
            'html_post_header' => 'textarea',
            'html_post_body' => 'textarea',
            'post_toc' => 'checkbox',
            //
            'MY_DB_DRIVER' => 'select',
            'BASE_PROTOCOL' => 'select',
            'WGR_REDIS_PORT' => 'number',
            'WGR_MEMCACHED_PORT' => 'number',
            'MY_CACHE_HANDLER' => 'select',
            'MY_SESSION_DRIVE' => 'select',
            'MY_APP_TIMEZONE' => 'select',
            'ALLOW_USING_MYSQL_DELETE' => 'select',
            'WGR_CSP_ENABLE' => 'select',
            'NUMBER_CHECKBOXS_INPUT' => 'number',
            'ANTI_SPAM_EXPIRED' => 'number',
            'CUSTOM_FAKE_POST_VIEW' => 'number',
            'ENABLE_AMP_VERSION' => 'select',
            // 'FAKE_WORDPRESS_VERSION' => '',
            'SITE_LANGUAGE_SUB_FOLDER' => 'select',
            // 'SITE_LANGUAGE_SUPPORT' => 'select',
            'SITE_LANGUAGE_DEFAULT' => 'select',
            'EBE_DATE_FORMAT' => 'select',
            'EBE_DATE_TEXT_FORMAT' => 'select',
            'THIS_IS_E_COMMERCE_SITE' => 'select',
            'g_firebase_title' => 'heading',
            'g_firebase_config' => 'textarea',
            'firebase_json_config' => 'textarea',
            'disable_local_login' => 'checkbox',
            'skipverify_firebase_email' => 'checkbox',
            'save_firebase_session' => 'checkbox',
            'firebase_auth_google' => 'checkbox',
            'firebase_auth_facebook' => 'checkbox',
            'firebase_auth_apple' => 'checkbox',
            'firebase_auth_microsoft' => 'checkbox',
            'firebase_auth_yahoo' => 'checkbox',
            'firebase_auth_twitter' => 'checkbox',
            'firebase_auth_github' => 'checkbox',
            'firebase_auth_email' => 'checkbox',
            'firebase_auth_anonymous' => 'checkbox',
            'firebase_auth_phone' => 'checkbox',
            'firebase_verify_phone' => 'checkbox',
            //
            'zalooa_access_token' => 'textarea',
            'zalooa_refresh_token' => 'textarea',
            'zalooa_expires_token' => 'number',
            // 
            'paypal_sdk_js' => 'textarea',
            // 
            'mail_queue_customer' => 'select',
            'mail_queue_admin' => 'select',
            'mail_queue_author' => 'select',
            'mail_queue_sending_type' => 'select',
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
        $firebase_note_phone = ' * Chức năng này dùng nhiều sẽ mất phí nếu đăng ký gói trả phí. Vui lòng xem bảng giá trước khi kích hoạt nó. <br> Bảng giá chung: https://firebase.google.com/pricing?hl=en&authuser=0 <br> Bảng giá chi tiết: https://cloud.google.com/identity-platform/pricing?authuser=0#pricing_table ';

        //
        $arr = [
            'body_font_size' => 'Cỡ chữ được thiết lập cho body (mặc định 14px).',
            'bodym_font_size' => 'Cỡ chữ được thiết lập cho body bản mobile (mặc định 13px).',
            'default_bg' => 'Là màu nền đặc trưng cho các menu, nút bấm trên toàn bộ website. Sử dụng: <strong>.default-bg</strong>',
            'sub_bg' => 'Màu nền cho các module khác, tạo sự khác biệt với màu nền chính ở trên. Sử dụng: <strong>.default2-bg, .sub-bg</strong>',
            'default_color' => 'Màu mặc định cho mọi font chữ trên website nếu module đó không được thiết lập màu riêng.',

            'enable_vue_js' => 'Khi chế độ này được kích hoạt, thư viện VueJS sẽ được nhúng vào frontend để sử dụng',
            'disable_fontawesome4' => 'Mặc định code sẽ nạp font Awesome 4, khi muốn sử dụng phiên bản khác, hãy kích hoạt chức năng này để bỏ nạp font Awesome 4 rồi sau đó nạp thủ công font mới trong file /custom/Views/get_header.php (tạo mới nếu chưa có file này)',
            'include_flatsome' => 'Nạp file thư viện Javascript viết bởi Flatsome. Một theme trên nền tảng Wordpress khá nổi tiếng.',
            'hide_captcha' => 'Là dạng mã xác thực ngầm được nào vào footer, dùng cho các chức năng cần xác thực tự động chống bot spam.',
            'show_child_category' => 'Khi chế độ này được kích hoạt, và khi truy cập vào danh mục ' . AdminTranslate::POST . ', nếu trong danh mục đó có các nhóm con thì các nhóm con sẽ được hiển thị thay vì hiển thị trực tiếp danh sách ' . AdminTranslate::POST,
            'post_toc' => 'Khi kích hoạt chế độ này, 1 menu trong chi tiết bài viết sẽ được kích hoạt dựa theo các thẻ heading.',
            'redirect_post_404' => 'Mặc định khi không tìm thấy bài viết, sẽ hiển thị trang 404. Nếu muốn chuyển hướng tới 1 URL nào đó, hãy thiết lập tại đây.',
            'redirect_product_404' => 'Mặc định khi không tìm thấy bài viết, sẽ hiển thị trang 404. Nếu muốn chuyển hướng tới 1 URL nào đó, hãy thiết lập tại đây.',
            'max_child_category' => 'Một vòng lặp sẽ lấy số lượng ' . AdminTranslate::POST . ' để hiển thị trong mỗi nhóm con',
            'show_child_products' => 'Khi chế độ này được kích hoạt, và khi truy cập vào danh mục ' . AdminTranslate::PROD . ', nếu trong danh mục đó có các nhóm con thì các nhóm con sẽ được hiển thị thay vì hiển thị trực tiếp danh sách ' . AdminTranslate::PROD,
            'max_child_products' => 'Một vòng lặp sẽ lấy số lượng ' . AdminTranslate::PROD . ' để hiển thị trong mỗi nhóm con',
            'eb_post_per_page' => 'Khi số này lớn hơn 0, trong trang chi tiết ' . AdminTranslate::POST . ' sẽ lấy các bài cùng nhóm với bài hiện tại để giới thiệu',
            'currency_format' => 'Sử dụng bảng mã riêng cho CSS, tham khảo tại đây: https://www.w3schools.com/cssref/css_entities.asp',
            'currency_locales_format' => 'Nhập mã để thiết lập format cho đơn vị tiền tệ ngoài những thiết lập mặc định tại đây. <br> Danh sách mã được hỗ trợ: https://gist.github.com/itvn9online/23962377410d288b696938d8808cb0a2',
            'currency_sd_format' => 'Đây là đơn vị tiền tệ cho phần structured data của google hoặc các công cụ tìm kiếm khác. Hiển thị CSS có thể là nhiều kiểu, còn cho phần SEO thì chỉ một kiểu. Ví dụ: VND, USD... Mặc định: USD',
            'currency_after_format' => 'Mặc định, đơn vị tiền tệ sẽ xuất hiện ở phía trước giá tiền, nếu bạn muốn nó xuất hiện ở phía sau, hãy đánh dấu vào đây và lưu lại.',
            'currency_big_format' => 'Khi bật chế độ này, các số tiền lớn hơn 1 triệu sẽ được chuyển đổi đơn vị sang triệu (hoặc tỷ). Ví dụ: 1,000,000đ sẽ đổi thành 1 triệu, 1,624,000,000đ sẽ đổi thành 1 tỷ 624 triệu.',
            'currency_fraction_digits' => 'Phần số thập phân sau mỗi đơn vị tiền tệ. Mặc định là 2 số. Ví dụ: 1,000.00 hoặc 1,000.50 (thiết lập là 0 để ẩn phần thập phân).',
            'shippings_fee' => 'Mặc định sẽ không hiển thị phí vận chuyển trong giỏ hàng. Thiết lập là 0 sẽ hiển thị Miễn phí vận chuyển, lớn hơn 0 sẽ hiển thị phí vận chuyển cố định. Sử dụng [qty] cho số lượng sản phẩm, ví dụ: 10.00 * [qty]',
            'deposits_money' => 'Khi thiết lập giá trị này, khách hàng bắt buộc phải đặt cọc theo số tiền đã được chỉ định. Có thể nhập %, ví dụ nhập: 25% thì khách hàng sẽ phải đặt cọc trước 25% cho mỗi đơn hàng.',
            //
            'eb_product_per_page' => 'Khi số này lớn hơn 0, trong trang chi tiết ' . AdminTranslate::PROD . ' sẽ lấy các bài cùng nhóm với bài hiện tại để giới thiệu',
            // 'main_slider_slug' => 'Nhập slug của slider chính vào đây, khi hàm the_slider không tìm được slider tương ứng thì nó sẽ lấy slider này để gán vào',
            'image' => 'Khi share ' . AdminTranslate::POST . ' lên mạng xã hội như Facebook, Zalo... ảnh này sẽ được hiển thị nếu link share không có ảnh đính kèm.',
            'tawk_to' => 'Chỉ nhập ID của widget chat https://tawk.to vào đây, phần mã còn lại hệ thống sẽ tự build và update khi cần thiết.',
            'registeronline' => 'Link đăng ký với bộ công thương. Trong file view, sử dụng hàm <strong>$option_model->the_bct( $getconfig );</strong> để in ra logo BCT màu đỏ.',
            'notificationbct' => 'Link thông báo với bộ công thương. Trong file view, sử dụng hàm <strong>$option_model->the_bct( $getconfig );</strong> để in ra logo BCT màu xanh.',
            'g_recaptcha_site_key' => 'Truy cập vào đây https://www.google.com/recaptcha/about/ -> tới bảng điều khiển site -> tìm tab Settings -> reCAPTCHA keys -> copy Site key và Secret key dán vào đây và lưu lại.',
            'g_recaptcha_secret_key' => 'Khi có đầy đủ 2 thông số để kích hoạt reCaptcha thì một số chỗ sử dụng captcha sẽ được dùng reCaptcha làm phương thức xác thực mặc định.',
            'g_firebase_config' => 'Trong https://console.firebase.google.com/u/0/ -> chọn Project cần kết nối -> Project settings -> General -> Your apps -> SDK setup and configuration -> Config -> Copy code ở Config và dán vào đây. Nếu website đã thiết lập chức năng đăng nhập qua firebase, khuyến nghị bạn tắt chức năng Đăng ký mặc định tại đây: ' . base_url('sadmin/configs') . '?support_tab=data_disable_register_member',
            'firebase_json_config' => 'Config của Firebase sau khi đã được biên dịch lại để các đoạn code trong PHP sử dụng.',
            'g_firebase_privacy_policy_url' => 'URL chính sách bảo mật khi sử dụng chức năng đăng nhập qua firebase',
            'g_firebase_terms_service_url' => 'URL điều khoản dịch vụ khi sử dụng chức năng đăng nhập qua firebase',
            'g_firebase_default_country' => 'Mã quốc gia sẽ select mặc định khi xác  thực số điện  thoại qua firebase. Danh sách các mã xem tại đây: https://github.com/firebase/firebaseui-web/blob/master/javascript/data/README.md',
            'firebase_sign_in_redirect_to' => 'URL sau khi đăng nhập thành công sẽ chuyển tới nếu không có tham số login_redirect. Mặc định sẽ chuyển về trang chủ.',
            'g_firebase_login_hint' => 'Mã quốc gia sẽ select mặc định khi xác  thực số điện  thoại qua firebase. Ví dụ: +84',
            'g_firebase_language_code' => 'Ngôn ngữ hiển thị Firebase. Mã ngôn ngữ xem tại đây: https://github.com/firebase/firebaseui-web/blob/master/LANGUAGES.md',
            'disable_local_login' => 'Khi chức năng đăng nhập qua Firebase được kích hoạt, bạn có thể tắt chức năng đăng nhập mặc định của website bằng cách kích hoạt option này.',
            'skipverify_firebase_email' => 'Khi thông tin từ firebase trả về không khớp với dữ liệu trên web, một email yêu cầu xác minh tài khoản sẽ được gửi đi. Bật tính năng này sẽ bỏ qua việc xác minh lại tài khoản.',
            'save_firebase_session' => 'Mặc định khi đăng nhập qua Firebase hoàn tất, kết nối của người dùng được xóa bỏ nhằm đảm bảo an toàn. Nếu muốn giữ lại phiên đăng nhập để tái sử dụng lần tới, hãy kích hoạt chức năng này.',
            'firebase_auth_google' => 'Đăng nhập bằng tài khoản Google',
            'firebase_auth_facebook' => 'Đăng nhập bằng tài khoản Facebook. Tạo app sau đó lấy App ID và App secret tại đây: https://developers.facebook.com/apps/ <br> * Nhớ truy cập vào Đăng nhập bằng Facebook -> Cài đặt -> URI chuyển hướng OAuth hợp lệ để tiến hành cài đặt URL chuyển hướng cho firebase: https://developers.facebook.com/apps/YOUR_FACEBOOK_APP_ID/fb-login/settings/ <br> * URL này có dạng: https://YOUR_FIREBASE_PROJECT_ID.firebaseapp.com/__/auth/handler',
            'firebase_auth_apple' => 'Đăng nhập bằng tài khoản Apple. Xem hướng dẫn tại đây: https://firebase.google.com/docs/auth/web/apple?authuser=0&hl=en',
            'firebase_auth_microsoft' => 'Đăng nhập bằng tài khoản Microsoft',
            'firebase_auth_yahoo' => 'Đăng nhập bằng tài khoản Yahoo',
            'firebase_auth_twitter' => 'Đăng nhập bằng tài khoản Twitter',
            'firebase_auth_github' => 'Đăng nhập bằng tài khoản Github. Tạo app sau đó lấy Client ID và Client secret tại đây: https://github.com/settings/developers',
            'firebase_auth_email' => 'Đăng nhập bằng email',
            'firebase_auth_anonymous' => 'Sử dụng với vai trò khách',
            'firebase_auth_phone' => 'Đăng nhập bằng số điện thoại ' . $firebase_note_phone,
            'firebase_verify_phone' => 'Chức năng xác thực số điện thoại qua Firebase tại đây: ' . base_url('firebases/phone_auth') . $firebase_note_phone,
            //
            'zalooa_app_id' => 'ID của ứng dụng trên Zalo, tạo và lấy tại đây: https://developers.zalo.me/apps <br> * Xem tài liệu code tại đây: https://github.com/zaloplatform/zalo-php-sdk',
            'zalooa_app_secret' => 'Secret của ứng dụng trên Zalo (thường dùng cho chức năng đăng nhập qua Zalo). <br> * Trong phần cài đặt của chức năng Đăng nhập Zalo https://developers.zalo.me/app/YOUR_ZALO_APP_ID/login thiết lập như sau: <br> - Home URL là: ' . base_url() . ' <br> - Callback URL là: ' . base_url('zalos/oa_connect') . ' <br> * Sau khi cập nhật đầy đủ thì có thể test code tại đây: ' . base_url('zalos/login_url') . ' <br> * Quản lý các app Zalo đã kết nối tại đây: https://zalo.me/profile/app-management',
            'zalooa_access_token' => 'Access token dùng để gửi tin nhắn qua ZNS, lấy Access token bằng công cụ API explorer tại đây: https://developers.zalo.me/tools/explorer/YOUR_ZALO_APP_ID <br> * Loại Access token: OA Access Token <br> - Trong Official Account -> Thiết lập chung -> https://developers.zalo.me/app/YOUR_ZALO_APP_ID/oa/settings <br> - Thiết lập Official Account Callback Url là: ' . base_url('zalos/get_access_token') . ' <br> * URL update Access Token tự động: ' . base_url('sadmin/zalooas/before_zns'),
            'zalooa_refresh_token' => 'Mỗi access token được tạo sẽ có một refresh token đi kèm. Refresh token cho phép bạn tạo lại access token mới khi access token hiện tại hết hiệu lực. <br> * Refresh token chỉ có thể sử dụng 1 lần. <br> * Xem hướng dẫn tạo access token mới từ refresh token tại đây: https://developers.zalo.me/docs/api/official-account-api/xac-thuc-va-uy-quyen/cach-1-xac-thuc-voi-giao-thuc-oauth/lay-access-token-tu-refresh-token-post-4970 . <br> * Hiệu lực: 3 tháng <br> * Nếu có Refresh token, có thể cập nhật lại Access token luôn tại đây: ' . base_url('sadmin/zalooas/refresh_access_token'),
            'zalooa_expires_token' => 'Khi access token hết hạn thì mà có refresh token thì 1 access token mới sẽ được cập nhật tự động <br> * Quản lý tài khoản ZNS: https://account.zalo.cloud/',
            'zalooa_webhook' => 'URL nhận các sự kiện ghi lại tương tác của người dùng với OA: có người dùng gửi tin nhắn tới Offical Account, các thay đổi liên quan tới ' . AdminTranslate::POST . ' (Article) hoặc cửa hàng (Zalo Shop) <br> * Cài đặt Webhook cho Zalo OA: https://developers.zalo.me/app/YOUR_ZALO_APP_ID/webhook <br> - ' . base_url('zalos/webhook') . ' <br> * Giới thiệu về Webhook: https://developers.zalo.me/docs/api/official-account-api/webhook/gioi-thieu-ve-webhook-post-4219',
            'zalooa_template_otp_id' => 'ID mẫu tin nhắn gửi qua ZNS, xem hướng dẫn tạo mẫu template tại đây: https://zalo.cloud/blog/huong-dan-tao-ung-dung-app-id-va-lien-ket-voi-zalo-oa-/kgua7vnkkvbyy88rma ',
            'zns_phone_for_test' => 'Số điện thoại dùng để test chức năng gửi tin nhắn qua Zalo ZNS. <br> * Gửi thử tin nhắn OTP qua ZNS tại đây: ' . base_url('sadmin/zalooas/send_test_otp_zns'),
            'zalooa_user_id_test' => 'User ID Zalo OD dùng để test chức năng gửi tin nhắn qua Zalo OA. Nếu để trống, hệ thống sẽ gửi tin nhắn test cho tài khoản của bạn. <br> * Gửi thử tin nhắn thông báo qua Zalo OA tại đây: ' . base_url('sadmin/zalooas/send_test_msg_oa'),
            'zalooa_template_login_id' => 'ID mẫu ZNS thông báo có đăng nhập vào website, thường dùng cho các website yêu cầu bảo mật cao',
            'zalooa_template_register_id' => 'ID mẫu ZNS thông báo khi người dùng đăng ký tài khoản thành công',
            'zalooa_template_booking_id' => 'ID mẫu ZNS gửi thông báo đặt hàng thành công',
            'zalooa_template_other1_id' => 'ID mẫu ZNS dùng để gửi thông báo khác (mẫu dự phòng 1)',
            'zalooa_template_other2_id' => 'ID mẫu ZNS dùng để gửi thông báo khác (mẫu dự phòng 2)',
            'zalooa_template_other3_id' => 'ID mẫu ZNS dùng để gửi thông báo khác (mẫu dự phòng 3)',
            'zalooa_template_other4_id' => 'ID mẫu ZNS dùng để gửi thông báo khác (mẫu dự phòng 4)',
            'zalooa_template_other5_id' => 'ID mẫu ZNS dùng để gửi thông báo khác (mẫu dự phòng 5)',
            //
            'site_max_width' => 'Bạn có thể thiết lập chiều rộng cho trang tại đây. Chiều rộng tiêu chuẩn: 1024px - Chiều rộng phổ biến: 1366px . Sử dụng: <strong>.row, .w90, .w99</strong>',
            'site_full_width' => 'Tương tự chiều rộng trang nhưng có độ rộng nhỉnh hơn chút. Chiều rộng tiêu chuẩn: 1024px - Chiều rộng phổ biến: 1666px . Sử dụng: <strong>.row-big, .w96</strong>',
            'main_banner_size' => 'Đây là kích thước dùng chung cho các banner chính, sử dụng bằng cách nhập <strong>%main_banner_size%</strong> vào mục <strong>Tùy chỉnh size ảnh</strong> trong cấu hình banner.',
            'second_banner_size' => 'Tương tự <strong>main_banner_size</strong>, đây là kích thước dùng chung cho các banner khác (nếu có), sử dụng bằng cách nhập <strong>%second_banner_size%</strong> vào mục <strong>Tùy chỉnh size ảnh</strong> trong cấu hình banner.',
            // 'custom_css' => '',
            'smtp_host_name' => 'IP hoặc host name của server mail. Gmail SMTP: <strong>smtp.gmail.com</strong>, Pepipost SMTP: <strong>smtp.pepipost.com</strong> <br> Vào đây https://myaccount.google.com/security để bật xác minh 2 bước cho tài khoản Gmail. <br> Vào đây https://myaccount.google.com/apppasswords để tạo mật khẩu ứng dụng Gmail. <br> Vào đây để sử dụng dịch vụ AWS của amazon https://us-east-2.console.aws.amazon.com/ses/home?region=us-east-2',
            'smtp_host_port' => 'Port nếu có. Gmail SSL port: <strong>465</strong>, Gmail TLS port: <strong>587</strong>, AmazonAWS TLS port <strong>587</strong>, Pepipost port <strong>2525</strong>.',
            'smtp_host_user' => 'Email hoặc tài khoản đăng nhập. Khuyên dùng Gmail.',
            'smtp_host_show_pass' => 'Mật khẩu ứng dụng Gmail hoặc mật khẩu đăng nhập email thông thường. Nên dùng gmail và mật khẩu ứng dụng để đảm bảo bảo mật.',
            'smtp_from' => 'Email người gửi. Để trống để sử dụng email đăng nhập luôn, hạn chế email gửi vào spam. Trường hợp tùy chỉnh thì email này phải có đuôi là @' . $_SERVER['HTTP_HOST'],
            'smtp_from_name' => 'Tên người gửi. Bạn có thể tùy biến tên người gửi tại đây. Ví dụ: Công ty ABC, Nguyên Văn A...',
            'smtp_no_reply' => 'Khi kích hoạt chế độ này, email reply sẽ được đặt là <strong>noreply@' . $_SERVER['HTTP_HOST'] . '</strong> để các hệ thống email xác nhận đây là mail không nhận phản hồi.',
            'smtp_test_email' => 'Thiết lập xong cấu hình, bạn có thể nhập thêm email người nhận và bấm vào đây để test email gửi đi: ' . base_url('sadmin/smtps') . '?test_mail=1',
            'smtp_test_bcc_email' => 'Thêm email để test chức năng BCC.',
            'smtp_test_cc_email' => 'Thêm email để test chức năng CC.',
            'smtp2_host_user' => 'Cấu hình mail dự phòng, khi mail chính có vấn đề thì mail này sẽ được kích hoạt để dùng tạm',
            'enable_hotlink_protection' => 'Chặn các website khác truy cập trực tiếp vào file ảnh trên host này.',
            'enable_device_protection' => 'Chặn đăng nhập trên nhiều thiết bị trong cùng một thời điểm. Nếu phát hiện, sẽ đưa ra popup cảnh báo cho người dùng.',
            'logout_device_protection' => 'Kích hoạt chức năng này nếu muốn khi phát hiện người dùng đăng nhập trên nhiều thiết bị, hệ thống sẽ tiến hành logout tài khoản của người dùng.',
            'block_device_protection' => 'Kích hoạt chức năng này nếu muốn khi phát hiện người dùng đăng nhập trên nhiều thiết bị, hệ thống sẽ tiến hành KHÓA tài khoản của người dùng.',
            'disable_register_member' => 'Khi muốn dừng việc đăng ký tài khoản trên website thì bật chức năng này lên. Admin vẫn có thể tạo tài khoản từ trang admin hoặc người dùng có thể đăng nhập thông qua firebase nếu website có thiết lập Đăng nhập qua firebase tại đây ' . base_url('sadmin/firebases') . '?support_tab=data_g_firebase_config',
            'login_rememberme' => 'When this function is enabled, the auto-login function will be enabled on every page.',
            'robots' => base_url('robots.txt'),
            'blog_private' => 'Việc tuân thủ yêu cầu này hoàn toàn phụ thuộc vào các công cụ tìm kiếm.',
            'emailnotice' => 'Một số chức năng sẽ gửi thông báo về email được thiết lập tại đây',
            'html_header' => 'Khi muốn nhúng mã HTML tùy chỉnh hoặc HTML của bên thứ 3 hoặc Dữ liệu có cấu trúc... vào trước thẻ đóng HEAH thì có thể nhúng tại đây. Hỗ trợ nhúng text template dạng %key% (trong đó key là giá trị của các option_name).',
            'html_body' => 'Khi muốn nhúng mã HTML tùy chỉnh hoặc HTML của bên thứ 3 hoặc Dữ liệu có cấu trúc... vào trước thẻ đóng BODY thì có thể nhúng tại đây. Hỗ trợ nhúng text template dạng %key% (trong đó key là giá trị của các option_name).',
            'web_favicon' => 'Your favicon must be a multiple of 48px square, for example: 48x48px, 96x96px, 144x144px and so on. Please using PNG or ICO file... SVG files don\'t have a specific size. View more: https://developers.google.com/search/docs/appearance/favicon-in-search?hl=en',
            'replace_post_content' => 'Khi cần thay thế nội dung của bài viết hàng loạt thì có thể sử dụng chức năng này. <br> Mẫu sử dụng: Nội dung cũ | Nội dung mới',
            'min_product_price' => 'Số tiền tối thiểu mà khách phải thanh toán cho mỗi đơn hàng.',
            'period_price' => 'Bấm [Thêm mới] để thêm các mức giá cho các gói nạp, bấm [Xóa] để loại bỏ một mức giá. <br> Hỗ trợ các đơn vị chuyển đổi: tr = triệu, k = nghìn, % = quy đổi theo giá gốc.',
            'bank_card_name' => 'Lưu ý: viết HOA không dấu',

            'autobank_token' => 'Tham số dùng để tăng độ bảo mật cho WebHook tự động xác thực quá trình thanh toán. <br> Khi có đầy đủ thông số này và thông tin ngân hàng nhận tiền, thông tin thanh toán qua ngân hàng và QR-Code sẽ được hiển thị tại trang thanh toán. <br> URL WebHook mặc định: ' . base_url('cassos/confirm'),

            'paypal_client_id' => 'Get Client ID here: https://developer.paypal.com/dashboard/applications/sandbox <br> Sandbox test accounts: https://developer.paypal.com/dashboard/accounts',
            'paypal_sdk_js' => 'Get the code here (rarely used): https://developer.paypal.com/sdk/js/configuration/',
            'bank_bin_code' => 'Chức năng tự động xác nhận tiền vào thông qua WebHook của https://casso.vn/ <br> Ưu tiên sử dụng tài khoản ngân hàng <strong>VietinBank</strong>.',
            'powered_by_eb' => 'Sử dụng lệnh <strong>$lang_model->the_web_license( $getconfig );</strong> để hiển thị thông điệp bản quyền mặc định.',
            // 
            'mail_queue_customer' => 'An email will be sent to the email the user entered during the ordering process. <br> Edit mail template in: ' . base_url('sadmin/orders/find_mail_template') . '<br> List parameter for replace template content: https://github.com/itvn9online/ci4-for-wordpress/blob/main/app/Models/MailQueue.php#L475',
            'mail_queue_admin' => 'An email will be sent to email setup in ' . base_url('sadmin/configs') . '?support_tab=data_emailnotice or ' . base_url('sadmin/configs') . '?support_tab=data_emailcontact. <br> Edit admin mail template in: ' . base_url('sadmin/orders/find_mail_template') . '?type=admin',
            'mail_queue_author' => 'An email will be sent to the author of the product the user has ordered. <br> Edit author mail template in: ' . base_url('sadmin/orders/find_mail_template') . '?type=author',
            'mail_queue_sending_type' => 'Mặc định email xác nhận đặt hàng sẽ được gửi ngay sau khi Đặt hàng thành công. Hoặc có thể chuyển thành gửi mail sau khi Thanh toán thành công.',
            // 
            'telegram_bot_token' => 'Token của bot trên Telegram. <br> Trong Telegram, tìm @BotFather rồi gõ lệnh /mybots để lấy danh sách bot (nếu có). <br> Bấm vào menu lệnh mà Telegram đưa ra để chọn bot và lấy Token. Sau khi có Token, hãy bấm vào đây để tìm Chat ID: ' . base_url('sadmin/smtps') . '?get_tele_chat_id=1 <br> Mở Telegram lên > Nhập Botfather tại thanh tìm kiếm > Chọn Botfather có tích xanh > Nhấn vào Start > Hệ thống sẽ hiển thị ra đoạn chat > Nhấn vào mục /newbot - create a new bot > Nhập tên cho Bot > Nhấn Gửi > Nhập tên người dùng cho Bot > Nhấn Gửi > Hệ thống gửi xác nhận thành công. https://wiki.matbao.net/kb/huong-dan-tao-bot-va-gui-thong-bao-telegram/',
            'telegram_chat_id' => 'ID nhóm chat trên Telegram. Bao gồm cả dấu - nếu có. Thay token vào link mẫu rồi lấy: https://api.telegram.org/bot{token}/getUpdates',
            //
            'html_products_body' => 'textarea',
            //
            'FTP_HOST' => 'Thông tin FTP - dùng để điều khiển file trong trường hợp bị lỗi permission. Mặc định là: 127.0.0.1',
            'CDN_BASE_URL' => 'URL để chạy CDN cho các file tĩnh (nếu có). Ví dụ: https://cdn.' . $_SERVER['HTTP_HOST'] . '/',
            'ALLOW_USING_MYSQL_DELETE' => 'Mặc định không cho xóa hoàn toàn dữ liệu trong mysql, nếu bạn muốn xóa hẳn thì có thể kích hoạt tính năng này.',
            'WGR_CSP_ENABLE' => 'On/ Off chế độ Content-Security-Policy. Nhớ điều chỉnh thông số src cho hợp lý.',
            'NUMBER_CHECKBOXS_INPUT' => 'Website nào cần dùng nhiều tăng số lượng bản ghi lên.',
            'ANTI_SPAM_EXPIRED' => 'Thời gian hết hạn cho mỗi token trong chức năng anti spam.',
            'CUSTOM_FAKE_POST_VIEW' => 'Mặc định mỗi lần truy cập bài viết thì bài đó sẽ được tăng lên 1 viewed. Nếu muốn tăng nhiều hơn, hãy tăng tham số này lên. Đặt là 0 sẽ loại bỏ chức năng cập nhật lượt xem.',
            'ENABLE_AMP_VERSION' => 'AMP viết tắt của Accelerated Mobile Pages là trang tăng tốc dành cho thiết bị di động của mỗi website.',
            'FAKE_WORDPRESS_VERSION' => 'Thiết lập thông số giả lập Wordpress để đánh lạc hướng các vụ tấn công vào mã nguồn. Giả lập hiện tại: ' . FAKE_WORDPRESS_VERSION,
            'SITE_LANGUAGE_SUB_FOLDER' => 'Nếu là sub-folder thì sẽ hỗ trợ prefix cho routes, url cũng sẽ thêm prefix vào ngay sau domain. Ví dụ: domain.com/vn hoặc domain.com/en',
            'SITE_LANGUAGE_SUPPORT' => 'Chọn danh sách ngôn ngữ sẽ hiển thị trên website này. Các Danh mục, Bài viết sẽ được bổ sung module nhân bản dữ liệu theo ngôn ngữ đã được thiết lập.',
            'HTTP_SYNC_HOST' => 'Prefix nhằm tránh xung đột khi sử dụng cache trong cùng 1 Server. Mặc định sẽ sử dụng THEMENAME (' . THEMENAME . ')',
            'CUSTOM_MD5_HASH_CODE' => 'Chuỗi sẽ thêm vào khi sử dụng hàm mdnam -> md5 -> tăng độ bảo mật cho chuỗi. Chỉ thay đổi khi thực sự cần thiết do thông số này sẽ có thể khiến toàn bộ chuỗi sử dụng hàm mdnam sẽ phải dựng lại.',
            'MY_SESSION_DRIVE' => 'Không nên sử dụng Cache và Session cùng một drive. Ví dụ: cache dùng Redis thì session nên dùng Memcached để có thể dọn dẹp cache khi cần thiết.',
            'CUSTOM_SESSION_PATH' => 'The location to save sessions to and is driver dependent. <br> * Only set when using hosting to change the default save path.',
            'MY_APP_TIMEZONE' => 'The default timezone that will be used in your application to display',
            'MY_CACHE_HANDLER' => 'The name of the preferred handler that should be used. If for some reason it is not available, the $backupHandler will be used in its place.',
            'WGR_REDIS_HOSTNAME' => 'Normally 127.0.0.1 or /tmp/redis.sock (The path to redis.sock may be different on each hosting)',
            'WGR_REDIS_PORT' => 'Normally 6379 or 0 if using unix socket.',
            'WGR_MEMCACHED_HOSTNAME' => 'Normally 127.0.0.1 or /tmp/memcached.sock (The path to redis.sock may be different on each hosting)',
            'WGR_MEMCACHED_PORT' => 'Normally 11211 or 0 if using unix socket.',
            // 'EBE_DATE_TEXT_FORMAT' => 'Định dạng ngày tháng cho hiển thị ngoài trang khách, thường dùng khi định cần hiển thị ngày tháng dạng chữ. Ví dụ: Jan through Dec thay vì 01 through 12. Mặc định sẽ dùng chung định dạng với tham số EBE_DATE_FORMAT.',
            'WGR_TABLE_PREFIX' => 'Xóa trắng để xem mặc định: ' . WGR_TABLE_PREFIX,
            'HTACCESSS_ALLOW' => 'Một số thư mục chỉ cho phép 1 số định dạng file được phép truy cập. Xóa trắng để xem mặc định: ' . HTACCESSS_ALLOW,
            //
            //'WGR_CATEGORY_PREFIX' => 'Xóa trắng để xem mặc định: ' . WGR_CATEGORY_PREFIX,
            //'WGR_PAGES_PREFIX' => 'Xóa trắng để xem mặc định: ' . WGR_PAGES_PREFIX,
            //
            'WGR_CATEGORY_PERMALINK' => 'Ví dụ: %taxonomy%-%term_id%-%slug%',
            'WGR_TAGS_PERMALINK' => 'Ví dụ: tag/%slug%',
            //'WGR_BLOGS_PERMALINK' => 'Xóa trắng để xem mặc định: ' . WGR_BLOGS_PERMALINK,
            'WGR_PRODS_PERMALINK' => 'Ví dụ: %taxonomy%-%term_id%-%slug%',
            'WGR_PROD_TAGS_PERMALINK' => 'Ví dụ: product-tag/%slug%',
            'WGR_TAXONOMY_PERMALINK' => 'Ví dụ: %taxonomy%-%term_id%-%slug%',
            //
            'WGR_POST_PERMALINK' =>
            'Ví dụ: %category_primary_slug%/%category_second_slug%/%post_name%-%ID%.%post_type%',
            //'WGR_BLOG_PERMALINK' => 'Xóa trắng để xem mặc định: ' . WGR_BLOG_PERMALINK,
            'WGR_PROD_PERMALINK' =>
            'Ví dụ: %ID%-%post_type%-%post_name%',
            'WGR_PAGE_PERMALINK' => 'Ví dụ: %ID%-%post_type%-%post_name%',
            'WGR_POSTS_PERMALINK' => 'Ví dụ: %ID%-%post_type%-%post_name%',
            'DEFAULT_SELECT_POST_COL' => 'Khi cần tối ưu việc select dữ liệu cho bảng post thì khai báo lại tham số này. Ví dụ: ID, post_permalink, post_excerpt, post_title, post_type, post_date, post_modified, comment_count',
            'THIS_IS_E_COMMERCE_SITE' => 'Khi chế độ này được kích hoạt, một số tính năng liên quan đến đặt hàng sẽ thay đổi. Ví dụ: tách riêng đơn hàng của từng shop.',
            //
            'WGR_CSP_DEFAULT_SRC' => 'Xóa trắng để xem mặc định: ' . WGR_CSP_DEFAULT_SRC,
            'WGR_CSP_SCRIPT_SRC' => 'Xóa trắng để xem mặc định: ' . WGR_CSP_SCRIPT_SRC,
            'WGR_CSP_STYLE_SRC' => 'Xóa trắng để xem mặc định: ' . WGR_CSP_STYLE_SRC,
            'WGR_CSP_IMG_SRC' => 'Xóa trắng để xem mặc định: ' . WGR_CSP_IMG_SRC,
            'WGR_CSP_CONNECT_SRC' => 'Xóa trắng để xem mặc định: ' . WGR_CSP_CONNECT_SRC,
            'WGR_CSP_CHILD_SRC' => 'Xóa trắng để xem mặc định: ' . WGR_CSP_CHILD_SRC,
            //
            'google_adsense' => 'Khi thông số này được thiết lập, mã google adsense sẽ tự động được thiết lập trong HEAD',
            'google_amp_adsense' => 'Khi thông số này được thiết lập, mã google adsense cho phiên bản amp sẽ tự động được thiết lập trong HEAD và ngay sau thẻ BODY.',
            'google_ads_txt_adsense' => 'Khi cần xác minh website bằng tệp ads.txt thì nhập nội dung tệp vào đây sau đó bấm lưu lại để tạo tệp.',
            'home_rating_value' => 'Vui lòng nhập dạng số thập phân. Ví dụ: 4.8 hoặc 4.5 hoặc 3.3',
            'home_rating_count' => 'Vui lòng nhập dạng số nguyên, tổng số lượng bình chọn website này, nếu là số ảo thì nhập trong khoảng 160 - 300.',
            'home_review_count' => 'Vui lòng nhập dạng số nguyên, tổng số lượng bài đánh giá website này, nếu là số ảo thì nhập dưới 70.',
            'off_schema_person' => 'Mặc định sẽ 1 đoạn Schema Person hoặc Schema Organization sẽ được tạo cho trang chủ, nếu không muốn sử dụng nó, hãy tắt đi ở đây.',
            // 
            'title' => 'The most effective page titles are about 10-70 characters long, including spaces.',
            'description' => 'For optimum effectiveness, meta descriptions should be 160-300 characters long.',
            'google_analytics' => 'Trong menu Quản trị của Google Analytics 4 - tìm đến Luồng dữ liệu - bấm vào chi tiết 1 luồng sau đó lấy: MÃ ĐO LƯỜNG (bắt đầu bằng chữ G-) rồi dán vào đây.',
        ];

        //
        $arr['html_products_header'] = $arr['html_header'];
        $arr['html_products_body'] = $arr['html_body'];
        //
        $arr['html_home_header'] = $arr['html_header'];
        $arr['html_home_body'] = $arr['html_body'];
        //
        $arr['html_product_header'] = $arr['html_header'];
        $arr['html_product_body'] = $arr['html_body'];

        //
        $arr['html_posts_header'] = $arr['html_header'];
        $arr['html_posts_body'] = $arr['html_body'];
        //
        $arr['html_post_header'] = $arr['html_header'];
        $arr['html_post_body'] = $arr['html_body'];

        //
        if (isset($arr[$key]) && $arr[$key] != '') {
            echo '<p class="controls-text-note">' . $arr[$key] . '</p>';
        }
    }

    // description của từng meta nếu có
    public static function defaultColor($key)
    {
        //echo $key . '<br>' . PHP_EOL;
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
            '' => 'Default by code',
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
            'eb_products_per_line' => self::$arr_posts_per_line,
            'eb_products_medium_per_line' => $arr_num_medium_line,
            'eb_products_small_per_line' => $arr_num_small_line,
            'eb_products_column_spacing' => self::$eb_column_spacing,
            'eb_products_row_align' => self::$eb_row_align,
            'eb_products_sidebar' => $show_sidebar,
            //
            'eb_product_per_line' => self::$arr_posts_per_line,
            'eb_product_medium_per_line' => $arr_num_medium_line,
            'eb_product_small_per_line' => $arr_num_small_line,
            'eb_product_column_spacing' => self::$eb_column_spacing,
            'eb_product_row_align' => self::$eb_row_align,
            'eb_product_sidebar' => $show_sidebar,
            //
            'smtp_secure' => [
                '' => 'Không bảo mật',
                'ssl' => 'SSL (port 465)',
                'tls' => 'TLS (port 587)',
            ],
            'bank_bin_code' => [
                '' => '[ Chọn ngân hàng ]'
            ],
            'currency_format' => [
                '' => '[ Chọn đơn vị tiền tệ ]',
                '/0111' => 'đ (/0111)',
                'vn/0111' => 'vnđ (vn/0111)',
                'VN/00d0' => 'VNĐ (VN/00d0)',
                'VND' => 'VND (VND)',
                // '$' => '$ ($)',
                'USD' => 'USD (USD)',
                '/00A5' => '&yen; (/00A5)',
                'NT$' => 'NT$ (NT$)',
                // 'KRW' => 'Korean won',
                '/20A9' => '&#8361; (/20A9)',
                // 'PHP' => '&#8369;',
                '/20B1' => '&#8369; (/20B1)'
            ],
            //
            'MY_DB_DRIVER' => [
                '' => 'Default by code',
                'MySQLi' => 'MySQLi',
                'Postgre' => 'Postgre',
                'PDO' => 'PDO',
                'Oracle' => 'Oracle',
            ],
            'BASE_PROTOCOL' => [
                '' => 'Default by code',
                'https' => 'https',
                'http' => 'http',
            ],
            'MY_CACHE_HANDLER' => [
                '' => 'Default by code',
                'file' => 'File',
                'redis' => 'Redis',
                'memcached' => 'Memcached',
                self::DISABLE_CACHE => 'Disable',
            ],
            'MY_SESSION_DRIVE' => [
                '' => 'Default by code',
                'FileHandler' => 'File',
                'RedisHandler' => 'Redis',
                'MemcachedHandler' => 'Memcached',
                'DatabaseHandler' => 'Database',
            ],
            'MY_APP_TIMEZONE' => [
                '' => 'Default by code',
            ],
            'ALLOW_USING_MYSQL_DELETE' => [
                '' => 'Default by code',
                '0' => 'Off',
                '1' => 'On',
            ],
            'WGR_CSP_ENABLE' => [
                '' => 'Default by code',
                '0' => 'Off',
                '1' => 'On',
            ],
            'ENABLE_AMP_VERSION' => [
                '' => 'Default by code',
                '0' => 'Off',
                '1' => 'On',
            ],
            'SITE_LANGUAGE_SUB_FOLDER' => [
                '' => 'Default by code',
                '0' => 'sub-domain',
                '1' => 'sub-folder',
            ],
            'SITE_LANGUAGE_DEFAULT' => $arr_default_display_lang,
            'EBE_DATE_FORMAT' => [
                '' => 'Default by code',
                // gán giá trị trống để ở view sẽ build ra ngày tháng hiện tại
                'Y-m-d' => '',
                'm-d-Y' => '',
                'd-m-Y' => '',
            ],
            'EBE_DATE_TEXT_FORMAT' => [
                '' => 'Default by code',
                // gán giá trị trống để ở view sẽ build ra ngày tháng hiện tại
                // Jan through Dec
                'M d, Y' => '',
                'd M Y' => '',
                'd M' => '',
                // January through December
                'F d, Y' => '',
                'd F Y' => '',
                'd F' => '',
                // Mon through Sun
                'D, M d, Y' => '',
                'D, d M Y' => '',
                'D, d M' => '',
                // 
                'D, F d, Y' => '',
                'D, d F Y' => '',
                'D, d F' => '',
            ],
            'THIS_IS_E_COMMERCE_SITE' => [
                '' => 'No',
                'yes' => 'Yes',
            ],
            'mail_queue_customer' => [
                '' => 'Default by code',
                'none' => 'Not send',
            ],
            'mail_queue_admin' => [
                '' => 'Default by code',
                'private' => 'Using private template',
                'none' => 'Not send',
            ],
            // với mail gửi cho tác giả thì ít khi dùng, web nào dùng thì bật lên
            'mail_queue_author' => [
                '' => 'Not send',
                'default' => 'Default by code',
                'private' => 'Using private template',
            ],
            'mail_queue_sending_type' => [
                '' => 'Default by code',
                // Chỉ gửi mail sau khi đã thanh toán -> trạng thái tương ứng với trạng thái đơn hàng -> private
                'private' => 'Paid',
            ],
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
            'copy_right_first' => 'Copyright &copy; ',
            'copy_right_last' => ' - Test version.',
            'powered_by_eb' => ' Managed by ' . PARTNER_BRAND_NAME,
        ];
        //echo $k . '<br>' . PHP_EOL;
        //echo $default_value . '<br>' . PHP_EOL;

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
