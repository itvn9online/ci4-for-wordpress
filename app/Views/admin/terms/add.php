<?php

// Libraries
use App\Libraries\TaxonomyType;

// css riêng cho từng post type (nếu có)
$base_model->add_css('admin/css/' . $taxonomy . '.css');

//
include ADMIN_ROOT_VIEWS . 'terms/add_breadcrumb.php';

?>
<div class="widget-box">
    <div class="widget-content nopadding">
        <form action="" method="post" name="admin_global_form" id="admin_global_form" accept-charset="utf-8" class="form-horizontal" target="target_eb_iframe">
            <input type="hidden" name="is_duplicate" id="is_duplicate" value="0" />
            <input type="hidden" name="data[lang_key]" value="<?php echo $data['lang_key']; ?>" />
            <div class="rf">
                <button type="button" onClick="click_duplicate_record();" class="btn btn-warning"><i class="fa fa-copy"></i> Nhân bản</button>
            </div>
            <div class="control-group">
                <label class="control-label">Ngôn ngữ</label>
                <div class="controls">
                    <?php

                    // chạy vòng lặp hiển thị các ngôn ngữ được hỗ trợ trên website
                    $lang_parent = $data['lang_parent'] > 0 ? $data['lang_parent'] : $data['term_id'];
                    foreach (SITE_LANGUAGE_SUPPORT as $v) {
                        if ($v['value'] == $data['lang_key']) {
                    ?>
                            | <strong class="redcolor"><?php echo $term_lang; ?></strong>
                        <?php
                            continue;
                        }
                        ?>
                        | <a href="<?php echo $term_model->get_admin_permalink($taxonomy, $lang_parent, $controller_slug); ?>&clone_lang=<?php echo $v['value']; ?>&preview_url=<?php echo urlencode($preview_url); ?>" class="bluecolor"><?php echo $v['text']; ?></a>
                    <?php
                    }

                    ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Tiêu đề</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Tiêu đề" name="data[name]" value="<?php $base_model->the_esc_html($data['name']); ?>" autofocus aria-required="true" required />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Tiêu đề (ngắn)</label>
                <div class="controls">
                    <input type="text" class="span6 required" placeholder="Tiêu đề (ngắn)" name="data[term_shortname]" value="<?php $base_model->the_esc_html($data['term_shortname']); ?>" />
                </div>
            </div>
            <?php

            // các mục không cho sửa slug -> vì sửa xong sẽ làm lệnh lấy tin tự động hoạt động sai
            if ($taxonomy == TaxonomyType::ADS) {
                if ($data['slug'] != '') {
            ?>
                    <div class="control-group">
                        <label class="control-label">PHP Code:</label>
                        <div class="controls">
                            <input type="text" class="span6" value="&lt;?php $this->post_model->the_ads('<?php echo $data['slug']; ?>'); ?&gt;" readonly />
                            <input type="hidden" name="data[slug]" value="<?php echo $data['slug']; ?>" />
                        </div>
                    </div>
                <?php
                }
            }
            // các mục khác cho hiển thị slug để sửa
            else {
                ?>
                <div class="control-group">
                    <label class="control-label">Slug</label>
                    <div class="controls">
                        <input type="text" title="Bấm đúp chuột để chỉnh sửa đường dẫn" class="span6" name="data[slug]" id="data_post_name" onDblClick="$('#data_post_name').removeAttr('readonly');" value="<?php echo $data['slug']; ?>" readonly />
                        <input type="hidden" name="old_slug" id="old_slug" value="<?php echo $data['slug']; ?>" />
                        <?php
                        if ($data['term_id'] > 0) {
                        ?>
                            <div>
                                <a href="<?php $term_model->the_term_permalink($data); ?>" class="bluecolor set-new-url"><?php echo $data['term_permalink']; ?></a> <i class="fa fa-eye bluecolor"></i>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            <?php
            }

            ?>
            <div class="control-group">
                <label class="control-label">Nội dung</label>
                <div class="controls f80">
                    <textarea id="Resolution" rows="30" data-height="<?php echo ($taxonomy == TaxonomyType::ADS ? '250' : '550'); ?>" class="ckeditor auto-ckeditor" placeholder="Nhập thông tin chi tiết..." name="data[description]"><?php echo $data['description']; ?></textarea>
                </div>
            </div>
            <?php
            // cho phép xác định cha con với danh mục
            if ($set_parent != '') {
            ?>
                <div class="control-group">
                    <label class="control-label">Danh mục cha</label>
                    <div class="controls">
                        <select data-select="<?php echo $data['parent']; ?>" name="data[parent]" id="data_parent" class="span5">
                            <option value="0">[ Chọn Danh mục cha ]</option>
                        </select>
                    </div>
                </div>
            <?php
            }
            ?>
            <div class="control-group">
                <label class="control-label">Số thứ tự:</label>
                <div class="controls">
                    <input type="number" class="span6" value="<?php echo $data['term_order']; ?>" name="data[term_order]" />
                    <p class="controls-text-note">Sắp xếp vị trí hiển thị, số càng to thì độ ưu tiên càng cao</p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Ảnh đại diện:</label>
                <div class="controls">
                    <input type="text" class="span6" value="<?php echo $data['term_avatar']; ?>" name="data[term_avatar]" id="data_term_avatar" />
                    <p class="controls-text-note">Ảnh đại diện của
                        <?php echo $name_type; ?>, thường dùng khi chia sẻ liên kết lên mạng xã hội như Zalo, Facebook hoặc làm hình nền cho Danh mục.
                    </p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Hình thu nhỏ:</label>
                <div class="controls">
                    <input type="text" class="span6" value="<?php echo $data['term_favicon']; ?>" name="data[term_favicon]" id="data_term_favicon" />
                    <p class="controls-text-note">Hình thu nhỏ của
                        <?php echo $name_type; ?>, một số giao diện sẽ sử dụng đến nó ở trong các menu. Để tối ưu tốc độ
                        cho website, kích thước ảnh thu nhỏ không được vượt quá 124x124 pixel.
                    </p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Trạng thái hiển thị:</label>
                <div class="controls">
                    <select data-select="<?php echo $data['term_status']; ?>" name="data[term_status]" id="data_term_status" class="span5">
                        <option value="<?php echo TaxonomyType::VISIBLE; ?>">Hiển thị</option>
                        <option value="<?php echo TaxonomyType::HIDDEN; ?>">Ẩn</option>
                    </select>
                    <p class="controls-text-note">Dùng khi cần ẩn các danh mục khỏi menu động.</p>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <h5>Thông số hiển thị cho widget:</h5>
                </div>
            </div>
            <?php

            // nạp các meta theo từng loại post
            foreach ($meta_detault as $k => $v) {
                $input_type = TaxonomyType::meta_type($k);

                //
            ?>
                <div class="control-group">
                    <?php
                    if ($input_type == 'checkbox') {
                    ?>
                        <div class="controls controls-checkbox">
                            <label>
                                <input type="checkbox" name="term_meta[<?php echo $k; ?>]" id="term_meta_<?php echo $k; ?>" value="on" data-value="<?php $term_model->echo_meta_term($data, $k); ?>" />
                                <?php echo $v; ?>
                            </label>
                            <?php

                            // hiển thị ghi chú nếu có
                            TaxonomyType::meta_desc($k);

                            ?>
                        </div>
                    <?php
                    }
                    // END if input type checkbox
                    else {
                    ?>
                        <label for="term_meta_<?php echo $k; ?>" class="control-label">
                            <?php echo $v; ?>
                        </label>
                        <div class="controls">
                            <?php

                            //
                            if ($input_type == 'textarea') {
                            ?>
                                <textarea placeholder="<?php echo $v; ?>" name="term_meta[<?php echo $k; ?>]" id="term_meta_<?php echo $k; ?>" class="span10"><?php $term_model->echo_meta_term($data, $k); ?></textarea>
                            <?php
                            }
                            // END if input type textarea
                            else if ($input_type == 'select' || $input_type == 'select_multiple') {
                                $select_multiple = '';
                                $meta_multiple = '';
                                if ($input_type == 'select_multiple') {
                                    $select_multiple = 'multiple';
                                    $meta_multiple = '[]';
                                }

                                // lấy danh sách page template cho page
                                if ($k == 'term_template') {
                                    $arr_template = $base_model->htaccess_custom_template(THEMEPATH . 'term-templates/', '.{php}', 'file');
                                    //print_r($arr_template);

                                    //
                                    $select_options = array(
                                        '' => '[ Mặc định ]'
                                    );
                                    foreach ($arr_template as $tmp_k => $tmp_v) {
                                        $tmp_v = basename($tmp_v, '.php');
                                        $select_options[$tmp_v] = str_replace('-', ' ', $tmp_v);
                                    }
                                }
                                // danh sách col HTML nếu có
                                else if ($k == 'term_col_templates') {
                                    $arr_template = $base_model->htaccess_custom_template(THEMEPATH . 'term-col-templates/', '.{html}', 'file');
                                    //print_r($arr_template);

                                    //
                                    $select_options = array(
                                        '' => '[ Mặc định ]'
                                    );
                                    foreach ($arr_template as $tmp_k => $tmp_v) {
                                        $tmp_v = basename($tmp_v, '.php');
                                        $select_options[$tmp_v] = str_replace('-', ' ', $tmp_v);
                                    }
                                }
                                // còn lại sẽ sử dụng select được thiết lập trong code
                                else {
                                    $select_options = TaxonomyType::meta_select($k);
                                }

                            ?>
                                <select data-select="<?php $term_model->echo_meta_term($data, $k); ?>" name="term_meta[<?php echo $k; ?>]<?php echo $meta_multiple; ?>" id="term_meta_<?php echo $k; ?>" class="span5" <?php echo $select_multiple; ?>>
                                    <?php

                                    foreach ($select_options as $option_k => $option_v) {
                                        echo '<option value="' . $option_k . '">' . $option_v . '</option>';
                                    }

                                    ?>
                                </select>
                            <?php
                            }
                            // END else if input type select                    
                            else {
                            ?>
                                <input type="<?php echo $input_type; ?>" class="span6" placeholder="<?php echo $v; ?>" name="term_meta[<?php echo $k; ?>]" id="term_meta_<?php echo $k; ?>" value="<?php $term_model->echo_meta_term($data, $k); ?>" />
                            <?php
                            }

                            // hiển thị ghi chú nếu có
                            TaxonomyType::meta_desc($k);

                            ?>
                        </div>
                    <?php
                    } // END else input type checkbox
                    ?>
                </div>
            <?php
            } // END auto add term_meta

            ?>
            <div class="end-term-add"></div>
            <div class="control-group">
                <div class="control-label">Taxonomy:</div>
                <div class="controls"><?php echo $data['taxonomy']; ?></div>
            </div>
            <div class="control-group">
                <div class="control-label">Total posts:</div>
                <div class="controls"><?php echo $data['count']; ?></div>
            </div>
            <div class="control-group">
                <div class="control-label">Total child term:</div>
                <div class="controls"><?php echo $data['child_count']; ?></div>
            </div>
            <div class="control-group">
                <div class="control-label">Next count child term:</div>
                <div class="controls"><?php echo ($data['child_last_count'] > 0 ? date('Y-m-d H:i:s', $data['child_last_count']) : ''); ?></div>
            </div>
            <?php

            // tạo module check độ chuẩn SEO cho danh mục
            if ($data['term_id'] > 0) {
                $linkEncode = urlencode($term_model->get_full_permalink($data));

                //
                foreach ([
                    [
                        'name' => 'Page speed',
                        'link' => 'https://pagespeed.web.dev/report?url=' . $linkEncode,
                    ], [
                        'name' => 'Structured data',
                        'link' => 'https://validator.schema.org/#url=' . $linkEncode,
                    ], [
                        'name' => 'Open Graph Facebook',
                        'link' => 'https://developers.facebook.com/tools/debug/?q=' . $linkEncode,
                    ], [
                        'name' => 'Open Graph Zalo',
                        'link' => 'https://developers.zalo.me/tools/debug-sharing?q=' . $linkEncode,
                    ], [
                        'name' => 'Security headers',
                        'link' => 'https://securityheaders.com/?q=' . $linkEncode . '&followRedirects=on',
                    ]
                ] as $v) {
            ?>
                    <div class="control-group">
                        <div class="control-label"><?php echo $v['name']; ?></div>
                        <div class="controls"><a href="<?php echo $v['link']; ?>" target="_blank" rel="nofollow"><?php echo $v['link']; ?></a></div>
                    </div>
            <?php
                }
            }

            ?>
            <div class="form-actions frm-fixed-btn cf">
                <?php
                if ($data['term_id'] > 0) {
                ?>
                    <a href="admin/<?php echo $controller_slug; ?>/delete?id=<?php echo $data['term_id']; ?>" onClick="return click_a_delete_record();" class="btn btn-danger btn-small" target="target_eb_iframe"><i class="fa fa-trash"></i> XÓA
                        <?php echo $name_type; ?>
                    </a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Lưu lại</button>
                <?php
                } else {
                ?>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Thêm mới
                        <?php echo $name_type; ?>
                    </button>
                <?php
                }
                ?>
            </div>
        </form>
    </div>
</div>
<br>
<div class="left-menu-space">
    <h3><?php echo $name_type; ?> khác:</h3>
    <ul id="oi_other_posts" class="s14">
        <li v-for="v in prev_term"><a :href="term_admin_permalink(current_taxonomy, v.term_id, controller_slug)">{{v.name}} ({{v.slug}})</a></li>
        <li class="bold"><?php echo $data['name']; ?></li>
        <li v-for="v in next_term"><a :href="term_admin_permalink(current_taxonomy, v.term_id, controller_slug)">{{v.name}} ({{v.slug}})</a></li>
    </ul>
</div>
<?php


/*
 * nạp thêm custom view nếu có
 */
$theme_private_view = str_replace(VIEWS_PATH, VIEWS_CUSTOM_PATH, __FILE__);
include VIEWS_PATH . 'private_require_view.php';


//
$base_model->JSON_parse([
    'arr_custom_cloumn' => $arr_custom_cloumn,
    'prev_term' => $prev_term,
    'next_term' => $next_term,
]);

//
$base_model->JSON_echo([
    // mảng này sẽ in ra dưới dạng JSON hoặc number
    'data_term_id' => ($data['term_id'] != '' ? $data['term_id'] : 0),
], [
    // mảng này sẽ in ra dưới dạng string
    'set_parent' => $set_parent,
    'name_type' => $name_type,
    'current_taxonomy' => $taxonomy,
    'controller_slug' => $controller_slug,
    'preview_url' => $preview_url,
    'preview_offset_top' => $preview_offset_top,
]);

//
$base_model->adds_js([
    'admin/js/preview_url.js',
    'admin/js/term_add.js',
    // js riêng cho từng taxonomy (nếu có)
    'admin/js/' . $taxonomy . '_add.js',
]);

?>
<script>
    //
    WGR_vuejs('#oi_other_posts', {
        prev_term: prev_term,
        next_term: next_term,
    });
</script>