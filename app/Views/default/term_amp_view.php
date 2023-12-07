<header class="amp-wp-article-header">
    <h1 class="amp-wp-title"><a href="<?php echo $amp_link; ?>"><?php echo $data['name']; ?></a></h1>
</header>
<?php
foreach ($post_data as $v) {
    $v_link = $base_model->amp_post_link($v);

    //
    $v['post_excerpt'] = trim(strip_tags($v['post_excerpt']));
    if (empty($v['post_excerpt'])) {
        $v['post_excerpt'] = trim(strip_tags($v['post_content']));
        $v['post_excerpt'] = $base_model->short_string($v['post_excerpt'], '300');
    }

    //
    $blog_posting_img = '';
    if (isset($v['post_meta'])) {
        if (isset($v['post_meta']['image_medium'])) {
            $blog_posting_img = $v['post_meta']['image_medium'];
        } else if (isset($v['post_meta']['image_large'])) {
            $blog_posting_img = $v['post_meta']['image_large'];
        } else if (isset($v['post_meta']['image'])) {
            $blog_posting_img = $v['post_meta']['image'];
        }
    }
?>
    <div class="amp-wp-blogs-list">
        <h2 class="amp-wp-blogs-title"><a href="<?php echo $v_link; ?>"><?php echo $v['post_title']; ?></a></h2>
        <?php
        if ($blog_posting_img != '' && strpos($blog_posting_img, '//') === false) {
            $blog_posting_url = DYNAMIC_BASE_URL . $blog_posting_img;
            $blog_posting_img = PUBLIC_PUBLIC_PATH . $blog_posting_img;
            // echo $blog_posting_img . '<br>' . PHP_EOL;
            if (is_file($blog_posting_img)) {
                $get_file_info = getimagesize($blog_posting_img);
                // print_r($get_file_info);
        ?>
                <div><a href="<?php echo $v_link; ?>"><amp-img src="<?php echo $blog_posting_url; ?>" width="<?php echo $get_file_info[0]; ?>" height="<?php echo $get_file_info[1]; ?>" class="amp-wp-enforced-sizes" sizes="(min-width: 350px) 350px, 100vw"></amp-img></a></div>
        <?php
            }
        }
        ?>
        <div class="amp-wp-blogs-padding">
            <div class="amp-wp-blogs-desc"><?php echo nl2br($v['post_excerpt']); ?></div>
            <div class="amp-wp-blogs-date"><?php echo date('d/m/Y H:i', strtotime($v['post_modified'])); ?></div>
        </div>
    </div>
<?php
}
