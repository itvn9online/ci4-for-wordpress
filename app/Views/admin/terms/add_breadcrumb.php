<ul class="admin-breadcrumb">
    <li><a href="admin/<?php echo $controller_slug; ?>">Danh sách
            <?php echo $name_type; ?>
        </a></li>
    <li>
        <?php
        if ($data['term_id'] > 0) {
            echo $data['name'] . ' | ';
        ?>
            Chỉnh sửa
        <?php
        } else {
        ?>
            Thêm mới
        <?php
        }
        echo $name_type;
        ?>
    </li>
</ul>