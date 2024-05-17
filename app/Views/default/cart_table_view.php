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
            foreach ([
                // 'image',
                'image_medium',
                // 'image_medium_large',
                // 'image_webp',
                '_regular_price',
            ] as $k2) {
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

        ?>
            <tr>
                <td class="cart-image">
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
                <td class="cart-quantity">
                    <div class="buttons_added">
                        <input type="button" value="-" data-value="-1" data-id="<?php echo $v['ID']; ?>" class="minus">
                        <input type="number" name="cart_quantity[]" value="1" size="4" min="0" step="1" data-id="<?php echo $v['ID']; ?>" data-price="<?php echo $post_meta['_regular_price']; ?>" inputmode="numeric" autocomplete="off" class="form-control-xoa change-cart-quantity" />
                        <input type="button" value="+" data-value="1" data-id="<?php echo $v['ID']; ?>" class="plus">
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
                foreach ([
                    '_regular_price',
                ] as $k2) {
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