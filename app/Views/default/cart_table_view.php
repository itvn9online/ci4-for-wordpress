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
    foreach ($data as $v) {
        $post_meta = $v['post_meta'];
        foreach ([
            'image',
            'image_medium_large',
            'image_webp',
            '_regular_price',
        ] as $k2) {
            if (!isset($post_meta[$k2])) {
                $post_meta[$k2] = '';
            }
        }

        //
        $post_meta['_regular_price'] = str_replace(',', '', $post_meta['_regular_price']);
        $post_meta['_regular_price'] *= 1;
        $sub_total += $post_meta['_regular_price'];

    ?>
        <tr>
            <td class="cart-image">
                <div>
                    <div data-img="<?php echo $post_meta['image']; ?>" data-large-img="<?php echo $post_meta['image_medium_large']; ?>" data-webp="<?php echo $post_meta['image_webp']; ?>" data-size="1" class="ti-le-global eb-blog-avt each-to-bgimg">&nbsp;</div>
                </div>
            </td>
            <td>
                <h3 class="cart-post_title"><a href="<?php echo $v['post_permalink']; ?>"><?php echo $v['post_title']; ?></a></h3>
                <p class="remove-from-cart">Remove</p>
            </td>
            <td class="cart-regular_price">
                <span class="ebe-currency"><?php echo $post_meta['_regular_price']; ?></span>
            </td>
            <td class="cart-quantity">
                <input type="number" value="1" min="0" class="form-control" />
            </td>
            <td class="cart-regular_price">
                <span class="ebe-currency"><?php echo $post_meta['_regular_price']; ?></span>
            </td>
        </tr>
    <?php
    }

    ?>
</table>
<br>