<?php

// Libraries
use App\Libraries\PostType;
//use App\Libraries\LanguageCost;

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
include __DIR__ . '/add_breadcrumb.php';

?>
<div class="widget-box ng-main-content" id="myApp">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="admin_global_form" onSubmit="return action_before_submit_post();" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <input type="hidden" name="is_duplicate" id="is_duplicate" value="0" />
            <input type="hidden" name="data[lang_key]" value="<?php echo $data['lang_key']; ?>" />
            <?php
            if ($data['ID'] > 0) {
            ?>
                <div class="rf">
                    <button type="button" onClick="click_duplicate_record();" class="btn btn-warning"><i class="fa fa-copy"></i> Nhân bản</button>
                </div>
            <?php
            }
            ?>
            <div class="control-group">
                <label class="control-label">Ngôn ngữ</label>
                <div class="controls">
                    <?php

                    //
                    include __DIR__ . '/change_lang.php';

                    ?>
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Tiêu đề</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Tiêu đề" name="data[post_title]" id="data_post_title" value="<?php $base_model->the_esc_html($data['post_title']); ?>" autofocus aria-required="true" required />
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_shorttitle" class="control-label">Tiêu đề (ngắn)</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Tiêu đề (ngắn)" name="data[post_shorttitle]" id="data_post_shorttitle" value="<?php $base_model->the_esc_html($data['post_shorttitle']); ?>" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Slug</label>
                <div class="controls">
                    <input type="text" title="Bấm đúp chuột để chỉnh sửa đường dẫn" class="span6" name="data[post_name]" id="data_post_name" onDblClick="$('#data_post_name').removeAttr('readonly');" value="<?php echo $data['post_name']; ?>" readonly />
                    <input type="hidden" name="old_postname" id="old_postname" value="<?php echo $data['post_name']; ?>" />
                    <?php
                    if ($data['ID'] > 0) {
                    ?>
                        <div>
                            <a href="<?php $post_model->the_post_permalink($data); ?>" class="bluecolor set-new-url"><?php echo $data['post_permalink']; ?></a> <i class="fa fa-eye bluecolor"></i>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <div class="control-group control-group-post_content">
                <label class="control-label">Nội dung</label>
                <div class="controls f80">
                    <textarea id="Resolution" rows="30" data-height="<?php echo $post_type == $ads_post_type ? '250' : '550'; ?>" class="ckeditor auto-ckeditor" placeholder="Nhập thông tin chi tiết..." name="data[post_content]"><?php echo $data['post_content']; ?></textarea>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Mô tả</label>
                <div class="controls f80">
                    <textarea placeholder="Tóm tắt" name="data[post_excerpt]" id="data_post_excerpt" class="span30 fix-textarea-height"><?php echo $data['post_excerpt']; ?></textarea>
                    <div>
                        <input type="checkbox" data-for="data_post_excerpt" class="click-enable-editer" /> Sử dụng chế độ soạn thảo HTML cho phần tóm tắt.
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Trạng thái</label>
                <div class="controls">
                    <select data-select="<?php echo $data['post_status']; ?>" name="data[post_status]" class="form-select">
                        <option :value="k" v-for="(v, k) in post_status">{{v}}</option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Số thứ tự</label>
                <div class="controls">
                    <input type="number" class="span3" placeholder="Số thứ tự" name="data[menu_order]" value="<?php echo $data['menu_order']; ?>" />
                </div>
            </div>
            <?php
            if (!empty($parent_post)) {
            ?>
                <div class="control-group">
                    <label class="control-label">Cha</label>
                    <div class="controls">
                        <select data-select="<?php echo $data['post_parent']; ?>" name="data[post_parent]" class="form-select">
                            <option value="">[ Không chọn cha ]</option>
                            <option :value="v.ID" v-for="v in parent_post">{{v.post_title}}</option>
                        </select>
                    </div>
                </div>
                <?php
            }

            // nạp các meta theo từng loại post
            foreach ($meta_default as $k => $v) {
                //
                if ($k == 'post_category' && $taxonomy == '') {
                    continue;
                } else if ($k == 'post_tags' && $tags == '') {
                    continue;
                } else if ($k == 'post_options' && $options == '') {
                    continue;
                }

                //
                $input_type = PostType::meta_type($k, $meta_custom_type);

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
                                <input type="checkbox" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>" value="on" data-value="<?php $post_model->echo_meta_post($data, $k); ?>" class="post_uncheck_meta" />
                                <?php echo $v; ?>
                            </label>
                            <?php

                            // hiển thị ghi chú nếu có
                            PostType::meta_desc($k, $meta_custom_desc);

                            ?>
                        </div>
                        <!-- uncheck phải cho ra khỏi label để không dính hiệu ứng click vào label -->
                        <input type="checkbox" name="post_uncheck_meta[<?php echo $k; ?>]" class="d-none post_uncheck_meta_<?php echo $k; ?>" value="on" />
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

                        // với 1 số post type có đặc thù riêng -> ví dụ danh mục
                        if ($k == 'post_category') {
                            $url_add_term = 'sadmin/terms/add/?taxonomy=' . $taxonomy;
                            if (isset($arr_taxonomy_controller[$taxonomy])) {
                                $url_add_term = 'sadmin/' . $arr_taxonomy_controller[$taxonomy] . '/add';
                            }
                        ?>
                            <select data-select="<?php $post_model->echo_meta_post($data, $k); ?>" name="post_meta[<?php echo $k; ?>][]" id="post_meta_<?php echo $k; ?>" class="form-select" multiple>
                                <option value="">[ Chọn <?php echo $v; ?> ]</option>
                            </select>
                            &nbsp; <a href="<?php echo $url_add_term; ?>" target="_blank" class="bluecolor"><i class="fa fa-plus"></i> Thêm <?php echo $v; ?> mới</a>
                            <div><a href="sadmin/<?php echo $controller_slug; ?>?term_id=<?php echo explode(',', $post_model->text_meta_post($data, $k))[0]; ?>" class="bluecolor"><i class="fa fa-search"></i> <?php echo $name_type; ?> cùng danh mục</a></div>
                        <?php
                        } // END if post category
                        else if ($k == 'post_tags') {
                            $url_add_term = 'sadmin/terms/add/?taxonomy=' . $tags;
                            if (isset($arr_taxonomy_controller[$tags])) {
                                $url_add_term = 'sadmin/' . $arr_taxonomy_controller[$tags] . '/add';
                            }
                        ?>
                            <select data-select="<?php $post_model->echo_meta_post($data, $k); ?>" name="post_meta[<?php echo $k; ?>][]" id="post_meta_<?php echo $k; ?>" class="form-select" multiple>
                                <option value="">[ Chọn <?php echo $v; ?> ]</option>
                            </select>
                            &nbsp; <a href="<?php echo $url_add_term; ?>" target="_blank" class="bluecolor"><i class="fa fa-plus"></i> Thêm <?php echo $v; ?> mới</a>
                        <?php
                        } // END if post tags
                        else if ($k == 'post_options') {
                            $url_add_term = 'sadmin/terms/add/?taxonomy=' . $options;
                            if (isset($arr_taxonomy_controller[$options])) {
                                $url_add_term = 'sadmin/' . $arr_taxonomy_controller[$options] . '/add';
                            }
                        ?>
                            <select data-select="<?php $post_model->echo_meta_post($data, $k); ?>" name="post_meta[<?php echo $k; ?>][]" id="post_meta_<?php echo $k; ?>" class="form-select" multiple>
                                <option value="">[ Chọn <?php echo $v; ?> ]</option>
                            </select>
                            &nbsp; <a href="<?php echo $url_add_term; ?>" target="_blank" class="bluecolor"><i class="fa fa-plus"></i> Thêm <?php echo $v; ?> mới</a>
                        <?php
                        } // END if post options
                        // mặc định thì hiển thị bình thường
                        else if ($input_type == 'textarea') {
                        ?>
                            <textarea placeholder="<?php echo $v; ?>" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>" class="f80 fix-textarea-height <?php echo PostType::meta_class($k); ?>"><?php $post_model->echo_meta_post($data, $k); ?></textarea>
                        <?php
                        } // END if post textarea
                        else if ($input_type == 'select' || $input_type == 'select_multiple') {
                            $select_multiple = '';
                            $meta_multiple = '';
                            if ($input_type == 'select_multiple') {
                                $select_multiple = 'multiple';
                                $meta_multiple = '[]';
                            }

                            // lấy danh sách page template cho page
                            if ($post_type == PostType::PAGE && $k == 'page_template') {
                                $arr_page_template = $base_model->EBE_get_file_in_folder(THEMEPATH . 'page-templates/', '.php', 'file');
                                //print_r( $arr_page_template );

                                //
                                $select_options = array(
                                    '' => '[ Mặc định ]'
                                );
                                foreach ($arr_page_template as $tmp_k => $tmp_v) {
                                    $tmp_v = basename($tmp_v, '.php');
                                    $select_options[$tmp_v] = str_replace('-', ' ', $tmp_v);
                                }

                                //
                            } else {
                                $select_options = PostType::meta_select($k);
                            }

                        ?>
                            <select data-select="<?php $post_model->echo_meta_post($data, $k); ?>" name="post_meta[<?php echo $k; ?>]<?php echo $meta_multiple; ?>" id="post_meta_<?php echo $k; ?>" class="form-select" <?php echo $select_multiple; ?>>
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
                        PostType::meta_desc($k, $meta_custom_desc);

                        ?>
                    </div>
                </div>
            <?php
            } // END foreach auto add post meta


            // thêm chức năng add link nhanh cho ADS
            if ($post_type == $ads_post_type) {
            ?>
                <div class="control-group">
                    <label for="quick_add_menu" class="control-label">Thêm liên kết nội bộ</label>
                    <div id="quick_add_menu" class="controls">
                        <?php

                        $quick_menu_list = $post_model->get_site_inlink($data['lang_key']);
                        //print_r( $quick_menu_list );
                        //echo implode( '', $quick_menu_list );

                        // chạy 1 vòng lặp -> lấy các loại menu ra để tạo select -> dễ lọc
                        foreach ($quick_menu_list as $k => $v) {
                        ?>
                            <div>
                                <select class="form-select">
                                    <!-- <option value="">[ Thêm nhanh Tiên kết ]</option> -->
                                    <option :value="v.value" v-for="v in quick_menu_list.<?php echo $k; ?>" :class="v.class" :data-xoa-disabled="v.selectable">{{v.text}}</option>
                                </select>
                            </div>
                            <br>
                        <?php
                        }

                        ?>
                        <p class="controls-text-note">Khi cần liên kết đến 1 URL trong website, có thể chọn 1 trong các liên kết nội bộ đã được liệt kê sẵn ở đây.</p>
                    </div>
                </div>
            <?php
            }

            //
            include __DIR__ . '/add_submit.php';

            ?>
        </form>
    </div>
</div>
<br>
<div class="left-menu-space">
    <h3 class="white-preview-url"><?php echo $name_type; ?> khác:</h3>
    <ul id="oi_other_posts" class="s14">
        <li v-for="v in prev_post"><a :href="post_admin_permalink(current_post_type, v.ID, controller_slug)">{{v.post_title}} ({{v.post_name}})</a></li>
        <li class="bold"><?php echo $data['post_title']; ?></li>
        <li v-for="v in next_post"><a :href="post_admin_permalink(current_post_type, v.ID, controller_slug)">{{v.post_title}} ({{v.post_name}})</a></li>
    </ul>
    <?php

    //
    if (!empty($child_post)) {
    ?>
        <h3 class="white-preview-url">Các <?php echo $name_type; ?> con khác (cùng post_parent hoặc lang_parent):</h3>
        <ul>
            <li v-for="v in child_post"><a :href="post_admin_permalink(current_post_type, v.ID, controller_slug)">{{v.post_type}} ({{v.lang_key}}) #{{v.ID}} {{v.post_title}} ({{v.post_name}}) | {{v.post_status}}</a></li>
        </ul>
    <?php
    }

    ?>
</div>
<?php

//
$base_model->JSON_parse([
    'parent_post' => $parent_post,
    'post_arr_status' => $post_arr_status,
    'quick_menu_list' => $quick_menu_list,
    'prev_post' => $prev_post,
    'next_post' => $next_post,
    'child_post' => $child_post,
]);

//
$base_model->JSON_echo([
    // mảng này sẽ in ra dưới dạng JSON hoặc number
    'auto_update_module' => $auto_update_module,
], [
    // mảng này sẽ in ra dưới dạng string
    'controller_slug' => $controller_slug,
    'current_post_type' => $post_type,
    'page_post_type' => PostType::PAGE,
    'url_next_post' => $url_next_post,
    'post_cat' => $post_cat,
    'post_tags' => $post_tags,
    'post_options' => $post_options,
    'post_lang_key' => $data['lang_key'],
    'preview_url' => $preview_url,
    'preview_offset_top' => $preview_offset_top,
]);

?>
<script type="text/javascript">
    WGR_vuejs('#myApp', {
        parent_post: parent_post,
        post_status: post_arr_status,
        quick_menu_list: quick_menu_list,
    });

    //
    console.log(prev_post);
    WGR_vuejs('#oi_other_posts', {
        prev_post: prev_post,
        next_post: next_post,
        child_post: child_post,
    });
</script>
<?php

//
$base_model->adds_js([
    'wp-admin/js/preview_url.js',
    'wp-admin/js/posts.js',
    'wp-admin/js/posts_add.js',
    // js riêng cho từng post type (nếu có)
    'wp-admin/js/' . $post_type . '.js',
    'wp-admin/js/' . $post_type . '_add.js',
]);
