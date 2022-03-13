<?php

// Libraries
use App\ Libraries\ UsersType;
use App\ Libraries\ DeletedStatus;

?>
<script>
var col_filter = <?php echo json_encode($col_filter); ?>;
var scope_data = <?php echo json_encode($data); ?>;

//
angular.module('myApp', []).controller('myCtrl', function($scope) {
    $scope.data = scope_data;
    $scope.list = <?php echo json_encode($arr_members_type); ?>;
    $scope.UsersType_listStatus = <?php echo json_encode(UsersType::listStatus()); ?>;
    $scope.for_action = '<?php echo $for_action; ?>';
    $scope.DeletedStatus_DELETED = '<?php echo DeletedStatus::DELETED; ?>';
});
</script>

<ul class="admin-breadcrumb">
    <li><a href="admin/<?php echo $controller_slug; ?>">Danh sách <?php echo $member_name; ?></a> (<?php echo $totalThread; ?>)</li>
    <?php
    if ( $member_type != '' ) {
        ?>
    <li><?php echo $member_name; ?></li>
    <?php
    }
    ?>
</ul>
<div ng-app="myApp" ng-controller="myCtrl">
    <div class="cf admin-search-form">
        <div class="lf f50">
            <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
                <input type="hidden" name="member_type" value="<?php echo $member_type; ?>">
                <div class="cf">
                    <div class="lf f30">
                        <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $member_name; ?>" autofocus aria-required="true" required>
                    </div>
                    <div class="lf f30">
                        <select name="user_status" data-select="<?php echo $by_user_status; ?>" onChange="document.frm_admin_search_controller.submit();">
                            <option value="all">- Trạng thái đăng nhập -</option>
                            <option value="{{k}}" ng-repeat="(k, v) in UsersType_listStatus">{{v}}</option>
                        </select>
                    </div>
                    <div class="lf f20">
                        <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="lf f50 text-right">
            <div class="d-inline"> <a href="admin/<?php echo $controller_slug; ?>/add" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới <?php echo $member_name; ?></a> </div>
            <div class="d-inline"><a href="admin/<?php echo $controller_slug; ?>?member_type=<?php echo $member_type; ?>&is_deleted=<?php echo DeletedStatus::DELETED; ?>" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
        </div>
    </div>
    <br>
    <table class="table table-bordered table-striped with-check table-list eb-table">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectall" name="selectall"/></th>
                <th>ID</th>
                <th>Tài khoản</th>
                <th>Email</th>
                <th>Tên hiển thị</th>
                <th>Nhóm</th>
                <th>Trạng thái đăng nhập</th>
                <th><a href="admin/<?php echo $controller_slug; ?>?member_type=<?php echo $member_type; ?>&order_by=last_login">Đăng nhập cuối <i class="fa fa-sort"></i></a></th>
                <th>Ngày đăng ký</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody id="admin_main_list">
            <tr data-id="{{v.ID}}" ng-repeat="v in data">
                <td>&nbsp;</td>
                <td><a href="admin/<?php echo $controller_slug; ?>/add?id={{v.ID}}">{{v.ID}}</a></td>
                <td><a href="admin/<?php echo $controller_slug; ?>/add?id={{v.ID}}">{{v.user_login}}</a></td>
                <td><a href="admin/<?php echo $controller_slug; ?>/add?id={{v.ID}}">{{v.user_email}}</a></td>
                <td>{{v.display_name}}</td>
                <td><a href="admin/<?php echo $controller_slug; ?>?member_type={{v.member_type}}">{{list[v.member_type]}}</a></td>
                <td>{{UsersType_listStatus[v.user_status]}}</td>
                <td>{{v.last_login.substr(0, 16)}}</td>
                <td>{{v.user_registered.substr(0, 16)}}</td>
                <td class="text-center"><div>
                        <div ng-if="v.is_deleted != DeletedStatus_DELETED">
                            <div><a href="admin/<?php echo $controller_slug; ?>/delete?id={{v.ID + for_action}}" onClick="return click_a_delete_record();" class="redcolor" target="target_eb_iframe"><i class="fa fa-trash"></i></a> </div>
                        </div>
                        <div ng-if="v.is_deleted == DeletedStatus_DELETED">
                            <div><a href="admin/<?php echo $controller_slug; ?>/restore?id={{v.ID + for_action}}" onClick="return click_a_restore_record();" class="bluecolor" target="target_eb_iframe"><i class="fa fa-undo"></i></a></div>
                        </div>
                    </div></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<?php

//
$base_model->add_js( 'admin/js/users.js' );
$base_model->add_js( 'admin/js/' . $member_type . '.js' );
