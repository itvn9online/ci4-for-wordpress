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
<div ng-app="myApp" ng-controller="myCtrl">
    <div class="cf admin-search-form">
        <div class="lf f62">
            <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
                <div class="cf">
                    <div class="lf f25">
                        <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $name_type; ?>" autofocus aria-required="true" required>
                    </div>
                    <div class="lf f25 hide-if-no-taxonomy">
                        <select name="term_id" data-select="<?php echo $by_term_id; ?>" data-taxonomy="<?php echo $taxonomy; ?>" onChange="document.frm_admin_search_controller.submit();" class="each-to-group-taxonomy">
                            <option value="0">- Danh mục <?php echo $name_type; ?> -</option>
                        </select>
                    </div>
                    <div class="lf f25">
                        <select name="post_status" data-select="<?php echo $post_status; ?>" onChange="document.frm_admin_search_controller.submit();">
                            <option value="">- Trạng thái <?php echo $name_type; ?> -</option>
                            <option value="{{k}}" ng-repeat="(k, v) in PostType_arrStatus">{{v}}</option>
                        </select>
                    </div>
                    <div class="lf f25">
                        <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="lf f38 text-right">
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
                <th>Tiêu đề <?php echo $name_type; ?></th>
                <th>Ảnh đại diện</th>
                <th>Danh mục</th>
                <th>Trạng thái</th>
                <th colspan="2">Ngày tạo/ Last Update</th>
                <th>Lang</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody id="admin_main_list">
            <tr data-id="{{v.ID}}" ng-repeat="v in data">
                <td>&nbsp;</td>
                <td>{{v.menu_order}}</td>
                <td><div><a href="{{v.admin_permalink}}" class="bold">{{v.post_title}} <i class="fa fa-edit"></i></a></div>
                    <div ng-class="post_type == PostType_MENU ? 'd-none' : ''"><a href="{{v.the_permalink}}" target="_blank" class="small blackcolor">{{v.post_name}} <i class="fa fa-external-link"></i></a></div></td>
                <td><div ng-class="post_type == PostType_MENU ? 'd-none' : ''" class="img-max-width"> <a href="{{v.admin_permalink}}"><img
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
                <td>{{v.post_date.substr(0, 16)}}</td>
                <td>{{v.post_modified.substr(0, 16)}}</td>
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
</div>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<script>
angular.module('myApp', []).controller('myCtrl', function ($scope) {
    $scope.data = <?php echo json_encode($data); ?>;
    $scope.post_type = '<?php echo $post_type; ?>';
    $scope.PostType_MENU = '<?php echo PostType::MENU; ?>';
    $scope.taxonomy = '<?php echo $taxonomy; ?>';
    $scope.controller_slug = '<?php echo $controller_slug; ?>';
    $scope.for_action = '<?php echo $for_action; ?>';
    $scope.PostType_DELETED = '<?php echo PostType::DELETED; ?>';
    $scope.PostType_arrStatus = <?php echo json_encode(PostType::arrStatus()); ?>;
});
</script>
<?php

//
include APPPATH . 'Views/admin/posts/sync_modal.php';

//
if ( $post_type == PostType::MENU ) {
    ?>
<pre><code>&lt;?php $menu_model->the_menu( '%slug%' ); ?&gt;</code></pre>
<?php
}

// css riêng cho từng post type (nếu có)
$base_model->add_js( 'admin/js/post_list.js' );
$base_model->add_js( 'admin/js/' . $post_type . '.js' );
