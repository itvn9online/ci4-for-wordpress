<?php

namespace App\Controllers\Traits;

use App\Libraries\PostType;

//
trait LayoutMediaTrait
{
    /**
     * Upload giả lập wordpress
     */
    protected function deny_visit_upload($upload_root = '', $remove_file = false, $hotlink_protection = false)
    {
        if ($upload_root == '') {
            $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        }
        $upload_root = rtrim($upload_root, '/') . '/';

        //
        $htaccess_file = $upload_root . '.htaccess';
        //die($htaccess_file);
        //echo $htaccess_file . '<br>' . "\n";

        // cập nhật lại nội dung file htaccess
        if ($remove_file === true && is_file($htaccess_file)) {
            $this->MY_unlink($htaccess_file);
        }

        //
        if (!is_file($htaccess_file)) {
            // tạo hotlink protection nếu có yêu cầu
            $hotlink_protection = '';
            if ($hotlink_protection === true) {
                $hotlink_protection = $this->helpersTmpFile(
                    'hotlink_protection',
                    [
                        'http_host' => $_SERVER['HTTP_HOST'],
                        'htaccess_allow' => HTACCESSS_ALLOW,
                    ]
                );
            }

            // nội dung chặn mọi truy cập tới các file trong này
            $this->base_model->ftp_create_file(
                $htaccess_file,
                // tạo file htaccess chỉ cho phép truy cập tới 1 số file được chỉ định
                $this->helpersTmpFile(
                    'htaccess_allow_deny',
                    [
                        'htaccess_allow' => HTACCESSS_ALLOW,
                        'created_from' => __CLASS__ . ':' . __LINE__,
                        'base_url' => DYNAMIC_BASE_URL,
                        'hotlink_protection' => $hotlink_protection,
                    ]
                ),
            );
        }
        //die( __CLASS__ . ':' . __LINE__ );

        //
        return $htaccess_file;
    }

