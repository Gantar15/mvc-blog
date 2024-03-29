<?php

namespace application\controllers;

use application\core\Controller;
use application\lib\Pagination;
use application\models\Account;
use application\models\Admin;
use application\core\View;

class MainController extends Controller {

    public $account;

    public function __construct($route)
    {
        $this->account = new Account();
        parent::__construct($route);
    }

    public function indexAction(){
        //Количество отображаемых на странице постов за раз
        $limit = 3;
        $pagination = new Pagination($this->route, $this->model->getNewestPostsCount(), $limit);
        if(!isset($this->route['page'])){
            $this->route['page'] = 1;
        }
        if($this->route['page'] > $pagination->totalPageCount){
            $this->view->redirect('main/index/'.$pagination->totalPageCount);
        }
        $paginationContent = $pagination->getContent();

        //Получаем информацию о постах
        $posts = $this->model->getNewestPostsByLimit($limit, $pagination->currentPage);
        for ($i = 0; $i < count($posts); $i++){
            $posts[$i]['col_of_comments'] = $this->model->commentsCount($posts[$i]['id']);
        }

        //Получаем инфу о пользователе, если он залогинился
        $userData = $this->account->getAuthorizeData();

        $this->view->render('Главная страница', [
            'posts' => $posts,
            'pagination' => $paginationContent,
            'userData' => $userData
        ]);
    }

    public function aboutAction(){
        $userData = $this->account->getAuthorizeData();

        $this->view->render('О мне', [
            'userData' => $userData
        ]);
    }

    public function privacyAction(){
        $userData = $this->account->getAuthorizeData();

        $this->view->render('Политика конфиденциальности',[
            'userData' => $userData
        ]);
    }

    public function contactAction(){
        $userData = $this->account->getAuthorizeData();

        if(!empty($_POST)) {
            $this->model->error = [];
            if (!$this->model->contactValidate($_POST)) {
                $this->view->message('Ошибка', $this->model->error, '', 'validation');
            }

            //Если запрос прислал пользователь, а не js для проверки полей, то отправляем письмо
            if (isset($_POST['login_trusted'])) {
                $this->model->sendMessage($_POST['message']);
                $this->view->message('Успех', 'Сообщение успешно отправлено :3', true, 'popup');
            } //Если данные верны, но все еще приходят проверки на валидность полей от js, то говорим, что уже все правильно
            else {
                exit(json_encode(['finally_valid' => true]));
            }
        }

        $this->view->render('Контакты', [
            'userData' => $userData
        ]);
    }


    //Постим комментарий(или ответ)
    public function postComment($userId, $userData, $postId){
        //У обычного комментария родительским комментарием будет сам комментарий,
        //а уже у ответа на этот коммент родительским комментарием будет коммент, под которым отвечал юзер(соре за часто повторяющиеся слова :3)
        $recentlyAddedCommentId = 0;
        if($_POST['record_type'] == 'comment') {
            if(isset($_POST['upper_comment_id'])) {
                $recentlyAddedCommentId = $this->model->commentPost($userId, $_POST['comment'], $postId, $_POST['record_type'], $_POST['upper_comment_id']);
            }
            else{
                $recentlyAddedCommentId = $this->model->commentPost($userId, $_POST['comment'], $postId, $_POST['record_type']);
            }
            $this->model->setParentCommentId($recentlyAddedCommentId, $recentlyAddedCommentId);
        }
        else if($_POST['record_type'] == 'answer'){
            if(isset($_POST['upper_comment_id'])) {
                $recentlyAddedCommentId = $this->model->commentPost($userId, $_POST['answer'], $postId, $_POST['record_type'], $_POST['upper_comment_id']);
            }
            else{
                $recentlyAddedCommentId = $this->model->commentPost($userId, $_POST['answer'], $postId, $_POST['record_type']);
            }
            $this->model->setParentCommentId($recentlyAddedCommentId, $_POST['parent_comment_id']);
        }
        $response = [
            'user_data' => $userData,
            'recently_added_comment_id' => $recentlyAddedCommentId
        ];

        //Если мы оставляем ответ под ответом, то отправляем имя пользователя, на чей коммент отвечаем
        if(isset($_POST['upper_comment_id'])) {
            $upperCommentAuthorId = $this->model->getCommentAuthorId($_POST['upper_comment_id']);
            $upperCommentUserInfo = $this->model->getUserInfoForAnswer($upperCommentAuthorId);
            $response['upper_comment_user_info'] = $upperCommentUserInfo;
        }

        //Отправляем данные о пользователе, который постит коммент(или ответ), в js
        $this->view->response($response);
    }

