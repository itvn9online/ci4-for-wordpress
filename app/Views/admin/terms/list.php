<?php

// Libraries
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ DeletedStatus;

//
//$base_model = new\ App\ Models\ Base();
$term_model = new\ App\ Models\ Term();

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/' . $taxonomy . '.css' );

?>
<ul class="admin-breadcrumb">
    <li><?php echo TaxonomyType::list($taxonomy, true); ?></li>
</ul>
<div class="cf admin-search-form">
    <div class="lf f50">
        <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
            <input type="hidden" name="taxonomy" value="<?php echo $taxonomy; ?>">
            <div class="cf">
                <div class="lf f30">
                    <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo TaxonomyType::list($taxonomy, true); ?>" autofocus>
                </div>
                <div class="lf f20">
                    <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="lf f50 text-right">
        <div class="d-inline"> <a href="<?php $term_model->admin_permalink( $taxonomy, 0, $controller_slug ); ?>" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới <?php echo TaxonomyType::list($taxonomy, true); ?></a> </div>
        <div class="d-inline"><a href="admin/<?php echo $controller_slug; ?>?taxonomy=<?php echo $taxonomy; ?>&is_deleted=<?php echo DeletedStatus::DELETED; ?>" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
    </div>
</div>
<br>
<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectall" name="selectall"/></th>
            <th>Tên bài viết</th>
            <th>Slug</th>
            <th class="d-none show-if-ads-type">Size</th>
            <th>Nội dung</th>
            <th>Ngôn ngữ</th>
            <th>Bài viết</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php

        echo $term_model->list_html_view( $data, '', $by_is_deleted, $controller_slug );
        //$term_model->get_admin_permalink($v['taxonomy'], $v['term_id']);

        /*

        function aaaaaaaaaaaaaaa( $data ) {
            foreach ( $data as $k => $v ) {
                ?>
        <tr>
            <td>&nbsp;</td>
            <td><a href="<?php echo $term_model->get_admin_permalink($v['taxonomy'], $v['term_id']); ?>"><?php echo $v['name']; ?> <i class="fa fa-edit"></i></a></td>
            <td><?php echo $v['slug']; ?></td>
            <td><?php echo $v['lang_key']; ?></td>
            <td><?php echo $v['description']; ?></td>
            <td><?php echo $v['count']; ?></td>
        </tr>
        <?php
        }
        }
        aaaaaaaaaaaaaaa( $data, $this );
        */

        ?>
    </tbody>
</table>
<div class="public-part-page"> <?php echo $pagination; ?> </div>
<p class="d-none">* Copy đoạn code bên dưới rồi cho vào nơi cần hiển thị block này ở trong view. Nhớ thay %slug% thành slug thật trong danh sách ở trên.</p>
<?php

if ( $taxonomy == TaxonomyType::ADS ) {
    ?>
<pre><code>&lt;?php $post_model = new\ App\ Models\ Post(); ?&gt;</code></pre>
<pre><code>&lt;?php $post_model->the_ads( '%slug%' ); ?&gt;</code></pre>
<?php
}

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/' . $taxonomy . '.js' );