    protected function media_upload($allow_upload = [], $md5 = false)
    {
        // print_r($_POST);
        // print_r($_FILES);
        // die(__CLASS__ . ':' . __LINE__);

        //
        $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        // echo $upload_root . '<br>' . "\n";

        //
        $this->deny_visit_upload($upload_root);

        //
        $upload_path = $this->media_path(
            [
                date('Y'),
                date('m'),
            ],
            $upload_root
        );
        // echo $upload_path . '<br>' . "\n";

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
        if ($upload_files = $this->request->getFiles()) {
            // print_r($upload_files);
            // die(__CLASS__ . ':' . __LINE__);

            // chạy vòng lặp để lấy các key upload -> xác định tên input tự động
            foreach ($_FILES as $key => $upload_image) {
                // echo $key . '<br>' . "\n";
                // print_r($upload_image);

                //
                // $multi_up = true;
                // không có size -> bỏ
                if (!isset($upload_image['size'])) {
                    continue;
                } else {
                    // size là dạng mảng -> multi upload
                    if (is_array($upload_image['size'])) {
                        // size quá nhỏ -> bỏ
                        if (empty($upload_image['size']) || $upload_image['size'][0] < 1) {
                            continue;
                        }
                    } else {
                        // $multi_up = false;
                        // giả lập muti up -> để lệnh sau có thể hoạt động được
                        $upload_files[$key] = [$upload_files[$key]];

                        //
                        if ($upload_image['size'] < 1) {
                            // size quá nhỏ -> bỏ
                            continue;
                        }
                    }
                }
                // print_r($upload_files[$key]);
                // die(__CLASS__ . ':' . __LINE__);

                //
                foreach ($upload_files[$key] as $file) {
                    // print_r($file);
                    // die(__CLASS__ . ':' . __LINE__);
                    if ($file->isValid() && !$file->hasMoved()) {
                        $file_name = $this->MY_get('set_filename');
                        if (empty($file_name)) {
                            $file_name = $file->getName();
                        } else {
                            // lấy phần mơng của file
                            $file_ext = $file->getClientExtension();
                            // return [$file_ext];
                            $file_name = $file_name . '.' . $file_ext;
                        }
                        // return [$file_name];
                        // echo $file_name . '<br>' . "\n";
                        $file_name = $this->base_model->_eb_non_mark_seo($file_name);
                        $file_name = sanitize_filename($file_name);
                        // khi cần bảo mật tên file thì thực hiện md5 cho nó
                        if ($md5 !== false) {
                            $file_name = md5($file_name) . '-' . md5(time()) . '-' . $file_name;
                        }
                        // echo $file_name . '<br>' . "\n";

                        // kiểm tra định dạng file
                        $mime_type = $file->getMimeType();
                        // echo $mime_type . '<br>' . "\n";
                        // continue;

                        //
                        $file_ext = $file->guessExtension();
                        // echo $file_ext . '<br>' . "\n";
                        if (empty($file_ext)) {
                            $this->result_json_type(
                                [
                                    'code' => __LINE__,
                                    'error' => 'Định dạng file chưa được hỗ trợ ' . $mime_type
                                ]
                            );
                            // $file_ext = basename($mime_type);
                        }
                        $file_ext = strtolower($file_ext);

                        //
                        $file_path = $upload_path . $file_name;
                        // echo $file_path . '<br>' . "\n";

                        // kiểm tra lại ext -> vì có 1 trường hợp mime type khác với ext truyền vào
                        $check_ext = pathinfo($file_path, PATHINFO_EXTENSION);
                        // echo $check_ext . '<br>' . "\n";

                        //
                        if ($check_ext != $file_ext) {
                            // $this->base_model->alert('Định dạng file không khớp nhau! ' . $check_ext . ' != ' . $file_ext, 'error');
                            $this->base_model->msg_error_session('Định dạng file không khớp nhau! ' . $check_ext . ' != ' . $file_ext);
                            continue;
                        }

                        // đổi tên file nếu file đã tồn tại
                        if (is_file($file_path)) {
                            for ($i = 1; $i < 100; $i++) {
                                $file_new_name = basename($file_name, '.' . $file_ext) . '_' . $i . '.' . $file_ext;
                                $file_path = $upload_path . $file_new_name;
                                // echo $file_path . '<br>' . "\n";
                                if (!is_file($file_path)) {
                                    $file_name = basename($file_path);
                                    break;
                                }
                            }
                        }
                        // echo $file_path . '<br>' . "\n";

                        // nếu không phải file ảnh
                        $check_mime_type = strtolower(explode('/', $mime_type)[0]);
                        $is_image = true;
                        if ($check_mime_type != 'image') {
                            $is_image = false;
                            $media_mime_type = [
                                'audio',
                                'video',
                            ];
                            // hỗ trợ up video, audio
                            if (in_array($check_mime_type, $media_mime_type)) {
                                //
                            }
                            // các file khác chưa xác định thì cứ gọi là bỏ qua đã
                            else {
                                // thêm vào tệp mở rộng để không cho truy cập file trực tiếp
                                $file_other_ext = 'daidq-ext';
                                $file_new_path = $file_path . '.' . $file_other_ext;
                                // echo $file_new_path . '<br>' . "\n";
                                if (is_file($file_new_path)) {
                                    for ($i = 1; $i < 100; $i++) {
                                        $file_new_path = $file_path . '.' . $file_other_ext . '_' . $i;
                                        // echo $file_new_path . '<br>' . "\n";
                                        if (!is_file($file_new_path)) {
                                            $file_path = $file_new_path;
                                            break;
                                        }
                                    }
                                } else {
                                    $file_path = $file_new_path;
                                }
                                // echo $file_path . '<br>' . "\n";
                                $file_name = basename($file_path);
                                // echo $file_name . '<br>' . "\n";
                                // die( __CLASS__ . ':' . __LINE__ );
                            }
                        }
                        // echo $file_path . '<br>' . "\n";

                        // nếu có kiểm duyệt định dạng file -> chỉ các file trong này mới được upload
                        if (!empty($allow_upload) && !in_array($file_ext, $allow_upload)) {
                            continue;
                        }
                        // nếu không, sẽ chặn các định dạng file có khả năng thực thi lệnh từ server
                        else if (in_array($file_ext, $arr_block_upload)) {
                            continue;
                        }

                        //
                        $file->move($upload_path, $file_name, true);

                        //
                        if (!is_file($file_path)) {
                            continue;
                        }
                        chmod($file_path, DEFAULT_FILE_PERMISSION);

                        //
                        if (!isset($arr_result[$key])) {
                            $arr_result[$key] = [];
                        }

                        //
                        $metadata = $this->media_attachment_metadata($file_path, $file_ext, $upload_path, $mime_type, $upload_root);

                        // optimize file gốc
                        if ($is_image === true) {
                            $new_quality = \App\Libraries\MyImage::quality($file_path);
                        }

                        //
                        if ($metadata !== false) {
                            $arr_result[$key][] = $metadata['file_uri'];
                        }
                    } else {
                        throw new \RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
                    }
                }
            }
            // die(__CLASS__ . ':' . __LINE__);
        }
        // print_r($arr_result);
        // die(__CLASS__ . ':' . __LINE__);

        //
        return $arr_result;
    }

