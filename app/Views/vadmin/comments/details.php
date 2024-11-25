<ul class="admin-breadcrumb">
    <li><a :href="'sadmin/' + vue_data.controller_slug">Danh sách {{vue_data.comment_name}}</a></li>
    <li>Chi tiết {{vue_data.comment_name}}</li>
</ul>
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
        'vue_data' => $vue_data,
    ]
);

?>
<script type="text/javascript">
    WGR_vuejs('#for_vue', {
        data: json_data,
        vue_data: vue_data,
    }, function() {
        //console.log(Math.random());
        $('.controls-comment_content').html(WGR_show_html_for_vuejs($('.controls-comment_content').html()));
    });
</script>