<?php

//
//$base_model = new\ App\ Models\ Base();
//$post_model = new\ App\ Models\ PostAdmin();

?>
<!-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet"> -->
<!-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" rel="stylesheet"> -->
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css"> -->
<link rel="stylesheet" href="./thirdparty/Nestable-master/style.css" />
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries --> 
<!-- WARNING: Respond.js doesn't work if you view the page via file:// --> 
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<div class="container container-edit-menu">
    <ol class="dd-tmp-list hide-if-edit-menu">
        <li class="dd-item" data-id="%id%" data-name="%name%" data-slug="%slug%" data-new="0" data-deleted="0">
            <div class="dd-handle">%newText%</div>
            <span class="button-delete btn btn-default btn-xs pull-right"
                      data-owner-id="%id%"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right"
                      data-owner-id="%id%"> <i class="fa fa-pencil" aria-hidden="true"></i> </span> %child_htm%</li>
    </ol>
    <div class="cf">
        <div class="lf f50">
            <h3>Menu</h3>
            <div class="dd nestable hide-if-edit-menu">
                <ol class="dd-list">
                    
                    <!--- Initial Menu Items ---> 
                    
                    <!--- Item1 --->
                    <li class="dd-item" data-id="1" data-name="Item 1" data-slug="item-slug-1" data-new="0" data-deleted="0">
                        <div class="dd-handle">Item 1</div>
                        <span class="button-delete btn btn-default btn-xs pull-right"
                      data-owner-id="1"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right"
                      data-owner-id="1"> <i class="fa fa-pencil" aria-hidden="true"></i> </span> </li>
                    
                    <!--- Item2 --->
                    <li class="dd-item" data-id="2" data-name="Item 2" data-slug="item-slug-2" data-new="0" data-deleted="0">
                        <div class="dd-handle">Item 2</div>
                        <span class="button-delete btn btn-default btn-xs pull-right"
                      data-owner-id="2"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right"
                      data-owner-id="2"> <i class="fa fa-pencil" aria-hidden="true"></i> </span> </li>
                    
                    <!--- Item3 --->
                    <li class="dd-item" data-id="3" data-name="Item 3" data-slug="item-slug-3" data-new="0" data-deleted="0">
                        <div class="dd-handle">Item 3</div>
                        <span class="button-delete btn btn-default btn-xs pull-right"
                      data-owner-id="3"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right"
                      data-owner-id="3"> <i class="fa fa-pencil" aria-hidden="true"></i> </span> 
                        <!--- Item3 children --->
                        <ol class="dd-list">
                            <!--- Item4 --->
                            <li class="dd-item" data-id="4" data-name="Item 4" data-slug="item-slug-4" data-new="0" data-deleted="0">
                                <div class="dd-handle">Item 4</div>
                                <span class="button-delete btn btn-default btn-xs pull-right"
                          data-owner-id="4"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right"
                          data-owner-id="4"> <i class="fa fa-pencil" aria-hidden="true"></i> </span> </li>
                            
                            <!--- Item5 --->
                            <li class="dd-item" data-id="5" data-name="Item 5" data-slug="item-slug-5" data-new="0" data-deleted="0">
                                <div class="dd-handle">Item 5</div>
                                <span class="button-delete btn btn-default btn-xs pull-right"
                          data-owner-id="5"> <i class="fa fa-times-circle-o" aria-hidden="true"></i> </span> <span class="button-edit btn btn-default btn-xs pull-right"
                          data-owner-id="5"> <i class="fa fa-pencil" aria-hidden="true"></i> </span> </li>
                        </ol>
                    </li>
                    
                    <!--------------------------->
                    
                </ol>
            </div>
        </div>
        <div class="lf f50 menu-edit-input">
            <div class="left-menu-space">
                <!-- ADD menu -->
                <form class="form-inline" onSubmit="return get_json_code_menu(this);" id="menu-add">
                    <h3>Th??m menu</h3>
                    <div>
                        <div class="form-group">
                            <label for="addInputName">Th??m nhanh</label>
                            <select id="quick_add_menu">
                                <option value="">[ Th??m nhanh menu ]</option>
                                <?php

                                $quick_menu_list = $post_model->quick_add_menu();
                                //print_r( $quick_menu_list );
                                //echo implode( '', $quick_menu_list );

                                ?>
                                <option ng-repeat="v in quick_menu_list" ng-value="v.value" ng-disabled="v.selectable" ng-class="v.class">{{v.text}}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputName">T??n menu</label>
                            <input type="text" class="form-control" id="addInputName" placeholder="Item name" required>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputSlug">???????ng d???n</label>
                            <input type="text" class="form-control" id="addInputSlug" placeholder="item-slug" required>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-info" id="addButton"><i class="fa fa-plus"></i> Th??m m???i</button>
                    </div>
                </form>
                <!-- ADD menu END -->
                <!-- EDIT menu -->
                <form class="hide-if-edit-menu" onSubmit="return get_json_code_menu(this);" id="menu-editor">
                    <h3>Ch???nh s???a: <span id="currentEditName"></span></h3>
                    <div class="form-group">
                        <label for="addInputName">T??n menu</label>
                        <input type="text" class="form-control" id="editInputName" placeholder="Item name" required>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="addInputSlug">???????ng d???n</label>
                            <input type="text" class="form-control" id="editInputSlug" placeholder="item-slug">
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-info" id="editButton"><i class="fa fa-save"></i> C???p nh???t</button>
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

$base_model->add_js( 'admin/js/add_edit_menu_v2.js' );

?>
<script src="./thirdparty/Nestable-master/jquery.nestable.js"></script> 
<script src="./thirdparty/Nestable-master/jquery.nestable++.js"></script>
<?php

$base_model->add_js( 'admin/js/add_edit_menu_v2_footer.js' );
