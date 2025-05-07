<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\LanguageCost;
use App\Libraries\ConfigType;
use App\Libraries\DeletedStatus;
use App\Libraries\PHPMaillerSend;
use App\ThirdParty\TelegramBot;

//
class Config404s extends Sadmin
{
    protected $view_edit = 'edit';

    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        // 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $link_id = $this->MY_post('link_id');
            $link_image = trim($this->MY_post('link_image'));

            // 
            if (!empty($link_image)) {
                $link_image = explode($_SERVER['HTTP_HOST'] . '/', $link_image);
                if (isset($link_image[1])) {
                    $link_image = '/' . $link_image[1];
                } else {
                    $link_image = $link_image[0];
                }
            }

            // 
            $msg_error = '';
            if (!empty($link_id)) {
                $result_id = $this->base_model->update_multiple('links', [
                    // SET
                    'link_image' => $link_image,
                ], [
                    // WHERE
                    'link_id' => $link_id,
                ], [
                    'debug_backtrace' => debug_backtrace()[1]['function'],
                    // hiển thị mã SQL để check
                    // 'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    // 'get_query' => 1,
                    // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
                    // 'no_remove_field' => 1
                ]);

                if ($result_id !== false) {
                    $this->base_model->result_json_type([
                        'code' => __LINE__,
                        'msg' => 'Cập nhật thành công!',
                        'link_image' => $link_image,
                        'link_id' => $link_id,
                    ]);
                }
            } else {
                $msg_error = 'link_id không được để trống!';
            }

            // 
            $this->base_model->result_json_type([
                'code' => __LINE__,
                'error' => $msg_error,
            ]);
        }

        // dọn dẹp dữ liệu cũ
        $cleanup = $this->base_model->scache('cleanup_config404s');
        if ($cleanup === null) {
            $this->base_model->delete_multiple('links', [
                // WHERE
                'link_image' => '',
                'link_updated <' => date(EBE_DATETIME_FORMAT, strtotime('-1 month')),
            ], [
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                // 'get_query' => 1,
            ]);

            // lưu cache -> ko dọn liên tục
            $this->base_model->scache('cleanup_config404s', date('r'), DAY);
        }

        // 
        $where_like = [];
        $by_keyword = trim($this->MY_get('s'));
        if ($by_keyword != '') {
            $where_like = [
                'link_name' => $by_keyword,
            ];
        }

        // 
        $data = $this->base_model->select(
            [
                // các trường cần lấy ra
                'link_id',
                'link_url',
                'link_name',
                'link_image',
                'link_target',
                'link_rel',
                'link_updated',
                'link_notes',
            ],
            'links',
            array(
                // các kiểu điều kiện where
                // 'member_type' => UsersType::MEMBER,
            ),
            array(
                'like_before' => $where_like,
                'order_by' => array(
                    'link_id' => 'DESC',
                ),
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                // 'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                // 'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                // 'getNumRows' => 1,
                // 'offset' => 0,
                'limit' => 100,
            )
        );
        // print_r($data);

        // 
        $this->teamplate_admin['content'] = view(
            'vadmin/config404s/' . $this->view_edit,
            array(
                // 'lang_key' => $this->lang_key,
                'data' => $data,
                'by_keyword' => $by_keyword,
            )
        );
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }
}
