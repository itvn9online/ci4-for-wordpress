<?php

// Libraries
use App\Libraries\PostType;

// css riêng cho từng post type (nếu có)
$base_model->add_css('admin/css/' . $post_type . '.css');

//
include ADMIN_ROOT_VIEWS . 'posts/add_breadcrumb.php';

?>
<p class="medium blackcolor"><span class="redcolor">*</span> Chức năng tạo menu bằng trình soạn thảo HTML. Nội dung
    trong khung soạn thảo sẽ được in trực tiếp ra HTML mà không cần qua công cụ hỗ trợ biên soạn.</p>
<div class="widget-box ng-main-content" id="myApp">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="admin_global_form" onSubmit="return action_before_submit_html_menu();" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <div class="control-group">
                <label class="control-label">Ngôn ngữ</label>
                <div class="controls">
                    <?php

                    //
                    include ADMIN_ROOT_VIEWS . 'posts/change_lang.php';

                    ?>
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Tiêu đề</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Tiêu đề" name="data[post_title]" id="data_post_title" value="<?php $base_model->the_esc_html($data['post_title']); ?>" autofocus aria-required="true" required />
                </div>
            </div>
            <?php

            // menu không cho sửa slug -> vì sửa xong sẽ làm lệnh lấy tin tự động hoạt động sai
            if ($data['post_name'] != '') {
            ?>
                <div class="control-group">
                    <label class="control-label">PHP Code:</label>
                    <div class="controls">
                        <input type="text" class="span6" onClick="this.select()" onDblClick="click2Copy(this);" value="&lt;?php $htmlmenu_model->the_menu( '<?php echo $data['post_name']; ?>' ); ?&gt;" readonly />
                    </div>
                </div>
            <?php
            }

            ?>
            <div class="control-group">
                <label class="control-label">Nội dung</label>
                <div class="controls f80">
                    <textarea id="Resolution" rows="30" data-height="550" class="ckeditor auto-ckeditor" placeholder="Nhập thông tin chi tiết..." name="data[post_content]"><?php echo $data['post_content']; ?></textarea>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Trạng thái</label>
                <div class="controls">
                    <select data-select="<?php echo $data['post_status']; ?>" name="data[post_status]" class="span5">
                        <option :value="k" v-for="(v, k) in post_status">{{v}}</option>
                    </select>
                </div>
            </div>
            <?php

            // nạp các meta theo từng loại post
            foreach ($meta_detault as $k => $v) {
                // menu thì không dùng ảnh đại diện
                if (
                    in_array($k, [
                        'post_category',
                        'post_tags',
                        'image',
                        'image_large',
                        'image_medium_large',
                        'image_medium',
                        'image_thumbnail',
                        'image_webp',
                    ])
                ) {
                    continue;
                }

                //
                $input_type = PostType::meta_type($k);

                //
                if ($input_type == 'hidden') {
            ?>
                    <input type="hidden" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>" value="<?php $post_model->echo_esc_meta_post($data, $k); ?>" />
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
                                <input type="checkbox" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>" value="on" data-value="<?php $post_model->echo_meta_post($data, $k); ?>" />
                                <?php echo $v; ?>
                            </label>
                            <?php

                            // hiển thị ghi chú nếu có
                            PostType::meta_desc($k);

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

                        //
                        if ($input_type == 'textarea') {
                        ?>
                            <textarea placeholder="<?php echo $v; ?>" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>" class="f80 <?php echo PostType::meta_class($k); ?>"><?php $post_model->echo_meta_post($data, $k); ?>
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
                            $select_options = PostType::meta_select($k);

                        ?>
                            <select data-select="<?php $post_model->echo_meta_post($data, $k); ?>" name="post_meta[<?php echo $k; ?>]<?php echo $meta_multiple; ?>" <?php echo $select_multiple;
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
                            <input type="<?php echo $input_type; ?>" class="span10" placeholder="<?php echo $v; ?>" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>" value="<?php $post_model->echo_esc_meta_post($data, $k); ?>" />
                        <?php
                        } // END else

                        // hiển thị ghi chú nếu có
                        PostType::meta_desc($k);

                        ?>
                    </div>
                </div>
            <?php
            } // END foreach auto add post meta

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

//
$base_model->JSON_echo([
    // mảng này sẽ in ra dưới dạng JSON hoặc number
], [
    // mảng này sẽ in ra dưới dạng string
    'preview_url' => $preview_url,
    'preview_offset_top' => $preview_offset_top,
]);

//
$base_model->adds_js([
    'admin/js/preview_url.js',
    'admin/js/posts.js',
    'admin/js/posts_add.js',
    // js riêng cho từng post type (nếu có)
    'admin/js/' . $post_type . '.js',
    'admin/js/' . $post_type . '_add.js',
]);
