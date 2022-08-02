<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" class="input-checkbox-all" /></th>
            <th>Tiêu đề <?php echo $name_type; ?></th>
            <th>Mã nhúng</th>
            <th>Trạng thái</th>
            <th colspan="2">Ngày tạo/ Last Update</th>
            <th>Lang</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="admin_main_list">
        <tr :data-id="v.ID" v-for="v in data">
            <td width="50" class="text-center"><input type="checkbox" :value="v.ID" class="input-checkbox-control" /></td>
            <td><a :href="v.admin_permalink" class="bold">{{v.post_title}} <i class="fa fa-edit"></i></a></td>
            <td><input type="text" class="span12" onClick="this.select()" onDblClick="click2Copy(this);" :value="'&lt;?php $htmlmenu_model->the_menu(\'' + v.post_name + '\'); ?&gt;'" readonly /></td>
            <td :class="'post_status post_status-' + v.post_status">{{PostType_arrStatus[v.post_status]}}</td>
            <td>{{v.post_date.substr(0, 16)}}</td>
            <td>{{v.post_modified.substr(0, 16)}}</td>
            <td width="90">{{v.lang_key}}</td>
            <td width="90" class="text-center"><?php
            include $admin_root_views . 'posts/list_action.php';
            ?></td>
        </tr>
    </tbody>
</table>
<div>
    <pre><code>&lt;?php $htmlmenu_model->the_menu( '%slug%' ); ?&gt;</code></pre>
</div>
