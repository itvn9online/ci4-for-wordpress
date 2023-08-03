<div class="control-group">
    <div class="control-label">Post type</div>
    <div class="controls"><?php echo $data['post_type']; ?></div>
</div>
<?php

// tạo module check độ chuẩn SEO cho bài viết
if ($data['ID'] > 0 && isset($data['post_permalink'])) {
    //print_r($data);
    //die(__FILE__ . ':' . __LINE__);
    $linkEncode = urlencode($post_model->get_full_permalink($data));

    //
    foreach ([
        [
            'name' => 'Page speed',
            'link' => 'https://pagespeed.web.dev/report?url=' . $linkEncode,
        ], [
            'name' => 'Structured data',
            'link' => 'https://validator.schema.org/#url=' . $linkEncode,
        ], [
            'name' => 'Open Graph Facebook',
            'link' => 'https://developers.facebook.com/tools/debug/?q=' . $linkEncode,
        ], [
            'name' => 'Open Graph Zalo',
            'link' => 'https://developers.zalo.me/tools/debug-sharing?q=' . $linkEncode,
        ], [
            'name' => 'Security headers',
            'link' => 'https://securityheaders.com/?q=' . $linkEncode . '&followRedirects=on',
        ]
    ] as $v) {
?>
        <div class="control-group">
            <div class="control-label"><?php echo $v['name']; ?></div>
            <div class="controls"><a href="<?php echo $v['link']; ?>" target="_blank" rel="nofollow"><?php echo $v['link']; ?></a></div>
        </div>
<?php
    }
}

?>
<div class="form-actions frm-fixed-btn cf">
    <?php
    if ($data['ID'] > 0) {
    ?>
        <a href="admin/<?php echo $controller_slug; ?>/delete?id=<?php echo $data['ID']; ?>" onClick="return click_a_delete_record();" class="btn btn-danger btn-small" target="target_eb_iframe"><i class="fa fa-trash"></i> XÓA
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