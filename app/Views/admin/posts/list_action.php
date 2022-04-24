<div v-if="v.post_status != PostType_DELETED">
    <div><a :href="'admin/' + controller_slug + '/delete?id=' + v.ID + for_action" onClick="return click_a_delete_record();" class="redcolor" target="target_eb_iframe"><i class="fa fa-trash"></i></a> </div>
</div>
<div class="d-inlines" v-if="v.post_status == PostType_DELETED">
    <div class="d-inline"><a :href="'admin/' + controller_slug + '/restore?id=' + v.ID + for_action" onClick="return click_a_restore_record();" class="bluecolor" target="target_eb_iframe"><i class="fa fa-undo"></i></a></div>
    &nbsp;
    <div class="d-inline"><a :href="'admin/' + controller_slug + '/remove?id=' + v.ID + for_action" onClick="return click_a_remove_record();" class="redcolor" target="target_eb_iframe"><i class="fa fa-remove"></i></a></div>
</div>
