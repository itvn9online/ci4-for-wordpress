<header class="amp-wp-article-header">
    <h1 class="amp-wp-title"><a href="<?php echo $amp_link; ?>"><?php echo $data['post_title']; ?></a></h1>
    <div>
        <?php
        if ($terms_link != '') {
        ?>
            <a href="<?php echo $terms_link; ?>"><?php echo $terms_title; ?></a> |
        <?php
        }
        ?>
        <?php echo $data['post_modified']; ?>
    </div>
</header>
<div class="amp-wp-article-content">
    <?php echo $data['post_content']; ?>
</div>
<br>
<h2><?php $lang_model->the_text('amp_details_same_post', 'Bài viết khác'); ?></h2>
<ul class="amp-related-posts">
    <?php

    // bài mới hơn
    foreach ($next_post as $v) {
        $v_link = $base_model->amp_post_link($v);
        // $v_link = $amp_base_url . $v['post_permalink'];
    ?>
        <li><a href="<?php echo $v_link; ?>"><?php echo $v['post_title']; ?></a></li>
    <?php
    }

    // bài cũ hơn
    foreach ($prev_post as $v) {
        $v_link = $base_model->amp_post_link($v);
        // $v_link = $amp_base_url . $v['post_permalink'];
    ?>
        <li><a href="<?php echo $v_link; ?>"><?php echo $v['post_title']; ?></a></li>
    <?php
    }

    ?>
</ul>