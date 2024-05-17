<?php

// Libraries
//use App\Libraries\OrderType;

// css riêng cho từng post type (nếu có)
$base_model->add_css('wp-admin/css/' . $post_type . '.css');

//
if (isset($_GET['print_data'])) {
    echo '<!-- ';
    print_r($data);
    print_r($meta_default);
    echo ' -->';
}

//
include ADMIN_ROOT_VIEWS . 'posts/add_breadcrumb.php';

?>
<div class="widget-box ng-main-content" id="myApp">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="admin_global_form" onSubmit="return action_before_submit_post();" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <div class="control-group">
                <label class="control-label">ID</label>
                <div class="controls"><?php echo $data['ID']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Mã hóa đơn</label>
                <div class="controls upper"><?php echo $data['post_name']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Tiêu đề</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Tiêu đề" name="data[post_title]" id="data_post_title" value="<?php $base_model->the_esc_html($data['post_title']); ?>" autofocus aria-required="true" required />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Nội dung</label>
                <div class="controls f80">
                    <textarea placeholder="Nội dung" name="data[post_content]" id="data_post_content" class="span30 fix-textarea-height"><?php echo $data['post_content']; ?></textarea>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Coupon code</label>
                <div class="controls bold"><?php echo $data['coupon']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Tổng tiền</label>
                <div class="controls bold">
                    <span class="ebe-currency-format"><?php echo $data['order_money']; ?></span>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Discount</label>
                <div class="controls bold">
                    <span class="ebe-currency-format"><?php echo $data['order_discount']; ?></span>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Bonus</label>
                <div class="controls bold">
                    <span class="ebe-currency-format"><?php echo $data['order_bonus']; ?></span>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Hạn sử dụng</label>
                <div class="controls"><?php echo $data['order_period']; ?> (tháng)</div>
            </div>
            <div class="control-group">
                <label class="control-label">Deposit</label>
                <div class="controls bold">
                    <span class="ebe-currency-format"><?php echo $data['deposit_value']; ?></span>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Shipping fee</label>
                <div class="controls bold">
                    <span class="ebe-currency-format"><?php echo $data['shipping_fee']; ?></span>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Trạng thái</label>
                <div class="controls">
                    <select data-select="<?php echo $data['post_status']; ?>" name="data[post_status]" class="span3">
                        <option :value="k" v-for="(v, k) in post_status">{{v}}</option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Thành viên</label>
                <div class="controls">
                    <a href="sadmin/users/add?id=<?php echo $data['post_author']; ?>" data-id="<?php echo $data['post_author']; ?>" class="each-to-email bluecolor" target="_blank"><?php echo $data['post_author']; ?></a> &nbsp;
                    <a href="sadmin/orders?user_id=<?php echo $data['post_author']; ?>" class="btn btn-inverse">Danh sách đơn hàng <i class="fa fa-search"></i></a>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">First name</label>
                <div class="controls"><?php echo $data['first_name']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Last name</label>
                <div class="controls"><?php echo $data['last_name']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Full name</label>
                <div class="controls"><?php echo $data['full_name']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Phone</label>
                <div class="controls"><?php echo $data['phone']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Company</label>
                <div class="controls"><?php echo $data['company']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Address</label>
                <div class="controls"><?php echo $data['address']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Ngày tạo</label>
                <div class="controls"><?php echo $data['post_date']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">Ngày cập nhật</label>
                <div class="controls"><?php echo $data['post_modified']; ?></div>
            </div>
            <div class="control-group">
                <label class="control-label">ID Sản phẩm</label>
                <div class="controls">
                    <?php
                    if ($data['post_parent'] > 0) {
                        // lấy thông tin sản phẩm
                        $post_parent_data = $base_model->select('*', 'posts', [
                            'ID' => $data['post_parent']
                        ], [
                            // 'show_query' => 1,
                            'limit' => 1,
                        ]);
                        // print_r($post_parent_data);
                        // die(__FILE__ . ':' . __LINE__);

                        //
                        if (!empty($post_parent_data)) {
                    ?>
                            <a href="<?php $post_model->the_post_permalink($post_parent_data); ?>" class="bluecolor" target="_blank">#<?php echo $data['post_parent']; ?> | <?php echo $post_parent_data['post_title']; ?> | <?php echo $post_parent_data['post_permalink']; ?></a>
                    <?php
                        }
                    } else {
                        echo $data['post_parent'];
                    }
                    ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Thông tin Sản phẩm</label>
                <div class="controls">
                    <pre><code><?php echo $data['post_excerpt']; ?></code></pre>
                    <p class="controls-text-note">Khi đơn hàng được xác định thanh toán cho 1 Sản phẩm/ Dịch vụ nào đó thì sẽ bổ sung thêm thông tin Sản phẩm/ Dịch vụ tại đây.</p>
                </div>
            </div>
            <?php
            if (!empty($data['pinged'])) {
            ?>
                <div class="control-group">
                    <label class="control-label">Dữ liệu thanh toán</label>
                    <div class="controls">
                        <pre><code><?php echo $data['pinged']; ?></code></pre>
                        <p class="controls-text-note">Khi đơn hàng được thanh toán tự động qua bên thứ 3, dữ liệu thanh toán sẽ được lưu tại đây.</p>
                    </div>
                </div>
            <?php
            }
            if (!empty($data['approve_data'])) {
            ?>
                <div class="control-group">
                    <label class="control-label">Approve data</label>
                    <div class="controls">
                        <pre><code><?php echo $data['approve_data']; ?></code></pre>
                        <p class="controls-text-note">Lưu trữ thông tin tóm tắt transaction gửi về từ Paypal...</p>
                    </div>
                </div>
            <?php
            }
            if (!empty($data['order_capture'])) {
            ?>
                <div class="control-group">
                    <label class="control-label">Order capture</label>
                    <div class="controls">
                        <pre><code><?php echo $data['order_capture']; ?></code></pre>
                        <p class="controls-text-note">Lưu trữ thông tin transaction gửi về từ Paypal.</p>
                    </div>
                </div>
            <?php
            }
            ?>
            <?php

            //
            include ADMIN_ROOT_VIEWS . 'posts/add_submit.php';

            ?>
        </form>
    </div>
</div>
<?php

//
$base_model->JSON_parse([
    'post_arr_status' => $post_arr_status,
]);

//
$base_model->JSON_echo([
    // mảng này sẽ in ra dưới dạng JSON hoặc number
    'auto_update_module' => $auto_update_module,
], [
    // mảng này sẽ in ra dưới dạng string
    'current_post_type' => $post_type,
    'url_next_post' => $url_next_post,
    //'post_cat' => $post_cat,
    //'post_tags' => $post_tags,
]);

?>
<script type="text/javascript">
    // do phần menu chưa xử lý được bằng vue-js nên vẫn phải dùng angular
    WGR_vuejs('#myApp', {
        post_status: post_arr_status,
    });
</script>
<?php

$base_model->add_js('wp-admin/js/posts.js');
$base_model->add_js('wp-admin/js/posts_add.js');
// css riêng cho từng post type (nếu có)
$base_model->add_js('wp-admin/js/' . $post_type . '.js');
