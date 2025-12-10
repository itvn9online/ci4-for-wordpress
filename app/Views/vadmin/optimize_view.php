<ul class="admin-breadcrumb">
    <li>Optimize code</li>
</ul>
<p>View file: <strong><?php echo basename(__FILE__); ?></strong></p>
<p>Total time: <strong><?php echo $total_time; ?></strong></p>
<p>
    <button type="button" class="btn btn-primary start-compiler-closure" onclick="return start_closure_compiler_echbay();">Bắt đầu nén file</button>
</p>
<?php

//
echo $data;

//
$base_model->add_js('wp-admin/js/optimizes.js');
