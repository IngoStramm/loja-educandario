<?php

/**
 * led_style_checkout
 *
 * Estilo na página de checkout para esconder campos do form
 * @return void
 */
function led_style_checkout()
{
    if (is_checkout()) :
?>
        <style>
            /*			
            .nome-do-aluno {
				display: none;
			}
			.nome-do-aluno-1 {
				display: block !important;
			}
            */
            #billing_first_name_field,
            #billing_last_name_field,
            #billing_cpf_field,
            #billing_country_field,
            /* #billing_postcode_field,
			#billing_address_1_field,
			#billing_number_field,
			#billing_address_2_field,
			#billing_neighborhood_field,
			#billing_city_field,
			#billing_state_field, */
            #billing_phone_field,
            #billing_cellphone_field,
            #billing_company,
            #billing_email_field,
            .woocommerce-additional-fields h3,
            .hide-optional .optional {
                display: none !important;
            }
        </style>
    <?php
    endif;
}

add_action('wp_head', 'led_style_checkout');

/**
 * led_set_hidden_values
 *
 * Adiciona campos "hidden" ao form do checkout (se os valores existirem)
 * 
 * @return void
 */
function led_set_hidden_values()
{

    $output = '';
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $cpf = get_user_meta($user_id, 'billing_cpf', true);
    $first_name = get_user_meta($user_id, 'billing_first_name', true);
    $last_name = get_user_meta($user_id, 'billing_last_name', true);
    $phone = get_user_meta($user_id, 'billing_phone', true);
    $email = $current_user->user_email;


    if ($cpf)
        $output .= '<input type="hidden" name="billing_cpf" value="' . $cpf . '" />';
    if ($first_name)
        $output .= '<input type="hidden" name="billing_first_name" value="' . $first_name . '" />';
    if ($last_name)
        $output .= '<input type="hidden" name="billing_last_name" value="' . $last_name . '" />';
    if ($phone)
        $output .= '<input type="hidden" name="billing_phone" value="' . $phone . '" />';
    if ($email)
        $output .= '<input type="text" name="billing_email" value="' . $email . '" />';

    echo $output;
}

// add_action('woocommerce_before_checkout_billing_form', 'led_set_hidden_values', 10, 1);


/**
 * led_remove_woo_checkout_fields
 *
 * Remove o campo de "Notas do pedido" do checkout
 * 
 * @param  array $fields
 * @return array
 */
function led_remove_woo_checkout_fields($fields)
{

    unset($fields['order']['order_comments']);

    return $fields;
}

add_filter('woocommerce_checkout_fields', 'led_remove_woo_checkout_fields', 12);

/**
 * led_set_CPF_not_required
 *
 * Define alguns campos do Checkout como não obrigatório
 * 
 * @param  array $fields
 * @return array
 */
function led_set_not_required_woo_checkout_fields($fields)
{
    $fields['billing']['billing_cpf']['required'] = false;
    $fields['billing']['billing_first_name']['required'] = false;
    $fields['billing']['billing_last_name']['required'] = false;
    $fields['billing']['billing_phone']['required'] = false;
    $fields['billing']['billing_number']['required'] = false;
    $fields['billing']['billing_neighborhood']['required'] = false;
    $fields['billing']['billing_cellphone']['required'] = false;
    $fields['billing']['billing_company']['required'] = false;
    $fields['billing']['billing_address_1']['required'] = false;
    $fields['billing']['billing_address_2']['required'] = false;
    $fields['billing']['billing_city']['required'] = false;
    $fields['billing']['billing_postcode']['required'] = false;
    $fields['billing']['billing_country']['required'] = false;
    $fields['billing']['billing_state']['required'] = false;

    $fields['billing']['billing_number']['class'] = array('form-row-wide');
    $fields['billing']['billing_neighborhood']['class'] = array('form-row-wide');
    return $fields;
}

add_filter('woocommerce_checkout_fields', 'led_set_not_required_woo_checkout_fields', 99999999);

/**
 * led_set_wide_row_woo_checkout_fields
 *
 * Define largura máxima para alguns campos
 * 
 * @param  array $fields
 * @return array
 */
