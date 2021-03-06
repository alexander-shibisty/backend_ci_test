<?php

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

        App::get_ci()->load->model('User_model');
        App::get_ci()->load->model('Login_model');
        App::get_ci()->load->model('Post_model');
        App::get_ci()->load->model('Rating_model');

        if (is_prod())
        {
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
        $posts =  Post_model::preparation(Post_model::get_all(), 'main_page');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_post($post_id){ // or can be $this->input->post('news_id') , but better for GET REQUEST USE THIS

        $post_id = intval($post_id);

        if (empty($post_id)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try
        {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }


        $posts =  Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }


    public function comment()
    { // or can be App::get_ci()->input->post('news_id') , but better for GET REQUEST USE THIS ( tests )
        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $this->load->helper(array('form', 'array'));
        $this->load->library('form_validation');

        // Validation rules
        $config = [
            [
                'field' => 'post_id',
                'label' => 'Post ID',
                'rules' => 'required'
            ],
            [
                'field' => 'message',
                'label' => 'Comment',
                'rules' => 'required|min_length[2]'
            ],
        ];

        $request_data = $this->get_json_post_body();

        $this->form_validation->set_data($request_data);
        $this->form_validation->set_rules($config);
        if($this->form_validation->run() === false){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $post_id = (int)element('post_id', $request_data);
        if(!Post_model::has_post($post_id)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $user_id = User_model::get_session_id();
        $message = (string)element('message', $request_data);

        try {
            Comment_model::create([
                'assign_id' => $post_id,
                'user_id' => $user_id,
                'text' => $message,
            ]);
        } catch(Exaption $error) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_TRY_LATER);
        }

        $post = null;
        try
        {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        $posts =  Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }


    public function login()
    {
        if(User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_DISABLED);
        }

        $this->load->helper(array('form', 'array'));
        $this->load->library('form_validation');
        
        // Validation rules
        $config = [
            [
                'field' => 'login',
                'label' => 'Login',
                'rules' => 'required|min_length[2]'
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|min_length[6]'
            ],
        ];

        // Validate
        $request_data = $this->get_json_post_body();

        $this->form_validation->set_data($request_data);
        $this->form_validation->set_rules($config);
        if($this->form_validation->run() === false){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $login = element('login', $request_data);
        $password = element('password', $request_data);

        // Get user info
        $user = null;
        try {
            $user = User_model::get_once([
                'email' => $login,
                'password' => $password,
            ]);
        } catch(Exaption $error) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_TRY_LATER);
        }
        
        $user_id = (int)element('id', $user);
        if ($user_id === 0){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        Login_model::start_session($user_id);

        return $this->response_success(['user' => $user_id]);
    }


    public function logout()
    {
        Login_model::logout();
        redirect(site_url('/'));
    }

    public function add_money(){
        // todo: add money to user logic
        return $this->response_success(['amount' => rand(1,55)]);
    }

    public function buy_boosterpack(){
        // todo: add money to user logic
        return $this->response_success(['amount' => rand(1,55)]);
    }


    public function like(){
        if(!User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_DISABLED);
        }

        $this->load->helper(array('form', 'array'));
        $this->load->library('form_validation');
        
        // Validation rules
        $config = [
            [
                'field' => 'post_id',
                'label' => 'Post ID',
                'rules' => 'required'
            ]
        ];

        // Validate
        $request_data = $this->get_json_post_body();

        $this->form_validation->set_data($request_data);
        $this->form_validation->set_rules($config);
        if($this->form_validation->run() === false){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $post_id = (int)element('post_id', $request_data);
        if(!Post_model::has_post($post_id)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }
        
        $user_id = User_model::get_session_id();
        if(Rating_model::has_like($post_id, $user_id)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            Rating_model::create([
                'post_id' => $post_id,
                'user_id' => $user_id,
            ]);
        } catch(Exaption $error) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_TRY_LATER);
        }
        
        // todo: add like post\comment logic
        return $this->response_success(['likes' => Rating_model::all_post_likes($post_id)]);
    }

    private function get_json_post_body(): ?array {
        $request_data = $this->security->xss_clean($this->input->raw_input_stream);
        $request_data = json_decode($request_data, true, 512, JSON_OBJECT_AS_ARRAY);

        return $request_data;
    }

}
