<?php

//
$base_model->adds_css([
    'admin/css/uploads_drag_drop.css',
    'admin/css/uploads.css',
]);

//
$upload_model = new \App\Models\Upload();

// tách file ra head -> vì ko rõ tại sao format code trên vscode bị lỗi
include __DIR__ . '/list_head.php';

?>
<ul class="admin-breadcrumb">
    <li>Danh sách
        <?php echo $name_type; ?> (
        <?php echo $totalThread; ?>)
    </li>
</ul>
<?php

// nạp HTML phần drag drop upload
include VIEWS_PATH . 'includes/uploads_drag_drop.php';

?>
<div class="cf admin-upload-filter <?php echo $mode; ?>">
    <div class="lf f10 big d-inlines">
        <div><a data-mode="grid" class="click-set-mode cur"><i class="fa fa-th-large"></i></a></div>
        <div><a data-mode="list" class="click-set-mode cur"><i class="fa fa-list"></i></a></div>
    </div>
    <div class="lf f40 admin-search-form">
        <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
            <input type="hidden" name="mode" id="mode_filter" value="<?php echo $mode; ?>">
            <?php

            // thêm các tham số ẩn khi tìm kiếm
            foreach ($hiddenSearchForm as $k => $v) {
            ?>
                <input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>">
            <?php
            }

            ?>
            <div class="cf">
                <div class="lf f30">
                    <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm <?php echo $name_type; ?>" autofocus aria-required="true" required>
                </div>
                <div class="lf f30">
                    <select data-select="<?php echo $attachment_filter; ?>" name="attachment-filter" id="attachment-filter">
                        <option value="">Tất cả</option>
                        <?php

                        //
                        foreach ($alow_mime_type as $k => $v) {
                        ?>
                            <option value="<?php echo $k; ?>">
                                <?php echo $v; ?>
                            </option>
                        <?php
                        }

                        ?>
                    </select>
                </div>
                <div class="lf f30">
                    <select data-select="<?php echo $month_filter; ?>" name="m" id="filter-by-date">
                        <option value="">Tất cả các tháng</option>
                        <?php

                        //
                        foreach ($m_filter as $v) {
                        ?>
                            <option value="<?php echo $v; ?>">Tháng
                                <?php echo $v; ?>
                            </option>
                        <?php
                        }

                        ?>
                    </select>
                </div>
                <div class="lf f10">
                    <button type="submit" class="btn-success"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>
    <div class="lf f50 text-center">
        <label for="upload_image" class="text-center greencolor cur">* Kéo thả ảnh vào đây hoặc Chọn ảnh để upload lên hệ thống</label>
        <form action="" method="post" name="frm_global_upload" role="form" enctype="multipart/form-data" target="target_eb_iframe">
            <input type="hidden" name="data" value="1" />
            <input type="file" name="upload_image[]" id="upload_image" accept="image/*,video/*,audio/*,application/*,text/*" multiple />
            <div class="d-none">
                <button type="submit">sb</button>
            </div>
        </form>
    </div>
</div>
<br>
<ul id="admin_main_list" class="cf admin-media-attachment <?php echo $mode; ?>">
    <?php

    // tách file ra head -> vì ko rõ tại sao format code trên vscode bị lỗi
    include __DIR__ . '/list_body.php';

    ?>
</ul>
<div class="public-part-page">
    <?php echo $pagination; ?> Trên tổng số
    <?php echo $totalThread; ?> bản ghi.
</div>
<?php

//
$base_model->adds_js([
    'admin/js/uploads.js',
    'admin/js/uploads_drag_drop.js',
    'javascript/uploads.js',
]);
