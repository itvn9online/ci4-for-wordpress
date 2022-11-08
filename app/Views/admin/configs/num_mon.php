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
            <div class="redcolor text-center l20">* Cấu hình dạng chuyên số nguyên. Dùng để thiết lập giá trị dạng số
                trong quá trình vận hành website sẽ có một số code cần dùng đến. <br>
                Lúc nào cần dùng thì code sẽ đặt tên
                cho config này trong file <strong>functions.php</strong> của từng theme, sau đó lấy giá trị trong đây để
                sử dụng nó. <br>
                - Để tăng số lượng input, hãy điều chỉnh tham số: <strong>NUMBER_NUMS_INPUT</strong> <br>
                - Để thay đổi tên cho input, hãy điều chỉnh tham số: <strong>TRANS_NUMS_LABEL</strong>
            </div>
            <?php

            //
            foreach ($meta_default as $k => $v) {
            ?>
            <div class="control-group eb-control-group cf">
                <div class="lf f30">
                    <label for="data_<?php echo $k; ?>" class="text-right right-menu-space">
                        <?php echo $v; ?>
                    </label>
                </div>
                <div class="lf f35">
                    <input type="number" class="span6" placeholder="<?php echo ConfigType::placeholder($k, $v); ?>"
                        name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>"
                        value="<?php echo htmlentities($data[$k], ENT_QUOTES, 'UTF-8'); ?>" />
                    <?php

                //
                ConfigType::meta_desc($k);

                    ?>
                </div>
                <div class="lf f35">
                    <input type="text" onDblClick="click2Copy(this);"
                        value="&lt;?php echo $this->getconfig-><?php echo $k; ?>; ?&gt;" class="span11" readonly />
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
        'arr_trans_label' => TRANS_NUMS_LABEL,
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