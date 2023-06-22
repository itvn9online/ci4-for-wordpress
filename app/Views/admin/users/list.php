<?php

// Libraries
use App\Libraries\UsersType;

//
$base_model->add_css('admin/css/users_list.css');
$base_model->add_js('admin/js/users_functions.js');

?>
<ul class="admin-breadcrumb">
    <li><a href="admin/<?php echo $controller_slug; ?>">Danh sách
            <?php echo $member_name; ?>
        </a>
        (
        <?php echo $totalThread; ?>)
    </li>
    <?php
    if ($member_type != '') {
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
            <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug . $controller_path; ?>" method="get">
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

    //
    $theme_private_view = ADMIN_ROOT_VIEWS . $list_view_path . '/list_table.php';
    //echo $theme_private_view . '<br>' . PHP_EOL;
    //echo __DIR__ . '/list_table.php' . '<br>' . PHP_EOL;
    if (file_exists($theme_private_view)) {
        include $theme_private_view;
    } else {
        include __DIR__ . '/list_table.php';
    }

    ?>
</div>
<div class="public-part-page">
    <?php echo $pagination; ?> Trên tổng số
    <?php echo $totalThread; ?> bản ghi.
</div>
<?php

//
$base_model->JSON_parse(
    [
        'col_filter' => $col_filter,
        'scope_data' => $data,
        'arr_members_type' => $arr_members_type,
        'UsersType_listStatus' => UsersType::statusList(),
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

    ]
);

?>
<script>
    var controller_slug = '<?php echo $controller_slug; ?>';

    //
    WGR_vuejs('#app', {
        allow_mysql_delete: allow_mysql_delete,
        member_name: '<?php echo $member_name; ?>',
        member_type: '<?php echo $member_type; ?>',
        controller_slug: controller_slug,
        data: scope_data,
        for_action: '<?php echo $for_action; ?>',
        DeletedStatus_DELETED: '<?php echo $DeletedStatus_DELETED; ?>',
        by_is_deleted: '<?php echo $by_is_deleted; ?>',
        UsersType_NO_LOGIN: UsersType_NO_LOGIN,
        UsersType_FOR_DEFAULT: UsersType_FOR_DEFAULT,
        list: arr_members_type,
        UsersType_listStatus: UsersType_listStatus,
    }, function() {
        action_change_user_status();
    });
</script>
<?php

//
$base_model->add_js('admin/js/users.js');
$base_model->add_js('admin/js/users_list.js');
$base_model->add_js('admin/js/' . $member_type . '.js');