    //Настраиваем отображение комментариев
    public function commentsSetup($userId, $commentsOnPageLimit, $answersOnPageLimit, $postId){
        //Получаем айди комментов, у которых есть ответы
        $commentsWithAnswersIds = $this->model->getCommentsWithAnswersIds($postId);
        //Получаем количество ответов для каждого коммента, у которого есть ответы
        $commentsColOfAnswers = $this->model->getCommentsColOfAnswers($commentsWithAnswersIds);

        $this->view->response([
            'authorize_user_id' => $userId,
            'comments_limit' => $commentsOnPageLimit,
            'answers_limit' => $answersOnPageLimit,
            'comments_with_answers_ids' => $commentsWithAnswersIds,
            'comments_col_of_answers' => $commentsColOfAnswers
        ]);
    }

    //Получаем информацию о комментах и их оценках
    public function commentsOffset($userId, $commentsOnPageLimit, $postId, $filterMode){
        //Инфа о комментах
        $commentsInfo = $this->model->getCommentsInfo($commentsOnPageLimit, intval($_POST['offset']), $postId, $userId, $filterMode);
        //Айди отображаемых комментов
        $commentsIds = array_map(function ($commentInf){
            return $commentInf['comment_id'];
        }, $commentsInfo);

        $commentsIds = array_unique($commentsIds);
        //Получаем инфу об оценках, которые поставил залогиненный пользователь комментариям
        $commentsWithActiveMarksData = $this->model->getRecordsWithActiveMarksData($userId, $commentsIds);

        $this->view->response([
            'comments_info' => $commentsInfo,
            'comments_with_active_marks_data' => $commentsWithActiveMarksData
        ]);
    }

    //Получаем информвцию о ответах и их оценках
    public function answersOffset($userId){
        $answersInfo = $this->model->getAnswersInfo(intval($_POST['answers_limit']), intval($_POST['answers_offset']), intval($_POST['parent_comment_id']));
        //Айди отображаемых ответов
        $answersIds = array_map(function ($commentInf){
            return $commentInf['comment_id'];
        }, $answersInfo);

        //Получаем инфу об оценках, которые поставил залогиненный пользователь комментариям
        $answersWithActiveMarksData = $this->model->getRecordsWithActiveMarksData($userId, $answersIds);

        $this->view->response([
            'answers_info' => $answersInfo,
            'answers_with_active_marks_data' => $answersWithActiveMarksData
        ]);
    }

    //Обновляем количество лайков или дизлайков у коммента или ответа
    public function commentMarksRefresh($userId, $postId){
        //Меняем колличество лайков или дизлайков
        $this->model->commentMarksUpdate($_POST['likes'], $_POST['dislikes'], $_POST['comment_id']);

        //Добавляем или изменяем инфу о поставившем лайк или дизлайк юзере в бд, а так же о комменте, который оценивался
        if($this->model->isMarksExists($_POST['comment_id'], $userId)) {
            if($_POST['type_of_mark'] == 'undefined'){                              //Если пользователь удалил оценку коммента, удаляем информацию о этой оценке из бд
                $this->model->deleteMarksData($_POST['comment_id'], $userId);
            }
            else {
                //Меняем тип оценки(лайк или дизлайк)
                $this->model->updateMarksData($_POST['comment_id'], $_POST['type_of_mark'], $userId);
            }
        }
        else{
            $this->model->setMarksData($userId, $_POST['comment_id'], $_POST['type_of_mark'], $postId);
        }
    }

