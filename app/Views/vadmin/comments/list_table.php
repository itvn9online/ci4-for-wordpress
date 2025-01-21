<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectall" name="selectall" /></th>
            <th>Tiêu đề</th>
            <th>Thông tin khác</th>
            <th>IP</th>
            <th>Ngày tạo</th>
            <!-- <th>Lang</th> -->
            <th>Trạng thái</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="admin_main_list" class="ng-main-content">
        <tr :data-id="v.comment_ID" v-for="v in data">
            <td>&nbsp;</td>
            <td>
                <div>
                    <a :href="'sadmin/' + controller_slug + '?comment_id=' + v.comment_ID" :data-id="v.comment_ID" class="orders-open-popup">{{v.comment_title}} <i class="fa fa-edit"></i></a>
                </div>
                <div>{{v.comment_slug}}</div>
                <div v-if="v.comment_parent > 0">- <a :href="'sadmin/' + controller_slug + '?comment_id=' + v.comment_parent">Reply for #{{v.comment_parent}} <i class="fa fa-reply"></i></a></div>
            </td>
            <td>
                <div>{{v.comment_author}}</div>
                <div v-if="v.user_id > 0"><a :href="'sadmin/users/add?id=' + v.user_id">{{v.comment_author_email}}</a></div>
                <div v-if="v.user_id < 1">{{v.comment_author_email}}</div>
                <div v-if="v.comment_author_url != ''"><a :href="v.comment_author_url" target="_blank">{{v.comment_author_url}}</a></div>
            </td>
            <td><a :href="'https://www.iplocation.net/ip-lookup?query=' + v.comment_author_IP" target="_blank" rel="nofollow">{{v.comment_author_IP}}</a></td>
            <td>{{v.comment_date.slice(0, 16)}}</td>
            <td>
                <div>{{v.comment_approved}}</div>
                <div>{{v.lang_key}}</div>
            </td>
            <td class="text-center big">
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