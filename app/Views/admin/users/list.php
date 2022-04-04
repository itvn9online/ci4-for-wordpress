<?php

// Libraries
use App\ Libraries\ UsersType;
use App\ Libraries\ DeletedStatus;

?>
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
<div id="app" class="ng-main-content">
    <div class="cf admin-search-form">
        <div class="lf f50">
            <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
                <input type="hidden" name="member_type" :value="member_type">
                <div class="cf">
                    <div class="lf f30">
                        <?php
                        if ( $by_is_deleted > 0 ) {
                            ?>
                        <input type="hidden" name="is_deleted" value="<?php echo $by_is_deleted; ?>">
                        <?php
                        }
                        ?>
                        <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $member_name; ?>" autofocus aria-required="true" required>
                    </div>
                    <div class="lf f30">
                        <select name="user_status" data-select="<?php echo $by_user_status; ?>" onChange="document.frm_admin_search_controller.submit();">
                            <option value="all">- Trạng thái đăng nhập -</option>
                            <option v-for="(v, k) in UsersType_listStatus" :value="k">{{v}}</option>
                        </select>
                    </div>
                    <div class="lf f20">
                        <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="lf f50 text-right">
            <?php

            //
            include __DIR__ . '/list_right_button.php';

            ?>
        </div>
    </div>
    <br>
    <?php

    //
    include __DIR__ . '/list_select_all.php';

    ?>
    <table class="table table-bordered table-striped with-check table-list eb-table">
        <thead>
            <tr>
                <th><input type="checkbox" class="input-checkbox-all" /></th>
                <th>ID</th>
                <th>Tài khoản</th>
                <th>Email</th>
                <th>Tên hiển thị</th>
                <th>Nhóm</th>
                <th>Trạng thái đăng nhập</th>
                <th><a :href="'admin/' + controller_slug + '?member_type=' + member_type + '&order_by=last_login'">Đăng nhập cuối <i class="fa fa-sort"></i></a></th>
                <th>Ngày đăng ký</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="v in data" :data-id="v.ID">
                <td class="text-center"><input type="checkbox" value="{{v.ID}}" class="input-checkbox-control" /></td>
                <td><a :href="'admin/' + controller_slug + '/add?id=' + v.ID">{{v.ID}}</a></td>
                <td><a :href="'admin/' + controller_slug + '/add?id=' + v.ID">{{v.user_login}}</a></td>
                <td><a :href="'admin/' + controller_slug + '/add?id=' + v.ID">{{v.user_email}}</a></td>
                <td>{{v.display_name}} ({{v.user_nicename}})</td>
                <td><a :href="'admin/' + controller_slug + '?member_type=' + v.member_type">{{list[v.member_type]}}</a></td>
                <td>{{UsersType_listStatus[v.user_status]}}</td>
                <td>{{v.last_login.substr(0, 16)}}</td>
                <td>{{v.user_registered.substr(0, 16)}}</td>
                <td class="text-center"><div>
                        <div v-if="v.is_deleted != DeletedStatus_DELETED">
                            <div><a :href="'admin/' + controller_slug + '/delete?id=' + v.ID + for_action" onClick="return click_a_delete_record();" class="redcolor" target="target_eb_iframe"><i class="fa fa-trash"></i></a> </div>
                        </div>
                        <div v-if="v.is_deleted == DeletedStatus_DELETED">
                            <div><a :href="'admin/' + controller_slug + '/restore?id=' + v.ID + for_action" onClick="return click_a_restore_record();" class="bluecolor" target="target_eb_iframe"><i class="fa fa-undo"></i></a></div>
                        </div>
                    </div></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<script>
var col_filter = <?php echo json_encode($col_filter); ?>;
var scope_data = <?php echo json_encode($data); ?>;

//
WGR_vuejs('#app', {
    member_name: '<?php echo $member_name; ?>',
    member_type: '<?php echo $member_type; ?>',
    controller_slug: '<?php echo $controller_slug; ?>',
    data: scope_data,
    for_action: '<?php echo $for_action; ?>',
    DeletedStatus_DELETED: '<?php echo DeletedStatus::DELETED; ?>',
    by_is_deleted: '<?php echo $by_is_deleted; ?>',
    list: <?php echo json_encode($arr_members_type); ?>,
    UsersType_listStatus: <?php echo json_encode(UsersType::listStatus()); ?>,
});
</script>
<?php

//
$base_model->add_js( 'admin/js/users.js' );
$base_model->add_js( 'admin/js/' . $member_type . '.js' );
