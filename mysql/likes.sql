CREATE TABLE `rating` (
    `post_id` int(11) NOT NULL,
    `user_id` int(11) UNSIGNED NOT NULL,
    `time_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_post_id
        FOREIGN KEY (post_id) 
        REFERENCES post(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_user_id
        FOREIGN KEY (user_id)
        REFERENCES user(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
