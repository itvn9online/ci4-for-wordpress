<?php

namespace App\Models;

// Libraries
use App\Libraries\PostType;
// use App\Libraries\TaxonomyType;
// use App\Libraries\DeletedStatus;
use App\Libraries\PHPMaillerSend;
use App\Libraries\LanguageCost;
use App\Helpers\HtmlTemplate;
use App\Libraries\OrderType;

//
class MailQueue extends EbModel
{
    public $table = 'mail_queue';
    public $primaryKey = 'id';
    public $request = null;
    public $post_model = null;
    public $option_model = null;
    public $order_model = null;
    public $lang_model = null;

    public function __construct()
    {
        parent::__construct();

        // 
        $this->request = \Config\Services::request();
        $this->post_model = new \App\Models\Post();
        $this->option_model = new \App\Models\Option();
        $this->order_model = new \App\Models\Order();
        $this->lang_model = new \App\Models\Lang();
    }

    /**
     * Thêm mới 1 email vào hàng đợi gửi đi
     **/
    public function insertMailq($data, $add_data = [])
    {
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        // dữ liệu mặc định
        foreach ([
            'ip' => $this->request->getIPAddress(),
            'session_id' => $this->base_model->MY_sessid(),
            'agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
            'mailto' => null,
            'title' => null,
            'content' => null,
            'status' => PostType::PENDING,
            'post_id' => 0,
            'order_id' => 0,
            'created_at' => time(),
        ] as $k => $v) {
            if (!isset($data[$k])) {
                $data[$k] = $v;
            }
        }

        // dữ liệu cố định (nếu có)
        foreach ($add_data as $k => $v) {
            $data[$k] = $v;
        }

        // nếu thiếu các thông số bắt buộc
        if (empty($data['mailto']) || empty($data['title']) || empty($data['content'])) {
            // thì chuyển trạng thái thành NHÁP -> mail sẽ không được gửi đi
            $data['status'] = PostType::DRAFT;
            // return false;
        }

        // 
        return $this->base_model->insert($this->table, $data);
    }

    /**
     * Cập nhật email (thường dùng khi đã gửi xong 1 email)
     **/
    public function updateMailq($id, $data)
    {
        return $this->base_model->update_multiple($this->table, $data, [
            // WHERE
            $this->primaryKey => $id,
        ], [
            'debug_backtrace' => debug_backtrace()[1]['function'],
            // hiển thị mã SQL để check
            // 'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            // 'get_query' => 1,
            // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
            //'no_remove_field' => 1
        ]);
    }

    /**
     * Lấy email trong hàng đợi
     **/
    public function get_pending_mailq($where = [], $filter = [])
    {
        $where['status'] = PostType::PENDING;

        // 
        return $this->get_mailq($where, $filter);
    }

    /**
     * Lấy email theo điều kiện where truyền vào
     **/
    public function get_mailq($add_where = [], $add_filter = [])
    {
        // 
        $where = [
            // mặc định chỉ lấy trong vòng 24h trở lại
            'created_at >' => time() - DAY,
            // 'lang_key' => LanguageCost::lang_key(),
        ];
        foreach ($add_where as $k => $v) {
            $where[$k] = $v;
        }

        // 
        $filter = [
            'order_by' => array(
                $this->primaryKey => 'ASC',
            ),
            // hiển thị mã SQL để check
            // 'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            // trả về COUNT(column_name) AS column_name
            //'selectCount' => 'ID',
            // trả về tổng số bản ghi -> tương tự mysql num row
            //'getNumRows' => 1,
            //'offset' => 0,
            'limit' => 1
        ];
        foreach ($add_filter as $k => $v) {
            $filter[$k] = $v;
        }

        // 
        return $this->base_model->select(
            '*',
            $this->table,
            $where,
            $filter
        );
    }

