<?php

// Libraries
use App\ Libraries\ CommentType;

//
//$base_model = new\ App\ Models\ Base();

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/' . $comment_type . '.css' );

?>
<ul class="admin-breadcrumb">
    <li>Danh sách <?php echo CommentType::list($comment_type); ?> (<?php echo $totalThread; ?>)</li>
</ul>
<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectall" name="selectall"/></th>
            <th>Tiêu đề</th>
            <th>Email</th>
            <th>Trạng thái</th>
            <th>IP</th>
            <th>Ngày tạo</th>
            <th>Lang</th>
        </tr>
    </thead>
    <tbody>
        <?php

        foreach ( $data as $k => $v ) {
            if ( $v[ 'comment_title' ] == '' ) {
                $v[ 'comment_title' ] = strip_tags( $v[ 'comment_content' ] );
                $v[ 'comment_title' ] = explode( "\n", $v[ 'comment_title' ] );
                $v[ 'comment_title' ] = $v[ 'comment_title' ][ 0 ];
            }

            ?>
        <tr>
            <td>&nbsp;</td>
            <td><a href="admin/comments?comment_type=<?php echo $comment_type; ?>&comment_id=<?php echo $v['comment_ID']; ?>"><?php echo $v['comment_content']; ?> <i class="fa fa-edit"></i></a></td>
            <td><?php echo $v['comment_author_email']; ?></td>
            <td><?php echo $v['comment_approved']; ?></td>
            <td><?php echo $v['comment_author_IP']; ?></td>
            <td><?php echo $v['comment_date']; ?></td>
            <td><?php echo $v['lang_key']; ?></td>
        </tr>
        <?php
        }

        ?>
    </tbody>
</table>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<p class="d-none">* Copy đoạn code bên dưới rồi cho vào nơi cần hiển thị block này ở trong view. Nhớ thay %slug% thành slug thật trong danh sách ở trên.</p>
<?php

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/' . $comment_type . '.js' );
