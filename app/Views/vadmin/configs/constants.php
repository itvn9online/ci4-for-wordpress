<?php

//
use App\Libraries\ConfigType;

// 
// print_r($data);

// css riêng cho từng config (nếu có)
$base_model->add_css('wp-admin/css/config_' . $config_type . '.css');

?>
<div id="app" :class="vue_data.config_type" class="widget-box">
    <ul class="admin-breadcrumb">
        <li>{{vue_data.config_name}} settings</li>
    </ul>
    <div class="widget-title"> <span class="icon"> <i class="icon-align-justify"></i> </span>
        <h5>{{vue_data.config_name}} settings</h5>
    </div>
    <div class="widget-content nopadding config-main">
        <form action="sadmin/constants/update_constants" method="post" name="admin_global_form" id="admin_global_form" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <div class="d-none">
                <textarea id="list_field_has_change" name="list_field_has_change" placeholder="Các input, textarea, select... nào có thay đổi thì mới thực hiện lưu dữ liệu."></textarea>
            </div>
            <div class="control-group">
                <div class="text-center l35">Ngôn ngữ: <strong>{{vue_data.lang_name}}</strong> </div>
            </div>
            <br>
            <div class="w99 redcolor l20 medium">
                <ul>
                    <li>Xin lưu ý! Phần constants này thuộc dạng đặc biệt nguy hiểm do thông số sẽ được lưu đồng thời vào file tĩnh để nạp cho website, chỉ thay đổi khi bạn có sẵn tài khoản host để xử lý phòng trường hợp lỗi có thể xảy ra.</li>
                    <li>Các constants này sẽ được lưu trữ tại file: <b><?php echo str_replace(ROOTPATH, '', DYNAMIC_CONSTANTS_PATH); ?></b></li>
                    <li>Các constants này có độ ưu tiên cao nhất, phủ định ngay cả constants được thiết lập cứng trong file <b>functions.php</b> của mỗi theme.</li>
                    <li>Chọn <b>is empty</b> hoặc nhập <b>IS_EMPTY</b> để thiết lập các constants trống.</li>
                    <li>Để sử dụng constants mặc định, hãy bỏ chọn <b>is empty</b> và xóa giá trị trong input tương ứng.</li>
                    <li>Trong trường hợp thay đổi dữ liệu ở đây xong mà gây lỗi website! <a href="removeDynamicConstants.php?token=<?php echo $base_model->MY_sessid(); ?>&code=<?php echo md5($session_data['user_email']); ?>" onclick="return confirm('Confirm remove DynamicConstants.php file!');" target="_blank" class="bold" style="color: #090 !important;">Bấm vào đây</a> để thực hiện xóa file <strong><?php echo str_replace(ROOTPATH, '', DYNAMIC_CONSTANTS_PATH); ?></strong> sau đó xử lý lỗi phát sinh nếu có.</li>
                </ul>
            </div>
            <?php

            // in ra nội dung constants hiện tại để tiện theo dõi
            if (is_file(DYNAMIC_CONSTANTS_PATH)) {
                $current_dynamic_constants = file_get_contents(DYNAMIC_CONSTANTS_PATH);
            ?>
                <div class="text-center w99">
                    <textarea title="Current dynamic constants" id="current_dynamic_constants" rows="<?php echo count(explode("\n", $current_dynamic_constants)); ?>" class="form-control small" readonly disabled><?php echo $base_model->the_esc_html($current_dynamic_constants); ?></textarea>
                </div>
                <br>
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
                        <div class="lf f25">&nbsp;</div>
                        <div class="lf f40 controls-checkbox">
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
                    else {
                    ?>
                        <div class="lf f25">
                            <label for="data_<?php echo $k; ?>" class="text-right right-menu-space">
                                <?php echo $v; ?>
                            </label>
                        </div>
                        <div class="lf f40">
                            <?php

                            if ($input_type == 'textarea') {
                            ?>
                                <textarea class="span10 required fix-textarea-height" placeholder="<?php echo $v; ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>"><?php echo $data[$k]; ?></textarea>
                            <?php
                            }
                            // END if textarea
                            else if ($input_type == 'select') {
                                $select_options = ConfigType::meta_select($k);

                                // 
                                if ($k == 'EBE_DATE_FORMAT' || $k == 'EBE_DATE_TEXT_FORMAT') {
                                    foreach ($select_options as $option_k => $option_v) {
                                        if ($option_v == '') {
                                            // $select_options[$option_k] = date($option_k) . ' (' . $option_k . ')';
                                            $select_options[$option_k] = date($option_k);
                                        }
                                    }
                                }

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
                    <div class="lf f10">
                        <label><input type="checkbox" data-id="data_<?php echo $k; ?>" class="each-to-is-empty" /> is empty</label>
                    </div>
                    <div class="lf f25">
                        <input type="text" onDblClick="click2Copy(this);" value="<?php echo $k; ?>" class="span11" readonly />
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
$base_model->JSON_parse([
    'timezone_identifiers_list' => timezone_identifiers_list(),
    'site_language_fixed' => SITE_LANGUAGE_FIXED,
]);

//
$base_model->adds_js([
    'wp-admin/js/config_function.js',
    'wp-admin/js/config_options_translate.js',
]);

//
$base_model->adds_js([
    'wp-admin/js/config.js',
    'wp-admin/js/config_' . $config_type . '.js',
], [
    //'cdn' => CDN_BASE_URL,
], [
    'defer'
]);

//
$base_model->JSON_parse([
    'checkout_config' => $checkout_config,
    'vue_data' => $vue_data,
]);

?>
<script>
    WGR_vuejs('#app', {
        vue_data: vue_data,
    });
</script>