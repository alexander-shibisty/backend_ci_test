<?php
/*
    Модель работы с авторизцией/регистрацией аккаунта
    @author: Dmitriy Nyashkin
    @email: dmitriy@nyashk.in
    @link: http://nyashk.in
*/


class Login_model extends CI_Model {
    public function __construct()
    {
        parent::__construct();
    }

    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
    }

    public static function start_session(int $user_id)
    {
        // если перенедан пользователь
        if (empty($user_id))
        {
            throw new CriticalException('No id provided!');
        }

        App::get_ci()->session->set_userdata('id', $user_id);
    }
}
