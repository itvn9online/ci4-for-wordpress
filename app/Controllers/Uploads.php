<?php
namespace App\Controllers;

// Libraries
//use App\Libraries\PostType;

//
class Uploads extends Users
{
    // định dạng file được phép upload
    public $allow_mime_type = [
        'image/jpeg',
        'image/jpg',
        'image/png',
    ];

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
        $file_name = $this->MY_post('file_name', '');
        if (empty($file_name)) {
            $this->result_json_type([
                'in' => __CLASS__,
                'code' => __LINE__,
                'error' => 'file name EMPTY!'
            ]);
        }

        // thêm ngày tháng năm vào tên file để tránh trùng lặp
        $file_name = date('ymd') . '-' . $file_name;

        //
        $upload_path = $this->get_path_upload($this->current_user_id, $dir);
        //$this->result_json_type(['upload_path' => $upload_path]);

        //
        $file_type = 'jpg';
        if (strpos($img, 'data:image/png;') !== false) {
            $file_type = 'png';
            $img = str_replace('data:image/png;base64,', '', $img);
        }
        else {
            $img = str_replace('data:image/jpeg;base64,', '', $img);
            $img = str_replace('data:image/jpg;base64,', '', $img);
        }
        $img = str_replace(' ', '+', $img);
        $file_path = $upload_path . $file_name . '.' . $file_type;
        $file_large_path = $file_path;
        $file_thumb_path = $upload_path . $file_name . '-thumb.' . $file_type;

        //
        //$this->result_json_type( [ $img ] ); // TEST
        //$this->result_json_type([$file_path]); // TEST
        //$this->result_json_type([$file_thumb_path]); // TEST

        //
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $success = file_put_contents($file_path, base64_decode($img));
        chmod($file_path, 0777);

        // kiểm tra định dạng file -> chỉ chấp nhận định dạng jpeg
        $mime_type = mime_content_type($file_path);

        if (!in_array($mime_type, $this->allow_mime_type)) {
            unlink($file_path);

            //
            $this->result_json_type([
                'in' => __CLASS__,
                'code' => __LINE__,
                'error' => 'mime type not support! ' . $mime_type
            ]);
        }

        // resize ảnh để chạy cho mượt
        if (file_exists($file_thumb_path)) {
            unlink($file_thumb_path);
        }
        $resize_img = \App\Libraries\MyImage::resize($file_large_path, $file_thumb_path, 220);
        chmod($file_thumb_path, 0777);

        //
        $img_thumb = str_replace(PUBLIC_PUBLIC_PATH, '', $file_thumb_path);

        //
        $this->result_json_type([
            'user' => $this->current_user_id,
            'img' => str_replace(PUBLIC_PUBLIC_PATH, '', $file_path),
            'mime_type' => $mime_type,
            'img_large' => str_replace(PUBLIC_PUBLIC_PATH, '', $file_large_path),
            'img_thumb' => $img_thumb,
            'success' => $success,
            'file_name' => $file_name,
        ]);
    }

    // upload ảnh đại diện cho user
    public function avatar_push()
    {
        return $this->image_push('profile');
    }
}