function led_set_wide_row_woo_checkout_fields($fields)
{
    $fields['billing']['billing_number']['class'] = array('form-row-wide');
    $fields['billing']['billing_neighborhood']['class'] = array('form-row-wide');
    return $fields;
}


add_filter('woocommerce_checkout_fields', 'led_set_wide_row_woo_checkout_fields', 99999999);

// Remove opção de adicionar endereço de entrega diferente do endereço de pagamento
add_filter('woocommerce_cart_needs_shipping_address', '__return_false');

/**
 * led_add_title_alunos
 * 
 * Exibe título antes da listagem de alunos
 * na página de checkout
 *
 * @return string
 */
function led_add_title_alunos()
{
    if (!is_user_logged_in())
        return;

    $alunos = led_get_alunos_from_current_user();

    // led_debug($alunos);
    if (!$alunos)
        return;

    $output = '<h3>' . __('Alunos', 'led') . '</h3>';
    $output .= '<p>' . __('Selecione para quais alunos destina-se este pedido.', 'led') . '</p>';
    echo $output;
}

add_action('woocommerce_before_checkout_billing_form', 'led_add_title_alunos');

/**
 * led_checkbox_alunos
 *
 * Adiciona o campo de checkbox dos alunos do usuário no form do checkout
 * 
 * @param  array $fields
 * @return array
 */
function led_checkbox_alunos($fields)
{

    if (!is_user_logged_in())
        return $fields;

    $alunos = led_get_alunos_from_current_user();

    if (!$alunos)
        return $fields;

    $i = 0;
    foreach ($alunos as $k => $aluno) :

        if (isset($aluno->post_title)) :

            $fields['billing']['_checkbox_nome_aluno_' . ($i + 1)] = array(
                'label'     => $aluno->post_title,
                'placeholder'   => _x('Nome do Aluno', 'placeholder', 'led'),
                'required'  => false,
                'class'     => array('checkbox-aluno', 'form-row-wide', 'nome-do-aluno-' . ($i + 1), 'hide-optional'),
                'clear'     => true,
                'type'        => 'checkbox',
                'priority'    => 1,
                'value'     => $aluno->ID
            );

            $i++;

        endif;

    endforeach;

    return $fields;
}

add_filter('woocommerce_checkout_fields', 'led_checkbox_alunos', 20);


/**
 * rec_add_filhos_hidden_fields
 *
 * Adiciona os campos "hidden" dos alunos no checkout 
 * 
 * @return string
 */
function led_add_filhos_hidden_fields()
{

    if (!is_user_logged_in())
        return;

    $output = '';
    $alunos = led_get_alunos_from_current_user();

    if (!$alunos)
        return;

    $i = 0;
    foreach ($alunos as $k => $aluno) :

        $serie = get_post_meta($aluno->ID, 'serie', true);

        if (isset($aluno->post_title)) :

            $output .= '<input class="nome-do-aluno nome-do-aluno-' . ($i + 1) . '" type="hidden" name="nome_aluno_' . ($i + 1) . '" value="' . $aluno->post_title . '" disabled="disabled" />';
            $output .= '<input class="serie-do-aluno serie-do-aluno-' . ($i + 1) . '" type="hidden" name="serie_aluno_' . ($i + 1) . '" value="' . $serie . '" data-aluno="' . $aluno->post_title . '" disabled="disabled" />';

            $i++;

        endif;

    endforeach;

    echo $output;
}

add_action('woocommerce_before_checkout_billing_form', 'led_add_filhos_hidden_fields');

/**
 * led_checkout_field_update_order_meta
 *
 * Atualiza o valor do campo meta dos alunos no pedido
 * 
 * @param  int $order_id
 * @return void
 */
function led_checkout_field_update_order_meta($order_id)
{

    for ($i = 1; $i <= 10; $i++) {
        if (!empty($_POST['nome_aluno_' . $i])) {
            update_post_meta($order_id, 'nome_aluno_' . $i, sanitize_text_field($_POST['nome_aluno_' . $i]));
        }
    }

    for ($i = 1; $i <= 10; $i++) {
        if (!empty($_POST['serie_aluno_' . $i])) {
            update_post_meta($order_id, 'serie_aluno_' . $i, sanitize_text_field($_POST['serie_aluno_' . $i]));
        }
    }
}