    // tạo thumbnail cho hình ảnh dựa theo path
    protected function media_attachment_metadata($file_path, $file_ext = '', $upload_path = '', $mime_type = '', $upload_root = '', $post_parent = 0)
    {
        if (!is_file($file_path)) {
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
        if ($upload_root == '') {
            $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        }
        //echo $upload_root . '<br>' . "\n";
        if ($upload_path == '') {
            $upload_path = dirname($file_path) . '/';
        }
        //echo $upload_path . '<br>' . "\n";

        //
        $file_uri = str_replace($upload_root, '', $file_path);
        //echo $file_uri . '<br>' . "\n";

        //
        //echo $file_ext . '<br>' . "\n";
        if ($file_ext == '') {
            $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
        }
        $file_ext = strtolower($file_ext);
        //die($file_ext);
        //echo $file_ext . '<br>' . "\n";

        //
        if ($mime_type == '') {
            $mime_type = mime_content_type($file_path);
        }
        //echo $mime_type . '<br>' . "\n";

        // nếu không phải file ảnh
        $is_image = true;
        if (strtolower(explode('/', $mime_type)[0]) != 'image') {
            $is_image = false;
        }

        //
        $post_title = basename($file_path, '.' . $file_ext);
        //echo $post_title . '<br>' . "\n";

        //
        $arr_list_size = PostType::media_size();
        // chỉ resize file ảnh
        $arr_allow_resize = [
            'bmp',
            'png',
            'jpg',
            'jpeg'
        ];

        // giả lập dữ liệu giống wordpress
        $arr_after_sizes = [];
        if ($is_image == true) {
            $get_file_info = getimagesize($file_path);
        } else {
            $get_file_info = [
                0,
                0
            ];
        }
        //print_r( $get_file_info );
        $file_size = filesize($file_path);
        //echo $file_size . '<br>' . "\n";
        //die( __CLASS__ . ':' . __LINE__ );
        foreach ($arr_list_size as $size_name => $size) {
            $resize_path = $upload_path . $post_title . '-' . $size_name . '.' . $file_ext;
            //echo $resize_path . '<br>' . "\n";
            //die( __CLASS__ . ':' . __LINE__ );
            //continue;

            /**
             * Sử dụng class tự viết hoặc tham kháo thư viện của CI3
             * https://codeigniter.com/userguide3/libraries/image_lib.html
             */
            // chỉ resize với các file được chỉ định (thường là file ảnh)
            if (in_array($file_ext, $arr_allow_resize)) {
                $resize_img = \App\Libraries\MyImage::resize($file_path, $resize_path, $size);
            }
            // các file khác không cần resize
            else {
                if (empty($get_file_info)) {
                    $get_file_info = [
                        0,
                        0
                    ];
                }
                //var_dump($is_image);
                //print_r($get_file_info);
                //die(__CLASS__ . ':' . __LINE__);
                $resize_img = [
                    'width' => $get_file_info[0],
                    'height' => $get_file_info[1],
                    'file_size' => $file_size,
                    'file' => basename($file_path),
                ];
            }
            $resize_img['mime-type'] = $mime_type;
            //print_r( $resize_img );

            //
            $arr_after_sizes[$size_name] = $resize_img;
        }
        //print_r( $arr_after_sizes );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        //print_r( $get_file_info );
        $arr_metadata = [
            'width' => $get_file_info[0],
            'height' => $get_file_info[1],
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
        // print_r($arr_metadata);
        $str_metadata = serialize($arr_metadata);
        // echo $str_metadata . '<br>' . "\n";
        // $test = unserialize($str_metadata);
        // print_r($test);

        //
        $data_insert = [
            'post_title' => $post_title,
            'post_status' => PostType::INHERIT,
            //'post_name' => $post_title,
            'post_name' => str_replace('.', '-', PostType::MEDIA_URI . $file_uri),
            'guid' => DYNAMIC_BASE_URL . PostType::MEDIA_URI . $file_uri,
            'post_type' => PostType::MEDIA,
            'post_mime_type' => $mime_type,
            'post_parent' => $post_parent,
        ];
        // print_r($data_insert);
        $_POST['post_meta'] = [
            '_wp_attachment_metadata' => $str_metadata,
            '_wp_attached_file' => $file_uri,
        ];
        // print_r($_POST);
        // die(__CLASS__ . ':' . __LINE__);
        $result_id = $this->post_model->insert_post($data_insert, $_POST['post_meta']);
        // print_r($result_id);
        if (is_array($result_id) && isset($result_id['error'])) {
            $this->base_model->alert($result_id['error'], 'error');
        }
        // die(__CLASS__ . ':' . __LINE__);
        // echo 'Result id: ' . $result_id . '<br>' . "\n";

        //
        return [
            'file_uri' => PostType::MEDIA_URI . $file_uri,
            'is_image' => $is_image,
            'metadata' => $arr_metadata,
            'data' => $data_insert,
            'meta' => $_POST['post_meta'],
        ];
    }

    // tạo path upload
    protected function media_path($data = [], $path = '')
    {
        if ($path == '') {
            //$path = PUBLIC_HTML_PATH . PostType::MEDIA_URI;
            $path = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        }
        foreach ($data as $v) {
            $path .= $v . '/';
            //echo $path . '<br>' . "\n";

            if (!is_dir($path)) {
                mkdir($path, DEFAULT_DIR_PERMISSION) or die('ERROR create dir (' . __CLASS__ . ':' . __LINE__ . ')! ' . $path);
                chmod($path, DEFAULT_DIR_PERMISSION);
            }
        }

        //
        return $path;
    }
}
