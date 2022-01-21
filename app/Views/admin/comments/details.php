<?php

//
use App\ Libraries\ CommentType;

//
//print_r( $data );
$data[ 'comment_content' ] = nl2br( $data[ 'comment_content' ] );

?>
<ul class="admin-breadcrumb">
    <li><a href="admin/<?php echo $controller_slug; ?>">Danh sách <?php echo CommentType::list($comment_type); ?></a></li>
    <li>Chi tiết
        <?php
        echo CommentType::list( $comment_type );
        ?>
    </li>
</ul>
<div class="widget-box">
    <div class="widget-content nopadding">
        <div class="form-horizontal">
            <?php

            foreach ( $data as $k => $v ) {
                ?>
            <div class="control-group">
                <label class="control-label"><?php echo str_replace('_',' ',$k); ?></label>
                <div class="controls"><?php echo $v; ?></div>
            </div>
            <?php
            }

            ?>
        </div>
    </div>
</div>
