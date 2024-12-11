<?php

// 
$base_model->add_css('wp-admin/css/shop_order.css');

?>
<ul class="admin-breadcrumb">
    <li><a href="sadmin/<?php echo $controller_slug; ?>">Danh sách Mail queue</a></li>
    <li>Chi tiết Mail queue</li>
</ul>
<p class="orgcolor show-if-order-popup">* Press <strong>ESC</strong> or <span onclick="top.hide_if_esc();" class="cur bluecolor">Click here</span> for close this window!</p>
<div class="widget-box">
    <div class="widget-content nopadding">
        <div class="form-horizontal">
            <div v-for="(v, k) in data" class="control-group">
                <label class="control-label">{{k.replace(/\_/gi, ' ')}}</label>
                <div :class="'controls-' + k" class="controls">{{v}}</div>
            </div>
        </div>
    </div>
</div>
<?php

//
$base_model->JSON_parse(
    [
        'json_data' => $data,
    ]
);

// 
$base_model->adds_js([
    'wp-admin/js/popup_functions.js',
    'wp-admin/js/mailqueue_details.js',
]);
