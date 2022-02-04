<?php
namespace App\ Controllers;

//
//use CodeIgniter\ Controller;

// Libraries
use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
use App\ Libraries\ UsersType;
//use App\ Libraries\ FtpAccount;

//
class Layout extends Sync {
    //public $CI = NULL;

    //
    public $lang_key = '';
    public $breadcrumb = [];
    public $getconfig = NULL;
    public $taxonomy_post_size = '';
    // danh sách các nhóm cha của nhóm hiện tại đang được xem
    public $taxonomy_slider = [];
    // danh sách ID nhóm của sản phẩm đang xem -> dùng để tìm các bài cùng nhóm khi xem chi tiết bài viết
    public $posts_parent_list = [];

    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_header = true;

    // controller nào bật cái này thì sẽ import thư viện angular js cho nó
    //public $enable_angular_js = false;

    // các định dạng file được phép truy cập trong thư mục upload
    protected $htaccess_allow = 'css|js|map|htm?l|xml|json|webmanifest|tff|eot|woff?|gif|jpe?g|tiff?|png|webp|bmp|ico|svg';

    public function __construct() {
        parent::__construct();

        //echo base_url('/') . '<br>' . "\n";

        //$this->base_model = new\ App\ Models\ Base();
        $this->option_model = new\ App\ Models\ Option();
        $this->lang_model = new\ App\ Models\ Lang();
        $this->term_model = new\ App\ Models\ Term();
        $this->post_model = new\ App\ Models\ Post();
        $this->menu_model = new\ App\ Models\ Menu();
        $this->user_model = new\ App\ Models\ User();
        $this->comment_model = new\ App\ Models\ Comment();

        //
        //$this->load->model( 'Blog', 'blog_model' );
        //$this->load->model( 'Ads', 'ads_model' );
        //$this->load->model( 'Upload', 'upload_model' );

        //
        //$this->session = \Config\ Services::session();
        $this->request = \Config\ Services::request();

        //
        /*
        helper( [
            //'cookie',
            'url',
            'form',
            'security'
        ] );
        */

        /*
         * bắt đầu code
         */
        // xác định ngôn ngữ hiện tại
        $this->lang_key = LanguageCost::set_lang();
        //$this->lang_key = LanguageCost::lang_key();
        //echo $this->lang_key . '<br>' . "\n";

        //$allurl = 'https://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];

        $getconfig = $this->option_model->list_config();
        //print_r( $getconfig );

        $getconfig = ( object )$getconfig;
        //print_r( $getconfig );
        $this->getconfig = $getconfig;

        //
        $this->current_user_id = 0;
        $this->session_data = $this->base_model->get_ses_login();
        //print_r( $this->session_data );

        // key lưu ID hiện tại của user
        //$this->wrg_cookie_login_key = 'wrg_logged_in_key';
        if ( !empty( $this->session_data ) && isset( $this->session_data[ 'userID' ] ) && $this->session_data[ 'userID' ] > 0 ) {
            $this->current_user_id = $this->session_data[ 'userID' ];
        }

        //
        $this->debug_enable = ( ENVIRONMENT !== 'production' );
        //var_dump( $this->debug_enable );

        //
        $this->cache_key = '';
        $this->cache_mobile_key = '';
        $this->cache = \Config\ Services::cache();

        //
        $this->isMobile = '';
        $this->teamplate = [];
        if ( $this->preload_header === true ) {
            //echo 'preload header <br>' . "\n";
            //$this->isMobile = $this->checkDevice( $_SERVER[ 'HTTP_USER_AGENT' ] );
            $this->isMobile = $this->WGR_is_mobile();
            //var_dump( $this->isMobile );

            //
            $this->global_header_footer();
        }
    }

    // trả về nội dung từ cache hoặc lưu cache nếu có
    protected function global_cache( $key, $value = '', $time = 300 ) {
        $key .= $this->cache_mobile_key . '-' . $this->lang_key;

        // lưu cache nếu có nội dung
        if ( $value != '' ) {
            return $this->cache->save( $key, $value, $time );
        }

        // trả về cache nếu có
        return $this->cache->get( $key );
    }

    // kiểm tra session của user, nếu đang đăng nhập thì bỏ qua chế độ cache
    protected function MY_cache( $key, $value = '', $time = 300 ) {
        // không thực thi cache đối với tài khoản đang đăng nhập
        if ( $this->current_user_id > 0 || isset( $_GET[ 'set_lang' ] ) ) {
            return NULL;
        }

        //
        return $this->global_cache( $key, $value, $time );
    }

