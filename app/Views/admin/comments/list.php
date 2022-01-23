<?php

// Libraries
use App\ Libraries\ CommentType;

//
//$base_model = new\ App\ Models\ Base();

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/' . $comment_type . '.css' );

?>
<script>
angular.module('myApp', []).controller('myCtrl', function($scope) {
    $scope.data = <?php echo json_encode($data); ?>;
});
</script>

<ul class="admin-breadcrumb">
    <li>Danh sách <?php echo CommentType::list($comment_type); ?> (<?php echo $totalThread; ?>)</li>
</ul>
<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectall" name="selectall"/></th>
            <th>Tiêu đề</th>
            <th>Email</th>
            <th>Trạng thái</th>
            <th>IP</th>
            <th>Ngày tạo</th>
            <th>Lang</th>
        </tr>
    </thead>
    <tbody ng-app="myApp" ng-controller="myCtrl" ng-init="controller_slug='<?php echo $controller_slug; ?>';">
        <tr ng-repeat="v in data">
            <td>&nbsp;</td>
            <td><a href="admin/{{controller_slug}}?comment_id={{v.comment_ID}}">{{v.comment_title}} <i class="fa fa-edit"></i></a></td>
            <td>{{v.comment_author_email}}</td>
            <td>{{v.comment_approved}}</td>
            <td>{{v.comment_author_IP}}</td>
            <td>{{v.comment_date}}</td>
            <td>{{v.lang_key}}</td>
        </tr>
    </tbody>
</table>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<p class="d-none">* Copy đoạn code bên dưới rồi cho vào nơi cần hiển thị block này ở trong view. Nhớ thay %slug% thành slug thật trong danh sách ở trên.</p>
<?php

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/' . $comment_type . '.js' );
