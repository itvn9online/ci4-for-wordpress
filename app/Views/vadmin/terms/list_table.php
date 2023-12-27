<?php

/**
 * Chức năng hiển thị bảng dữ liệu dạng table.
 * Tách riêng kiểu này để khi các loại terms khác nhau muốn hiển thị các dữ liệu khác nhau thì chỉ cần copy file list_table.php ra thư mục view tương ứng rồi chỉnh sửa nó là được.
 **/
?>
<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" class="input-checkbox-all" /></th>
            <th>ID</th>
            <th>Tên <?php echo $name_type; ?></th>
            <th>Slug</th>
            <!-- <th>Tên rút gọn</th> -->
            <th class="d-none show-if-ads-type">Size</th>
            <th>Nội dung</th>
            <th>Ngôn ngữ</th>
            <th>Tổng</th>
            <th>STT</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="admin_term_list">
        <tr>
            <td width="50" class="text-center"><input type="checkbox" value="{{v.term_id}}" class="input-checkbox-control" /></td>
            <td>{{v.term_id}}</td>
            <td>
                <div><span data-id="{{v.parent}}" data-taxonomy="<?php echo $taxonomy; ?>" data-line="{{v.gach_ngang}}" class="parent-term-name"></span> <a href="{{v.get_admin_permalink}}">{{v.gach_ngang}}{{v.name}} <i class="fa fa-edit"></i></a></div>
                <div>{{v.term_shortname}}</div>
            </td>
            <td>
                <div>{{v.slug}}</div>
                <div><a href="{{v.view_url}}" target="_blank">{{v.view_url}} <i class="fa fa-eye"></i></a></div>
            </td>
            <td class="d-none show-if-ads-type"><?php echo $ads_size; ?></td>
            <td>&nbsp;</td>
            <td width="90">{{v.lang_key}}</td>
            <td width="90">{{v.count}}</td>
            <td width="60"><input type="text" data-id="{{v.term_id}}" value="{{v.term_order}}" size="5" class="form-control s change-update-term_order" /></td>
            <td width="150" class="text-center big">
                <?php
                include ADMIN_ROOT_VIEWS . 'terms/list_action.php';
                ?>
            </td>
        </tr>
        <?php

        //echo $term_model->list_html_view( $data, '', $by_is_deleted, $controller_slug );
        //$term_model->get_admin_permalink($v['taxonomy'], $v['term_id']);

        ?>
    </tbody>
</table>