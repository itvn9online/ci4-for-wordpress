<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" class="input-checkbox-all" /></th>
            <th>Tiêu đề <?php echo $name_type; ?></th>
            <th>Ảnh đại diện</th>
            <th>Danh mục</th>
            <th>Trạng thái</th>
            <th colspan="2">Ngày tạo/ <a :href="'admin/' + controller_slug + '?order_by=post_modified'">Cập nhật cuối <i
                        class="fa fa-sort"></i></a></th>
            <th>Lang</th>
            <th>STT</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="admin_main_list">
        <tr :data-id="v.ID" v-for="v in data">
            <td width="50" class="text-center"><input type="checkbox" :value="v.ID" class="input-checkbox-control" />
            </td>
            <td>
                <div><a :href="v.admin_permalink" class="bold">{{v.post_title}} <i class="fa fa-edit"></i></a></div>
                <div><a :href="v.the_permalink" target="_blank" class="small blackcolor">{{v.post_name}} <i
                            class="fa fa-eye"></i></a></div>
            </td>
            <td>
                <div class="img-max-width"> <a :href="v.admin_permalink"><img :src="v.thumbnail" height="90"
                            data-class="each-to-img-src" style="height: 90px; width: auto;" /></a> </div>
            </td>
            <td :data-id="v.main_category_key" :data-taxonomy="taxonomy" :data-uri="'admin/' + controller_slug"
                class="each-to-taxonomy">&nbsp;</td>
            <td :class="'post_status post_status-' + v.post_status">{{PostType_arrStatus[v.post_status]}}</td>
            <td>{{v.post_date.substr(0, 16)}}</td>
            <td>{{v.post_modified.substr(0, 16)}}</td>
            <td width="90">{{v.lang_key}}</td>
            <td width="60"><input type="number" :data-id="v.ID" :value="v.menu_order" size="5"
                    class="form-control s change-update-menu_order" /></td>
            <td width="90" class="text-center"><?php
            include $admin_root_views . 'posts/list_action.php';
            ?></td>
        </tr>
    </tbody>
</table>