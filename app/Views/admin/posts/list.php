<?php

// Libraries
use App\ Libraries\ PostType;

//
//$base_model = new\ App\ Models\ Base();
//$post_model = new\ App\ Models\ PostAdmin();

// css riêng cho từng post type (nếu có)
$base_model->add_css( 'admin/css/' . $post_type . '.css' );

?>
<ul class="admin-breadcrumb">
    <li>Danh sách <?php echo $name_type; ?> (<?php echo $totalThread; ?>)</li>
</ul>
<div class="cf admin-search-form">
    <div class="lf f50">
        <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
            <div class="cf">
                <div class="lf f30">
                    <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $name_type; ?>" autofocus aria-required="true" required>
                </div>
                <div class="lf f30 hide-if-no-taxonomy">
                    <select name="term_id" data-select="<?php echo $by_term_id; ?>" data-taxonomy="<?php echo $taxonomy; ?>" onChange="document.frm_admin_search_controller.submit();" class="each-to-taxonomy-group">
                        <option value="0">- Nhóm <?php echo $name_type; ?> -</option>
                    </select>
                </div>
                <div class="lf f20">
                    <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="lf f50 text-right">
        <?php

        // menu sẽ được tự động khởi tạo khi dùng hàm để gọi -> không cho add thủ công
        if ( $post_type != PostType::MENU ) {
            ?>
        <div class="d-inline"><a href="<?php $post_model->admin_permalink( $post_type, 0, $controller_slug ); ?>" class="btn btn-success btn-mini"> <i class="fa fa-plus"></i> Thêm mới <?php echo $name_type; ?></a></div>
        <?php
        }

        ?>
        <div class="d-inline"><a href="admin/<?php echo $controller_slug; ?>?post_status=<?php echo PostType::DELETED; ?>" class="btn btn-mini"> <i class="fa fa-trash"></i> Lưu trữ</a></div>
    </div>
</div>
<br>
<table class="table table-bordered table-striped with-check table-list eb-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectall" name="selectall"/></th>
            <th>STT</th>
            <th>Tên bài viết</th>
            <th>Ảnh đại diện</th>
            <th>Chuyên mục</th>
            <th>Trạng thái</th>
            <th colspan="2">Ngày tạo/ Last Update</th>
            <th>Lang</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody id="admin_main_list" ng-app="myApp" ng-controller="myCtrl">
        <tr data-id="{{v.ID}}" ng-repeat="v in data">
            <td>&nbsp;</td>
            <td>{{v.menu_order}}</td>
            <td><div><a href="{{v.admin_permalink}}" class="bold">{{v.post_title}} <i class="fa fa-edit"></i></a></div>
                <div><a href="{{v.the_permalink}}" target="_blank" class="small blackcolor">{{v.post_name}} <i class="fa fa-external-link"></i></a></div></td>
            <td><div ng-if="post_type == PostType_MENU"> &nbsp; </div>
                <div ng-if="post_type != PostType_MENU"> <a href="{{v.admin_permalink}}"><img
                                                                                              ng-src="{{v.thumbnail}}"
                                                                                              src="images/_blank.png"
                                                                                              height="90"
                                                                                              data-class="each-to-img-src"
                                                                                              style="height: 90px; width: auto;" /></a> </div></td>
            <td data-id="{{v.main_category_key}}"
                data-taxonomy="{{taxonomy}}"
                data-uri="admin/{{controller_slug}}"
                class="each-to-taxonomy">&nbsp;</td>
            <td>{{v.post_status}}</td>
            <td>{{v.post_date}}</td>
            <td>{{v.post_modified}}</td>
            <td>{{v.lang_key}}</td>
            <td class="text-center"><div>
                    <div ng-if="v.post_status != PostType_DELETED">
                        <div><a href="admin/{{controller_slug}}/delete?id={{v.ID + for_action}}" onClick="return click_a_delete_record();" class="redcolor" target="target_eb_iframe"><i class="fa fa-trash"></i></a> </div>
                    </div>
                    <div class="d-inlines" ng-if="v.post_status == PostType_DELETED">
                        <div class="d-inline"><a href="admin/{{controller_slug}}/restore?id={{v.ID + for_action}}" onClick="return click_a_restore_record();" class="bluecolor" target="target_eb_iframe"><i class="fa fa-undo"></i></a></div>
                        &nbsp;
                        <div class="d-inline"><a href="admin/{{controller_slug}}/remove?id={{v.ID + for_action}}" onClick="return click_a_remove_record();" class="redcolor" target="target_eb_iframe"><i class="fa fa-remove"></i></a></div>
                    </div>
                </div></td>
        </tr>
    </tbody>
</table>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<div class="text-right"><a href="admin/<?php echo $controller_slug; ?>?auto_update_module=1" class="btn btn-info"> <i class="fa fa-refresh"></i> Đồng bộ lại dữ liệu theo tiêu chuẩn chung</a></div>
<p class="d-none">* Copy đoạn code bên dưới rồi cho vào nơi cần hiển thị block này ở trong view. Nhớ thay %slug% thành slug thật trong danh sách ở trên.</p>
<script>
angular.module('myApp', []).controller('myCtrl', function ($scope) {
    $scope.data = <?php echo json_encode($data); ?>;
    $scope.post_type = '<?php echo $post_type; ?>';
    $scope.PostType_MENU = '<?php echo PostType::MENU; ?>';
    $scope.taxonomy = '<?php echo $taxonomy; ?>';
    $scope.controller_slug = '<?php echo $controller_slug; ?>';
    $scope.for_action = '<?php echo $for_action; ?>';
    $scope.PostType_DELETED = '<?php echo PostType::DELETED; ?>';
});
</script>
<?php

if ( $post_type == PostType::MENU ) {
    ?>
<pre><code>&lt;?php $menu_model->the_menu( '%slug%' ); ?&gt;</code></pre>
<?php
}

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/post_list.js' );
$base_model->add_js( 'admin/js/' . $post_type . '.js' );
