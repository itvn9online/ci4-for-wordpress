<?php

namespace App\Controllers;

// Libraries
use App\Libraries\MediaType;

//
class Uploads extends Users
{
    // định dạng file được phép upload
    public $allow_image_type = MediaType::IMAGE_MIME_TYPE;
    public $allow_media_type = MediaType::ALLOW_MIME_TYPE;

    public function __construct()
    {
        parent::__construct();
    }

    // chức năng upload ảnh thông qua ajax
    public function image_push($dir = '')
    {
        //
        $img = $this->MY_post('img', '');
        if (empty($img)) {
            $this->result_json_type([
                'in' => __CLASS__,
                'code' => __LINE__,
                'error' => 'img EMPTY!'
            ]);
        }

        // tên file
        $file_name = $this->MY_post('file_name', '');
        if (empty($file_name)) {
            $this->result_json_type([
                'in' => __CLASS__,
                'code' => __LINE__,
                'error' => 'file name EMPTY!'
            ]);
        }

        // thời gian chỉnh sửa file
        $last_modified = $this->MY_post('last_modified', '');
        // tên file lấy theo thời gian chỉnh sửa -> nếu không có gì khác bọt thì khỏi upload lại
        $format_modified = 'ymdHis';
        if (empty($last_modified)) {
            $last_modified = time();
            //$format_modified = 'ymdH';
        }

        // thêm ngày tháng năm vào tên file để tránh trùng lặp -> cho ngày tháng về trước vì còn liên quan đến sắp xếp file -> ko insert db nên ko order được
        $file_name =  date($format_modified, $last_modified) . '-' . $file_name;

        //
        $upload_path = $this->get_path_upload($this->current_user_id, $dir);
        //$this->result_json_type(['upload_path' => $upload_path]);

        //
        $file_type = 'jpg';
        if (strpos($img, 'data:image/png;') !== false) {
            $file_type = 'png';
            $img = str_replace('data:image/png;base64,', '', $img);
        } else {
            $img = str_replace('data:image/jpeg;base64,', '', $img);
            $img = str_replace('data:image/jpg;base64,', '', $img);
        }
        $img = str_replace(' ', '+', $img);
        $file_path = $upload_path . $file_name . '.' . $file_type;

        //
        $file_thumb_path = $upload_path . 'thumb/';
        if (!is_dir($file_thumb_path)) {
            mkdir($file_thumb_path, 0777);
        }
        $file_thumb_path .= $file_name . '.' . $file_type;

        //
        $file_medium_path = $upload_path . 'medium/';
        if (!is_dir($file_medium_path)) {
            mkdir($file_medium_path, 0777);
        }
        $file_medium_path .= $file_name . '.' . $file_type;

        //
        $file_large_path = $upload_path . 'medium_large/';
        if (!is_dir($file_large_path)) {
            mkdir($file_large_path, 0777);
        }
        $file_large_path .= $file_name . '.' . $file_type;

        //
        //$this->result_json_type( [ $img ] ); // TEST
        //$this->result_json_type([$file_path]); // TEST
        //$this->result_json_type([$file_thumb_path]); // TEST

        //
        $success = 0;
        $mime_type = $file_type;
        if (!file_exists($file_path)) {
            $success = $this->base_model->eb_create_file($file_path, base64_decode($img));

            // kiểm tra định dạng file -> chỉ chấp nhận định dạng jpeg
            $mime_type = mime_content_type($file_path);

            if (!in_array($mime_type, $this->allow_image_type)) {
                unlink($file_path);

                //
                $this->result_json_type([
                    'in' => __CLASS__,
                    'code' => __LINE__,
                    'error' => 'mime type not support! ' . $mime_type
                ]);
            }
        }

        // resize ảnh để chạy cho mượt
        $arr_sizes = MediaType::media_size();
        if (!file_exists($file_thumb_path)) {
            $rs = \App\Libraries\MyImage::resize($file_path, $file_thumb_path, $arr_sizes[MediaType::MEDIA_THUMBNAIL]);
            chmod($file_thumb_path, 0777);
        }
        if (!file_exists($file_medium_path)) {
            $rs = \App\Libraries\MyImage::resize($file_path, $file_medium_path, $arr_sizes[MediaType::MEDIA_MEDIUM]);
            chmod($file_medium_path, 0777);
        }
        if (!file_exists($file_large_path)) {
            $rs = \App\Libraries\MyImage::resize($file_path, $file_large_path, $arr_sizes[MediaType::MEDIA_MEDIUM_LARGE]);
            chmod($file_large_path, 0777);
        }
        $img_webp = $file_medium_path;
        if (file_exists($file_medium_path)) {
            $create_webp = \App\Libraries\MyImage::webpConvert($file_medium_path);
            if ($create_webp != '') {
                $img_webp = $create_webp;
            }
        }

        //
        $img_medium = str_replace(PUBLIC_PUBLIC_PATH, '', $file_medium_path);
        $img_webp = str_replace(PUBLIC_PUBLIC_PATH, '', $img_webp);

        // cập nhật luôn avatar cho tài khoản này
        $update_avt = $this->MY_post('update_avt', 0);
        $result_id = $update_avt;
        if ($update_avt * 1 > 0) {
            $result_id = $this->base_model->update_multiple('users', [
                // SET
                'avatar' => $img_webp,
            ], [
                // WHERE
                'ID' => $this->current_user_id,
            ], [
                //'debug_backtrace' => debug_backtrace()[1]['function'],
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
                //'no_remove_field' => 1
            ]);
            $result_id = __LINE__; // TEST
        }

        //
        $this->result_json_type([
            'user' => $this->current_user_id,
            'mime_type' => $mime_type,
            'img' => str_replace(PUBLIC_PUBLIC_PATH, '', $file_path),
            //'img_large' => str_replace(PUBLIC_PUBLIC_PATH, '', $file_path),
            'img_large' => str_replace(PUBLIC_PUBLIC_PATH, '', $file_large_path),
            'img_thumb' => str_replace(PUBLIC_PUBLIC_PATH, '', $file_thumb_path),
            'img_webp' => $img_webp,
            'img_medium' => $img_medium,
            'success' => $success,
            'file_name' => $file_name,
            'file_type' => $file_type,
            'dir' => $dir,
            'last_modified' => $last_modified,
            'result_id' => $result_id,
            'update_avt' => $update_avt,
        ]);
    }

    // upload ảnh đại diện cho user
    public function avatar_push()
    {
        return $this->image_push('profile');
    }

    // upload các ảnh khác thì mặc định cho vào thư mục gallery
    public function gallery_push()
    {
        return $this->image_push('gallery');
    }
}
