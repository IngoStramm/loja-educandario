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

function led_woocommerce_after_checkout_validation($data, $errors)
{
    if (isset($_POST['led_parcelamento_text'])) {
        $parcelamento = $_POST['led_parcelamento_text'];
        $_SESSION['parcelamento'] = $parcelamento;
    }
}
add_action('woocommerce_after_checkout_validation', 'led_woocommerce_after_checkout_validation', 999, 2);

function led_woocommerce_checkout_order_processed($order_id, $posted_data, $order)
{
    // verificar se está definido o valor de $_SESSION['parcelamento']
    // led_debug($_GET);
    if (isset($_SESSION['parcelamento'])) {
        $parcelamento = $_SESSION['parcelamento'];
        update_post_meta($order_id, 'parcelamento', $parcelamento);
    } else {
        update_post_meta($order_id, 'parcelamento', __('Sem parcelamento', 'led'));
        wp_die();
    }
}
add_action('woocommerce_checkout_order_processed', 'led_woocommerce_checkout_order_processed', 999, 3);

function led_woocommerce_admin_order_data_after_order_details($order)
{
    $parcelamento = get_post_meta($order->id, 'parcelamento', true);

    if ($parcelamento) {
        echo '<strong>' . __('Parcelamento:', 'led') . '</strong> ' . $parcelamento . '</p>';
    }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'led_woocommerce_admin_order_data_after_order_details', 10, 1);

function led_woocommerce_order_details_after_order_table($order)
{
    $parcelamento = get_post_meta($order->get_id(), 'parcelamento', true);
    if ($parcelamento) {
        echo '<p><strong>' . __('Parcelamento', 'led') . ':</strong> ' . $parcelamento . '</p>';
    }
}
add_action('woocommerce_order_details_after_order_table', 'led_woocommerce_order_details_after_order_table', 10, 1);

// criar função que exibe o valor do meta field parcelamento no e-mail de notificação de novo pedido para o dono da loja
function led_woocommerce_email_order_meta_fields($fields, $sent_to_admin, $order)
{
    $parcelamento = get_post_meta($order->get_id(), 'parcelamento', true);
    if ($parcelamento) {
        $fields['parcelamento'] = array(
            'label' => __('Parcelamento', 'led'),
            'value' => $parcelamento,
        );
    }
    return $fields;
}
add_filter('woocommerce_email_order_meta_fields', 'led_woocommerce_email_order_meta_fields', 10, 3);