    // hiển thị nội dung từ cache -> thêm 1 số đoạn comment HTML vào
    protected function show_cache( $content ) {
        echo $content;

        //
        echo '<!-- Served from: ' . $this->cache_key . ' by ebcache' . PHP_EOL;
        echo 'Caching using hard disk drive. Recommendations using SSD drive for your website.' . PHP_EOL;
        echo 'Compression = gzip -->';

        //
        return true;
    }

    // chỉ gọi đến chức năng nạp header, footer khi cần hiển thị
    protected function global_header_footer() {
        //print_r($this->getconfig);
        $this->teamplate[ 'header' ] = view( 'header_view', array(
            // các model dùng chung thì cho vào header để sau sử dụng luôn
            'base_model' => $this->base_model,
            'menu_model' => $this->menu_model,
            'option_model' => $this->option_model,
            'post_model' => $this->post_model,
            'term_model' => $this->term_model,
            'lang_model' => $this->lang_model,

            //
            //'session' => $this->session,

            'getconfig' => $this->getconfig,
            'session_data' => $this->session_data,
            'current_user_id' => $this->current_user_id,
            'debug_enable' => $this->debug_enable,
            //'menu' => $menu,
            //'allurl' => $allurl,
            'isMobile' => $this->isMobile
        ) );

        //
        $this->teamplate[ 'footer' ] = view( 'footer_view' );

        //
        //$this->teamplate[ 'enable_angular_js' ] = $this->enable_angular_js;

        //
        return true;
    }

    // fake function wp_is_mobile of wordpress
    protected function WGR_is_mobile() {
        if ( empty( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
            $is_mobile = false;
        } else if ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Mobile' ) !== false // Many mobile devices (all iPhone, iPad, etc.)
            ||
            strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Android' ) !== false ||
            strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Silk/' ) !== false ||
            strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Kindle' ) !== false ||
            strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'BlackBerry' ) !== false ||
            strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Opera Mini' ) !== false ||
            strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Opera Mobi' ) !== false ) {
            // thêm key cho bản mobile
            $this->cache_mobile_key = '---mobile';

            //
            $is_mobile = true;
        } else {
            $is_mobile = false;
        }

