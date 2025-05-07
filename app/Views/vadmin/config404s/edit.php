<?php

// print_r($data);

// css riêng cho từng config (nếu có)
$base_model->add_css('wp-admin/css/sonfig404s.css');

?>
<div id="app">
    <ul class="admin-breadcrumb">
        <li><a href="sadmin/config404s">404 Monitor</a></li>
    </ul>
    <div class="cf admin-search-form">
        <div class="lf f62">
            <form name="frm_admin_search_controller" action="./sadmin/config404s" method="get">
                <div class="cf">
                    <div class="lf f80">
                        <input name="s" value="<?php echo $by_keyword; ?>" placeholder="Tìm kiếm URL" autofocus>
                    </div>
                    <div class="lf f20">
                        <button type="submit" class="btn-success"><i class="fa fa-search"></i> Tìm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <br>
    <div class="widget-content nopadding config-main">
        <table class="table table-bordered table-striped with-check table-list eb-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>URL</th>
                    <th>Name</th>
                    <th>Redirect</th>
                    <th>Target</th>
                    <th>IP</th>
                    <th>Updated</th>
                    <th>Referer</th>
                </tr>
            </thead>
            <tbody id="admin_main_list">
                <tr :data-id="v.ID" v-for="v in data">
                    <td>{{v.link_id}}</td>
                    <td>{{v.link_url}}</td>
                    <td>
                        <a :href="'//' + v.link_url + v.link_name" target="_blank" class="small">{{v.link_name}}</a>
                    </td>
                    <td>
                        <input type="text" :value="v.link_image" :title="v.link_image" :data-id="v.link_id" class="change-update-link-redirect small" />
                    </td>
                    <td>{{v.link_target}}</td>
                    <td>
                        <input type="text" :value="v.link_rel" :value="v.link_rel" class="links-link_rel small" readonly />
                    </td>
                    <td class="small">{{v.link_updated}}</td>
                    <td>
                        <input v-if="v.link_notes != ''" type="text" :value="v.link_notes" :title="v.link_notes" class="links-link_notes small" readonly />
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>
<?php

//
$base_model->JSON_parse(
    [
        'vue_data' => $data,
    ]
);

// 
$base_model->add_js('wp-admin/js/config404s.js');
