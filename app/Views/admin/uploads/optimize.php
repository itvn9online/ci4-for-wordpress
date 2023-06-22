<?php

// Libraries
use App\Libraries\PostType;
use App\Libraries\MyImage;

//
$max_quality_img = 250000;
//$max_size_img = 2300;
$max_size_img = 0;

?>
<ul class="admin-breadcrumb">
    <li>Danh sách Tối ưu hóa hình ảnh (
        <?php echo $totalThread; ?>)
    </li>
</ul>
<p class="medium">Chức năng tối ưu hóa hình ảnh vượt quá <strong>
        <?php echo ($max_quality_img / 1000); ?>
    </strong>kb</p>
<div class="public-part-page">
    <?php echo $pagination; ?> Trên tổng số
    <?php echo $totalThread; ?> bản ghi
    (
    <?php echo $totalPage; ?> trang).
</div>
<?php

//
if (class_exists('Imagick')) {
?>
    <p class="medium grrencolor"><strong>Imagick</strong> enable</p>
<?php
}

//
//print_r( $data );
//die( __FILE__ . ':' . __LINE__ );
foreach ($data as $k => $v) {
    //print_r( $v );

    //
    $attachment_metadata = unserialize($v['post_meta']['_wp_attachment_metadata']);

    //
    if ($v['post_type'] == PostType::WP_MEDIA) {
        $uri = PostType::WP_MEDIA_URI;
    } else {
        $uri = PostType::MEDIA_URI;
    }

    //
    $file_path = PUBLIC_PUBLIC_PATH . $uri . $attachment_metadata['file'];
    $file_size = filesize($file_path);
    if ($file_size < $max_quality_img) {
        echo $file_path . ' (' . ceil($file_size / 1000) . ')<br>' . PHP_EOL;
        $img_src = str_replace(PUBLIC_PUBLIC_PATH, '', $file_path);
        echo '<a href="' . $img_src . '" target="_blank" class="bluecolor">' . $img_src . ' (' . ceil($file_size / 1000) . ')</a> <br>' . PHP_EOL;

        //
        continue;
    }
    //print_r( $v );
    //continue;
    $attachment_metadata['file_size'] = $file_size;

    //
    $get_file_info = getimagesize($file_path);
    //print_r( $get_file_info );

    // nếu size ảnh to quá -> tiến hành resize lại
    if ($max_size_img > 0 && $get_file_info[0] > $max_size_img && $get_file_info[1] > $max_size_img) {
        // resize theo chiều rộng
        if ($get_file_info[0] > $max_size_img) {
            MyImage::resize($file_path, '', $max_size_img);
        }
        // resize theo chiều cao
        else if ($get_file_info[1] > $max_size_img) {
            MyImage::resize($file_path, '', 0, $max_size_img);
        }
    }

    //
    //print_r( $attachment_metadata );
    //echo $file_size . '<br>' . PHP_EOL;
    //echo $file_path . '<br>' . PHP_EOL;
    //echo $uri . $attachment_metadata[ 'file' ] . '<br>' . PHP_EOL;

    //
    $dir_path = dirname($file_path) . '/';
    //echo $dir_path . '<br>' . PHP_EOL;

    //
    $dst_file = $file_path;
    //$dst_file = $dir_path . '___' . basename( $file_path );
    echo $dst_file . ' (' . ceil($file_size / 1000) . ')<br>' . PHP_EOL;

    // -> optimize
    MyImage::quality($file_path, $dst_file, $attachment_metadata['width'], $attachment_metadata['height']);

    //
    $img_src = str_replace(PUBLIC_PUBLIC_PATH, '', $dst_file);
    clearstatcache();
    echo '<a href="' . $img_src . '" target="_blank" class="greencolor">' . $img_src . ' (' . ceil(filesize($dst_file) / 1000) . ')</a> <br>' . PHP_EOL;

    // bắt đầu resize
    foreach ($attachment_metadata['sizes'] as $k2 => $v2) {
        // chỉ optimize với các file thuộc dạng copy từ bản gốc
        if ($v2['width'] < $attachment_metadata['width']) {
            continue;
        }
        //print_r( $v2 );

        // dung lượng file không đủ thì bỏ qua
        $file2_path = $dir_path . $v2['file'];
        $file2_size = filesize($file2_path);
        if ($file2_size < $max_quality_img) {
            continue;
        }
        //echo $file2_path . '<br>' . PHP_EOL;

        //
        $dst_file = $file2_path;
        //$dst_file = $dir_path . '______' . basename( $file2_path ); // TEST
        echo $dst_file . ' (' . ceil($file2_size / 1000) . ')<br>' . PHP_EOL;

        // -> optimize
        MyImage::quality($file2_path, $dst_file, $v2['width'], $v2['height']);

        //
        $img_src = str_replace(PUBLIC_PUBLIC_PATH, '', $dst_file);
        clearstatcache();
        echo '<a href="' . $img_src . '" target="_blank" class="greencolor">' . $img_src . ' (' . ceil(filesize($dst_file) / 1000) . ')</a> <br>' . PHP_EOL;
    }
}

//
$base_model->JSON_echo(
    [
        // mảng này sẽ in ra dưới dạng JSON hoặc number
        'totalPage' => $totalPage,
    ],
    [
        // mảng này sẽ in ra dưới dạng string

    ]
);

// js riêng cho từng post type (nếu có)
$base_model->add_js('admin/js/optimize.js');
