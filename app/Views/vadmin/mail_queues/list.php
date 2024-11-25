<?php

// 
$base_model->add_css('wp-admin/css/mail-queues-list.css');

?>
<ul class="admin-breadcrumb">
    <li>Danh sách Mail queue (<?php echo $totalThread; ?>)</li>
</ul>
<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th>Email</th>
            <th>Tiêu đề</th>
            <th>IP</th>
            <th>Status</th>
            <th>Post ID</th>
            <th>Order ID</th>
            <th>Date</th>
            <th>Updated</th>
            <th>Sended</th>
        </tr>
    </thead>
    <tbody id="admin_main_list" class="ng-main-content">
        <tr :data-id="v.id" v-for="v in data" class="mail-queues-list" :class="v.status">
            <td>{{v.mailto}}</td>
            <td><a :href="'sadmin/mailqueues?mail_id=' + v.id">{{v.title}}</a></td>
            <td>{{v.ip}}</td>
            <td>{{v.status}}</td>
            <td>{{v.post_id}}</td>
            <td>{{v.order_id}}</td>
            <td>{{datetime(v.created_at * 1000)}}</td>
            <td>{{v.updated_at != null ? datetime(v.updated_at * 1000) : ''}}</td>
            <td>{{v.sended_at != null ? datetime(v.sended_at * 1000) : ''}}</td>
        </tr>
    </tbody>
</table>
<div class="public-part-page">
    <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.
</div>
<?php

//
$base_model->JSON_parse(
    [
        'json_data' => $data,
    ]
);

?>
<script type="text/javascript">
    WGR_vuejs('#for_vue', {
        for_action: '<?php echo $for_action; ?>',
        controller_slug: '<?php echo $controller_slug; ?>',
        data: json_data,
    });
</script>