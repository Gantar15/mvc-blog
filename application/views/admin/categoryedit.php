<div class="posts">
    <div class="main_header_block">
        <div class="title">
            <p>Изменение категории</p>
            <p>здесь вы можете изменить категорию</p>
        </div>
        <div class="pages_way">
            <a href="/admin/main">
                <img src="/public/imgs/home.png"/>
                <span>Главная</span>
            </a>
            <div class="arrow-right">&gt;</div>
            <a href="/admin/categories/1">
                <span>Категории</span>
            </a>
            <div class="arrow-right">&gt;</div>
            <a href="/admin/categoryedit/<?=$category['id']?>">
                <span>Изменение</span>
            </a>
        </div>
    </div>
    <section class="posts_add group_box active_box">
        <div class="add_category">
            <div class="posts_add_header group_box_header">
                <p>Добавить категорию</p>
                <div class="controllers">
                    <div class="trey">&ndash;</div>
                    <div class="close">&times;</div>
                </div>
            </div>
            <div class="general_form_message">
                <p></p>
                <div onclick="document.querySelector('.general_form_message').classList.remove('active')">&times;</div>
            </div>
            <form method="post" action="">
                <div class="add_category_flex">
                    <div class="add_info">
                        <div>
                            <label for="name">
                                <div></div><p>Название категории</p>
                            </label>
                            <input type="text" id="name" name="name" placeholder="Введите название" value="<?=$category['name']?>">
                        </div>
                        <div>
                            <label for="description">
                                <div></div><p>Описание категории</p>
                            </label>
                            <textarea type="text" id="description" name="description" placeholder="Опишите категорию"><?=$category['description']?></textarea>
                        </div>
                    </div>
                    <div class="second_half">
                        <div class="add_image">
                            <input name="icon" type="file">
                        </div>
                    </div>
                </div>
                <div class="submit_items_block">
                    <input type="submit" value="Отправить &#10004;">
                    <a href="/admin/categoryedit/<?=$category['id']?>"><input type="button" value="Сбросить &#10006;"></a>
                </div>
            </form>
        </div>
    </section>
</div>
