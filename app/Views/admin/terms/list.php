<?php

// Libraries
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ DeletedStatus;

//
//$base_model = new\ App\ Models\ Base();
//$term_model = new\ App\ Models\ Term();

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/' . $taxonomy . '.css' );

?>
<script>
var term_data = <?php echo json_encode($data); ?>;
var for_redirect = '<?php echo ($by_is_deleted != '' ? '&is_deleted=' . $by_is_deleted : ''); ?>'
var controller_slug = '<?php echo $controller_slug; ?>';

//
angular.module('myApp', []).controller('myCtrl', function($scope) {
    $scope.data = term_data;
});
</script>

<ul class="admin-breadcrumb">
    <li><?php echo $name_type; ?> (<?php echo $totalThread; ?>)</li>
</ul>
<div class="cf admin-search-form">
    <div class="lf f50">
        <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
            <div class="cf">
                <div class="lf f30">
                    <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $name_type; ?>" autofocus aria-required="true" required>
                </div>
                <div class="lf f20">
                    <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="lf f50 text-right">
        <div class="d-inline"> <a href="<?php $term_model->admin_permalink( $taxonomy, 0, $controller_slug ); ?>" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới <?php echo $name_type; ?></a> </div>
        <div class="d-inline"><a href="admin/<?php echo $controller_slug; ?>?is_deleted=<?php echo DeletedStatus::DELETED; ?>" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
    </div>
</div>
<br>
<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectall" name="selectall"/></th>
            <th>ID</th>
            <th>Tên bài viết</th>
            <th>Slug</th>
            <th class="d-none show-if-ads-type">Size</th>
            <th>Nội dung</th>
            <th>Ngôn ngữ</th>
            <th>Bài viết</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="term_list_tmp" ng-app="myApp" ng-controller="myCtrl"
           ng-init="controller_slug=controller_slug;
                    DeletedStatus_DELETED='<?php echo DeletedStatus::DELETED; ?>';
                    for_redirect=for_redirect;">
        <tr data-id="{{v.term_id}}" class="each-to-child-term" ng-repeat="v in data">
            <td>&nbsp;</td>
            <td>{{v.term_id}}</td>
            <td><a href="{{v.get_admin_permalink}}">{{v.gach_ngang}}{{v.name}} <i class="fa fa-edit"></i></a></td>
            <td><a href="{{v.view_url}}" target="_blank">{{v.slug}} <i class="fa fa-external-link"></i></a></td>
            <td class="d-none show-if-ads-type">{{v.term_meta['custom_size']}}</td>
            <td>&nbsp;</td>
            <td>{{v.lang_key}}</td>
            <td>{{v.count}}</td>
            <td class="text-center"><div>
                    <div ng-if="v.is_deleted == DeletedStatus_DELETED">
                        <div><a href="admin/{{controller_slug}}/restore?id={{v.term_id}}{{for_redirect}}" onClick="return click_a_restore_record();" target="target_eb_iframe" class="bluecolor"><i class="fa fa-undo"></i></a></div>
                    </div>
                    <div class="d-inlines" ng-if="v.is_deleted != DeletedStatus_DELETED">
                        <div><a href="admin/{{controller_slug}}/term_status?id={{v.term_id}}&current_status={{v.term_status}}{{for_redirect}}" target="target_eb_iframe" data-status="{{v.term_status}}" class="record-status-color"><i class="fa fa-eye"></i></a></div>
                        &nbsp;
                        <div><a href="admin/{{controller_slug}}/delete?id={{v.term_id}}{{for_redirect}}" onClick="return click_a_delete_record();" target="target_eb_iframe" class="redcolor"><i class="fa fa-trash"></i></a></div>
                    </div>
                </div></td>
        </tr>
        <?php

        //echo $term_model->list_html_view( $data, '', $by_is_deleted, $controller_slug );
        //$term_model->get_admin_permalink($v['taxonomy'], $v['term_id']);

        ?>
    </tbody>
</table>
<div class="public-part-page"> <?php echo $pagination; ?> </div>
<p class="d-none">* Copy đoạn code bên dưới rồi cho vào nơi cần hiển thị block này ở trong view. Nhớ thay %slug% thành slug thật trong danh sách ở trên.</p>
<?php

if ( $taxonomy == TaxonomyType::ADS ) {
    ?>
<pre><code>&lt;?php $post_model->the_ads( '%slug%' ); ?&gt;</code></pre>
<?php
}

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/terms.js' );
$base_model->add_js( 'admin/js/' . $taxonomy . '.js' );