        //
        return $is_mobile;
    }

    /*
     * v2 -> fake wordpress function
     */
    protected function checkDevice( $useragent ) {
        return $this->WGR_is_mobile();
    }

    protected function create_breadcrumb( $text, $url = '' ) {
        if ( $url != '' ) {
            $this->breadcrumb[] = '<li><a href="' . $url . '">' . $text . '</a></li>';
        } else {
            $this->breadcrumb[] = '<li>' . $text . '</li>';

        }
        //print_r( $this->breadcrumb );

        //
        return false;
    }

    protected function create_term_breadcrumb( $cats ) {
        //print_r( $cats );
        $this->taxonomy_slider[] = $cats;
        $this->posts_parent_list[] = $cats[ 'term_id' ];

        //
        if ( $this->taxonomy_post_size == '' && isset( $cats[ 'term_meta' ][ 'taxonomy_custom_post_size' ] ) ) {
            $this->taxonomy_post_size = $cats[ 'term_meta' ][ 'taxonomy_custom_post_size' ];
        }

        //
        if ( $cats[ 'parent' ] > 0 ) {
            $parent_cats = $this->term_model->get_all_taxonomy( $cats[ 'taxonomy' ], $cats[ 'parent' ] );
            //print_r( $parent_cats );

            $this->create_term_breadcrumb( $parent_cats );
        }

        //
        return $this->create_breadcrumb( $cats[ 'name' ], $this->term_model->get_the_permalink( $cats ) );
    }

    public function page404( $msg_404 = '' ) {
        $this->teamplate[ 'main' ] = view( '404', array(
            'seo' => $this->base_model->default_seo( '404 not found', '404' ),
            'breadcrumb' => '',
            // thông điệp của việc xuất hiện lỗi 404
            'msg_404' => $msg_404,
        ) );
        return view( 'layout_view', $this->teamplate );
    }

    protected function category( $data, $post_type, $taxonomy, $file_view = 'category_view', $ops = [] ) {
        //$config['base_url'] = $this->term_model->get_the_permalink();
        //$config['per_page'] = 50;
        //$config['uri_segment'] = 3;

        //
        if ( !isset( $ops[ 'page_num' ] ) ) {
            $ops[ 'page_num' ] = 1;
        }

        //
        $this->cache_key = 'taxonomy' . $data[ 'term_id' ] . '-page' . $ops[ 'page_num' ];
        $cache_value = $this->MY_cache( $this->cache_key );
        // Will get the cache entry named 'my_foo'
        //var_dump( $cache_value );
        // có thì in ra cache là được
        if ( $cache_value !== NULL ) {
            return $this->show_cache( $cache_value );
        }

        //
        //echo 'this category <br>' . "\n";
        //print_r( $data );
        $data = $this->term_model->get_all_taxonomy( $taxonomy, $data[ 'term_id' ], [
            'parent' => $data[ 'term_id' ]
        ] );
        //print_r( $data );
        $data = $this->term_model->get_child_terms( [ $data ], [] );
        //print_r( $data );

        $data = $data[ 0 ];
        //print_r( $data );


        //
        //$this->create_breadcrumb( $data[ 'name' ] );
        $this->create_term_breadcrumb( $data );
        //print_r( $this->taxonomy_slider );
        $seo = $this->base_model->seo( $data, $this->term_model->get_the_permalink( $data ) );

        // chỉnh lại thông số cho canonical
        if ( $ops[ 'page_num' ] > 1 ) {
            $seo[ 'canonical' ] = rtrim( $seo[ 'canonical' ], '/' ) . '/page/' . $ops[ 'page_num' ];
        }

        // lấy danh sách nhóm con xem có không
        //$child_cat = $this->term_model->get_all_taxonomy( $data[ 'taxonomy' ] );
        //print_r( $child_cat );

        // lấy banner quảng cáo theo taxonomy nếu có
        $taxonomy_slider = $this->term_model->get_the_slider( $this->taxonomy_slider );
        //echo $taxonomy_slider . '<br>' . "\n";
        if ( $taxonomy_slider == '' ) {
            $taxonomy_slider = $this->lang_model->get_the_text( 'main_slider_slug' );
        }
        //echo $taxonomy_slider . '<br>' . "\n";
        if ( $taxonomy_slider != '' ) {
            $taxonomy_slider = $this->post_model->get_the_ads( $taxonomy_slider, 0, [
                'add_class' => 'taxonomy-auto-slider'
            ] );
        }

        // -> views
        $this->teamplate[ 'breadcrumb' ] = view( 'breadcrumb_view', array(
            'breadcrumb' => $this->breadcrumb
        ) );

        $this->teamplate[ 'main' ] = view( $file_view, array(
            //'post_per_page' => $post_per_page,
            'taxonomy_post_size' => $this->taxonomy_post_size,
            //'taxonomy_slider' => $this->taxonomy_slider,
            'taxonomy_slider' => $taxonomy_slider,
            'ops' => $ops,
            'seo' => $seo,
            'post_type' => $post_type,
            'getconfig' => $this->getconfig,
            'data' => $data,
        ) );
        $cache_value = view( 'layout_view', $this->teamplate );

        // Save into the cache for 5 minutes
        $cache_save = $this->MY_cache( $this->cache_key, $cache_value );
        //var_dump( $cache_save );

        //
        return $cache_value;
    }

    // hàm lấy dữ liệu đầu vào và xử lý các vấn đề bảo mật nếu có
    private function MY_data( $a, $default_value = '', $xss_clean = true ) {
        // với kiểu chuỗi -> so sánh lấy chuỗi trống
        if ( is_string( $a ) && $a == '' ) {
            return $default_value;
        } else if ( is_numeric( $a ) ) {
            return $a;
        } else if ( empty( $a ) ) {
            return $default_value;
        }

        //
        return $a;
    }
    protected function MY_get( $key, $default_value = '', $xss_clean = true ) {
        return $this->MY_data( $this->request->getGet( $key ), $default_value, $xss_clean );
    }
    protected function MY_post( $key, $default_value = '', $xss_clean = true ) {
        return $this->MY_data( $this->request->getPost( $key ), $default_value, $xss_clean );
    }

    /*
     * Upload giả lập wordpress
     */
    protected function deny_visit_upload( $upload_root = '', $remove_file = false ) {
        if ( $upload_root == '' ) {
            $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        }

        //
        $htaccess_file = $upload_root . '.htaccess';
        //die($htaccess_file);
        //echo $htaccess_file . '<br>' . "\n";

        // cập nhật lại nội dung file htaccess
        if ( $remove_file === true && file_exists( $htaccess_file ) ) {
            $this->MY_unlink( $htaccess_file );
        }

        //
        if ( !file_exists( $htaccess_file ) ) {
            // nội dung chặn mọi truy cập tới các file trong này
            $this->base_model->_eb_create_file( $htaccess_file, trim( '

#
Order allow,deny
Deny from all 
Allow from 127.0.0.1

# Allow access to files with extensions -> in apache
<FilesMatch "\.(' . $this->htaccess_allow . ')$">
Order allow,deny
Allow from all
</FilesMatch>

# too many redirect if not image -> in apache, openlitespeed
<IfModule mod_rewrite.c>
RewriteCond %{REQUEST_URI} !^.*\.(' . $this->htaccess_allow . ')$
RewriteRule ^(\.*) ' . DYNAMIC_BASE_URL . '$1 [F]
</IfModule>

#

' ), [
                //'set_permission' => 0644,
                'set_permission' => 0711,
                'ftp' => 1,
            ] );
        }
        //die( __FILE__ . ':' . __LINE__ );

        //
        return $htaccess_file;
    }

    protected function media_upload( $xss_clean = true, $allow_upload = [] ) {
        //print_r( $_POST );
        //print_r( $_FILES );

        //
        $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        //echo $upload_root . '<br>' . "\n";

        //
        $this->deny_visit_upload( $upload_root );

        //
        $upload_path = $this->media_path( [
            date( 'Y' ),
            date( 'm' ),
        ], $upload_root );
        //echo $upload_path . '<br>' . "\n";

        // mảng trả về danh sách file đã upload
        $arr_result = [];

        // 1 số định dạng file không cho phép upload trực tiếp
        $arr_block_upload = [
            'php',
            'exe',
            'py',
            'sh'
        ];

        //
        if ( $imagefile = $this->request->getFiles() ) {
            //print_r( $imagefile );

            // chạy vòng lặp để lấy các key upload -> xác định tên input tự động
            foreach ( $_FILES as $key => $upload_image ) {
                //echo $key . '<br>' . "\n";

                //
                foreach ( $imagefile[ $key ] as $file ) {
                    //print_r( $file );
                    if ( $file->isValid() && !$file->hasMoved() ) {
                        $file_name = $file->getName();
                        //echo $file_name . '<br>' . "\n";
                        $file_name = $this->base_model->_eb_non_mark_seo( $file_name );
                        $file_name = sanitize_filename( $file_name );
                        //echo $file_name . '<br>' . "\n";

                        //
                        $file_ext = $file->guessExtension();
                        //echo $file_ext . '<br>' . "\n";
                        $file_ext = strtolower( $file_ext );
                        //echo $file_ext . '<br>' . "\n";

                        //
                        $file_path = $upload_path . $file_name;
                        // đổi tên file nếu file đã tồn tại
                        if ( file_exists( $file_path ) ) {
                            for ( $i = 1; $i < 100; $i++ ) {
                                $file_new_name = basename( $file_name, '.' . $file_ext ) . '_' . $i . '.' . $file_ext;
                                $file_path = $upload_path . $file_new_name;
                                if ( !file_exists( $file_path ) ) {
                                    $file_name = basename( $file_path );
                                    break;
                                }
                            }
                        }
                        //echo $file_path . '<br>' . "\n";

                        // kiểm tra định dạng file
                        $mime_type = $file->getMimeType();
                        //echo $mime_type . '<br>' . "\n";
                        //continue;

                        // nếu không phải file ảnh
                        $is_image = true;
                        if ( strtolower( explode( '/', $mime_type )[ 0 ] ) != 'image' ) {
                            $is_image = false;

                            // thêm vào tệp mở rộng để không cho truy cập file trực tiếp
                            $file_other_ext = 'daidq-ext';
                            $file_new_path = $file_path . '.' . $file_other_ext;
                            //echo $file_new_path . '<br>' . "\n";
                            if ( file_exists( $file_new_path ) ) {
                                for ( $i = 1; $i < 100; $i++ ) {
                                    $file_new_path = $file_path . '.' . $file_other_ext . '_' . $i;
                                    //echo $file_new_path . '<br>' . "\n";
                                    if ( !file_exists( $file_new_path ) ) {
                                        $file_path = $file_new_path;
                                        break;
                                    }
                                }
                            } else {
                                $file_path = $file_new_path;
                            }
                            //echo $file_path . '<br>' . "\n";
                            $file_name = basename( $file_path );
                            //echo $file_name . '<br>' . "\n";
                            //die( __FILE__ . ':' . __LINE__ );
                        }

                        // nếu có kiểm duyệt định dạng file -> chỉ các file trong này mới được upload
                        if ( !empty( $allow_upload ) && !in_array( $file_ext, $allow_upload ) ) {
                            continue;
                        }
                        /*
                        // nếu không, sẽ chặn các định dạng file có khả năng thực thi lệnh từ server
                        else if ( in_array( $file_ext, $arr_block_upload ) ) {
                            continue;
                        }
                        */

                        //
                        $file->move( $upload_path, $file_name, true );

                        //
                        if ( !file_exists( $file_path ) ) {
                            continue;
                        }
                        chmod( $file_path, 0766 );

                        //
                        if ( !isset( $arr_result[ $key ] ) ) {
                            $arr_result[ $key ] = [];
                        }

                        //
                        $metadata = $this->media_attachment_metadata( $file_path, $file_ext, $upload_path, $mime_type, $upload_root );

                        // optimize file gốc
                        if ( $is_image === true ) {
                            $new_quality = \App\ Libraries\ MyImage::quality( $file_path );
                        }

                        //
                        if ( $metadata !== false ) {
                            $arr_result[ $key ][] = $metadata[ 'file_uri' ];
                        }
                    } else {
                        throw new\ RuntimeException( $file->getErrorString() . '(' . $file->getError() . ')' );
                    }
                }
            }
            //die( __FILE__ . ':' . __LINE__ );
        }
        //print_r( $arr_result );
        //die( __FILE__ . ':' . __LINE__ );

        //
        return $arr_result;
    }

    // tạo thumbnail cho hình ảnh dựa theo path
    protected function media_attachment_metadata( $file_path, $file_ext = '', $upload_path = '', $mime_type = '', $upload_root = '', $post_parent = 0 ) {
        if ( !file_exists( $file_path ) ) {
            return false;
        }
        //echo $file_path . '<br>' . "\n";

        // bảo mật file, lỗi thì xóa luôn file này đi
        /*
        if ( $this->security->xss_clean( $file_path, TRUE ) === FALSE ) {
            unlink( $file_path );
            die( 'ERROR! xss file upload' );
        }
        */
        //unlink( $file_path );
        //continue;

        //
        //echo 'upload ok: ' . $v . '<br>' . "\n";

        //
        if ( $upload_root == '' ) {
            $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        }
        //echo $upload_root . '<br>' . "\n";
        if ( $upload_path == '' ) {
            $upload_path = dirname( $file_path ) . '/';
        }
        //echo $upload_path . '<br>' . "\n";

        //
        $file_uri = str_replace( $upload_root, '', $file_path );
        //echo $file_uri . '<br>' . "\n";

        //
        if ( $file_ext == '' ) {
            $file_ext = pathinfo( $file_path, PATHINFO_EXTENSION );
        }
        $file_ext = strtolower( $file_ext );
        //echo $file_ext . '<br>' . "\n";

        //
        if ( $mime_type == '' ) {
            $mime_type = mime_content_type( $file_path );
        }
        //echo $mime_type . '<br>' . "\n";

        // nếu không phải file ảnh
        $is_image = true;
        if ( strtolower( explode( '/', $mime_type )[ 0 ] ) != 'image' ) {
            $is_image = false;
        }

        //
        $post_title = basename( $file_path, '.' . $file_ext );
        //echo $post_title . '<br>' . "\n";

        //
        $arr_list_size = PostType::media_size();
        // chỉ resize file ảnh
        $arr_allow_resize = [
            'png',
            'jpg',
            'jpeg'
        ];

        // giả lập dữ liệu giống wordpress
        $arr_after_sizes = [];
        if ( $is_image == true ) {
            $get_file_info = getimagesize( $file_path );
        } else {
            $get_file_info = [
                0,
                0
            ];
        }
        $file_size = filesize( $file_path );
        foreach ( $arr_list_size as $size_name => $size ) {
            $resize_path = $upload_path . $post_title . '-' . $size_name . '.' . $file_ext;
            //echo $resize_path . '<br>' . "\n";
            //die( __FILE__ . ':' . __LINE__ );
            //continue;

            /*
             * Sử dụng class tự viết hoặc tham kháo thư viện của CI3
             * https://codeigniter.com/userguide3/libraries/image_lib.html
             */
            // chỉ resize với các file được chỉ định (thường là file ảnh)
            if ( in_array( $file_ext, $arr_allow_resize ) ) {
                $resize_img = \App\ Libraries\ MyImage::resize( $file_path, $resize_path, $size );
            }
            // các file khác không cần resize
            else {
                $resize_img = [
                    'width' => $get_file_info[ 0 ],
                    'height' => $get_file_info[ 1 ],
                    'file_size' => $file_size,
                    'file' => basename( $file_path ),
                ];
            }
            $resize_img[ 'mime-type' ] = $mime_type;
            //print_r( $resize_img );

            //
            $arr_after_sizes[ $size_name ] = $resize_img;
        }
        //print_r( $arr_after_sizes );
        //die( __FILE__ . ':' . __LINE__ );

        //
        //print_r( $get_file_info );
        $arr_metadata = [
            'width' => $get_file_info[ 0 ],
            'height' => $get_file_info[ 1 ],
            'file_size' => $file_size,
            'file' => $file_uri,
            'sizes' => $arr_after_sizes,
            'image_meta' => [
                'aperture' => 0,
                'credit' => '',
                'camera' => '',
                'caption' => '',
                'created_timestamp' => time(),
                'copyright' => '',
                'focal_length' => 0,
                'iso' => 0,
                'shutter_speed' => 0,
                'title' => '',
                'orientation' => 0,
                'keywords' => [],
            ]
        ];
        //print_r( $arr_metadata );
        $str_metadata = serialize( $arr_metadata );
        //echo $str_metadata . '<br>' . "\n";
        //$test = unserialize( $str_metadata );
        //print_r( $test );

        //
        $data_insert = [
            'post_title' => $post_title,
            'post_status' => PostType::INHERIT,
            //'post_name' => $post_title,
            'post_name' => str_replace( '.', '-', PostType::MEDIA_URI . $file_uri ),
            'guid' => DYNAMIC_BASE_URL . PostType::MEDIA_URI . $file_uri,
            'post_type' => PostType::MEDIA,
            'post_mime_type' => $mime_type,
            'post_parent' => $post_parent,
        ];
        //print_r( $data_insert );
        $_POST[ 'post_meta' ] = [
            '_wp_attachment_metadata' => $str_metadata,
            '_wp_attached_file' => $file_uri,
        ];
        //print_r( $_POST );
        //die( __FILE__ . ':' . __LINE__ );
        $result_id = $this->post_model->insert_post( $data_insert, $_POST[ 'post_meta' ] );
        //print_r( $result_id );
        if ( is_array( $result_id ) && isset( $result_id[ 'error' ] ) ) {
            $this->base_model->alert( $result_id[ 'error' ], 'error' );
        }
        //die( __FILE__ . ':' . __LINE__ );
        //echo 'Result id: ' . $result_id . '<br>' . "\n";

        //
        return [
            'file_uri' => PostType::MEDIA_URI . $file_uri,
            'is_image' => $is_image,
            'metadata' => $arr_metadata,
            'data' => $data_insert,
            'meta' => $_POST[ 'post_meta' ],
        ];
    }

    // tạo path upload
    protected function media_path( $data = [], $path = '' ) {
        if ( $path == '' ) {
            //$path = PUBLIC_HTML_PATH . PostType::MEDIA_URI;
            $path = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;

        }
        foreach ( $data as $v ) {
            $path .= $v . '/';
            //echo $path . '<br>' . "\n";

            if ( !is_dir( $path ) ) {
                mkdir( $path, 0766 )or die( 'ERROR create dir (' . basename( __FILE__ ) . ':' . __FUNCTION__ . ':' . __LINE__ . ')! ' . $path );
                chmod( $path, 0766 );
            }
        }

        //
        return $path;
    }

    // đồng bộ dữ liệu login của thành viên về 1 định dạng chung
    protected function sync_login_data( $result ) {
        $result[ 'user_pass' ] = '';
        $result[ 'ci_pass' ] = '';
        // hỗ trợ phiên bản code cũ -> tạo thêm dữ liệu tương ứng
        $result[ 'userID' ] = $result[ 'ID' ];
        $result[ 'userName' ] = $result[ 'display_name' ];
        $result[ 'userEmail' ] = $result[ 'user_email' ];
        // quyền admin
        $arr_admin_group = [
            UsersType::AUTHOR,
            UsersType::MOD,
            UsersType::ADMIN,
        ];
        if ( in_array( $result[ 'member_type' ], $arr_admin_group ) ) {
            $result[ 'userLevel' ] = UsersType::ADMIN_LEVEL;
        } else {
            $result[ 'userLevel' ] = UsersType::GUEST_LEVEL;
        }
        //print_r( $result );
        //die( __FILE__ . ':' . __LINE__ );

        //
        return $result;
    }
}