add_action('woocommerce_checkout_update_order_meta', 'led_checkout_field_update_order_meta');

/**
 * led_checkout_field_display_admin_order_meta
 * 
 * Exibe o valor dos campos na tela de edição do pedido 
 * e na tela de confirmação do pedido
 *
 * @param  object $order
 * @return void
 */
function led_checkout_field_display_admin_order_meta($order)
{

    for ($i = 1; $i <= 10; $i++) {
        $aluno = get_post_meta($order->id, 'nome_aluno_' . $i, true);
        $serie = get_post_meta($order->id, 'serie_aluno_' . $i, true);
        if ($aluno) :
            echo '<p>';
            echo '<strong>' . __('Nome do Aluno', 'rec') . ':</strong> ' . $aluno;
            if ($serie)
                echo ' — <strong>' . __('Série', 'rec') . ':</strong> ' . $serie;
            echo '</p>';
        endif;
    }
}

add_action('woocommerce_admin_order_data_after_billing_address', 'led_checkout_field_display_admin_order_meta', 10, 1);
add_action('woocommerce_order_details_after_customer_details', 'led_checkout_field_display_admin_order_meta', 10, 1);

/**
 * led_add_alunos_to_emails_notifications
 *
 * Exibe os campos dos alunos nas notificações de e-mail do WooCommerce
 * @param  object $order
 * @param  boolean $sent_to_admin
 * @param  string $plain_text
 * @param  string $email
 * @return string
 */
function led_add_alunos_to_emails_notifications($order, $sent_to_admin, $plain_text, $email)
{

    $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;

    $output = '';

    for ($i = 1; $i <= 10; $i++) {
        $aluno = get_post_meta($order_id, 'nome_aluno_' . $i, true);
        $serie = get_post_meta($order_id, 'serie_aluno_' . $i, true);

        if ($aluno) :
            $output .= '<div>';
            $output .= '<strong>' . __('Nome do Aluno', 'rec') . ':</strong> <span class="text">' . $aluno . '</span>';
            if ($serie) :
                $output .= '  —  <strong>' . __('Série', 'rec') . ':</strong> <span class="text">' . $serie . '</span>';
            endif;
            $output .= '</div>';
        endif;
    }

    echo $output;
}

add_action('woocommerce_email_customer_details', 'led_add_alunos_to_emails_notifications', 15, 4);

/**
 * led_renaming_order_status
 *
 * Renomeia o status do pedido
 * 
 * @param  array $order_statuses
 * @return array
 */
function led_renaming_order_status($order_statuses)
{

    foreach ($order_statuses as $key => $status) {
        if ('wc-processing' === $key)
            $order_statuses['wc-processing'] = _x('Pagamento efetuado', 'Order status', 'led');
    }
    return $order_statuses;
}

add_filter('wc_order_statuses', 'led_renaming_order_status');

/**
 * led_hide_price_addcart_not_logged_in
 * 
 * Esconde o preço dos produtos se o usuário
 * não estiver logado
 *
 * @param  string $price
 * @param  object $product
 * @return void
 */
function led_hide_price_addcart_not_logged_in($price, $product)
{
    if (!is_user_logged_in()) {
        $price = null;
    }
    return $price;
}

add_filter('woocommerce_get_price_html', 'led_hide_price_addcart_not_logged_in', 9999, 2);

/**
 * led_hide_product_form_add_to_cart
 * 
 * Esconde (via CSS) a opção de comprar produtos
 * se o usuário não estiver logado
 *
 * @return string
 */
function led_hide_product_form_add_to_cart()
{
    if (is_user_logged_in())
        return;
    ?>
    <style>
        .elementor-widget-woocommerce-product-add-to-cart {
            display: none !important;
        }
    </style>
<?php
}

add_action('wp_head', 'led_hide_product_form_add_to_cart');