    //Обновляем количество лайков или дизлайков для поста
    public function postMarksRefresh($userId, $postId){
        //Меняем колличество лайков или дизлайков
        $this->model->postMarksUpdate($_POST['post_likes'], $_POST['post_dislikes'], $postId);

        //Добавляем или изменяем инфу о поставившем лайк или дизлайк юзере в бд, а так же о посте, который оценивался
        if($this->model->isPostMarksExists($postId, $userId)) {
            if($_POST['type_of_mark'] == 'undefined'){                              //Если пользователь удалил оценку поста, удаляем информацию о этой оценке из бд
                $this->model->deletePostMarksData($postId, $userId);
            }
            else {
                //Меняем тип оценки(лайк или дизлайк)
                $this->model->updatePostMarksData($postId, $_POST['type_of_mark'], $userId);
            }
        }
        else{
            $this->model->setPostMarksData($userId, $postId, $_POST['type_of_mark']);
        }
    }


    public function postAction(){
        $admin = new Admin();
        $postId = $this->route['id'];
        if(!$admin->postExistCheck($postId)) {
            View::errorCode('404');
        }


        $userId = '';
        if(isset($_COOKIE['authorize'])){
            $userId = $_COOKIE['authorize'];
        }
        elseif(isset($_SESSION['authorize'])){
            $userId = $_SESSION['authorize'];
        }
        $userData = $this->account->getAuthorizeData();


        //Постим комментарий(или ответ), который пришел из js
        if ( isset($_POST['record_type']) ){
            $this->postComment($userId, $userData, $postId);
        }


        //Отображаем комментарии через js
        $commentsOnPageLimit = 15; //Количество отображаемых комментов за раз
        $answersOnPageLimit = 7; //Количество отображаемых ответов на коммент за раз
        if( isset($_POST['offset']) ) {
            $this->commentsOffset($userId, $commentsOnPageLimit, $postId, $_POST['filter_mode']);
        }
        else if( isset($_POST['setup']) ){
            $this->commentsSetup($userId, $commentsOnPageLimit, $answersOnPageLimit, $postId);
        }
        //Отображаем ответы на комменты через js
        else if( isset($_POST['answers_offset']) ) {
            $this->answersOffset($userId);
        }


        //Обновляем кол-во лайков или дизлайков у коммента или ответа
        if( isset($_POST['likes']) && isset($_POST['dislikes']) ) {
            $this->commentMarksRefresh($userId, $postId);
        }


        //Удаляем комментарий или ответ
        if(isset($_POST['remove_comment_id'])){
            $this->model->removeCommentById($_POST['remove_comment_id'], $_POST['type_of_comment']);
        }
        //Редактируем комментарий или ответ
        elseif(isset($_POST['edit_comment_id'])) {
            $this->model->editCommentById($_POST['edit_comment_id'], $_POST['changed_comment_text']);
        }


        //Отправляем количество лайков и дизлайков под данным постом и айди авторизованного пользователя
        if( isset($_POST['get_post_marks'])) {
            $marks = $this->model->getPostMarks($postId)[0];
            $this->view->response([
                'likes' => $marks['likes'],
                'dislikes' => $marks['dislikes'],
                'user_id' => $userId
            ]);
        }
        //Отправляем тип активной отметки(лайк или дизлайк)
        if(isset($_POST['get_active_mark_type'])){
            $markType = $this->model->getActiveMarkType($postId, $userId);
            $this->view->response(['mark_type' => $markType]);
        }
        //Обновляем кол-во лайков или дизлайков для поста
        if( isset($_POST['post_likes']) && isset($_POST['post_dislikes']) ) {
            $this->postMarksRefresh($userId, $postId);
        }


        //Увеличиваем количество просмотров на один
        if(isset($_POST['increment_views'])){
            $this->model->incrementPostViews($postId);
        }


        $post = $admin->getPostById($postId);
        $colOfComments = $this->model->commentsCount($postId);
        if(isset($_POST['get_comments_count'])) {
            $this->view->response($colOfComments);
        }

        $params = [
            'post' => $post,
            'userData' => $userData,
            'colOfComments' => $colOfComments
        ];
        $this->view->render('Пост', $params);
    }
}
