// thêm nút add ảnh đại diện
add_and_show_post_avt('#post_meta_image');


if (typeof page_post_type != 'undefined' && current_post_type == page_post_type) {
    WGR_load_textediter('#data_post_excerpt');
}
