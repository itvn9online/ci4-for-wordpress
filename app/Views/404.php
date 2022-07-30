<?php

$base_model->add_css( 'css/404.css', [
    'cdn' => CDN_BASE_URL,
] );

?>
<div class="text-center error-page">
    <h1 data-h1="404">404</h1>
    <p class="not-found" style="-webkit-text-stroke: #ccc 0.1px;" data-p="NOT FOUND">NOT FOUND</p>
    <div class="msg_404 redcolor s14"><?php echo $msg_404; ?></div>
</div>
<div id="particles-js" class="text-center"> <a href="<?php echo DYNAMIC_BASE_URL; ?>">Về trang chủ</a> </div>
