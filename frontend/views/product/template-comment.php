<?php if(!empty($product['review_info'])){ ?>
    <section class="comment">
        <h2>Đánh giá</h2>
        <div class="comment_list">
            <?php foreach($product['review_info'] as $row) { ?>
                <div class="comment_item">
                    <img class="comment_avatar" src="<?= !empty($row['avatar']) ? $row['avatar'] : '/images/icon/user-icon.svg' ?>" alt="">
                    <div class="comment_group_right">
                        <div class="user_name flex-item-center">
                            <p><?= $row['fullname'] ?></p>
                            <span>•</span>
                            <span><?= $row['date_review'] ?></span>
                        </div>
                        <p><?= $row['content'] ?></p>
                        <?php if(!empty($row['video_image'])){  ?>
                            <div class="video_image_comment slider-comment-nav">
                                <?php 
                                    foreach($row['video_image'] as $link){ 
                                        $is_video = false;
                                        $element = '<div class="item_slide slide_nav"><img class="img_slide_nav" src="'. $link .'"></div>';
                                        if(strpos($link, 'mp4') !== false)
                                            $element = '<div class="item_slide slide_nav position-relative">
                                                            <video class="img_slide_nav" width="640" height="360">
                                                                <source src="'. $link .'" type="video/mp4">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                            <div class="icon_play flex-center"><img src="/images/icon/play.svg"></div>
                                                        </div>';
                                ?>
                                    <?= $element ?>
                                <?php } ?>
                            </div>
                                <div class="video_image_comment slider-comment-for hide">
                                <?php 
                                    foreach($row['video_image'] as $link){ 
                                        $is_video = false;
                                        $element = '<div class="item_slide item_for"><img class="" src="'. $link .'"></div>';
                                        if(strpos($link, 'mp4') !== false)
                                            $element = '<div class="item_slide item_for">
                                                            <video class="" width="640" height="360" controls>
                                                                <source src="'. $link .'" type="video/mp4">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        </div>';
                                ?>
                                    <?= $element ?>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="more_comment">
            <button product-id="<?= $_GET['id'] ?>" class="see_more_comment">Xem thêm</button>
        </div>
    </section>
<?php } ?>