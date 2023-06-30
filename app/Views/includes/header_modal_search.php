<!-- search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="get" action="search">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel"><i class="fa fa-search"></i> <?php $lang_model->the_text('header_search_label', 'Tìm kiếm'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="search" name="s" class="form-control" value="" placeholder="<?php $lang_model->the_text('header_search_label', 'Tìm kiếm'); ?>" onClick="this.select();" aria-required="true" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close"><?php $lang_model->the_text('header_search_close', 'Đóng'); ?></button>
                    <button type="submit" class="btn btn-primary" aria-label="Search"><i class="fa fa-search"></i> <?php $lang_model->the_text('header_search_label', 'Tìm kiếm'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>