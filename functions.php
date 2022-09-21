<?php

function led_debug($debug)
{
    echo '<pre>';
    var_dump($debug);
    echo '</pre>';
}

function led_get_alunos_from_current_user()
{
    if (!is_user_logged_in())
        return;
    $user_id = get_current_user_id();
    $cpf = get_user_meta($user_id, 'billing_cpf', true);
    if (!$cpf)
        return;
    $args = array(
        'post_type' => 'alunos',
        'meta_query' => array(
            array(
                'key' => 'user-cpf',
                'value' => $cpf,
                'compare' => '='
            )
        )
    );
    $alunos = get_posts($args);
    return $alunos;
}
