<?php

// Libraries
use App\Libraries\CommentType;

?>
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
                <div id="comment_ID<?php echo $v['comment_ID']; ?>" class="cf bypostauthor">
                    <div>
                        <span class="bold">#<?php echo $v['comment_ID']; ?></span> -
                        <?php echo $v['comment_author_email']; ?> (<?php echo $v['comment_author']; ?>) -
                        <?php echo $v['comment_rate']; ?> <i class="fa fa-star orgcolor"></i> -
                        <?php echo $v['comment_author_IP']; ?>
                    </div>
                    <div class="rf tools-add_comments">
                        <span onclick="return edit_comments_show(<?php echo $v['comment_ID']; ?>);" class="cur">Reply</span> |
                        <!-- <span onclick="return reply_comments_show(<?php echo $v['comment_ID']; ?>);" class="cur">Edit</span> | -->
                        <a href="sadmin/<?php echo $v['comment_type']; ?>s?comment_id=<?php echo $v['comment_ID']; ?>">Details</a> |
                        <?php

                        if ($v['comment_approved'] == CommentType::APPROVED) {
                        ?>
                            <a href="sadmin/<?php echo $controller_slug; ?>/unapprove_comments?id=<?php echo $v['comment_ID']; ?>" target="target_eb_iframe" class="orgcolor">Unapprove</a> |
                        <?php
                        } else {
                        ?>
                            <a href="sadmin/<?php echo $controller_slug; ?>/approve_comments?id=<?php echo $v['comment_ID']; ?>" target="target_eb_iframe" class="greencolor">Approve</a> |
                        <?php
                        }

                        ?>
                        <a href="sadmin/<?php echo $controller_slug; ?>/trash_comments?id=<?php echo $v['comment_ID']; ?>" onclick="return before_trash_comments();" target="target_eb_iframe" class="redcolor">Trash</a>
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
                    <div class="d-none">
                        <input type="number" name="data[comment_ID]" id="data_comment_ID" value="0" />
                        <input type="number" name="data[comment_parent]" id="data_comment_parent" value="0" />
                        <input type="number" name="data[comment_post_ID]" value="<?php echo $data['ID']; ?>" />
                    </div>
                    <div class="row left-menu-space">
                        <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <div class="p">
                                <input type="text" name="data[comment_author]" data-placeholder="<?php echo $session_data['display_name']; ?>" placeholder="Full name *" class="form-control" required aria-required="true" />
                            </div>
                        </div>
                        <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <div class="p">
                                <input type="email" name="data[comment_author_email]" data-placeholder="<?php echo $session_data['user_email']; ?>" placeholder="Email" class="form-control" />
                            </div>
                        </div>
                        <div class="col col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <div class="p">
                                <textarea placeholder="Comment content *" name="data[comment_content]" required aria-required="true" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <div class="p">
                                <select name="data[comment_rate]" class="form-select">
                                    <?php

                                    // 
                                    for ($i = 5; $i > 0; $i--) {
                                    ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> star</option>
                                    <?php
                                    }

                                    ?>
                                </select>
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