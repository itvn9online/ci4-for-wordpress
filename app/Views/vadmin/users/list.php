<?php

// Libraries
use App\Libraries\UsersType;

//
$base_model->add_css('wp-admin/css/users_list.css');
$base_model->add_js('wp-admin/js/users_functions.js');

?>
<ul class="admin-breadcrumb">
    <li><a href="sadmin/<?php echo $controller_slug; ?>">Danh sách
            <?php echo $member_name; ?>
        </a>
        (
        <?php echo $totalThread; ?>)
    </li>
    <?php
    if ($member_type != '' && $member_name != '') {
    ?>
        <li>
            <?php echo $member_name; ?>
        </li>
    <?php
    }
    ?>
</ul>
<div id="app" class="ng-main-content">
    <div class="cf admin-search-form">
        <div class="lf f50">
            <form name="frm_admin_search_controller" action="./sadmin/<?php echo $controller_slug . $controller_path; ?>" method="get">
                <input type="hidden" name="member_type" :value="member_type">
                <div class="cf">
                    <div class="lf f30">
                        <?php
                        if ($by_is_deleted > 0) {
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
                    <div class="lf f15">
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

    // TEST
    //echo ADMIN_ROOT_VIEWS . $member_type . '/list_table.php';

    // list table của từng member type nếu được thiết lập trong controller
    if ($list_table_path != '') {
        echo '<div class="wgr-view-path">' . ADMIN_ROOT_VIEWS . $list_table_path . '/list_table.php</div>';

        // sử dụng list table riêng của member type nếu có khai báo
        include ADMIN_ROOT_VIEWS . $list_table_path . '/list_table.php';
    } else {
        $has_private_view = false;

        // list table của từng member type nếu tìm thấy file
        if ($member_type != '') {
            $theme_default_view = ADMIN_ROOT_VIEWS . $member_type . '/list_table.php';
            // nạp file kiểm tra private view
            include VIEWS_PATH . 'private_view.php';
        }

        // list table mặc định
        if ($has_private_view === false) {
            // nạp view riêng của từng theme nếu có
            $theme_default_view = __DIR__ . '/list_table.php';
            // nạp file kiểm tra private view
            include VIEWS_PATH . 'private_view.php';
        }
    }

    ?>
</div>
<div class="public-part-page"><?php echo $pagination; ?> Trên tổng số <?php echo number_format($totalThread); ?> bản ghi.</div>
<?php

//
$base_model->JSON_parse(
    [
        'col_filter' => $col_filter,
        'scope_data' => $data,
        'arr_members_type' => $arr_members_type,
        'UsersType_listStatus' => UsersType::statusList(),
        //
        'data_vuejs' => [
            'member_name' => $member_name,
            'member_type' => $member_type,
            'for_action' => $for_action,
            'DeletedStatus_DELETED' => $DeletedStatus_DELETED,
            'by_is_deleted' => $by_is_deleted,
        ],
    ]
);

//
$base_model->JSON_echo(
    [
        // mảng này sẽ in ra dưới dạng JSON hoặc number
        'UsersType_NO_LOGIN' => UsersType::NO_LOGIN,
        'UsersType_FOR_DEFAULT' => UsersType::FOR_DEFAULT,
    ],
    [
        // mảng này sẽ in ra dưới dạng string
        'controller_slug' => $controller_slug,
    ]
);

?>
<script type="text/javascript">
    var params_vuejs = {
        allow_mysql_delete: allow_mysql_delete,
        controller_slug: controller_slug,
        data: scope_data,
        UsersType_NO_LOGIN: UsersType_NO_LOGIN,
        UsersType_FOR_DEFAULT: UsersType_FOR_DEFAULT,
        list: arr_members_type,
        UsersType_listStatus: UsersType_listStatus,
    };
    for (let x in data_vuejs) {
        params_vuejs[x] = data_vuejs[x];
    }
    //console.log(params_vuejs);

    //
    WGR_vuejs('#app', params_vuejs, function() {
        action_change_user_status();
    });
</script>
<?php

//
$base_model->add_js('wp-admin/js/users.js');
$base_model->add_js('wp-admin/js/users_list.js');
$base_model->add_js('wp-admin/js/' . $member_type . '.js');
