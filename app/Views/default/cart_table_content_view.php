<?php

// 
use App\Libraries\UsersType;

// 
$id_author = '';
if (!empty($data)) {
    // print_r($data);
?>
    <table class="cart-table">
        <thead>
            <tr>
                <th class="product-name" colspan="2">Product</th>
                <th class="product-price">Price</th>
                <th class="product-quantity">Quantity</th>
                <th class="product-subtotal"><?php $lang_model->the_text('cart_sidebar_subtotal', 'Subtotal'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php

            //
            foreach ($data as $v) {
                $post_meta = $v['post_meta'];
                foreach (
                    [
                        // 'image',
                        'image_medium',
                        // 'image_medium_large',
                        // 'image_webp',
                        '_regular_price',
                    ] as $k2
                ) {
                    if (!isset($post_meta[$k2])) {
                        $post_meta[$k2] = '';
                    }
                }

                //
                $post_meta['_regular_price'] = str_replace(',', '', $post_meta['_regular_price']);

                //
                if (!isset($post_meta['image_medium']) || $post_meta['image_medium'] == '') {
                    if (isset($post_meta['image']) && $post_meta['image'] != '') {
                        $post_meta['image_medium'] = $post_meta['image'];
                    }
                }

                // nếu là sàn thương mại điện tử -> hiển thị thêm thông tin của shop
                if ($id_author == '' && THIS_IS_E_COMMERCE_SITE == 'yes') {
                    // gán id_author để pha sau ko hiển thị if này nữa
                    $id_author = $v['post_author'];
                    // echo $id_author;
                    // die(__FILE__ . ':' . __LINE__);

                    // lấy thông tin author
                    $author_data = $user_model->get_user_by_id($v['post_author'], [
                        // 'member_type' => UsersType::MEMBER,
                    ], [
                        'where_in' => array(
                            'member_type' => array(
                                UsersType::MEMBER,
                                UsersType::ADMIN,
                            )
                        ),
                    ]);
                    // print_r($author_data);

                    // không tìm thấy thì hiển thị thông tin đang khóa
                    if (empty($author_data)) {
            ?>
                        <tr>
                            <td colspan="5" class="cart-vendor-locked">Account info not found #<?php echo $v['post_author']; ?></td>
                        </tr>
                    <?php
                        break;
                    }

                    // xác định tên hiển thị
                    if (!empty($author_data['display_name'])) {
                        $displayName = $author_data['display_name'];
                    } else {
                        // mặc định sẽ hiển thị username
                        $displayName = $author_data['user_login'];
                    }

                    ?>
                    <tr>
                        <td class="text-center">
                            <i class="fa fa-check-square-o cart-vendor-checked s18"></i>
                        </td>
                        <td colspan="4" class="cart-vendor-name s18">
                            <span data-id="<?php echo $id_author; ?>" class="cart-vendor-id carted-vendor-id"><?php echo $displayName; ?></span> <i class="fa fa-shopping-basket"></i>
                        </td>
                    </tr>
                    <?php

                    // xem tài khoản này có đang bị khóa hay không
                    if ($author_data['user_status'] != UsersType::FOR_DEFAULT) {
                    ?>
                        <tr>
                            <td colspan="5" class="cart-vendor-locked">Account is temporarily locked!</td>
                        </tr>
                <?php
                        break;
                    }
                }

                ?>
                <tr data-id="<?php echo $v['ID']; ?>">
                    <td width="110" class="cart-image">
                        <div class="global-a-posi">
                            <a href="<?php echo $v['post_permalink']; ?>">&nbsp;</a>
                            <div data-img="<?php echo $post_meta['image_medium']; ?>" data-size="1" class="ti-le-global eb-blog-avt each-to-bgimg">&nbsp;</div>
                        </div>
                    </td>
                    <td>
                        <h3 class="cart-post_title"><a href="<?php echo $v['post_permalink']; ?>"><?php echo $v['post_title']; ?></a></h3>
                        <div class="cart-mobile-regular_price">
                            <div class="d-none">
                                <span class="ebe-currency-format"><?php echo $post_meta['_regular_price']; ?></span>
                            </div>
                            <div>
                                <span data-id="<?php echo $v['ID']; ?>" class="ebe-currency change-cart-regular_price"></span>
                            </div>
                        </div>
                        <p class="remove-from-cart">
                            <span onclick="return remove_from_cart(<?php echo $v['ID']; ?>);" class="cur redcolor">[Remove <i class="fa fa-trash"></i>]</span>
                        </p>
                        <input type="hidden" name="cart_id[]" value="<?php echo $v['ID']; ?>" autocomplete="off" readonly aria-required="true" required />
                    </td>
                    <td class="product-price cart-regular_price">
                        <span class="ebe-currency-format"><?php echo $post_meta['_regular_price']; ?></span>
                    </td>
                    <td data-id="<?php echo $v['ID']; ?>" class="cart-quantity">
                        <div data-id="<?php echo $v['ID']; ?>" class="buttons_added">
                            <button type="button" data-value="-1" data-id="<?php echo $v['ID']; ?>" class="minus">-</button>
                            <input type="number" name="cart_quantity[]" value="1" size="4" min="0" step="1" data-id="<?php echo $v['ID']; ?>" data-price="<?php echo $post_meta['_regular_price']; ?>" inputmode="numeric" autocomplete="off" class="form-control-xoa change-cart-quantity" />
                            <button type="button" data-value="1" data-id="<?php echo $v['ID']; ?>" class="plus">+</button>
                        </div>
                    </td>
                    <td class="product-subtotal cart-regular_price">
                        <span data-id="<?php echo $v['ID']; ?>" class="ebe-currency change-cart-regular_price"></span>
                    </td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
    <br />
    <br />
<?php
}

// sản phẩm của vendor khác (nếu có)
if (isset($other_data) && !empty($other_data)) {
    // print_r($other_data);
    // echo 'id_author: ' . $id_author . '<br>' . PHP_EOL;

?>
    <h3 class="text-center"><?php $lang_model->the_text('cart_prod_another_supplier', 'Product from another supplier'); ?></h3>
    <table class="cart-table">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th class="product-name" colspan="2">Product</th>
                <th class="product-price">Price</th>
            </tr>
        </thead>
        <tbody>
            <?php

            // 
            foreach ($other_data as $v) {
                $post_meta = $v['post_meta'];
                foreach (
                    [
                        // 'image',
                        'image_medium',
                        // 'image_medium_large',
                        // 'image_webp',
                        '_regular_price',
                    ] as $k2
                ) {
                    if (!isset($post_meta[$k2])) {
                        $post_meta[$k2] = '';
                    }
                }

                //
                $post_meta['_regular_price'] = str_replace(',', '', $post_meta['_regular_price']);

                //
                if (!isset($post_meta['image_medium']) || $post_meta['image_medium'] == '') {
                    if (isset($post_meta['image']) && $post_meta['image'] != '') {
                        $post_meta['image_medium'] = $post_meta['image'];
                    }
                }

                // 
                $cart_vendor_link = '';

                // hiển thị thông tin author
                if ($id_author != $v['post_author'] && THIS_IS_E_COMMERCE_SITE == 'yes') {
                    // lấy thông tin author
                    $author_data = $user_model->get_user_by_id($v['post_author'], [
                        'member_type' => UsersType::MEMBER,
                    ]);
                    // print_r($author_data);

                    // bỏ qua nếu ko tìm thấy hoặc tài khoản đã khóa
                    if (empty($author_data) || $author_data['user_status'] != UsersType::FOR_DEFAULT) {
                        continue;
                    }

                    // 
                    $id_author = $v['post_author'];

                    // xác định tên hiển thị
                    if (!empty($author_data['display_name'])) {
                        $displayName = $author_data['display_name'];
                    } else {
                        // mặc định sẽ hiển thị username
                        $displayName = $author_data['user_login'];
                    }

                    // 
                    $cart_vendor_link = 'actions/cart?shop_id=' . $id_author;

            ?>
                    <tr>
                        <td class="text-center">
                            <a href="<?php echo $cart_vendor_link; ?>" class="cart-vendor-check"><i class="fa fa-square-o s18"></i></a>
                        </td>
                        <td colspan="3" class="cart-vendor-name">
                            <a href="<?php echo $cart_vendor_link; ?>"><span data-id="<?php echo $id_author; ?>" class="cart-vendor-id"><?php echo $displayName; ?></span> <i class="fa fa-shopping-cart"></i></a>
                        </td>
                    </tr>
                <?php
                }

                // hiển thị thông tin sản phẩm
                ?>
                <tr>
                    <td width="110" class="text-center">
                        <a href="<?php echo $cart_vendor_link; ?>" class="cart-vendor-check"><i class="fa fa-square-o s18"></i></a>
                    </td>
                    <td width="90" class="cart-image cart-small-image">
                        <div class="global-a-posi">
                            <a href="<?php echo $v['post_permalink']; ?>">&nbsp;</a>
                            <div data-img="<?php echo $post_meta['image_medium']; ?>" data-size="1" class="ti-le-global eb-blog-avt each-to-bgimg">&nbsp;</div>
                        </div>
                    </td>
                    <td>
                        <h3 class="cart-post_title"><a href="<?php echo $v['post_permalink']; ?>"><?php echo $v['post_title']; ?></a></h3>
                        <div class="cart-mobile-regular_price">
                            <div class="d-none">
                                <span class="ebe-currency-format"><?php echo $post_meta['_regular_price']; ?></span>
                            </div>
                        </div>
                        <p class="remove-from-cart">
                            <span onclick="return remove_from_cart(<?php echo $v['ID']; ?>);" class="cur redcolor">[Remove <i class="fa fa-trash"></i>]</span>
                        </p>
                    </td>
                    <td class="product-price cart-regular_price">
                        <span class="ebe-currency-format"><?php echo $post_meta['_regular_price']; ?></span>
                    </td>
                </tr>
            <?php
            }

            ?>
        </tbody>
    </table>
<?php
}

?>
<div class="cart-hidden-table d-none">
    <table class="cart-table">
        <thead>
            <tr class="upper">
                <th class="product-name">Product</th>
                <th class="product-subtotal"><?php $lang_model->the_text('cart_sidebar_subtotal', 'Subtotal'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php

            //
            foreach ($data as $v) {
                $post_meta = $v['post_meta'];
                foreach (
                    [
                        '_regular_price',
                    ] as $k2
                ) {
                    if (!isset($post_meta[$k2])) {
                        $post_meta[$k2] = '';
                    }
                }

                //
                $post_meta['_regular_price'] = str_replace(',', '', $post_meta['_regular_price']);
            ?>
                <tr>
                    <td>
                        <?php echo $v['post_title']; ?> x <strong data-id="<?php echo $v['ID']; ?>" class="change-product-quantity">1</strong>
                    </td>
                    <td class="cart-regular_price">
                        <span data-id="<?php echo $v['ID']; ?>" class="ebe-currency change-cart-regular_price"></span>
                    </td>
                </tr>
            <?php
            }

            ?>
        </tbody>
    </table>
</div>