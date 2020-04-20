<?php

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Rating_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'rating';

    /** @var int */
    protected $user_id;
    /** @var string */
    protected $post_id;

    /** @var string */
    protected $time_created;

    public static function create(array $data)
    {
        App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();

        return new static(App::get_ci()->s->get_insert_id());
    }

    public static function has_like(int $post_id, int $user_id): bool {
        $data = App::get_ci()->s->from(self::CLASS_TABLE)->where([
            'post_id' => $post_id,
            'user_id' => $user_id,
        ])->one();

        return count($data) > 0;
    }

    public static function all_post_likes(int $post_id): int {
        $result = App::get_ci()->s->from(self::CLASS_TABLE)->where([
            'post_id' => $post_id,
        ])->count();
        
        return $result;
    }
}
