<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectall" name="selectall" /></th>
            <th>Tiêu đề</th>
            <th>Email</th>
            <th>Trạng thái</th>
            <th>IP</th>
            <th>Ngày tạo</th>
            <th>Lang</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="admin_main_list" class="ng-main-content">
        <tr v-for="v in data">
            <td>&nbsp;</td>
            <td><a :href="'sadmin/' + controller_slug + '?comment_id=' + v.comment_ID">{{v.comment_title}} <i class="fa fa-edit"></i></a> {{v.comment_slug}}</td>
            <td>{{v.comment_author_email}}</td>
            <td>{{v.comment_approved}}</td>
            <td>{{v.comment_author_IP}}</td>
            <td>{{v.comment_date.substr(0, 16)}}</td>
            <td>{{v.lang_key}}</td>
            <td class="text-center">
                <div>
                    <div v-if="v.is_deleted != DeletedStatus_DELETED">
                        <div><a :href="'sadmin/' + controller_slug + '/delete?id=' + v.comment_ID + for_action" onClick="return click_a_delete_record();" class="redcolor" target="target_eb_iframe"><i class="fa fa-trash"></i></a> </div>
                    </div>
                    <div v-if="v.is_deleted == DeletedStatus_DELETED">
                        <div><a :href="'sadmin/' + controller_slug + '/restore?id=' + v.comment_ID + for_action" onClick="return click_a_restore_record();" class="bluecolor" target="target_eb_iframe"><i class="fa fa-undo"></i></a></div>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>