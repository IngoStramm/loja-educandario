<?php

add_action('wp_enqueue_scripts', 'led_frontend_scripts');

function led_frontend_scripts()
{

    $min = (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '10.0.0.3'))) ? '' : '.min';

    if (empty($min)) :
        wp_enqueue_script('loja-educandario-livereload', 'http://localhost:35729/livereload.js?snipver=1', array(), null, true);
    endif;

    wp_enqueue_script('jquery-mask', LED_URL . 'assets/js/jquery.mask' . $min . '.js', array('jquery'), '1.14.15', true);

    wp_register_script('loja-educandario-script', LED_URL . 'assets/js/loja-educandario' . $min . '.js', array('jquery'), '1.0.0', true);

    wp_enqueue_script('loja-educandario-script');

    wp_localize_script('loja-educandario-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

    wp_enqueue_style('loja-educandario-style', LED_URL . 'assets/css/loja-educandario.css', array(), false, 'all');
}
