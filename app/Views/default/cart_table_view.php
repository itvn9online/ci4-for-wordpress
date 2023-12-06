<table class="cart-table">
    <tr>
        <th>Product image</th>
        <th>Product name</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Total</th>
    </tr>
    <?php

    //
    // $sub_total = 0;
    // $sub_price_total = 0;

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
        // $post_meta['_regular_price'] *= 1;
        // $sub_price_total += $post_meta['_regular_price'];
        // $sub_total += 1;

    ?>
        <tr>
            <td class="cart-image">
                <div>
                    <div data-img="<?php echo $post_meta['image_medium']; ?>" data-size="1" class="ti-le-global eb-blog-avt each-to-bgimg">&nbsp;</div>
                </div>
            </td>
            <td>
                <h3 class="cart-post_title"><a href="<?php echo $v['post_permalink']; ?>"><?php echo $v['post_title']; ?></a></h3>
                <p class="remove-from-cart">Remove <i class="fa fa-trash"></i></p>
            </td>
            <td class="cart-regular_price">
                <span class="ebe-currency"><?php echo $post_meta['_regular_price']; ?></span>
            </td>
            <td class="cart-quantity">
                <input type="number" value="1" min="0" data-id="<?php echo $v['ID']; ?>" data-price="<?php echo $post_meta['_regular_price']; ?>" class="form-control change-cart-quantity" />
            </td>
            <td class="cart-regular_price">
                <span data-id="<?php echo $v['ID']; ?>" class="ebe-currency change-cart-regular_price"></span>
            </td>
        </tr>
    <?php
    }

    ?>
</table>
<br>
<div class="text-right cart-sub-total">Sub-total (<span class="total-cart-quantity"></span> item): <span class="ebe-currency total-cart-regular_price"></span></div>
<br>