    /**
     * lấy và gửi email theo session hiện tại của người dùng
     **/
    public function session_sending_mailq()
    {
        $data = $this->get_pending_mailq([
            'session_id' => $this->base_model->MY_sessid(),
            'status' => PostType::PENDING,
        ], [
            'limit' => 5,
        ]);

        // 
        if (empty($data)) {
            return false;
        }

        // 
        $smtp_config = $this->option_model->get_smtp();
        //print_r($smtp_config);

        // 
        $result = [];
        foreach ($data as $v) {
            $result[] = $v['mailto'];

            // 
            $sended = PHPMaillerSend::the_send([
                'to' => $v['mailto'],
                'subject' => $v['title'],
                'message' => $v['content'],
            ], $smtp_config);

            // 
            if ($sended === true) {
                $result[] = true;

                // 
                $this->updateMailq($v['id'], [
                    'status' => PostType::PRIVATELY,
                    // 'comment' => null,
                ]);
            } else {
                $result[] = false;

                // 
                $this->updateMailq($v['id'], [
                    'status' => PostType::DRAFT,
                    'comment' => 'Mail send faild!',
                ]);
            }
        }

        // 
        return $result;
    }

    /**
     * trả về nội dung của mail template
     **/
    public function contentMailq($key = '', $in_cache = true)
    {
        $slug = 'booking-done-mail';
        if ($key != '') {
            $slug .= '-' . $key;
        }

        // có trong cache thì lấy trong cache -> dùng key get_the_ads để cache được clear mỗi khi update ads
        $in_cache = 'get_the_ads-' . $slug;
        $data = $this->base_model->scache($in_cache);
        if ($in_cache === true && $data !== null) {
            return $data;
        }

        // chưa có thì kiểm tra trong db
        $data = $this->base_model->select(
            'ID AS post_id, post_title AS title, post_content AS content',
            'posts',
            array(
                // các kiểu điều kiện where
                'post_type' => PostType::ADS,
                'post_status' => PostType::PRIVATELY,
                'post_name' => $slug,
                'lang_key' => LanguageCost::lang_key(),
            ),
            array(
                'order_by' => array(
                    'ID' => 'DESC',
                ),
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 1
            )
        );

        // 
        if (empty($data)) {
            $data_insert = [
                'post_type' => PostType::ADS,
                'post_status' => PostType::PRIVATELY,
                'post_name' => $slug,
                // 'post_title' => str_replace('-', ' ', $slug),
                'post_title' => $this->lang_model->get_the_text('order_received_view_h1', 'Thank you. Your order has been received.'),
                'post_content' => HtmlTemplate::html('booking-done-mail.html'),
            ];
            $this->post_model->insert_post($data_insert);

            // 
            return [
                'title' => $data_insert['post_title'],
                'content' => $data_insert['post_content'],
            ];
        }
        $this->base_model->scache($in_cache, $data, HOUR);

        // 
        return $data;
    }

