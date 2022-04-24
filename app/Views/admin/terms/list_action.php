<div data-deleted="{{v.is_deleted}}" class="show-if-trash">
    <div class="d-inline"><a href="admin/{{controller_slug}}/restore?id={{v.term_id}}{{for_action}}" onClick="return click_a_restore_record();" target="target_eb_iframe" class="bluecolor"><i class="fa fa-undo"></i></a></div>
    &nbsp;
    <div class="d-inline"><a href="admin/{{controller_slug}}/remove?id={{v.term_id}}{{for_action}}" onClick="return click_a_remove_record();" target="target_eb_iframe" class="redcolor"><i class="fa fa-remove"></i></a></div>
</div>
<div data-deleted="{{v.is_deleted}}" class="d-inlines hide-if-trash">
    <div><a href="admin/{{controller_slug}}/term_status?id={{v.term_id}}&current_status={{v.term_status}}{{for_action}}" target="target_eb_iframe" data-status="{{v.term_status}}" class="record-status-color"><i class="fa fa-eye"></i></a></div>
    &nbsp;
    <div><a href="admin/{{controller_slug}}/delete?id={{v.term_id}}{{for_action}}" onClick="return click_a_delete_record();" target="target_eb_iframe" class="redcolor"><i class="fa fa-trash"></i></a></div>
</div>
