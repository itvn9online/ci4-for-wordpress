<?php

//
use App\ Libraries\ UsersType;

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
            <th><a :href="'admin/' + controller_slug + '?member_type=' + member_type + '&order_by=last_login'">Đăng nhập cuối <i class="fa fa-sort"></i></a></th>
            <th>Ngày đăng ký</th>
            <th width="90">Trạng thái</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="admin_main_list">
        <tr v-for="v in data" :data-id="v.ID">
            <td class="text-center"><input type="checkbox" :value="v.ID" class="input-checkbox-control" /></td>
            <td><a :href="'admin/' + controller_slug + '/add?id=' + v.ID">{{v.ID}}</a></td>
            <td><a :href="'admin/' + controller_slug + '/add?id=' + v.ID">{{v.user_login}}</a></td>
            <td><a :href="'admin/' + controller_slug + '/add?id=' + v.ID">{{v.user_email}}</a></td>
            <td>{{v.display_name}}<span v-if="v.user_nicename != ''"> ({{v.user_nicename}})</span></td>
            <td><a :href="'admin/' + controller_slug + '?member_type=' + v.member_type">{{list[v.member_type]}}</a></td>
            <td>{{v.last_login.substr(0, 16)}}</td>
            <td>{{v.user_registered.substr(0, 16)}}</td>
            <!-- <td>{{UsersType_listStatus[v.user_status]}}</td> -->
            <td :title="UsersType_listStatus[v.user_status]" :data-id="v.ID" :data-status="v.user_status" class="text-center medium d-inlines click-change-user-status"><div :data-id="v.ID" data-status="<?php echo UsersType::NO_LOGIN; ?>" class="cur"><i class="fa fa-toggle-off"></i></div>
                <div :data-id="v.ID" data-status="<?php echo UsersType::FOR_DEFAULT; ?>" class="cur greencolor"><i class="fa fa-toggle-on"></i></div></td>
            <td width="90" class="text-center"><?php
            include dirname( __DIR__ ) . '/users/list_action.php';
            ?></td>
        </tr>
    </tbody>
</table>
