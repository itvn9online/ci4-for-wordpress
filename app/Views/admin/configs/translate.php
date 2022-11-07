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
            <div class="redcolor text-center l20">* Bản dịch cho website. Sử dụng bằng cách copy code ở cột bên phải và
                cho vào view. Hàm <strong>the_text()</strong> sẽ thực thi echo luôn. Nếu chỉ muốn lấy về đoạn text mà
                không echo, hãy sử dụng: <strong>get_the_text()</strong>.</div>
            <?php

            //
            foreach ($meta_default as $k => $v) {
                $lang_k = str_replace('lang_', '', $k);
                //echo $lang_k . '<br>' . "\n";
                $input_type = ConfigType::meta_type($lang_k);
                //echo $k . '<br>' . "\n";
                //echo $input_type . '<br>' . "\n";
            
            ?>
            <div class="control-group eb-control-group cf">
                <div class="lf f15">
                    <label for="data_<?php echo $k; ?>" class="text-right right-menu-space">
                        <?php echo $v; ?>
                    </label>
                </div>
                <div class="lf f50">
                    <?php

                if ($input_type == 'textarea') {
                    ?>
                    <textarea class="span10 required fix-textarea-height" style="height:100px"
                        placeholder="<?php echo $v; ?>" name="data[<?php echo $k; ?>]"
                        id="data_<?php echo $k; ?>"><?php echo $data[$k]; ?></textarea>
                    <?php
                } // END if textarea
                else {
                    ?>
                    <input type="text" class="span10" placeholder="<?php echo ConfigType::placeholder($lang_k, $v); ?>"
                        name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>"
                        value="<?php echo htmlentities($data[$k], ENT_QUOTES, 'UTF-8'); ?>" />
                    <?php
                }

                //
                ConfigType::meta_desc($lang_k);

                    ?>
                </div>
                <div class="lf f35">
                    <input type="text" onDblClick="click2Copy(this);"
                        value="&lt;?php $lang_model->the_text( '<?php echo str_replace('lang_', '', $k); ?>' ); ?&gt;"
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
        'arr_trans_label' => TRANS_TRANS_LABEL,
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