<div data-deleted="{{v.is_deleted}}" class="show-if-trash">
        <div class="d-inline"><a href="admin/{{controller_slug}}/restore?id={{v.term_id}}{{for_action}}" onClick="return click_a_restore_record();" target="target_eb_iframe" class="bluecolor"><i class="fa fa-undo"></i></a></div>
        &nbsp;
        <div class="d-inline"><a href="admin/{{controller_slug}}/remove?id={{v.term_id}}{{for_action}}" onClick="return click_a_remove_record();" target="target_eb_iframe" class="redcolor"><i class="fa fa-remove"></i></a></div>
</div>
<div data-deleted="{{v.is_deleted}}" class="d-inlines hide-if-trash">
        <div><a href="javascript:;" title="Thêm nhiều nhóm con" data-bs-toggle="modal" data-bs-target="#termMultiAddModal" data-controller="{{controller_slug}}" data-id="{{v.term_id}}" data-name="{{v.name}}" onClick="return open_modal_add_multi_term({{v.term_id}});" class="greencolor get-parent-term-name"><i class="fa fa-plus"></i></a></div>
        &nbsp;
        <div><a href="admin/{{controller_slug}}/term_status?id={{v.term_id}}{{for_action}}" target="target_eb_iframe" data-id="{{v.term_id}}" data-status="{{v.term_status}}" class="record-status-color"><i class="fa fa-eye"></i></a></div>
        &nbsp;
        <div><a href="admin/{{controller_slug}}/delete?id={{v.term_id}}{{for_action}}" onClick="return click_a_delete_record();" target="target_eb_iframe" class="redcolor"><i class="fa fa-trash"></i></a></div>
</div>