<?php

//
//$base_model = new\ App\ Models\ Base();
$post_model = new\ App\ Models\ Post();

// Libraries
use App\ Libraries\ PostType;

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/' . $post_type . '.css' );

?>
<ul class="admin-breadcrumb">
    <li>Danh sách <?php echo PostType::list($post_type); ?> (<?php echo $totalThread; ?>)</li>
</ul>
<div class="cf admin-search-form">
    <div class="lf f80">
        <form name="frm_admin_search_controller" action="./admin/posts" method="get">
            <input type="hidden" name="post_type" value="<?php echo $post_type; ?>">
            <div class="cf">
                <div class="lf f20">
                    <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo PostType::list($post_type); ?>">
                </div>
                <div class="lf f20 hide-if-no-taxonomy">
                    <select name="term_id" data-select="<?php echo $by_term_id; ?>" data-taxonomy="<?php echo $taxonomy; ?>" onChange="document.frm_admin_search_controller.submit();" class="each-to-taxonomy-group">
                        <option value="0">- Nhóm <?php echo PostType::list($post_type); ?> -</option>
                    </select>
                </div>
                <div class="lf f10">
                    <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="lf f20">
        <?php

        // menu sẽ được tự động khởi tạo khi dùng hàm để gọi -> không cho add thủ công
        if ( $post_type != PostType::MENU ) {
            ?>
        <div class="buttons text-right"> <a href="<?php $post_model->admin_permalink( $post_type ); ?>" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới <?php echo PostType::list($post_type); ?></a> </div>
        <?php
        }

        ?>
    </div>
</div>
<br>
<table class="table table-bordered table-striped with-check table-list">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectall" name="selectall"/></th>
            <th>Tên bài viết</th>
            <th>Url</th>
            <th>Ảnh đại diện</th>
            <th>Chuyên mục</th>
            <th>Trạng thái</th>
            <th>Tin nổi bật</th>
            <th>Ngày tạo</th>
            <th>STT</th>
            <th>Last Update</th>
            <th>Lang</th>
        </tr>
    </thead>
    <tbody>
        <?php

        foreach ( $data as $k => $v ) {
            ?>
        <tr>
            <td>&nbsp;</td>
            <td><a href="<?php $post_model->admin_permalink( $post_type, $v['ID'] ); ?>"><?php echo $v['post_title']; ?> <i class="fa fa-edit"></i></a></td>
            <td><?php echo $post_model->show_meta_post($v['post_meta'], 'url_redirect'); ?></td>
            <td><img src="<?php echo $post_model->get_post_thumbnail($v['post_meta']); ?>" height="90" style="height: 90px; width: auto;" /></td>
            <td data-id="<?php echo $post_model->show_meta_post($v['post_meta'], 'post_category'); ?>" data-taxonomy="<?php echo $taxonomy; ?>" data-uri="admin/posts?post_type=<?php echo $post_type; ?>" class="each-to-taxonomy">&nbsp;</td>
            <td><?php echo $v['post_status']; ?></td>
            <td><?php echo $v['pinged']; ?></td>
            <td><?php echo $v['post_date']; ?></td>
            <td><?php echo $v['menu_order']; ?></td>
            <td><?php echo $v['post_modified']; ?></td>
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

if ( $post_type == PostType::MENU ) {
    ?>
<pre><code>&lt;?php $this->menu_model->the_menu( '%slug%' ); ?&gt;</code></pre>
<?php
}

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/post_list.js' );
$base_model->add_js( 'admin/js/' . $post_type . '.js' );
