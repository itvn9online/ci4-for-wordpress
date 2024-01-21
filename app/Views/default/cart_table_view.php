<table class="cart-table">
    <thead>
        <tr>
            <th class="product-name" colspan="2">Product</th>
            <th class="product-price">Price</th>
            <th class="product-quantity">Quantity</th>
            <th class="product-subtotal">Subtotal</th>
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
                    <p class="remove-from-cart">
                        <span onclick="return remove_from_cart(<?php echo $v['ID']; ?>);" class="cur">[Remove <i class="fa fa-remove"></i>]</span>
                    </p>
                    <input type="hidden" name="cart_id[]" value="<?php echo $v['ID']; ?>" autocomplete="off" readonly aria-required="true" required />
                </td>
                <td class="cart-regular_price">
                    <span class="ebe-currency-format"><?php echo $post_meta['_regular_price']; ?></span>
                </td>
                <td class="cart-quantity">
                    <input type="number" name="cart_quantity[]" value="1" min="0" data-id="<?php echo $v['ID']; ?>" data-price="<?php echo $post_meta['_regular_price']; ?>" class="form-control change-cart-quantity" />
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