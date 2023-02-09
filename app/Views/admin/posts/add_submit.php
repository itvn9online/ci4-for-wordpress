<div class="form-actions frm-fixed-btn cf">
    <?php
    if ($data['ID'] > 0) {
        ?>
        <a href="admin/<?php echo $controller_slug; ?>/delete?id=<?php echo $data['ID']; ?>"
            onClick="return click_a_delete_record();" class="btn btn-danger btn-small" target="target_eb_iframe"><i
                class="fa fa-trash"></i> XÓA
            <?php echo $name_type; ?>
        </a>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Lưu lại</button>
        <?php
    } else {
        ?>
        <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Thêm mới
            <?php echo $name_type; ?>
        </button>
        <?php
    }
    ?>
</div>