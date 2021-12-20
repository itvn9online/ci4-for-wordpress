<?php
namespace App\ Controllers;

//
//use CodeIgniter\ Controller;

// Libraries
use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
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

    public function __construct( $preload_header = true ) {
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
        $this->session = \Config\ Services::session();
        $this->request = \Config\ Services::request();

        //
        helper( [
            'url',
            'form',
            'security'
        ] );

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
        $this->session_data = $this->session->get( 'admin' );
        //print_r( $this->session_data );
        if ( !empty( $this->session_data ) && isset( $this->session_data[ 'userID' ] ) && $this->session_data[ 'userID' ] > 0 ) {
            /*
             * duy trì trạng thái đăng nhập
             * -> do phiên đăng nhập nó cứ hết gọi là thường xuyên -> mình cũng cập nhật lại session thường xuyên
             */
            $this->session->set( 'admin', $this->session_data );

            //
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
        if ( $preload_header === true ) {
            //echo 'preload_header <br>' . "\n";
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
            return false;
        }

        //
        return $this->global_cache( $key, $value, $time );
    }

    // hiển thị nội dung từ cache -> thêm 1 số đoạn comment HTML vào
    protected function show_cache( $content ) {
        echo $content;
        //echo '<script>console.log("%c in cache: ' . $this->cache_key . '", "color: green;");</script>' . "\n";
        echo '<!-- Served from: ' . $this->cache_key . ' by ebcache
Caching using hard disk drive. Recommendations using SSD drive for your website. 
Compression = gzip -->';
        return true;
    }

    // chỉ gọi đến chức năng nạp header, footer khi cần hiển thị
    protected function global_header_footer() {
        $this->teamplate[ 'header' ] = view( 'header_view', array(
            // các model dùng chung thì cho vào header để sau sử dụng luôn
            'base_model' => $this->base_model,
            'menu_model' => $this->menu_model,
            'option_model' => $this->option_model,
            'post_model' => $this->post_model,
            'term_model' => $this->term_model,
            'lang_model' => $this->lang_model,

            //
            'session' => $this->session,

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

    protected function checkDevice( $useragent ) {
        /*
         * v2 -> fake wordpress function
         */
        return $this->WGR_is_mobile();

        /*
         * v1
         */
        return preg_match( '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent ) || preg_match( '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr( $useragent, 0, 4 ) );
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
        // không có cache thì tiếp tục
        if ( !$cache_value ) {
            //echo '<!-- no cache -->';
        }
        // có thì in ra cache là được
        else {
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
        if ( empty( $a ) ) {
            return $default_value;
        }

        if ( $xss_clean === true ) {
            if ( !is_array( $a ) ) {
                // xss_clean bị hủy bỏ ở CI4
                return $a;
                //return $this->security->xss_clean( $a );
            }

            //
            /*
			foreach ( $a as $k => $v ) {
				if ( !empty( $v ) ) {
					// xss_clean bị hủy bỏ ở CI4
					//$a[ $k ] = $this->security->xss_clean( $v );
				}
			}
            */
        }
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
    protected function media_upload( $xss_clean = true ) {
        require_once PUBLIC_HTML_PATH . 'vendor/functionsResizeImg.php';

        //
        //print_r( $_POST );
        //print_r( $_FILES );

        // mảng trả về danh sách file đã upload
        $arr_result = [];

        // chạy vòng lặp và up tất cả các file được chọn
        foreach ( $_FILES as $key => $upload_image ) {
            //$upload_image = $_FILES[ $key ];
            //print_r( $upload_image );
            //continue;

            //
            $upload_name = $upload_image[ 'name' ];
            $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
            $upload_path = $this->media_path( [
                date( 'Y' ),
                date( 'm' ),
            ], $upload_root );
            //echo $upload_path . '<br>' . "\n";

            //
            $arr_list_size = PostType::media_size();
            // chỉ resize file ảnh
            $arr_allow_resize = [
                'png',
                'jpg',
                'jpeg'
            ];

            //
            foreach ( $upload_name as $k => $v ) {
                $v = $this->base_model->_eb_non_mark_seo( $v );
                $v = sanitize_filename( $v );
                //echo $v . '<br>' . "\n";

                //
                $file_ext = pathinfo( $v, PATHINFO_EXTENSION );
                //echo $file_ext . '<br>' . "\n";
                $file_path = $upload_path . $v;
                // đổi tên file nếu file đã tồn tại
                if ( file_exists( $file_path ) ) {
                    for ( $i = 1; $i < 100; $i++ ) {
                        $file_new_name = $this->base_model->_eb_non_mark_seo( basename( $v, '.' . $file_ext ) ) . '_' . $i . '.' . $file_ext;
                        $file_path = $upload_path . $file_new_name;
                        if ( !file_exists( $file_path ) ) {
                            break;
                        }
                    }
                }
                //die( $file_path );
                //echo $file_path . '<br>' . "\n";

                //
                if ( move_uploaded_file( $upload_image[ 'tmp_name' ][ $k ], $file_path ) ) {
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

                    $file_uri = str_replace( $upload_root, '', $file_path );
                    //echo $file_uri . '<br>' . "\n";

                    //
                    $post_title = basename( $file_path, '.' . $file_ext );

                    // giả lập dữ liệu giống wordpress
                    $arr_after_sizes = [];
                    foreach ( $arr_list_size as $size_name => $size ) {
                        $resize_path = dirname( $file_path ) . '/' . $post_title . '-' . $size_name . '.' . $file_ext;
                        //echo $resize_path . '<br>' . "\n";

                        /*
                         * Sử dụng class tự viết hoặc tham kháo thư viện của CI3
                         * https://codeigniter.com/userguide3/libraries/image_lib.html
                         */
                        // chỉ resize với các file được chỉ định (thường là file ảnh)
                        if ( in_array( strtolower( $file_ext ), $arr_allow_resize ) ) {
                            $resize_img = WGR_resize_images( $file_path, $resize_path, $size );
                        }
                        // các file khác không cần resize
                        else {
                            $resize_img = [
                                'file' => basename( $file_path ),
                            ];
                        }
                        $resize_img[ 'mime-type' ] = $upload_image[ 'type' ][ $k ];
                        //print_r( $resize_img );

                        //
                        $arr_after_sizes[ $size_name ] = $resize_img;
                    }
                    //print_r( $arr_after_sizes );

                    //
                    $get_file_info = getimagesize( $file_path );
                    //print_r( $get_file_info );
                    $arr_metadata = [
                        'width' => $get_file_info[ 0 ],
                        'height' => $get_file_info[ 1 ],
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
                        'post_name' => $post_title,
                        'guid' => DYNAMIC_BASE_URL . PostType::MEDIA_URI . $file_uri,
                        'post_type' => PostType::MEDIA,
                        'post_mime_type' => $upload_image[ 'type' ][ $k ],
                    ];
                    //print_r( $data_insert );
                    $_POST[ 'post_meta' ] = [
                        '_wp_attachment_metadata' => $str_metadata,
                        '_wp_attached_file' => $file_uri,
                    ];
                    //print_r( $_POST );
                    //die( __FILE__ . ':' . __LINE__ );
                    $this->post_model->insert_post( $data_insert, $_POST[ 'post_meta' ] );

                    //
                    if ( !isset( $arr_result[ $key ] ) ) {
                        $arr_result[ $key ] = [];
                    }
                    $arr_result[ $key ][] = PostType::MEDIA_URI . $file_uri;
                }
            }
        }
        //print_r( $arr_result );
        //die( 'j ghf fd' );

        //
        return $arr_result;
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
                mkdir( $path, 0777 );
                chmod( $path, 0777 );
            }
        }

        //
        return $path;
    }

    protected function set_validation_error( $errors ) {
        //print_r( $errors );
        foreach ( $errors as $error ) {
            $this->session->setFlashdata( 'msg_error', $error );
            break;
        }
        //die( __FILE__ . ':' . __LINE__ );
    }

    // đồng bộ nội dung về 1 kiểu
    protected function replace_content( $str ) {
        $str = str_replace( '../../../public/upload/', 'upload/', $str );
        $str = str_replace( '/public/upload/', '/upload/', $str );
        $str = str_replace( base_url() . '/', '', $str );

        //
        return $str;
    }

    /*
     * trả về tên của class và loại bỏ phần namespace thừa
     */
    protected function get_class_name( $role ) {
        return basename( str_replace( '\\', '/', $role ) );
    }

    /*
     * trả về URL của controller theo định dạng của namespace
     * đầu vào là __CLASS__
     * đầu ra sẽ cắt bỏ phần namespace ở đầu, giữ lại phần controller sau -> REUQEST URL
     */
    protected function base_class_url( $str ) {
        // lấy thư mục chứa file hiện tại
        //echo __DIR__ . '<br>' . "\n";
        $current_dir = basename( __DIR__ );
        //echo $current_dir . '<br>' . "\n";

        //
        //echo $str . '<br>' . "\n";
        $str = str_replace( '\\', '/', $str );
        //echo $str . '<br>' . "\n";

        // cắt chuỗi
        $str = explode( $current_dir . '/', $str );
        //print_r( $str );

        //
        if ( isset( $str[ 1 ] ) ) {
            return strtolower( $str[ 1 ] );
        }

        //
        return strtolower( $str[ 0 ] );
    }

    /*
     * Hỗ trợ điều khiển file thông qua FTP account -> do không phải host nào cũng có thể điều khiển file bằng php thuần
     */
    protected function MY_unlink( $f ) {
        if ( @!unlink( $f ) ) {
            $file_model = new\ App\ Models\ File();

            return $file_model->FTP_unlink( $f );
        }

        //
        return true;
    }

    protected function MY_copy( $from, $to, $file_permission = 0777 ) {
        if ( @!copy( $from, $to ) ) {
            $file_model = new\ App\ Models\ File();

            return $file_model->FTP_copy( $from, $to );
        }
        if ( $file_permission > 0 ) {
            chmod( $to, $file_permission );
        }

        //
        return true;
    }
}