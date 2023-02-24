<?php

//
use App\Libraries\LanguageCost;

//
$current_language = LanguageCost::typeList($lang_key);

//
include ADMIN_ROOT_VIEWS . 'posts/add_breadcrumb.php';

?>
<div class="s15">
    <p class="redcolor s20"><i class="fa fa-warning"></i> Ngôn ngữ hiển thị không trùng khớp!</p>
    <p>Website đang hiển thị bằng ngôn ngữ: <b><?php echo $current_language; ?></b></p>
    <p><?php echo $name_type; ?> này đang được thiết lập hiển thị trên ngôn ngữ: <b><?php echo $post_lang; ?></b></p>
    <p>Nếu bạn muốn tạo phiên bản với ngôn ngữ <b><?php echo $current_language; ?></b> cho <?php echo $name_type; ?> này, hãy <a href="<?php echo $post_model->get_admin_permalink($data['post_type'], $data['ID'], $controller_slug); ?>&lang_duplicate=1" class="btn btn-primary">bấm vào đây</a> và chờ trong giây lát... Hệ thống sẽ thực hiện nhân bản bản ghi này, sau đó bạn sửa lại nội dung cho phù hợp với ngôn ngữ cần hiển thị.</p>
</div>