<div id="commentsdiv" class="w99">
    <div class="postbox">
        <div class="postbox-header">
            <h3 class="ui-sortable-handle">Comments</h3>
        </div>
        <p><button type="button" onclick="return add_comments_show();" class="btn btn-light">Add Comment</button></p>
        <div class="post-comments-list">
            <?php
            foreach ($data_comments as $v) {
            ?>
                <div class="cf bypostauthor">
                    <div>
                        <span class="bold"><?php echo $v['comment_author']; ?></span> -
                        <?php echo $v['comment_author_email']; ?> -
                        <?php echo $v['comment_author_IP']; ?>
                    </div>
                    <div class="rf tools-add_comments">
                        <span onclick="return add_comments_show();" class="cur">Reply</span> |
                        <span onclick="return add_comments_show();" class="cur">Edit</span> |
                        <a href="sadmin/<?php echo $controller_slug; ?>/unapprove_comments?id=<?php echo $v['comment_ID']; ?>" target="target_eb_iframe">Unapprove</a> |
                        <a href="sadmin/<?php echo $controller_slug; ?>/trash_comments?id=<?php echo $v['comment_ID']; ?>" target="target_eb_iframe">Trash</a>
                    </div>
                    <div><i class="fa fa-quote-left"></i> <?php echo $v['comment_content']; ?></div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="AddCommentsModal" tabindex="-1" aria-labelledby="AddCommentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="sadmin/<?php echo $controller_slug; ?>/add_comments" method="post" accept-charset="utf-8" target="target_eb_iframe">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="AddCommentsModalLabel">Add New Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="number" name="data[comment_post_ID]" value="<?php echo $data['ID']; ?>" class="d-none" />
                    <div class="row">
                        <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <div class="p">
                                <input type="text" name="data[comment_author]" data-placeholder="<?php echo $session_data['display_name']; ?>" placeholder="Full name" class="form-control" />
                            </div>
                        </div>
                        <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <div class="p">
                                <input type="text" name="data[comment_author_email]" data-placeholder="<?php echo $session_data['user_email']; ?>" placeholder="Email" class="form-control" />
                            </div>
                        </div>
                        <div class="col col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <div class="p">
                                <textarea placeholder="Comment content" name="data[comment_content]" required aria-required="true" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary">Add Comment</button>
                </div>
            </div>
        </form>
    </div>
</div>