<?php

// Libraries
//use App\Libraries\OrderType;

// css riêng cho từng post type (nếu có)
$base_model->add_css('admin/css/' . $post_type . '.css');

//
//print_r($data);
//print_r($meta_detault);

//
include ADMIN_ROOT_VIEWS . 'posts/add_breadcrumb.php';

?>
<div class="widget-box ng-main-content" id="myApp">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="admin_global_form" onSubmit="return action_before_submit_post();" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <div class="control-group">
                <label for="data_post_title" class="control-label">ID</label>
                <div class="controls">
                    <?php echo $data['ID']; ?>
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Mã hóa đơn</label>
                <div class="controls upper">
                    <?php echo $data['post_name']; ?>
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Tiêu đề</label>
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
                <label for="data_post_title" class="control-label">Tổng tiền</label>
                <div class="controls bold">
                    <?php echo number_format($data['order_money']); ?> VNĐ
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Hạn sử dụng</label>
                <div class="controls">
                    <?php echo $data['order_period']; ?> (tháng)
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
                    <a href="admin/users/add?id=<?php echo $data['post_author']; ?>" data-id="<?php echo $data['post_author']; ?>" class="each-to-email bluecolor" target="_blank">
                        <?php echo $data['post_author']; ?>
                    </a> &nbsp;
                    <a href="admin/orders?user_id=<?php echo $data['post_author']; ?>" class="btn btn-inverse">Danh sách đơn hàng <i class="fa fa-search"></i></a>
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Ngày tạo</label>
                <div class="controls">
                    <?php echo $data['post_date']; ?>
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Ngày cập nhật</label>
                <div class="controls">
                    <?php echo $data['post_modified']; ?>
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">ID Sản phẩm</label>
                <div class="controls">
                    <?php echo $data['guid']; ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Thông tin Sản phẩm</label>
                <div class="controls">
                    <pre><code><?php echo $data['post_excerpt']; ?></code></pre>
                    <p class="controls-text-note">Khi đơn hàng được xác định thanh toán cho 1 Sản phẩm/ Dịch vụ nào đó thì sẽ bổ sung thêm thông tin Sản phẩm/ Dịch vụ tại đây.</p>
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Dữ liệu thanh toán</label>
                <div class="controls">
                    <pre><code><?php echo $data['pinged']; ?></code></pre>
                    <p class="controls-text-note">Khi đơn hàng được thanh toán tự động qua bên thứ 3, dữ liệu thanh toán sẽ được lưu tại đây.</p>
                </div>
            </div>
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

?>
<script>
    var current_post_type = '<?php echo $post_type; ?>';
    var auto_update_module = '<?php echo $auto_update_module; ?>';
    var url_next_post = '<?php echo $url_next_post; ?>';
    //var post_cat = '<?php echo $post_cat; ?>';
    //var post_tags = '<?php echo $post_tags; ?>';

    // do phần menu chưa xử lý được bằng vue-js nên vẫn phải dùng angular
    WGR_vuejs('#myApp', {
        post_status: post_arr_status,
    });
</script>
<?php

$base_model->add_js('admin/js/posts.js');
$base_model->add_js('admin/js/posts_add.js');
// css riêng cho từng post type (nếu có)
$base_model->add_js('admin/js/' . $post_type . '.js');
