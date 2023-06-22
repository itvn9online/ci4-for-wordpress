<?php

//
$sizes_name = '';
foreach ($all_src as $size_name => $file) {
    $sizes_name .= ' data-' . $size_name . '="' . $file . '"' . PHP_EOL;
}

?>
<div data-title="<?php echo $v['post_name']; ?>" class="media-attachment-padding cf">
    <div class="lf f25 attachment-list-padding">
        <div data-id="<?php echo $v['ID']; ?>" data-add_img_tag="<?php echo $add_img_tag; ?>" data-insert="<?php echo $str_insert_to; ?>" data-size="<?php echo $img_size; ?>" data-width="<?php echo $data_width; ?>" data-height="<?php echo $data_height; ?>" data-input_type="<?php echo $input_type; ?>" data-mime="<?php echo explode('/', $v['post_mime_type'])[0]; ?>" data-mime_type="<?php echo $v['post_mime_type']; ?>" <?php echo $sizes_name; ?> data-srcset="<?php echo implode(', ', $data_srcset); ?>" data-sizes="(max-width: <?php echo $attachment_metadata['width']; ?>px) 100vw, <?php echo $attachment_metadata['width']; ?>px" onDblClick="return click_set_img_for_input('<?php echo $v['ID']; ?>');" class="media-attachment-img" style="<?php echo $background_image; ?>">&nbsp;</div>
    </div>
    <div class="lf f75">
        <div class="left-menu-space10">
            <p class="bold"><?php echo $v['post_name']; ?></p>
            <div class="cf medium18">
                <div class="lf f50"><strong onClick="return click_set_img_for_input('<?php echo $v['ID']; ?>');" class="greencolor cur"><i class="fa fa-plus"></i></strong></div>
                <div class="lf f50"><a href="admin/<?php echo $controller_slug; ?>/delete?id=<?php echo $v['ID'] . $uri_quick_upload; ?>" target="target_eb_iframe" onClick="return confirm('Xác nhận xóa tệp này?');"><i class="fa fa-trash"></i></a></div>
            </div>
        </div>
    </div>
</div>