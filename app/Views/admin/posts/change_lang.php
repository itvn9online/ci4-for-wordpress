<?php

// chạy vòng lặp hiển thị các ngôn ngữ được hỗ trợ trên website
$lang_parent = $data['lang_parent'] > 0 ? $data['lang_parent'] : $data['ID'];
foreach (SITE_LANGUAGE_SUPPORT as $v) {
    if ($v['value'] == $data['lang_key']) {
?>
        | <strong class="redcolor"><?php echo $post_lang; ?></strong>
    <?php
        continue;
    }
    ?>
    | <a href="<?php echo $post_model->get_admin_permalink($data['post_type'], $lang_parent, $controller_slug); ?>&clone_lang=<?php echo $v['value']; ?>&preview_url=<?php echo urlencode($preview_url); ?>" class="bluecolor"><?php echo $v['text']; ?></a>
<?php
}
