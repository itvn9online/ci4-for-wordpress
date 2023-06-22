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
                    <li>Cấu hình dạng chuyên checkbox. Dùng để BẬT/ TẮT một chức năng nào đó
                        trong website. Sử dụng bằng cách copy code ở cột bên phải và cho vào code (trong view sẽ bỏ đoạn <strong>this-></strong> đi).</li>
                    <li>Giá trị trả về sẽ là <strong>on</strong> hoặc <strong>off</strong>, câu lệnh chuẩn để sử dụng trong <strong>if else</strong> sẽ là <strong>== 'on'</strong> hoặc <strong>!= 'on'</strong></li>
                    <li>Hàm <strong>the_checkbox()</strong> sẽ thực thi echo luôn. Nếu chỉ muốn lấy về giá trị mà không echo, hãy sử dụng: <strong>get_the_checkbox()</strong></li>
                    <li>Tham số của hàm <strong>the_checkbox()</strong>:
                        <ol>
                            <li><strong>key</strong>: dùng để phân biệt giữa các bản ghi khác nhau.</li>
                        </ol>
                    </li>
                    <li>Để tăng số lượng checkbox, hãy điều chỉnh tham số: <strong>NUMBER_CHECKBOXS_INPUT</strong></li>
                    <li>Để thay đổi tên cho checkbox, hãy điều chỉnh tham số: <strong>TRANS_CHECKBOXS_LABEL</strong></li>
                </ul>
            </div>
            <?php

            //
            foreach ($meta_default as $k => $v) {
            ?>
                <div class="control-group eb-control-group cf">
                    <div class="lf f30">&nbsp;</div>
                    <div class="lf f35 controls-checkbox">
                        <label for="data_<?php echo $k; ?>">
                            <input type="checkbox" name="data[<?php echo $k; ?>]" id="data_<?php echo $k; ?>" value="on" data-value="<?php echo $data[$k]; ?>" />
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
                        <div>
                            <input type="text" onDblClick="click2Copy(this);" value="$this->checkbox_model->the_checkbox('<?php echo $k; ?>');" class="span11" readonly />
                        </div>
                        <div>
                            <input type="text" onDblClick="click2Copy(this);" value="$checkbox_model->the_checkbox('<?php echo $k; ?>');" class="span11" readonly />
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
        'arr_trans_label' => TRANS_CHECKBOXS_LABEL,
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