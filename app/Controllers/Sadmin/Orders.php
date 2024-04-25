<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\OrderType;

//
class Orders extends Posts
{
    protected $post_type = OrderType::ORDER;

    // tham số dùng để thay đổi bảng cần gọi dữ liệu
    public $table = 'orders';
    public $metaTable = 'ordermeta';
    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'orders';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_view_path = 'orders';
    // tham số dùng để đổi file view khi xem danh sách bài viết nếu muốn
    protected $list_view_path = 'orders';
    public $order_model = null;
    public $mail_queue_model = null;

    //
    public function __construct()
    {
        parent::__construct();

        //
        $this->order_model = new \App\Models\Order();
        $this->mail_queue_model = new \App\Models\MailQueue();

        //
        $this->post_arr_status = OrderType::arrStatus();
    }

    protected function updating($id)
    {
        // cập nhật thông tin đơn hàng
        parent::updating($id);

        // -> cập nhật lại số dư của user trong cache
        return $this->order_model->cache_user_fund($id);
    }

    /**
     * Xác định mail template để tiện edit
     **/
    public function find_mail_template($auto_redirect = true)
    {
        $type = $this->MY_get('type');
        if ($type != '') {
            if ($type != 'author' && $type != 'admin') {
                $type = '';
            }
        }
        $data = $this->mail_queue_model->contentMailq($type, false);
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        // 
        if (isset($data['post_id']) && !empty($data['post_id'])) {
            die(header('Location: ' . base_url('sadmin/adss/add') . '?id=' . $data['post_id']));
        } else if ($auto_redirect === true) {
            return $this->find_mail_template(false);
        }

        // 
        return false;
    }
}
