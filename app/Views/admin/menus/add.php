<?php

// Libraries
use App\ Libraries\ PostType;

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/' . $post_type . '.css' );

?>
<ul class="admin-breadcrumb">
    <li><a href="admin/<?php echo $controller_slug; ?>">Danh sách <?php echo $name_type; ?></a></li>
    <li>
        <?php
        if ( $data[ 'ID' ] > 0 ) {
            ?>
        Chỉnh sửa
        <?php
        } else {
            ?>
        Thêm mới
        <?php
        }
        echo $name_type . ' ' . $data[ 'post_title' ];
        ?>
    </li>
</ul>
<?php

// kích hoạt chức năng tự động cập nhật bài viết khi có module gì hot
if ( $auto_update_module > 0 ) {
    //echo $auto_update_module;
    ?>
<p class="orgcolor text-center medium show-if-end-function">* Kích hoạt chức năng tự động cập nhật bài viết để nhận các tính mới...</p>
<?php
}

?>
<div class="widget-box ng-main-content" ng-app="myApp" ng-controller="myCtrl">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="admin_global_form" onSubmit="return action_before_submit_post();" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <div class="control-group">
                <label class="control-label">Ngôn ngữ</label>
                <div class="controls">
                    <?php
                    echo $post_lang;
                    ?>
                </div>
            </div>
            <div class="control-group">
                <label for="data_post_title" class="control-label">Tiêu đề</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Tiêu đề" name="data[post_title]" id="data_post_title" value="<?php echo $data['post_title']; ?>" autofocus aria-required="true" required />
                </div>
            </div>
            <?php

            // menu không cho sửa slug -> vì sửa xong sẽ làm lệnh lấy tin tự động hoạt động sai
            if ( $data[ 'post_name' ] != '' ) {
                ?>
            <div class="control-group">
                <label class="control-label">PHP Code:</label>
                <div class="controls">
                    <input type="text" class="span6" onClick="this.select()" onDblClick="click2Copy(this);" value="&lt;?php $menu_model->the_menu( '<?php echo $data['post_name']; ?>' ); ?&gt;" readonly />
                </div>
            </div>
            <?php
            }

            ?>
            <div class="control-group hide-if-edit-menu">
                <label class="control-label">Nội dung</label>
                <div class="controls" style="width:80%;">
                    <textarea id="Resolution" rows="30" data-height="550" class="ckeditor auto-ckeditor" placeholder="Nhập thông tin chi tiết..." name="data[post_content]"><?php echo $data['post_content']; ?></textarea>
                </div>
            </div>
            <div class="control-group hide-if-edit-menu">
                <label class="control-label">Mô tả</label>
                <div class="controls" style="width:80%;">
                    <textarea placeholder="Tóm tắt" name="data[post_excerpt]" id="data_post_excerpt" class="span30 fix-textarea-height"><?php echo $data['post_excerpt']; ?></textarea>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Trạng thái</label>
                <div class="controls">
                    <select data-select="<?php echo $data['post_status']; ?>" name="data[post_status]" class="span5">
                        <option ng-repeat="(k, v) in post_status" value="{{k}}">{{v}}</option>
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

            // nạp các meta theo từng loại post
            foreach ( $meta_detault as $k => $v ) {
                // menu thì không dùng ảnh đại diện
                if ( in_array( $k, [
                        'post_category',
                        'post_tags',
                        'image',
                        'image_large',
                        'image_medium_large',
                        'image_medium',
                        'image_thumbnail',
                        'image_webp',
                    ] ) ) {
                    continue;
                }

                //
                $input_type = PostType::meta_type( $k );

                //
                if ( $input_type == 'hidden' ) {
                    ?>
            <input type="hidden" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>" value="<?php $post_model->echo_meta_post($data, $k); ?>" />
            <?php

            //
            continue;
            } // END if hidden type

            //
            if ( $input_type == 'checkbox' ) {
                ?>
            <div class="control-group hide-if-edit-menu post_meta_<?php echo $k; ?>">
                <div class="controls controls-checkbox">
                    <label for="post_meta_<?php echo $k; ?>">
                        <input type="checkbox" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>" value="on" data-value="<?php $post_model->echo_meta_post($data, $k); ?>" />
                        <?php echo $v; ?></label>
                    <?php

                    // hiển thị ghi chú nếu có
                    PostType::meta_desc( $k );

                    ?>
                </div>
            </div>
            <?php

            //
            continue;
            } // END if checkbox

            ?>
            <div class="control-group hide-if-edit-menu post_meta_<?php echo $k; ?>">
                <label for="post_meta_<?php echo $k; ?>" class="control-label"><?php echo $v; ?></label>
                <div class="controls">
                    <?php

                    //
                    if ( $input_type == 'textarea' ) {
                        ?>
                    <textarea style="width:80%;" placeholder="<?php echo $v; ?>" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>" class="<?php echo PostType::meta_class($k); ?>"><?php $post_model->echo_meta_post($data, $k); ?>
</textarea>
                    <?php
                    } // END if post textarea
                    else if ( $input_type == 'select' || $input_type == 'select_multiple' ) {
                        $select_multiple = '';
                        $meta_multiple = '';
                        if ( $input_type == 'select_multiple' ) {
                            $select_multiple = 'multiple';
                            $meta_multiple = '[]';
                        }

                        //
                        $select_options = PostType::meta_select( $k );

                        ?>
                    <select data-select="<?php $post_model->echo_meta_post($data, $k); ?>" name="post_meta[<?php echo $k; ?>]<?php echo $meta_multiple; ?>" <?php echo $select_multiple; ?>>
                        <?php

                        foreach ( $select_options as $option_k => $option_v ) {
                            echo '<option value="' . $option_k . '">' . $option_v . '</option>';
                        }

                        ?>
                    </select>
                    <?php
                    } // END if post select
                    else {
                        ?>
                    <input type="<?php echo $input_type; ?>" class="span10" placeholder="<?php echo $v; ?>" name="post_meta[<?php echo $k; ?>]" id="post_meta_<?php echo $k; ?>" value="<?php $post_model->echo_meta_post($data, $k); ?>" />
                    <?php
                    } // END else

                    // hiển thị ghi chú nếu có
                    PostType::meta_desc( $k );

                    ?>
                </div>
            </div>
            <?php
            } // END foreach auto add post meta

            ?>
            <div class="form-actions frm-fixed-btn">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Lưu lại</button>
                <?php
                if ( $data[ 'ID' ] > 0 ) {
                    ?>
                <a href="admin/<?php echo $controller_slug; ?>/delete?id=<?php echo $data[ 'ID' ]; ?>" onClick="return click_a_delete_record();" class="btn btn-danger" target="target_eb_iframe"><i class="fa fa-trash"></i> XÓA</a>
                <?php
                }
                ?>
            </div>
        </form>
    </div>
    <?php

    //
    //require __DIR__ . '/add_edit_menu.php';
    require __DIR__ . '/add_edit_menu_v2.php';

    ?>
</div>
<script>
var current_post_type='<?php echo $post_type; ?>';
var auto_update_module='<?php echo $auto_update_module; ?>';
var url_next_post='<?php echo $url_next_post; ?>';
//var post_cat = '<?php echo $post_cat; ?>';
//var post_tags = '<?php echo $post_tags; ?>';

// do phần menu chưa xử lý được bằng vue-js nên vẫn phải dùng angular
angular.module('myApp', []).controller('myCtrl', function ($scope) {
    $scope.post_status = <?php echo json_encode($post_arr_status); ?>;
    $scope.quick_menu_list = <?php echo json_encode($quick_menu_list); ?>;
    angular.element(document).ready(function () {
        $('.ng-main-content').addClass('loaded');
    });
});
</script>
<?php

$base_model->add_js( 'admin/js/posts.js' );
$base_model->add_js( 'admin/js/posts_add.js' );
// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/' . $post_type . '.js' );

//$base_model->add_js( 'admin/js/maruti.form_common.js' );