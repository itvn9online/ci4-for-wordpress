<div id="breadcrumb-top1">
    <div class="thread-details-tohome">
        <div class="w90">
            <ul class="cf" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                <li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem"><a href="./"
                        itemprop="item" title="Trang chủ" class="breadcrumb-home"><i class="fa fa-home"></i> <span
                            itemprop="name">Trang chủ</span></a>
                    <meta itemprop="position" content="1">
                </li>
                <?php
                echo implode(' ', $breadcrumb);
                ?>
            </ul>
        </div>
    </div>
</div>