<?php

// Libraries
use App\ Libraries\ PostType;

//
$upload_model = new\ App\ Models\ Upload();

//
$uri_quick_upload = [];
foreach ( $_GET as $k => $v ) {
    $uri_quick_upload[] = $k . '=' . $v;
}

//
if ( isset( $_GET[ 'quick_upload' ] ) ) {
    //$uri_quick_upload = '&quick_upload=1';
    ?>
<style>
body {
    background: white;
    padding-top: 0;
    padding-left: 0;
}
#admin-header, #sidebar, #content-header, .admin-copyright, .hide-if-quick-edit, #target_eb_iframe {
    display: none !important;
}
/*
.show-if-quick-upload {
    display: block !important;
}
    */
</style>
<?php
}

//
$str_insert_to = '';
if ( isset( $_GET[ 'insert_to' ] ) ) {
    $str_insert_to = $_GET[ 'insert_to' ];
    ?>
<div class="rf"><strong onClick="return hide_if_esc();" class="cur medium18"><i class="fa fa-close"></i></strong></div>
<p class="text-center redcolor">* Bấm vào dấu <strong class="greencolor"><i class="fa fa-plus"></i></strong> hoặc bấm <strong>đúp chuột</strong> vào hình ảnh để nhúng ảnh vào nội dung</p>
<?php
}

//
$add_img_tag = '';
if ( isset( $_GET[ 'add_img_tag' ] ) ) {
    $add_img_tag = $_GET[ 'add_img_tag' ];
}

//
$img_size = '';
if ( isset( $_GET[ 'img_size' ] ) ) {
    $img_size = $_GET[ 'img_size' ];
}

//
$input_type = '';
if ( isset( $_GET[ 'input_type' ] ) ) {
    $input_type = $_GET[ 'input_type' ];
}

//
if ( !empty( $uri_quick_upload ) ) {
    $uri_quick_upload = '&' . implode( '&', $uri_quick_upload );
} else {
    $uri_quick_upload = '';
}

?>
<ul class="admin-breadcrumb">
    <li>Danh sách <?php echo $name_type; ?> (<?php echo $totalThread; ?>)</li>
</ul>
<div class="cf">
    <div class="lf f50 admin-search-form">
        <form name="frm_admin_search_controller" action="./admin/<?php echo $controller_slug; ?>" method="get">
            <?php

            // thêm các tham số ẩn khi tìm kiếm
            foreach ( $hiddenSearchForm as $k => $v ) {
                ?>
            <input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>">
            <?php
            }

            ?>
            <br>
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
    <div class="lf f50 text-center">
        <label for="upload_image" class="text-center greencolor cur">* Chọn ảnh để upload lên hệ thống (có thể chọn nhiều ảnh cùng lúc)</label>
        <form action="" method="post" name="frm_global_upload" role="form" enctype="multipart/form-data" target="target_eb_iframe">
            <input type="hidden" name="data" value="1" />
            <input type="file" name="upload_image[]" id="upload_image" accept="image/*" multiple />
        </form>
    </div>
</div>
<br>
<ul class="cf admin-media-attachment">
    <?php

    //
    //print_r( $data );
    foreach ( $data as $k => $v ) {
        //print_r( $v );
        $src = $upload_model->get_thumbnail( $v );
        //echo 'src: ' . $src . '<br>' . "\n";
        //continue;

        if ( $str_insert_to != '' ) {
            $all_src = $upload_model->get_all_media( $v );
        }
        $all_src[ 'thumbnail' ] = $src;
        //$all_src = json_encode( $all_src );
        //print_r( $all_src );

        // xác định url cho ảnh
        if ( $v[ 'post_type' ] == PostType::WP_MEDIA ) {
            $short_uri = PostType::WP_MEDIA_URI;
        } else {
            $short_uri = PostType::MEDIA_URI;
        }

        //
        $attachment_metadata = unserialize( $v[ 'post_meta' ][ '_wp_attachment_metadata' ] );
        //print_r( $attachment_metadata );
        $data_srcset = [
            $short_uri . $attachment_metadata[ 'file' ] . ' ' . $attachment_metadata[ 'width' ] . 'w'
        ];

        //
        $data_width = '';
        $data_height = '';
        foreach ( $attachment_metadata[ 'sizes' ] as $k_sizes => $sizes ) {
            //echo $k_sizes . '<br>' . "\n";
            //print_r( $sizes );

            //
            if ( $k_sizes == 'large' ) {
                $data_width = $sizes[ 'width' ];
                $data_height = $sizes[ 'height' ];
            }

            //
            $data_srcset[] = $short_uri . $sizes[ 'file' ] . ' ' . $sizes[ 'width' ] . 'w';
        }
        //print_r( $data_srcset );

        ?>
    <li data-id="<?php echo $v['ID']; ?>">
        <div title="<?php echo $v['post_name']; ?>" class="media-attachment-padding">
            <div class="d-none show-if-hover-upload lf medium18"><strong onClick="return click_set_img_for_input('<?php echo $v['ID']; ?>');" class="greencolor cur"><i class="fa fa-plus"></i></strong></div>
            <div class="d-none remove-attachment show-if-hover-upload rf medium18"><a href="admin/<?php echo $controller_slug; ?>/delete?id=<?php echo $v['ID'] . $uri_quick_upload; ?>" target="target_eb_iframe" onClick="return confirm('Xác nhận xóa tệp này?');"><i class="fa fa-trash"></i></a></div>
            <div data-id="<?php echo $v['ID']; ?>"
                 data-add_img_tag="<?php echo $add_img_tag; ?>"
                 data-insert="<?php echo $str_insert_to; ?>"
                 data-size="<?php echo $img_size; ?>"
                 data-width="<?php echo $data_width; ?>"
                 data-height="<?php echo $data_height; ?>"
                 data-input_type="<?php echo $input_type; ?>"
                 <?php
        foreach ($all_src as $size_name => $file) {
            echo ' data-' . $size_name . '="' . $file . '"' . "\n";
        }
        ?>
                 data-srcset="<?php echo implode(', ', $data_srcset); ?>"
                 data-sizes="(max-width: <?php echo $attachment_metadata['width']; ?>px) 100vw, <?php echo $attachment_metadata['width']; ?>px"
                 onDblClick="return click_set_img_for_input('<?php echo $v['ID']; ?>');"
                 class="media-attachment-img"
                 style="background-image: url('<?php echo $src; ?>');">&nbsp;</div>
        </div>
    </li>
    <?php
    }

    ?>
</ul>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<?php

//
$base_model->add_js( 'admin/js/uploads.js' );
