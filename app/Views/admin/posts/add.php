<?php

// Libraries
use App\Libraries\PostType;

// css riêng cho từng post type (nếu có)
$base_model->add_css('admin/css/' . $post_type . '.css');

//
include $admin_root_views . 'posts/add_breadcrumb.php';

?>
<div class="widget-box ng-main-content" id="myApp">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="admin_global_form"
            onSubmit="return action_before_submit_post();" accept-charset="utf-8" class="form-horizontal"
            target="target_eb_iframe">
            <input type="hidden" name="is_duplicate" id="is_duplicate" value="0" />
            <?php
            if ($data['ID'] > 0) {
                ?>
            <div class="rf">
                <button type="button" onClick="click_duplicate_record();" class="btn btn-warning"><i
                        class="fa fa-copy"></i> Nhân bản</button>
            </div>
            <?php
            }
            ?>
            <div class="control-group">
                <label class="control-label">Ngôn ngữ</label>
                <div class="controls">
                    <?php echo $post_lang; ?>
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
                <label for="data_post_title" class="control-label">Tiêu đề (ngắn)</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Tiêu đề (ngắn)" name="data[post_shorttitle]"
                        id="data_post_shorttitle" value="<?php echo $data['post_shorttitle']; ?>" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Slug</label>
                <div class="controls">
                    <input type="text" title="Bấm đúp chuột để chỉnh sửa đường dẫn" class="span6" name="data[post_name]"
                        id="data_post_name" onDblClick="$('#data_post_name').removeAttr('readonly');"
                        value="<?php echo $data['post_name']; ?>" readonly />
                    <?php
                    if ($data['ID'] > 0) {
                        ?>
                    <a href="<?php $post_model->the_post_permalink($data); ?>" class="bluecolor">Xem <i
                            class="fa fa-eye"></i></a>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Nội dung</label>
                <div class="controls" style="width:80%;">
                    <textarea id="Resolution" rows="30"
                        data-height="<?php echo $post_type == PostType::ADS ? '250' : '550'; ?>"
                        class="ckeditor auto-ckeditor" placeholder="Nhập thông tin chi tiết..."
                        name="data[post_content]"><?php echo $data['post_content']; ?></textarea>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Mô tả</label>
                <div class="controls" style="width:80%;">
                    <textarea placeholder="Tóm tắt" name="data[post_excerpt]" id="data_post_excerpt"
                        class="span30 fix-textarea-height"><?php echo $data['post_excerpt']; ?></textarea>
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
            <div class="control-group">
                <label class="control-label">Số thứ tự</label>
                <div class="controls">
                    <input type="number" class="span3" placeholder="Số thứ tự" name="data[menu_order]"
                        value="<?php echo $data['menu_order']; ?>" />
                </div>
            </div>
            <?php
            if (!empty($parent_post)) {
                ?>
            <div class="control-group">
                <label class="control-label">Cha</label>
                <div class="controls">
                    <select data-select="<?php echo $data['post_parent']; ?>" name="data[post_parent]" class="span5">
                        <option value="">[ Không chọn cha ]</option>
                        <option :value="v.ID" v-for="v in parent_post">{{v.post_title}}</option>
                    </select>
                </div>
            </div>
            <?php
            }

            // nạp các meta theo từng loại post
            foreach ($meta_detault as $k => $v) {
                //
                if ($k == 'post_category' && $taxonomy == '') {
                    continue;
                } else if ($k == 'post_tags' && $tags == '') {
                    continue;
                }

                //
                $input_type = PostType::meta_type($k);

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

                        // với 1 số post type có đặc thù riêng -> ví dụ danh mục
                        if ($k == 'post_category') {
                            ?>
                    <select data-select="<?php $post_model->echo_meta_post($data, $k); ?>"
                        name="post_meta[<?php echo $k; ?>][]" id="post_meta_<?php echo $k; ?>" multiple>
                        <option value="">[ Chọn
                            <?php echo $v; ?> ]
                        </option>
                    </select>
                    &nbsp; <a href="admin/terms/add/?taxonomy=<?php echo $taxonomy; ?>" target="_blank"
                        class="bluecolor"><i class="fa fa-plus"></i> Thêm
                        <?php echo $v; ?> mới
                    </a>
                    <?php
                        } // END if post category
                        else if ($k == 'post_tags') {
                            ?>
                    <select data-select="<?php $post_model->echo_meta_post($data, $k); ?>"
                        name="post_meta[<?php echo $k; ?>][]" id="post_meta_<?php echo $k; ?>" multiple>
                        <option value="">[ Chọn
                            <?php echo $v; ?> ]
                        </option>
                    </select>
                    &nbsp; <a href="admin/terms/add/?taxonomy=<?php echo $tags; ?>" target="_blank" class="bluecolor"><i
                            class="fa fa-plus"></i> Thêm
                        <?php echo $v; ?> mới
                    </a>
                    <?php
                        } // END if post tags
                        // mặc định thì hiển thị bình thường
                        else if ($input_type == 'textarea') {
                            ?>
                    <textarea style="width:80%;" placeholder="<?php echo $v; ?>" name="post_meta[<?php echo $k; ?>]"
                        id="post_meta_<?php echo $k; ?>" class="<?php echo PostType::meta_class($k); ?>"><?php $post_model->echo_meta_post($data, $k); ?>
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

                            // lấy danh sách page template cho page
                            if ($post_type == PostType::PAGE && $k = 'page_template') {
                                $arr_page_template = $base_model->EBE_get_file_in_folder(THEMEPATH . 'page-templates/', '.{php}', 'file');
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
                    <select data-select="<?php $post_model->echo_meta_post($data, $k); ?>"
                        name="post_meta[<?php echo $k; ?>]<?php echo $meta_multiple; ?>"
                        id="post_meta_<?php echo $k; ?>" <?php echo $select_multiple; ?>>
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
                        PostType::meta_desc($k);

                        ?>
                </div>
            </div>
            <?php
            } // END foreach auto add post meta
            

            // thêm chức năng add link nhanh cho ADS
            if ($post_type == PostType::ADS) {
                ?>
            <div class="control-group">
                <label for="quick_add_menu" class="control-label">Thêm kết nội bộ</label>
                <div class="controls">
                    <select id="quick_add_menu">
                        <option value="">[ Thêm nhanh Tiên kết ]</option>
                        <?php

                            $quick_menu_list = $post_model->quick_add_menu();
                            //print_r( $quick_menu_list );
                            //echo implode( '', $quick_menu_list );
                        
                            ?>
                        <option :value="v.value" v-for="v in quick_menu_list" :class="v.class" :disabled="v.selectable">
                            {{v.text}}</option>
                    </select>
                </div>
            </div>
            <?php
            }

            //
            include $admin_root_views . 'posts/add_submit.php';

            ?>
        </form>
    </div>
</div>
<?php

//
$base_model->JSON_parse([
    'parent_post' => $parent_post,
    'post_arr_status' => $post_arr_status,
    'quick_menu_list' => $quick_menu_list,
]);

?>
<script>
var current_post_type = '<?php echo $post_type; ?>';
var page_post_type = '<?php echo PostType::PAGE; ?>';
var auto_update_module = '<?php echo $auto_update_module; ?>';
var url_next_post = '<?php echo $url_next_post; ?>';
var post_cat = '<?php echo $post_cat; ?>';
var post_tags = '<?php echo $post_tags; ?>';

// do phần menu chưa xử lý được bằng vue-js nên vẫn phải dùng angular
WGR_vuejs('#myApp', {
    parent_post: parent_post,
    post_status: post_arr_status,
    quick_menu_list: quick_menu_list,
});
</script>
<?php

$base_model->add_js('admin/js/posts.js');
$base_model->add_js('admin/js/posts_add.js');
// css riêng cho từng post type (nếu có)
$base_model->add_js('admin/js/' . $post_type . '.js');