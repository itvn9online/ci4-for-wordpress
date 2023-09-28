<?php

/**
 * File này chỉ để giả lập wordpress, cho mấy thanh niên thích quậy phá nhìn vào ban đầu tưởng là wordpress -> dể chọc phá theo hướng đấy -> faild
 * Truy cập vào đường dẫn admin thì redirect sang trang login như đúng rồi
 **/

die(header('Location: https://' . $_SERVER['HTTP_HOST'] . '/wp-login.php?redirect_to=' . urlencode('https://' . $_SERVER['HTTP_HOST'] . '/wp-admin/') . '&reauth=1'));
