<?php

// Libraries
use App\ Libraries\ PostType;

//
$base_model->add_css( 'admin/css/uploads.css' );

//
$upload_model = new\ App\ Models\ Upload();

//
$uri_quick_upload = [];
foreach ( $_GET as $k => $v ) {
    //echo $k . '<br>' . "\n";
    $uri_quick_upload[] = $k . '=' . $v;
}

//
if ( $mode == 'list' ) {
    $inc_style = 'list_list';
} else {
    $inc_style = 'list_grid';
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
#admin-header, #adminmenumain, #sidebar, #content-header, .admin-copyright, .hide-if-quick-edit, #target_eb_iframe {
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
            foreach ( $hiddenSearchForm as $k => $v ) {
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
                        foreach ( $alow_mime_type as $k => $v ) {
                            ?>
                        <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
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
                        foreach ( $m_filter as $v ) {
                            ?>
                        <option value="<?php echo $v; ?>">Tháng <?php echo $v; ?></option>
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
        <label for="upload_image" class="text-center greencolor cur">* Chọn ảnh để upload lên hệ thống (có thể chọn nhiều ảnh cùng lúc)</label>
        <form action="" method="post" name="frm_global_upload" role="form" enctype="multipart/form-data" target="target_eb_iframe">
            <input type="hidden" name="data" value="1" />
            <input type="file"
                   name="upload_image[]"
                   id="upload_image"
                   accept="image/*,video/*,audio/*,application/*,text/*"
                   multiple />
            <div class="d-none">
                <button type="submit">sb</button>
            </div>
        </form>
    </div>
</div>
<br>
<ul id="admin_main_list" class="cf admin-media-attachment <?php echo $mode; ?>">
    <?php

    //
    //print_r( $data );
    //die( __FILE__ . ':' . __LINE__ );
    foreach ( $data as $k => $v ) {
        //print_r( $v );

        //
        $all_src = [];
        $data_srcset = [];
        $data_width = '';
        $data_height = '';
        $src = $upload_model->get_thumbnail( $v );
        //echo 'src: ' . $src . '<br>' . "\n";
        //continue;

        // với định dạng khác -> chưa xử lý
        if ( strtolower( explode( '/', $v[ 'post_mime_type' ] )[ 0 ] ) != 'image' ) {
            $background_image = '';
            $attachment_metadata = [
                'width' => 0,
            ];
        }
        // xử lý riêng với hình ảnh
        else {
            $background_image = 'background-image: url(\'' . $src . '\');';

            //
            if ( $str_insert_to != '' ) {
                $all_src = $upload_model->get_all_media( $v );
            }
            //print_r( $all_src );
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
            //continue;
            if ( $attachment_metadata[ 'width' ] > 0 ) {
                $data_srcset = [
                    $short_uri . $attachment_metadata[ 'file' ] . ' ' . $attachment_metadata[ 'width' ] . 'w'
                ];
            }

            //
            foreach ( $attachment_metadata[ 'sizes' ] as $k_sizes => $sizes ) {
                //echo $k_sizes . '<br>' . "\n";
                //print_r( $sizes );
                //continue;

                //
                if ( isset( $sizes[ 'width' ] ) ) {
                    if ( $k_sizes == 'large' ) {
                        $data_width = $sizes[ 'width' ];
                        $data_height = $sizes[ 'height' ];
                    }

                    //
                    $data_srcset[] = $short_uri . $sizes[ 'file' ] . ' ' . $sizes[ 'width' ] . 'w';
                }
            }
        }
        $all_src[ 'thumbnail' ] = $src;
        //print_r( $data_srcset );

        ?>
    <li data-id="<?php echo $v['ID']; ?>">
        <?php
        include __DIR__ . '/' . $inc_style . '.php';
        //include __DIR__ . '/list_grid.php';
        ?>
    </li>
    <?php
    }

    ?>
</ul>
<div class="public-part-page"> <?php echo $pagination; ?> Trên tổng số <?php echo $totalThread; ?> bản ghi.</div>
<?php

//
$base_model->add_js( 'admin/js/uploads.js' );
