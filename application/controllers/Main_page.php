<?php

use Model\Login_model;
use Model\Post_model;
use Model\User_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (is_prod()) {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts = Post_model::preparation(Post_model::get_all(), 'main_page');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_post($post_id)
    { // or can be $this->input->post('news_id') , but better for GET REQUEST USE THIS

        $post_id = intval($post_id);

        if (empty($post_id)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }


        $posts = Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }


    public function comment($post_id, $message)
    { // or can be App::get_ci()->input->post('news_id') , but better for GET REQUEST USE THIS ( tests )

        if (!User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post_id = intval($post_id);

        if (empty($post_id) || empty($message)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        // Todo: 2 nd task Comment
        $post->comment();

        $posts = Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }


    /**
     * @throws Exception
     */
    public function login($user_id = null)
    {
        $post = json_decode($this->security->xss_clean($this->input->raw_input_stream));

        // Right now for tests we use from controller
        $login = $post->login ?? null;
        $password = $post->password ?? null;

        if (empty($login) || empty($password)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $user = User_model::get_one($login, $password);

        if ($user) {
            $userModel = User_model::preparation($user, 'main_page');
            Login_model::start_session($user);

            return $this->response_success(['user' => $user->get_id()]);
        } else {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }
    }


    public function logout()
    {
        Login_model::logout();
        redirect(site_url('/'));
    }

    public function add_money()
    {
        // todo: 4th task  add money to user logic
        return $this->response_success(['amount' => rand(1, 55)]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }

    public function buy_boosterpack()
    {
        // todo: 5th task add money to user logic
        return $this->response_success(['amount' => rand(1, 55)]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }


    public function like()
    {
        // todo: 3rd task add like post\comment logic
        return $this->response_success(['likes' => rand(1, 55)]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }

}
