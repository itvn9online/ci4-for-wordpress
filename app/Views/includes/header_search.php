<form method="get" action="search">
    <div class="input-search form-group">
        <input type="search" name="s" class="form-control" value="" placeholder="<?php $lang_model->the_text('header_search_label', 'Tìm kiếm'); ?>" onClick="this.select();" aria-required="true" required>
    </div>
    <div class="input-group-btn">
        <button type="submit" class="btn btn-primary" aria-label="Search"><i class="fa fa-search"></i> <?php $lang_model->the_text('header_search_label', 'Tìm kiếm'); ?></button>
    </div>
    <div class="search-results">
        <ul class="search-list"></ul>
    </div>
</form>