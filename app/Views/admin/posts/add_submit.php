<div class="form-actions frm-fixed-btn">
    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Lưu lại</button>
    <?php
    if ( $data[ 'ID' ] > 0 ) {
        ?>
    <a href="admin/<?php echo $controller_slug; ?>/delete?id=<?php echo $data[ 'ID' ]; ?>" onClick="return click_a_delete_record();" class="btn btn-danger" target="target_eb_iframe"><i class="fa fa-trash"></i> XÓA</a>
    <?php
    }
    ?>
</div>
