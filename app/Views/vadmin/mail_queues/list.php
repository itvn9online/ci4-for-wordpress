<?php

// 
$base_model->adds_css([
    'wp-admin/css/mail-queues-list.css',
    'wp-admin/css/order_list.css',
]);

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
            <td>
                <a :href="'sadmin/mailqueues?mail_id=' + v.id" :data-id="v.id" class="orders-open-popup">{{v.title}} <i class="fa fa-edit"></i></a>
            </td>
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
<iframe id="order_details_iframe" name="order-details-iframe" title="Orderdetails iframe" src="about:blank" width="66%" frameborder="0" class="hide-if-esc">AJAX form</iframe>
<?php

//
$base_model->JSON_parse(
    [
        'json_data' => $data,
        'json_params' => [
            'for_action' => $for_action,
            'controller_slug' => $controller_slug,
        ],
    ]
);

// 
$base_model->adds_js([
    'wp-admin/js/popup_functions.js',
    'wp-admin/js/mailqueue_list.js',
]);