    /**
     * Tạo nội dung cho email gửi đi
     **/
    public function bookingDoneMail($key = '', $id = 0, $data = null)
    {
        // 
        $mail_tmp = $this->contentMailq($key);
        $str = $mail_tmp['content'];
        // TEST
        // $str = HtmlTemplate::html('booking-done-mail.html');

        // 
        if (empty($data)) {
            if ($id < 1) {
                die(__CLASS__ . ':' . __LINE__);
            }

            // 
            $data = $this->order_model->get_order(
                array(
                    // các kiểu điều kiện where
                    't1.ID' => $id,
                    // 't1.post_password' => $key,
                    // 't1.post_status' => OrderType::PENDING,
                ),
                array(
                    /*
                    'where_in' => [
                        't1.post_status' => [
                            OrderType::PENDING,
                        ],
                    ],
                    */
                    'where_not_in' => [
                        't1.post_status' => [
                            OrderType::DELETED,
                        ],
                    ],
                    'order_by' => array(
                        't1.ID' => 'DESC'
                    ),
                    // hiển thị mã SQL để check
                    // 'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    // trả về COUNT(column_name) AS column_name
                    //'selectCount' => 'ID',
                    // trả về tổng số bản ghi -> tương tự mysql num row
                    //'getNumRows' => 1,
                    //'offset' => 0,
                    'limit' => 1
                )
            );

            // 
            if (empty($data)) {
                die(__CLASS__ . ':' . __LINE__);
            }
        }

        // 
        $data['order_money'] *= 1;
        $data['order_discount'] *= 1;
        if ($data['order_discount'] < 0) {
            $data['order_discount'] = 0 - $data['order_discount'];
        }
        $data['shipping_fee'] *= 1;
        $data['order_bonus'] *= 1;
        if ($data['order_bonus'] < 0) {
            $data['order_bonus'] = 0 - $data['order_bonus'];
        }
        // 
        $data['order_amount'] = $data['order_money'] - $data['order_bonus'] - $data['order_discount'] + $data['shipping_fee'];

        // xem có phần tạm ứng trước hay không
        $deposit_money = 0;
        $deposit_balance = 0;
        if ($data['deposit_money'] != '') {
            $deposit_money = $data['deposit_money'];

            // nếu là tính theo % thì quy đổi từ tổng tiền ra deposit
            if (strpos($deposit_money, '%') !== false) {
                $deposit_money = $this->base_model->number_only($deposit_money);
                $deposit_money = $data['order_amount'] / 100 * $deposit_money;
            } else {
                $deposit_money *= 1;
            }
            $deposit_balance = $data['order_amount'] - $deposit_money;
        }

        // 
        // print_r($data);

        // 
        if (!isset($data['post_excerpt'])) {
            die(__CLASS__ . ':' . __LINE__);
        }
        $post_excerpt = json_decode($data['post_excerpt']);
        // print_r($post_excerpt);

        // 
        $product_list = '';
        foreach ($post_excerpt as $v) {
            $product_list .= '
<tr>
    <td>
        <div><img src="' . DYNAMIC_BASE_URL . $v->image . '" height="94" /></div>
        <div><strong>' . $v->post_title . ' x ' . $v->_quantity . '</strong></div>
    </td>
    <td>' . $v->_price . '</td>
</tr>';
        }

        // với phần location -> chuyển đổi từ ID sang tên
        $city_name = '';
        $state_name = '';
        $country_name = '';
        if (isset($data['city'])) {
            $city_name = $this->getCityName($data['city']);
        }
        if (isset($data['state'])) {
            $state_name = $this->getCityName($data['state']);
        }
        if (isset($data['country'])) {
            $country_name = $this->getCityName($data['country']);
        }

        // 
        $str = HtmlTemplate::render($str, [
            'year' => date('Y'),
            'current_time' => date('r'),
            'web_link' => base_url(),
            'order_link' => $this->order_model->orderReceiveToken($data['ID'], $data['post_password']),
            'product_list' => $product_list,
            // 
            'lang_total' => $this->lang_model->get_the_text('cart_sidebar_total', 'Total'),
            'lang_subtotal' => $this->lang_model->get_the_text('cart_sidebar_subtotal', 'Subtotal'),
            'lang_coupon' => $this->lang_model->get_the_text('cart_sidebar_coupon', 'Coupon'),
            'lang_bonus' => $this->lang_model->get_the_text('cart_sidebar_bonus', 'Bonus'),
            'lang_shipping' => $this->lang_model->get_the_text('cart_sidebar_shipping', 'Shipping'),
            'lang_deposit' => $this->lang_model->get_the_text('cart_sidebar_deposit_money', 'Deposit'),
            'lang_remaining_amount' => $this->lang_model->get_the_text('cart_sidebar_deposit_balance', 'Remaining amount'),
            'the_title' => $this->lang_model->get_the_text('order_received_view_h1', 'Thank you. Your order has been received.'),
            // 
            'deposit_balance' => $deposit_balance,
            'city_name' => $city_name,
            'state_name' => $state_name,
            'country_name' => $country_name,
            'city' => $city_name,
            'state' => $state_name,
            'country' => $country_name,
            // 
            'post_date' => date(EBE_DATE_FORMAT . ' H:i:s', strtotime($data['post_date'])),
            'ip' => $data['order_ip'],
            'agent' => $data['order_agent'],
        ], $data, '%', '%');
        // print_r($str);

        // 
        // return $str;
        return [
            'title' => $mail_tmp['title'],
            'content' => $str,
        ];
    }

    /**
     * lấy tên địa điểm dựa theo id
     **/
    protected function getCityName($id)
    {
        // 
        if (empty($id) || !is_numeric($id)) {
            return null;
        }

        // 
        $data = $this->base_model->select(
            'name',
            'terms',
            array(
                // các kiểu điều kiện where
                'term_id' => $id,
            ),
            array(
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 1
            )
        );

        // 
        if (empty($data)) {
            return null;
        }
        // print_r($data);

        // 
        return $data['name'];
    }
}
