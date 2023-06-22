<?php

//
use App\Libraries\ConfigType;

//print_r( $data );

// css riêng cho từng config (nếu có)
$base_model->add_css('admin/css/config_' . $config_type . '.css');

?>
<div id="app" :class="vue_data.config_type" class="widget-box">
    <ul class="admin-breadcrumb">
        <li>Cài đặt {{vue_data.config_name}}</li>
    </ul>
    <div class="widget-title"> <span class="icon"> <i class="icon-align-justify"></i> </span>
        <h5>Cài đặt {{vue_data.config_name}}</h5>
    </div>
    <div class="widget-content nopadding config-main">
        <form action="" method="post" name="admin_global_form" id="admin_global_form" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <div class="d-none">
                <textarea id="list_field_has_change" name="list_field_has_change" placeholder="Các input, textarea, select... nào có thay đổi thì mới thực hiện lưu dữ liệu."></textarea>
            </div>
            <p class="bold medium text-center top-menu-space">Ngôn ngữ: <strong>{{vue_data.lang_name}}</strong> </p>
            <?php

            //
            if ($config_type == ConfigType::SMTP) {
            ?>
                <div class="control-group eb-control-group cf">
                    <div class="lf f15">
                        <label class="text-right right-menu-space">PHPMailer version</label>
                    </div>
                    <div class="lf f50">
                        <div class="bold s15">
                            <?php
                            echo file_get_contents(APPPATH . 'ThirdParty/PHPMailer/phpmailer/phpmailer/VERSION', 1);
                            ?>
                        </div>
                        <p class="controls-text-note">Kiểm tra và tải phiên bản mới nhất tại đây: https://github.com/PHPMailer/PHPMailer</p>
                    </div>
                </div>
            <?php
            } else if ($config_type == ConfigType::SOCIAL) {
            ?>
                <div class="control-group eb-control-group cf">
                    <div class="lf f15">
                        <label class="text-right right-menu-space">Share icons</label>
                    </div>
                    <div class="lf f50">Chức năng tạo khối chia sẻ lên mạng xã hội theo cấu trúc chung.</div>
                    <div class="lf f35"><input type="text" onDblClick="click2Copy(this);" value="&lt;?php echo $this->option_model->share_icons($this-><?php echo $example_prefix; ?>); ?&gt;" class="span11" readonly />
                    </div>
                </div>
                <?php
            }

            //
            foreach ($meta_default as $k => $v) {
                // chỉ hiển thị các meta có giá trị (được đặt tên)
                if ($v == '') {
                    continue;
                }

                //
                $input_type = ConfigType::meta_type($k);
                //echo $k . '<br>' . PHP_EOL;
                //echo $input_type . '<br>' . PHP_EOL;

                //
                if ($input_type == 'heading') {
                ?>
                    <p class="bold medium text-center top-menu-space">
                        <?php echo $v; ?>
                    </p>
                <?php
                    continue;
                }

                //
                if ($input_type == 'hidden') {
                ?>
                    <input type="<?php echo $input_type; ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="<?php echo htmlentities($data[$k], ENT_QUOTES, 'UTF-8'); ?>" />
                <?php
                    continue;
                } // END if hidden

                ?>
                <div class="control-group eb-control-group cf">
                    <?php
                    if ($input_type == 'checkbox') {
                    ?>
                        <div class="lf f15">&nbsp;</div>
                        <div class="lf f50 controls-checkbox">
                            <label for="data_<?php echo $k; ?>">
                                <input type="checkbox" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="on" data-value="<?php echo $data[$k]; ?>" />
                                <?php echo $v; ?>
                            </label>
                            <?php

                            // hiển thị ghi chú nếu có
                            ConfigType::meta_desc($k);

                            ?>
                        </div>
                    <?php
                    }
                    // END if checkbox
                    else if ($input_type == 'color') {
                    ?>
                        <div class="lf f15">
                            <label for="data_<?php echo $k; ?>" class="text-right right-menu-space">
                                <?php echo $v; ?>
                            </label>
                        </div>
                        <div class="lf f50">
                            <input type="color" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="<?php echo $data[$k]; ?>" placeholder="<?php echo ConfigType::defaultColor($k); ?>" class="span2 auto-reset-site-color" />
                            - <a href="javascript:;" data-set="data_<?php echo $k; ?>" class="bluecolor click-to-set-site-color">Nhập mã màu</a> - <a href="javascript:;" data-set="data_<?php echo $k; ?>" class="bluecolor click-to-reset-site-color">Mặc định</a>
                            <?php

                            // hiển thị ghi chú nếu có
                            ConfigType::meta_desc($k);

                            ?>
                        </div>
                    <?php
                    }
                    // END if color
                    else {
                    ?>
                        <div class="lf f15">
                            <label for="data_<?php echo $k; ?>" class="text-right right-menu-space">
                                <?php echo $v; ?>
                            </label>
                        </div>
                        <div class="lf f50">
                            <?php

                            if ($input_type == 'textarea') {
                            ?>
                                <textarea class="span10 required fix-textarea-height" style="height:100px" placeholder="<?php echo $v; ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>"><?php echo $data[$k]; ?></textarea>
                            <?php
                            }
                            // END if textarea
                            else if ($input_type == 'select') {
                                $select_options = ConfigType::meta_select($k);

                            ?>
                                <select data-select="<?php echo $data[$k]; ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" class="span5">
                                    <?php

                                    foreach ($select_options as $option_k => $option_v) {
                                        echo '<option value="' . $option_k . '">' . $option_v . '</option>';
                                    }

                                    ?>
                                </select>
                            <?php
                            }
                            // END if select
                            else {
                                // thay đổi độ rộng của input cho phù hợp
                                $span10 = 'span10';
                                if ($input_type != 'text') {
                                    $span10 = 'span5';
                                }

                                //
                            ?>
                                <input type="<?php echo $input_type; ?>" class="<?php echo $span10; ?>" placeholder="<?php echo ConfigType::placeholder($k, $v); ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="<?php echo htmlentities($data[$k], ENT_QUOTES, 'UTF-8'); ?>" />
                            <?php
                            }

                            //
                            ConfigType::meta_desc($k);

                            ?>
                        </div>
                    <?php
                    } // END else checkbox
                    ?>
                    <div class="lf f35">
                        <div>
                            <input type="text" onDblClick="click2Copy(this);" value="$this-><?php echo $example_prefix; ?>-><?php echo $k; ?>" class="span11" readonly />
                        </div>
                        <div>
                            <input type="text" onDblClick="click2Copy(this);" value="&lt;?php $<?php echo $example_prefix; ?>-><?php echo $k; ?>; ?&gt;" class="span11" readonly />
                        </div>
                    </div>
                </div>
            <?php
            } // END foreach

            ?>
            <div class="form-actions frm-fixed-btn cf">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Cập nhật
                    {{vue_data.config_name}}</button>
            </div>
        </form>
    </div>
</div>
<?php

//
//print_r($getconfig);
//echo 'fb_app_id: ' . $getconfig->fb_app_id;
$base_model->JSON_echo([
    // mảng này sẽ in ra dưới dạng JSON hoặc number
], [
    // mảng này sẽ in ra dưới dạng string
    'fb_app_id' => $getconfig->fb_app_id,
    'zalo_app_id' => $zalooa_config->zalooa_app_id,
]);

//
$base_model->adds_js(
    [
        'admin/js/config_function.js',
        'admin/js/config_options_translate.js',
    ]
);

//
$base_model->adds_js(
    [
        'admin/js/config.js',
        'admin/js/config_' . $config_type . '.js',
    ],
    [
        //'cdn' => CDN_BASE_URL,
    ],
    [
        'defer'
    ]
);

//
$base_model->JSON_parse(
    [
        'arr_trans_label' => TRANS_TRANS_LABEL,
        'checkout_config' => $checkout_config,
        'vue_data' => $vue_data,
    ]
);

?>
<script>
    WGR_vuejs('#app', {
        vue_data: vue_data,
    });
</script>