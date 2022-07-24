<?php

//
//print_r( $data );
$data[ 'comment_content' ] = nl2br( $data[ 'comment_content' ] );

?>
<ul class="admin-breadcrumb">
    <li><a :href="'admin/' + vue_data.controller_slug">Danh sách {{vue_data.comment_name}}</a></li>
    <li>Chi tiết {{vue_data.comment_name}}</li>
</ul>
<div class="widget-box">
    <div class="widget-content nopadding">
        <div class="form-horizontal">
            <div v-for="(v, k) in data" class="control-group">
                <label class="control-label">{{k.replace(/\_/gi, ' ')}}</label>
                <div class="controls">{{v}}</div>
            </div>
        </div>
    </div>
</div>
<script>
WGR_vuejs('#for_vue', {
    data: <?php echo json_encode($data); ?>,
    vue_data: <?php echo json_encode($vue_data); ?>,
});
</script> 
