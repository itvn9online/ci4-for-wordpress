<ul class="admin-breadcrumb">
    <li>Dọn dẹp website</li>
</ul>
<h3>Chức năng này sẽ dọn dẹp cache trên website, nạp lại dữ liệu mới nhất cho web:</h3>
<p>Nơi lưu <strong>file</strong> cache:
    <?php echo WRITE_CACHE_PATH; ?>
</p>
<div class="left-menu-space">
    <div class="row">
        <div class="col">
            <div>
                <form action="admin/dashboard/cleanup_matching_cache" method="post" role="form" onsubmit="return waiting_cleanup_cache();" enctype="multipart/form-data" target="target_eb_iframe">
                    <input type="hidden" name="data" value="post" />
                    <br>
                    <div>
                        <button type="submit" class="btn btn-info"><i class="fa fa-file-word-o"></i> Xóa cache bài viết/
                            tin
                            tức...
                            (post*)</button>
                    </div>
                </form>
            </div>
            <br>
            <div>
                <form action="admin/dashboard/cleanup_matching_cache" method="post" role="form" onsubmit="return waiting_cleanup_cache();" enctype="multipart/form-data" target="target_eb_iframe">
                    <input type="hidden" name="data" value="get_page" />
                    <br>
                    <div>
                        <button type="submit" class="btn btn-info"><i class="fa fa-file-word-o"></i> Xóa cache trang
                            tĩnh
                            (get_page*)</button>
                    </div>
                </form>
            </div>
            <br>
            <div>
                <form action="admin/dashboard/cleanup_matching_cache" method="post" role="form" onsubmit="return waiting_cleanup_cache();" enctype="multipart/form-data" target="target_eb_iframe">
                    <input type="hidden" name="data" value="term" />
                    <br>
                    <div>
                        <button type="submit" class="btn btn-success"><i class="fa fa-file-word-o"></i> Xóa cache danh
                            mục
                            (term*)</button>
                    </div>
                </form>
            </div>
            <br>
            <div>
                <form action="admin/dashboard/cleanup_matching_cache" method="post" role="form" onsubmit="return waiting_cleanup_cache();" enctype="multipart/form-data" target="target_eb_iframe">
                    <input type="hidden" name="data" value="get_the_menu" />
                    <br>
                    <div>
                        <button type="submit" class="btn btn-dark"><i class="fa fa-bars"></i> Xóa cache menu
                            (get_the_menu*)</button>
                    </div>
                </form>
            </div>
            <br>
            <div>
                <form action="admin/dashboard/cleanup_matching_cache" method="post" role="form" onsubmit="return waiting_cleanup_cache();" enctype="multipart/form-data" target="target_eb_iframe">
                    <input type="hidden" name="data" value="user" />
                    <br>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-users"></i> Xóa cache cá nhân
                            (user*)</button>
                    </div>
                </form>
            </div>
            <br>
            <div class="tborder">
                <form action="" method="post" role="form" onsubmit="return waiting_cleanup_cache();" enctype="multipart/form-data" target="target_eb_iframe">
                    <input type="hidden" name="data" value="1" />
                    <br>
                    <div>
                        <button type="submit" class="btn btn-danger"><i class="fa fa-magic"></i> Xóa toàn bộ cache trên
                            web</button>
                    </div>
                </form>
            </div>
            <br>
        </div>
        <div class="col">
            <div>
                <form action="admin/dashboard/reset_term_permalink" method="post" role="form" onsubmit="return waiting_cleanup_cache();" enctype="multipart/form-data" target="target_eb_iframe">
                    <br>
                    <div>
                        <button type="submit" class="btn btn-warning"><i class="fa fa-refresh"></i> Cập nhật lại
                            Permalink cho
                            Terms</button>
                    </div>
                </form>
            </div>
            <br>
            <div>
                <form action="admin/dashboard/reset_post_permalink" method="post" role="form" onsubmit="return waiting_cleanup_cache();" enctype="multipart/form-data" target="target_eb_iframe">
                    <br>
                    <div>
                        <button type="submit" class="btn btn-danger"><i class="fa fa-refresh"></i> Cập nhật lại
                            Permalink cho
                            Posts</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<br>
<?php

//
$base_model->adds_js([
    'admin/js/cleanup.js',
]);
