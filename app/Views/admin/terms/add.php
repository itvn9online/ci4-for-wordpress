<?php

// Libraries
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ LanguageCost;

//
//$base_model = new\ App\ Models\ Base();
//$term_model = new\ App\ Models\ Term();

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/' . $taxonomy . '.css' );

?>
<ul class="admin-breadcrumb">
    <li><a href="admin/<?php echo $controller_slug; ?>">Danh sách <?php echo $name_type; ?></a></li>
    <li>
        <?php
        if ( $data[ 'term_id' ] > 0 ) {
            ?>
        Chỉnh sửa
        <?php
        } else {
            ?>
        Thêm mới
        <?php
        }
        echo TaxonomyType::list( $taxonomy, true );
        ?>
    </li>
</ul>
<div class="widget-box" ng-app="myApp" ng-controller="myCtrl">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="admin_global_form" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <input type="hidden" name="is_duplicate" id="is_duplicate" value="0" />
            <div class="rf">
                <button type="button" onClick="click_duplicate_record();" class="btn btn-warning"><i class="fa fa-copy"></i> Nhân bản</button>
            </div>
            <div class="control-group">
                <label class="control-label">Ngôn ngữ</label>
                <div class="controls">
                    <?php
                    echo LanguageCost::list( $data[ 'lang_key' ] != '' ? $data[ 'lang_key' ] : $lang_key );
                    ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Tiêu đề</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Tiêu đề" name="data[name]" value="<?php echo $data['name']; ?>" autofocus aria-required="true" required />
                </div>
            </div>
            <?php

            // các mục không cho sửa slug -> vì sửa xong sẽ làm lệnh lấy tin tự động hoạt động sai
            if ( $taxonomy == TaxonomyType::ADS ) {
                if ( $data[ 'slug' ] != '' ) {
                    ?>
            <div class="control-group">
                <label class="control-label">PHP Code:</label>
                <div class="controls">
                    <input type="text" class="span6" value="&lt;?php $this->post_model->the_ads( '<?php echo $data['slug']; ?>' ); ?&gt;" readonly />
                </div>
            </div>
            <?php
            }
            }
            // các mục khác cho hiển thị slug để sửa
            else {
                ?>
            <div class="control-group">
                <label class="control-label">Slug</label>
                <div class="controls">
                    <input type="text" title="Bấm đúp chuột để chỉnh sửa đường dẫn" class="span6" name="data[slug]" id="data_post_name" onDblClick="$('#data_post_name').removeAttr('readonly');" value="<?php echo $data['slug']; ?>" readonly />
                    <?php
                    if ( $data[ 'term_id' ] > 0 ) {
                        ?>
                    <a href="<?php $term_model->the_permalink($data); ?>" class="bluecolor">Xem <i class="fa fa-eye"></i></a>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <?php
            }

            ?>
            <div class="control-group">
                <label class="control-label">Nội dung</label>
                <div class="controls" style="width:80%;">
                    <textarea id="Resolution" rows="30" data-height="550" class="ckeditor auto-ckeditor" placeholder="Nhập thông tin chi tiết..." name="data[description]"><?php echo $data['description']; ?></textarea>
                </div>
            </div>
            <?php
            // cho phép xác định cha con với danh mục
            if ( $set_parent != '' ) {
                ?>
            <div class="control-group">
                <label class="control-label">Danh mục cha</label>
                <div class="controls">
                    <select data-select="<?php echo $data['parent']; ?>" name="data[parent]" id="data_parent">
                        <option value="0">[ Chọn Danh mục cha ]</option>
                    </select>
                </div>
            </div>
            <?php
            }
            ?>
            <div class="control-group">
                <label class="control-label">Số thứ tự:</label>
                <div class="controls">
                    <input type="number" class="span6" value="<?php echo $data['term_order']; ?>" name="data[term_order]" />
                    <p class="controls-text-note">Sắp xếp vị trí hiển thị, số càng to thì độ ưu tiên càng cao</p>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <h5>Thông số hiển thị cho widget:</h5>
                </div>
            </div>
            <?php

            // nạp các meta theo từng loại post
            foreach ( $meta_detault as $k => $v ) {
                $input_type = TaxonomyType::meta_type( $k );

                //
                ?>
            <div class="control-group">
                <?php
                if ( $input_type == 'checkbox' ) {
                    ?>
                <div class="controls controls-checkbox">
                    <label>
                        <input type="checkbox" name="term_meta[<?php echo $k; ?>]" id="term_meta_<?php echo $k; ?>" value="on" data-value="<?php $term_model->echo_meta_term($data, $k); ?>" />
                        <?php echo $v; ?></label>
                    <?php

                    // hiển thị ghi chú nếu có
                    TaxonomyType::meta_desc( $k );

                    ?>
                </div>
                <?php
                }
                // END if input type checkbox
                else {
                    ?>
                <label for="term_meta_<?php echo $k; ?>" class="control-label"><?php echo $v; ?></label>
                <div class="controls">
                    <?php

                    //
                    if ( $input_type == 'textarea' ) {
                        ?>
                    <textarea style="width:80%;" placeholder="<?php echo $v; ?>" name="term_meta[<?php echo $k; ?>]" id="term_meta_<?php echo $k; ?>"><?php $term_model->echo_meta_term($data, $k); ?>
</textarea>
                    <?php
                    }
                    // END if input type textarea
                    else if ( $input_type == 'select' ) {
                        $select_options = TaxonomyType::meta_select( $k );

                        ?>
                    <select data-select="<?php $term_model->echo_meta_term($data, $k); ?>" name="term_meta[<?php echo $k; ?>]">
                        <?php

                        foreach ( $select_options as $option_k => $option_v ) {
                            echo '<option value="' . $option_k . '">' . $option_v . '</option>';
                        }

                        ?>
                    </select>
                    <?php
                    }
                    // END else if input type select                    
                    else {
                        ?>
                    <input type="<?php echo $input_type; ?>" class="span6" placeholder="<?php echo $v; ?>" name="term_meta[<?php echo $k; ?>]" id="term_meta_<?php echo $k; ?>" value="<?php $term_model->echo_meta_term($data, $k); ?>" />
                    <?php
                    }

                    // hiển thị ghi chú nếu có
                    TaxonomyType::meta_desc( $k );

                    ?>
                </div>
                <?php
                } // END else input type checkbox
                ?>
            </div>
            <?php
            } // END auto add term_meta

            ?>
            <div class="form-actions frm-fixed-btn">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Lưu lại</button>
                <?php
                if ( $data[ 'term_id' ] > 0 ) {
                    ?>
                <a href="admin/terms/<?php echo $controller_slug; ?>?id=<?php echo $data[ 'term_id' ]; ?>" onClick="return click_a_delete_record();" class="btn btn-danger" target="target_eb_iframe"><i class="fa fa-trash"></i> XÓA</a>
                <?php
                }
                ?>
            </div>
        </form>
    </div>
</div>
<script>
WGR_widget_add_custom_style_to_field();

//
var set_parent = '<?php echo $set_parent; ?>';
var data_term_id = <?php echo ($data[ 'term_id' ] != '' ? $data[ 'term_id' ] : 0); ?>;

//
angular.module('myApp', []).controller('myCtrl', function ($scope) {
    $scope.data_term_id = data_term_id;
});
</script>
<?php

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/term_add.js' );
$base_model->add_js( 'admin/js/' . $taxonomy . '.js' );
