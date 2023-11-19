<?php

?>
<!-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet"> -->
<!-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" rel="stylesheet"> -->
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css"> -->
<link rel="stylesheet" href="./wp-includes/thirdparty/Nestable-2.0/style.css" />
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<div class="container container-edit-menu">
    <!-- menu template: không được gán class trực tiếp, tránh xung đột -->
    <ol class="dd-tmp-list hide-if-edit-menu">
        <li data-class="%dd-item%" data-id="%id%" data-name="%name%" data-content="%content%" data-img="%img%" data-slug="%slug%" data-target="%target%" data-rel="%rel%" data-icon="%icon%" data-css="%css%" data-custom-css="" data-new="0" data-deleted="0">
            <div data-class="%dd-handle%">%name%</div>
            <span data-class="%button-delete%" data-owner-id="%id%"> <i data-class="%fa-times%" aria-hidden="true"></i> </span> <span data-class="%button-edit%" data-owner-id="%id%"> <i data-class="%fa-pencil%" aria-hidden="true"></i> </span> %child_htm%
        </li>
    </ol>
    <!-- END menu template -->
    <div class="cf">
        <div class="lf f50 fullsize-if-mobile">
            <h3>Menu</h3>
            <div class="dd nestable hide-if-edit-menu">
                <ol class="dd-list">

                    <!--- Initial Menu Items --->

                    <!--- Item1 --->
                    <li class="dd-item" data-id="1" data-name="Item 1" data-slug="item-slug-1" data-new="0" data-deleted="0">
                        <div class="dd-handle">Item 1</div>
                        <span class="button-delete btn btn-default btn-xs pull-right" data-owner-id="1"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right" data-owner-id="1"> <i class="fa fa-pencil" aria-hidden="true"></i> </span>
                    </li>

                    <!--- Item2 --->
                    <li class="dd-item" data-id="2" data-name="Item 2" data-slug="item-slug-2" data-new="0" data-deleted="0">
                        <div class="dd-handle">Item 2</div>
                        <span class="button-delete btn btn-default btn-xs pull-right" data-owner-id="2"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right" data-owner-id="2"> <i class="fa fa-pencil" aria-hidden="true"></i> </span>
                    </li>

                    <!--- Item3 --->
                    <li class="dd-item" data-id="3" data-name="Item 3" data-slug="item-slug-3" data-new="0" data-deleted="0">
                        <div class="dd-handle">Item 3</div>
                        <span class="button-delete btn btn-default btn-xs pull-right" data-owner-id="3"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right" data-owner-id="3"> <i class="fa fa-pencil" aria-hidden="true"></i> </span>
                        <!--- Item3 children --->
                        <ol class="dd-list">
                            <!--- Item4 --->
                            <li class="dd-item" data-id="4" data-name="Item 4" data-slug="item-slug-4" data-new="0" data-deleted="0">
                                <div class="dd-handle">Item 4</div>
                                <span class="button-delete btn btn-default btn-xs pull-right" data-owner-id="4"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right" data-owner-id="4"> <i class="fa fa-pencil" aria-hidden="true"></i> </span>
                            </li>

                            <!--- Item5 --->
                            <li class="dd-item" data-id="5" data-name="Item 5" data-slug="item-slug-5" data-new="0" data-deleted="0">
                                <div class="dd-handle">Item 5</div>
                                <span class="button-delete btn btn-default btn-xs pull-right" data-owner-id="5"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right" data-owner-id="5"> <i class="fa fa-pencil" aria-hidden="true"></i> </span>
                            </li>
                        </ol>
                    </li>

                    <!--------------------------->

                </ol>
            </div>
            <br>
            <div onclick="return restore_json_menu_in_html_menu();" class="cur greencolor bold">* Khôi phục lại mã JSON cho menu từ HTML menu (dùng khi JSON menu bị lỗi mà HTML không lỗi).</div>
        </div>
        <div class="lf f50 fullsize-if-mobile menu-edit-input">
            <div class="left-menu-space">
                <!-- ADD menu -->
                <form class="form-inline" onSubmit="return get_json_add_menu(this);" id="menu-add">
                    <h3>Thêm menu</h3>
                    <div id="quick_add_menu" class="form-group">
                        <p>* Thêm nhanh menu. Chọn 1 trong các link có sẵn dưới đây sau đó bấm [Thêm mới]</p>
                        <?php

                        //
                        $quick_menu_list = $post_model->get_site_inlink($data['lang_key']);
                        //print_r($quick_menu_list);
                        //echo implode('', $quick_menu_list);

                        // chạy 1 vòng lặp -> lấy các loại menu ra để tạo select -> dễ lọc
                        foreach ($quick_menu_list as $k => $v) {
                            if (!isset($v[0]['type'])) {
                                $v[0]['type'] = '';
                            }
                        ?>
                            <div>
                                <select data-type="<?php echo $v[0]['type']; ?>" class="form-select">
                                    <!-- <option value="">[ Thêm nhanh menu ]</option> -->
                                    <option ng-repeat="v in quick_menu_list.<?php echo $k; ?>" ng-value="v.value" data-xoa-ng-disabled="v.selectable" ng-class="v.class">{{v.text}}</option>
                                </select>
                            </div>
                            <br>
                        <?php
                        }

                        ?>
                    </div>
                    <h3 id="currentEditName" style="display: none;">Chỉnh sửa: <span class="s14"></span></h3>
                    <div>
                        <div class="form-group">
                            <label for="addInputName">Tên menu</label>
                            <input type="text" class="form-control" data-set="name" id="addInputName" placeholder="Item name" aria-required="true" required>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputSlug">Đường dẫn</label>
                            <input type="text" class="form-control" data-set="slug" id="addInputSlug" placeholder="Item URL">
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputImg">Hình ảnh</label>
                            <input type="text" class="form-control" data-set="img" id="addInputImg" placeholder="Item image">
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputContent">Nội dung</label>
                            <input type="text" class="form-control" data-set="content" id="addInputContent" placeholder="Item content">
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputIcon">Font-awesome icon</label>
                            <input type="text" class="form-control" data-set="icon" id="addInputIcon" placeholder="Item icon">
                            <div class="text-center">
                                <a href="https://fontawesome.com/v4/icons/" rel="nofollow" class="bluecolor">https://fontawesome.com/v4/icons/</a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputTarget">Target</label>
                            <input type="text" class="form-control" data-set="target" id="addInputTarget" placeholder="Item target: _blank, _parent...">
                            <div class="text-center greencolor">_blank, _parent, _self, _top</div>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputRel">Rel</label>
                            <input type="text" class="form-control" data-set="rel" id="addInputRel" placeholder="Item rel: noreferrer, noopener...">
                            <div class="text-center greencolor">noreferrer, noopener, nofollow</div>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputCcc">Custom CSS</label>
                            <input type="text" class="form-control" data-set="css" id="addInputCcc" placeholder="Item css">
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-info" id="addButton2">
                            <span class="hide-for-edit-menu"><i class="fa fa-plus"></i> Thêm mới</span>
                            <span class="show-for-edit-menu" style="display: none;"><i class="fa fa-save"></i> Cập nhật</span>
                        </button>
                    </div>
                </form>
                <!-- ADD menu END -->
                <!-- EDIT menu -->
                <form class="hide-if-edit-menu d-none" onSubmit="return get_json_edit_menu(this);" id="menu-editor">
                    <div class="form-group">
                        <label for="editInputName">Tên menu</label>
                        <input type="text" class="form-control" data-set="name" id="editInputName" placeholder="Item name" aria-required="true" required>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputSlug">Đường dẫn</label>
                            <input type="text" class="form-control" data-set="slug" id="editInputSlug" placeholder="Item slug" aria-required="true" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editInputImg">Hình ảnh</label>
                        <input type="text" class="form-control" data-set="img" id="editInputImg" placeholder="Item image">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-info" id="editButton"><i class="fa fa-save"></i> Cập nhật</button>
                        <button type="button" onclick="$('#menu-editor').fadeOut();" class="btn btn-danger" id="editButton"><i class="fa fa-close"></i> Hủy bỏ</button>
                    </div>
                </form>
                <!-- EDIT menu END -->
            </div>
        </div>
    </div>
    <div class="output-container hide-if-edit-menu">
        <h2 class="text-center">Output:</h2>
        <form class="form">
            <textarea class="form-control" id="json-output" rows="5" style="width: 100%;"></textarea>
        </form>
    </div>
</div>
<br>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script> -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script> -->
<?php

//
$base_model->adds_js([
    'wp-admin/js/add_edit_menu_v2.js',
    'wp-includes/thirdparty/Nestable-2.0/jquery.nestable.js',
    'wp-includes/thirdparty/Nestable-2.0/jquery.nestable++.js',
    'wp-admin/js/add_edit_menu_v2_footer.js',
]);
