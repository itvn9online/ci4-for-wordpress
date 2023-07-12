<?php

//
use App\Libraries\ConfigType;

//
//print_r( $data );

// css riêng cho từng config (nếu có)
$base_model->add_css('admin/css/config_' . $config_type . '.css');

?>
<div id="my_app" :class="vue_data.config_type" class="widget-box">
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
            <div class="control-group">
                <div class="text-center l35">Ngôn ngữ: <strong>{{vue_data.lang_name}}</strong> </div>
            </div>
            <br>
            <div class="w99 redcolor l20 medium">
                <ul>
                    <li>Bản dịch cho website. Sử dụng bằng cách copy code ở cột bên phải và cho vào code (trong view sẽ bỏ đoạn <strong>this-></strong> đi).</li>
                    <li>Hàm <strong>the_text()</strong> sẽ thực thi echo luôn. Nếu chỉ muốn lấy về giá trị mà không echo, hãy sử dụng: <strong>get_the_text()</strong></li>
                    <li>Tham số của hàm <strong>the_text()</strong>:
                        <ol>
                            <li><strong>key</strong>: dùng để phân biệt giữa các bản ghi khác nhau.</li>
                            <li><strong>default_value</strong>: giá này trị sẽ được insert vào CSDL nếu chưa có.</li>
                        </ol>
                    </li>
                    <li>Để XÓA 1 bản ghi:
                        <ol>
                            <li>Xóa hết nội dung của bản ghi đó và bấm cập nhật, các bản ghi không có nội dung sẽ bị xóa.</li>
                            <li>Vào code tìm và xóa đoạn code tương ứng nếu không bản ghi sẽ tự động được tạo mới khi hàm <strong>get_the_text()</strong> hoặc <strong>the_text()</strong> được gọi trở lại.</li>
                        </ol>
                    </li>
                    <li>Để THÊM 1 bản ghi, hãy copy lệnh PHP dưới đây và cho vào trong file code, bản ghi sẽ được tự động thêm vào hệ thống nếu chưa có.</li>
                    <li>Để thay đổi tên cho input, hãy điều chỉnh tham số: <strong>TRANS_TRANS_LABEL</strong></li>
                    <li>Tìm và thay đổi code cho các phiên bản cũ: <strong>getconfig->custom_checkbox</strong></li>
                </ul>
            </div>
            <div class="text-center">
                <div>
                    <input type="text" onDblClick="click2Copy(this);" value="$this->lang_model->the_text('lang_key', 'lang default value');" class="span8 bold text-center" style="max-width: 666px;" readonly />
                </div>
                <div>
                    <input type="text" onDblClick="click2Copy(this);" value="&lt;?php $lang_model->the_text('lang_key', 'lang default value'); ?&gt;" class="span8 bold text-center" style="max-width: 666px;" readonly />
                </div>
            </div>
            <br>
            <?php

            //
            foreach ($trans_data as $k => $v) {
                $lang_k = str_replace('lang_', '', $k);
                //echo $lang_k . '<br>' . PHP_EOL;
                if (isset($trans_custom_type[$k])) {
                    $input_type = ConfigType::meta_type($lang_k);
                } else {
                    $input_type = 'textarea';
                }
                //echo $k . '<br>' . PHP_EOL;
                //echo $input_type . '<br>' . PHP_EOL;
                if (strpos($v, '"') === false) {
                    $non_html = strip_tags($v);
                } else {
                    $non_html = '';
                }

            ?>
                <div class="control-group eb-control-group cf">
                    <div class="lf f15">
                        <label for="data_<?php echo $k; ?>" class="text-right right-menu-space">
                            <?php echo $k; ?>
                        </label>
                    </div>
                    <div class="lf f50">
                        <?php

                        if ($input_type == 'textarea') {
                        ?>
                            <textarea class="span10 required fix-textarea-height change-auto-save-translate" placeholder="<?php echo $non_html; ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>"><?php echo htmlentities($v, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <?php
                        } // END if textarea
                        else {
                        ?>
                            <input type="text" class="span10" placeholder="<?php echo ConfigType::placeholder($lang_k, $v); ?>" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="<?php echo htmlentities($v, ENT_QUOTES, 'UTF-8'); ?>" />
                        <?php
                        }

                        //
                        ConfigType::meta_desc($lang_k);

                        ?>
                    </div>
                    <div class="lf f35">
                        <div>
                            <input type="text" onDblClick="click2Copy(this);" value="$this->lang_model->the_text('<?php echo str_replace('lang_', '', $k); ?>');" class="span11" readonly />
                        </div>
                        <div>
                            <input type="text" onDblClick="click2Copy(this);" value="&lt;?php $lang_model->the_text('<?php echo str_replace('lang_', '', $k); ?>'); ?&gt;" class="span11" readonly />
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
$base_model->JSON_parse(
    [
        'arr_meta_default' => $meta_default,
        'arr_trans_label' => TRANS_TRANS_LABEL,
        'vue_data' => $vue_data,
    ]
);

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

?>
<script>
    WGR_vuejs('#my_app', {
        vue_data: vue_data,
    });
</script>