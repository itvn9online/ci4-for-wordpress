<?php

/**
 * Chức năng hiển thị bảng dữ liệu dạng table.
 * Tách riêng kiểu này để khi các loại posts khác nhau muốn hiển thị các dữ liệu khác nhau thì chỉ cần copy file list_table.php ra thư mục view tương ứng rồi chỉnh sửa nó là được.
 **/
?>
<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" class="input-checkbox-all" /></th>
            <th>Tiêu đề <?php echo $name_type; ?></th>
            <th>Mã nhúng</th>
            <th>Ngày tạo/ <a :href="'sadmin/' + controller_slug + '?order_by=post_modified'">Cập nhật cuối <i class="fa fa-sort"></i></a></th>
            <!-- <th>Lang</th> -->
            <th>Trạng thái</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="admin_main_list">
        <tr :data-id="v.ID" v-for="v in data">
            <td width="50" class="text-center"><input type="checkbox" :value="v.ID" class="input-checkbox-control" />
            </td>
            <td><a :href="v.admin_permalink" class="bold">{{v.post_title}} <i class="fa fa-edit"></i></a></td>
            <td><input type="text" class="span12" onClick="this.select()" onDblClick="click2Copy(this);" :value="'&lt;?php $menu_model->the_menu(\'' + v.post_name + '\'); ?&gt;'" readonly /></td>
            <td>
                <div>{{v.post_date.substr(0, 16)}}</div>
                <div>{{v.post_modified.substr(0, 16)}}</div>
            </td>
            <td>
                <div :class="'post_status post_status-' + v.post_status">{{PostType_arrStatus[v.post_status]}}</div>
                <div>{{v.lang_key}}</div>
            </td>
            <td class="text-center big">
                <?php
                include ADMIN_ROOT_VIEWS . 'posts/list_action.php';
                ?>
            </td>
        </tr>
    </tbody>
</table>
<div>
    <pre><code>&lt;?php $menu_model->the_menu( '%slug%' ); ?&gt;</code></pre>
</div>