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
        <form action="" method="post" name="admin_global_form" id="admin_global_form" accept-charset="utf-8"
            class="form-horizontal" target="target_eb_iframe">
            <div class="d-none">
                <textarea id="list_field_has_change" name="list_field_has_change"
                    placeholder="Các input, textarea, select... nào có thay đổi thì mới thực hiện lưu dữ liệu."></textarea>
            </div>
            <div class="control-group">
                <div class="text-center l35">Ngôn ngữ: <strong>{{vue_data.lang_name}}</strong> </div>
            </div>
            <br>
            <div class="redcolor text-center l20">* Cấu hình dạng chuyên checkbox. Dùng để BẬT/ TẮT một chức năng nào đó
                trong website. <br>
                Website nào cần dùng thì code sẽ đặt tên cho checkbox và người dùng sẽ tiến hành bật tắt
                tại đây. Code sẽ if else để BẬT/ TẮT tính năng tương ứng. <br>
                - Để tăng số lượng checkbox, hãy điều chỉnh tham số: <strong>NUMBER_CHECKBOXS_INPUT</strong> <br>
                - Để thay đổi tên cho checkbox, hãy điều chỉnh tham số: <strong>TRANS_CHECKBOXS_LABEL</strong>
            </div>
            <?php

            //
            foreach ($meta_default as $k => $v) {
            ?>
            <div class="control-group eb-control-group cf">
                <div class="lf f30">&nbsp;</div>
                <div class="lf f35 controls-checkbox">
                    <label for="data_<?php echo $k; ?>">
                        <input type="checkbox" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="on"
                            data-value="<?php echo $data[$k]; ?>" />
                        <span class="replace-text-label">
                            <?php echo $v; ?>
                        </span>
                    </label>
                    <?php

                // hiển thị ghi chú nếu có
                ConfigType::meta_desc($k);

                    ?>
                </div>
                <div class="lf f35">
                    <input type="text" onDblClick="click2Copy(this);"
                        value="&lt;?php echo ($this->getconfig-><?php echo $k; ?> == 'on' ? 'on' : 'off'); ?&gt;"
                        class="span11" readonly />
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
        'arr_trans_label' => TRANS_CHECKBOXS_LABEL,
        'vue_data' => $vue_data,
    ]
);

//
$base_model->add_js('admin/js/config_function.js');

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