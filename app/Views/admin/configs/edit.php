<?php

//
use App\ Libraries\ ConfigType;
use App\ Libraries\ LanguageCost;

//
//$base_model = new\ App\ Models\ Base();

// css riêng cho từng config (nếu có)
$base_model->add_css( 'admin/css/config_' . $config_type . '.css' );

?>
<ul class="admin-breadcrumb">
    <li>Cài đặt <?php echo ConfigType::list( $config_type ); ?></li>
</ul>
<div class="widget-box <?php echo $config_type; ?>">
    <div class="widget-title"> <span class="icon"> <i class="icon-align-justify"></i> </span>
        <h5>Cài đặt <?php echo ConfigType::list( $config_type ); ?></h5>
    </div>
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="contact-form" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <div class="control-group">
                <div class="text-center l35">Ngôn ngữ: <strong><?php echo LanguageCost::list( $lang_key ); ?></strong> </div>
            </div>
            <?php

            //print_r( $data );

            //
            if ( $config_type == ConfigType::TRANS ) {
                //
                ?>
            <br>
            <div class="w99">
                <pre><code>&lt;?php $lang_model = new\ App\ Models\ Lang(); ?&gt;</code></pre>
            </div>
            <br>
            <div class="redcolor text-center l20">* Bản dịch cho website. Sử dụng bằng cách copy code ở cột bên phải và cho vào view. Hàm <strong>the_text()</strong> sẽ thực thi echo luôn. Nếu chỉ muốn lấy về đoạn text mà không echo, hãy sử dụng: <strong>get_the_text()</strong>.</div>
            <?php
            }

            //
            foreach ( $meta_default as $k => $v ) {

                if ( $v != '' ) {
                    $input_type = ConfigType::meta_type( $k );

                    ?>
            <div class="control-group eb-control-group cf">
                <?php
                if ( $input_type == 'checkbox' ) {
                    ?>
                <div class="lf f15">&nbsp;</div>
                <div class="lf f85 controls-checkbox">
                    <label>
                        <input type="checkbox" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="on" data-value="<?php echo $data[$k]; ?>" />
                        <?php echo $v; ?></label>
                    <?php

                    // hiển thị ghi chú nếu có
                    ConfigType::meta_desc( $k );

                    ?>
                </div>
                <?php
                } else {
                    ?>
                <div class="lf f15">
                    <label for="data_<?php echo $k; ?>" class="text-right right-menu-space"><?php echo $v; ?></label>
                </div>
                <div class="lf f50">
                    <?php

                    if ( $input_type == 'textarea' ) {
                        ?>
                    <textarea class="span10 required fix-textarea-height" style="height:100px" placeholder="<?php echo $v; ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>"><?php echo $data[$k]; ?></textarea>
                    <?php
                    } else if ( $input_type == 'select' ) {
                        $select_options = ConfigType::meta_select( $k );

                        ?>
                    <select data-select="<?php echo $data[$k]; ?>" name="data[<?php echo $k; ?>]">
                        <?php

                        foreach ( $select_options as $option_k => $option_v ) {
                            echo '<option value="' . $option_k . '">' . $option_v . '</option>';
                        }

                        ?>
                    </select>
                    <?php
                    } else {
                        // thay đổi độ rộng của inpurt cho phù hợp
                        $span10 = 'span10';
                        if ( $input_type == 'number' ) {
                            $span10 = 'span3';
                        } else if ( $input_type == 'email' ) {
                            $span10 = 'span5';
                        }

                        //
                        ?>
                    <input type="<?php echo $input_type; ?>" class="<?php echo $span10; ?>" placeholder="<?php echo $v; ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="<?php echo $data[$k]; ?>" />
                    <?php
                    }

                    //
                    ConfigType::meta_desc( $k );

                    ?>
                </div>
                <?php
                if ( $config_type == ConfigType::TRANS ) {
                    //
                    ?>
                <div class="lf f35">
                    <input type="text" onClick="this.select()" value="&lt;?php $lang_model->the_text( '<?php echo str_replace('lang_','',$k); ?>' ); ?&gt;" class="span11" readonly />
                </div>
                <?php
                }
                ?>
            </div>
            <?php
            }
            }
            }

            ?>
            <div class="form-actions frm-fixed-btn">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Cập nhật</button>
            </div>
        </form>
    </div>
</div>
<!--
<link rel="stylesheet" href="admin/css/colorpicker.css"/>
<link rel="stylesheet" href="admin/css/uniform.css"/>
<script src="admin/js/menu-edit.js"></script> 
<script src="admin/js/select2.min.js"></script> 
<script src="admin/js/maruti.form_common.js"></script> 
<script src="admin/js/bootstrap-colorpicker.js"></script>
-->
<?php

$base_model->add_js( 'admin/js/config.js' );
$base_model->add_js( 'admin/js/config_' . $config_type . '.js' );
