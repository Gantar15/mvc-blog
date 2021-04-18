
<section class="user_main column">
    <section class="user_header">
        <div class="user_icon_block">
            <img class="user_header_icon" src="/public/users_icons/<?=$userPageData['id']?>.png"/>
            <div class="name_block">
                <div class="flex">
                    <p class="user_name"><?=$userPageData['name']?></p>
                    <div class="notifications">
                        <p>Уведомления</p>
                        <img src="/public/imgs/notification.svg">
                    </div>
                </div>
            </div>
        </div>
        <div class="hd_flex">
            <div class="name_block">
                <div class="flex">
                    <p class="user_name"><?=$userPageData['name']?></p>
                    <div class="notifications">
                        <p>Уведомления</p>
                        <img src="/public/imgs/notification.svg">
                    </div>
                </div>
            </div>
            <p class="user_description">Lorem ipsum ntur, corporis cum debitis deleniti dicta dolore enim ex explicabo facilis id imbcaecati perferendis quas qui quibusdam recusandae reiciendis rem repellat saepe tenetur totam voluptas voluptatum.</p>
            <div class="subscribers_block">
                <div class="subscribe_button">Подписаться</div>
                <p class="subscribers_count">319 тыс. подписчиков</p>
            </div>
        </div>
    </section>
    <section class="user_activities_menu">
        <div class="item user_posts active">Публикации</div>
        <div class="item user_subscribes">Подписки</div>
        <div class="item about_author">Об авторе</div>
    </section>
    <section class="posts_block">
        <?php if (empty($posts)): ?>
            <div class="empty_posts_box">
                <p>У данного пользователя нет постов</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="blog_recording">
                    <a href="<?='/post/'.$post['id']?>" class="blog_img">
                        <img src="/public/uploaded_information/<?=$post["id"]?>.jpg">
                    </a>
                    <div class="main_post_content">
                        <div class="record_container">
                            <div class="blog_recording_block">
                                <div class="name_block">
                                    <a href="<?='/post/'.$post['id']?>" class="blog_name">
                                        <?=htmlspecialchars($post['name'], ENT_QUOTES|ENT_HTML5, 'UTF-8', true);?>
                                    </a>
                                    <div class="blog_description">
                                        <?=htmlspecialchars($post['description'], ENT_QUOTES|ENT_HTML5, 'UTF-8', true);?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="last_block">
                            <div class="last_block_info">
                                <div class="likes">
                                    <img src="/public/imgs/like.svg"/>
                                    <p><?=$post['likes'] . ' ' . $this->valuesFormatter($post['likes'], 'лайков', 'лайк', 'лайка')?></p>
                                </div>
                                <div class="views">
                                    <img src="/public/imgs/eye.svg"/>
                                    <p><?=$post['views'] . ' ' . $this->valuesFormatter($post['views'], 'просмотров', 'просмотр', 'просмотра')?></p>
                                </div>
                                <div class="share annotation_block" data-annotation-content = "поделиться">
                                    <img src="/public/imgs/share.svg"/>
                                </div>
                            </div>
                            <div class="blog_date annotation_block" data-annotation-content = "последнее изменение">
                                <img src="/public/imgs/clock.svg"/>
                                <p>
                                    <?php
                                    $postDateStr = $post['date_of_create'];
                                    if($post['date_of_last_edit']) {
                                        $postDateStr = $post['date_of_last_edit'];
                                    }
                                    $postDate = strtotime($postDateStr);

                                    $arr = [
                                        'января',
                                        'февраля',
                                        'марта',
                                        'апреля',
                                        'мая',
                                        'июня',
                                        'июля',
                                        'августа',
                                        'сентября',
                                        'октября',
                                        'ноября',
                                        'декабря'
                                    ];

                                    $month = $arr[date('n', $postDate)-1];
                                    echo date('j ', $postDate) . $month . date(' Y', $postDate);
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if(!empty($pagination)):?>
            <div class="buttons_block">
                <?=$pagination?>
            </div>
        <?php endif;?>
    </section>
</section>

<footer>
    <div class="footer_head">
        <div class="column">
            <div class="header_logo">
                <a href="/">
                    <img src="/public/imgs/header_logo_footer.png">
                </a>
            </div>
            <ul class="main_navigation">
                <li><a href="/">Главная</a></li>
                <li><a href="/about">О нас</a></li>
                <li><a href="/contact">Обратная связь</a></li>
                <li><a href="/categories">Категории</a></li>
            </ul>
            <div class="to_page_top" onclick="window.scrollTo({
                        top: 0,
                        left: 0,
                        behavior: 'smooth'
                    });"><span>&and;</span></div>
        </div>
    </div>
    <div class="footer_downtown">
        <div class="column pd_s">
            <article class="copyright">&copy;Егор Павловскииийи. 2020 БГТУ - шарага.</article>
            <article class="social">
                <a href="#"><img src="/public/imgs/twitter.svg" alt="twitter"></a>
                <a href="#"><img src="/public/imgs/instagram.svg" alt="instagram"></a>
                <a href="#"><img src="/public/imgs/youtube.svg" alt="youtube"></a>
                <a href="#"><img src="/public/imgs/facebook.svg" alt="facebook"></a>
            </article>
            <p>My mvc blog</p>
        </div>
    </div>
</footer>

<script type="module">
    import Annotations from "/public/scripts/annotations.js";
    let annotations = new Annotations('.annotation_block');
    annotations.dispatch();
</script>
<script type="module">
    import shareLink from "/public/scripts/share_link.js";
    shareLink('.blog_recording', '.share', '.blog_name');
</script>
<script type="module">
    import waveButtonDispatcher from "/public/scripts/wave_button.js";
    //Анимация при нажатии на пункт из панели пользователя
    const menuButtons = document.querySelectorAll('.user_activities_menu .item:not(.profile_submenu)');
    waveButtonDispatcher([...menuButtons]);
</script>