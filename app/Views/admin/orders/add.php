<?php

// Libraries
use App\Libraries\OrderType;

// css riêng cho từng post type (nếu có)
$base_model->add_css('admin/css/' . $post_type . '.css');

//
//print_r( $data );

//
include $admin_root_views . 'posts/add_breadcrumb.php';

?>
<div class="widget-box ng-main-content" id="myApp">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="admin_global_form"
            onSubmit="return action_before_submit_post();" accept-charset="utf-8" class="form-horizontal"
            target="target_eb_iframe">
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
                    <input type="text" class="span6 required" placeholder="Tiêu đề" name="data[post_title]"
                        id="data_post_title" value="<?php echo $data['post_title']; ?>" autofocus aria-required="true"
                        required />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Nội dung</label>
                <div class="controls" style="width:80%;">
                    <textarea placeholder="Nội dung" name="data[post_content]" id="data_post_content"
                        class="span30 fix-textarea-height"><?php echo $data['post_content']; ?></textarea>
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Tổng tiền</label>
                <div class="controls bold">
                    <?php echo number_format($data['post_parent']); ?> VNĐ
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Hạn sử dụng</label>
                <div class="controls">
                    <?php echo $data['comment_count']; ?> (tháng)
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
                <div class="controls"><a href="admin/users/add?id=<?php echo $data['post_author']; ?>"
                        data-id="<?php echo $data['post_author']; ?>" class="each-to-email bluecolor" target="_blank">
                        <?php echo $data['post_author']; ?>
                    </a> </div>
            </div>
            <?php

            // nạp các meta theo từng loại post
            foreach ($meta_detault as $k => $v) {
                // đơn hàng thì không dùng ảnh đại diện
                if (in_array($k, [
                    'post_category',
                    'post_tags',
                    'image',
                    'image_large',
                    'image_medium_large',
                    'image_medium',
                    'image_thumbnail',
                    'image_webp',
                ])) {
                    continue;
                }

                //
                $input_type = OrderType::meta_type($k);

                //
                if ($input_type == 'hidden') {
            ?>
            <input type="hidden" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>"
                value="<?php $post_model->echo_meta_post($data, $k); ?>" />
            <?php

                    //
                    continue;
                } // END if hidden type
            
                //
                if ($input_type == 'checkbox') {
            ?>
            <div class="control-group post_meta_<?php echo $k; ?>">
                <div class="controls controls-checkbox">
                    <label for="post_meta_<?php echo $k; ?>">
                        <input type="checkbox" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>"
                            value="on" data-value="<?php $post_model->echo_meta_post($data, $k); ?>" />
                        <?php echo $v; ?>
                    </label>
                    <?php

                    // hiển thị ghi chú nếu có
                    OrderType::meta_desc($k);

                    ?>
                </div>
            </div>
            <?php

                    //
                    continue;
                } // END if checkbox
            
            ?>
            <div class="control-group post_meta_<?php echo $k; ?>">
                <label for="post_meta_<?php echo $k; ?>" class="control-label">
                    <?php echo $v; ?>
                </label>
                <div class="controls">
                    <?php

                // mặc định thì hiển thị bình thường
                if ($input_type == 'textarea') {
                    ?>
                    <textarea style="width:80%;" placeholder="<?php echo $v; ?>" name="post_meta[<?php echo $k; ?>]"
                        id="post_meta_<?php echo $k; ?>" class="<?php echo OrderType::meta_class($k); ?>"><?php $post_model->echo_meta_post($data, $k); ?>
</textarea>
                    <?php
                } // END if post textarea
                else if ($input_type == 'select' || $input_type == 'select_multiple') {
                    $select_multiple = '';
                    $meta_multiple = '';
                    if ($input_type == 'select_multiple') {
                        $select_multiple = 'multiple';
                        $meta_multiple = '[]';
                    }

                    //
                    $select_options = OrderType::meta_select($k);

                    ?>
                    <select data-select="<?php $post_model->echo_meta_post($data, $k); ?>"
                        name="post_meta[<?php echo $k; ?>]<?php echo $meta_multiple; ?>" <?php echo $select_multiple;
                        ?>>
                        <?php

                    foreach ($select_options as $option_k => $option_v) {
                        echo '<option value="' . $option_k . '">' . $option_v . '</option>';
                    }

                        ?>
                    </select>
                    <?php
                } // END if post select
                else {
                    ?>
                    <input type="<?php echo $input_type; ?>" class="span10" placeholder="<?php echo $v; ?>"
                        name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>"
                        value="<?php $post_model->echo_meta_post($data, $k); ?>" />
                    <?php
                } // END else
            
                // hiển thị ghi chú nếu có
                OrderType::meta_desc($k);

                    ?>
                </div>
            </div>
            <?php
            } // END foreach auto add post meta
            
            ?>
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
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Dữ liệu thanh toán</label>
                <div class="controls">
                    <?php echo $data['pinged']; ?>
                </div>
            </div>
            <?php

            //
            include $admin_root_views . 'posts/add_submit.php';

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