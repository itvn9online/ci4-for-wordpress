<?php

//
use App\ Libraries\ ConfigType;
use App\ Libraries\ LanguageCost;

//
//$base_model = new\ App\ Models\ Base();

//print_r( $data );

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
    <div class="widget-content nopadding config-main">
        <form action="" method="post" name="admin_global_form" id="admin_global_form" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <div class="d-none">
                <textarea id="list_field_has_change" name="list_field_has_change" placeholder="Các input, textarea, select... nào có thay đổi thì mới thực hiện lưu dữ liệu."></textarea>
            </div>
            <div class="control-group">
                <div class="text-center l35">Ngôn ngữ: <strong><?php echo LanguageCost::list( $lang_key ); ?></strong> </div>
            </div>
            <?php

            //
            if ( $config_type == ConfigType::SMTP ) {
                ?>
            <div class="control-group eb-control-group cf">
                <div class="lf f15">
                    <label class="text-right right-menu-space">PHPMailer version</label>
                </div>
                <div class="lf f50">
                    <div class="bold s15">
                        <?php
                        echo file_get_contents( APPPATH . 'ThirdParty/PHPMailer/VERSION', 1 );
                        ?>
                    </div>
                    <p class="controls-text-note">Kiểm tra và tải phiên bản mới nhất <a href="https://github.com/PHPMailer/PHPMailer/releases" target="_blank" rel="noreferrer" class="bold bluecolor">tại đây</a>.</p>
                </div>
            </div>
            <?php
            }

            //
            foreach ( $meta_default as $k => $v ) {
                // chỉ hiển thị các meta có giá trị (được đặt tên)
                if ( $v == '' ) {
                    continue;
                }

                //
                $lang_k = str_replace( 'lang_', '', $k );
                //echo $lang_k . '<br>' . "\n";
                $input_type = ConfigType::meta_type( $lang_k );
                //echo $k . '<br>' . "\n";
                //echo $input_type . '<br>' . "\n";

                //
                if ( $input_type == 'hidden' ) {
                    ?>
            <input type="<?php echo $input_type; ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="<?php echo htmlentities( $data[$k], ENT_QUOTES, 'UTF-8' ); ?>" />
            <?php
            continue;
            } // END if hidden

            ?>
            <div class="control-group eb-control-group cf">
                <?php
                if ( $input_type == 'checkbox' ) {
                    ?>
                <div class="lf f15">&nbsp;</div>
                <div class="lf f85 controls-checkbox">
                    <label for="data_<?php echo $k; ?>">
                        <input type="checkbox" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="on" data-value="<?php echo $data[$k]; ?>" />
                        <?php echo $v; ?></label>
                    <?php

                    // hiển thị ghi chú nếu có
                    ConfigType::meta_desc( $lang_k );

                    ?>
                </div>
                <?php
                }
                // END if checkbox
                else if ( $input_type == 'color' ) {
                    ?>
                <div class="lf f15">
                    <label for="data_<?php echo $k; ?>" class="text-right right-menu-space"><?php echo $v; ?></label>
                </div>
                <div class="lf f85">
                    <input type="color" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="<?php echo $data[$k]; ?>" placeholder="<?php echo ConfigType::defaultColor($lang_k); ?>" class="span2 auto-reset-site-color" />
                    - <a href="javascript:;" data-set="data_<?php echo $k; ?>" class="bluecolor click-to-set-site-color">Nhập mã màu</a> - <a href="javascript:;" data-set="data_<?php echo $k; ?>" class="bluecolor click-to-reset-site-color">Mặc định</a>
                    <?php

                    // hiển thị ghi chú nếu có
                    ConfigType::meta_desc( $lang_k );

                    ?>
                </div>
                <?php
                }
                // END if color
                else {
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
                    }
                    // END if textarea
                    else if ( $input_type == 'select' ) {
                        $select_options = ConfigType::meta_select( $lang_k );

                        ?>
                    <select data-select="<?php echo $data[$k]; ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>">
                        <?php

                        foreach ( $select_options as $option_k => $option_v ) {
                            echo '<option value="' . $option_k . '">' . $option_v . '</option>';
                        }

                        ?>
                    </select>
                    <?php
                    }
                    // END if select
                    else {
                        // thay đổi độ rộng của inpurt cho phù hợp
                        $span10 = 'span10';
                        if ( $input_type == 'number' ) {
                            $span10 = 'span3';
                        } else if ( $input_type == 'email' ) {
                            $span10 = 'span5';
                        }

                        //
                        ?>
                    <input type="<?php echo $input_type; ?>" class="<?php echo $span10; ?>" placeholder="<?php echo ConfigType::placeholder($lang_k, $v); ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="<?php echo htmlentities( $data[$k], ENT_QUOTES, 'UTF-8' ); ?>" />
                    <?php
                    }

                    //
                    ConfigType::meta_desc( $lang_k );

                    ?>
                </div>
                <?php
                } // END else checkbox
                ?>
            </div>
            <?php
            } // END foreach

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
