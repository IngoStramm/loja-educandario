document.addEventListener('DOMContentLoaded', function () {
    // code
});

jQuery(function ($) {

    const led_verifica_formato_cep = function (val) {
        const verifica = /^[0-9]{5}-[0-9]{3}$/;
        val = $.trim(val);
        if (verifica.test(val)) {
            return true;
        } else {
            return false;
        }
    };

    var led_exibe_esconde_campos_checkout = function (val, force_show, fields) {
        if (typeof force_show !== 'undefined' && typeof fields !== 'undefined') {
            // console.log('1');
            // console.log('force_show: ' + force_show);
            // console.log('fields: ' + fields);
            if (force_show && fields) {
                // console.log('1.1');
                for (var a = fields.length - 1; a >= 0; a--) {
                    fields[a].addClass('visivel');
                }
            } else if (!force_show && fields) {
                // console.log('1.2');
                for (var b = fields.length - 1; b >= 0; i--) {
                    fields[b].removeClass('visivel');
                }
            }
        } else if (led_verifica_formato_cep(val)) {
            // console.log('2');
            $('#billing_address_1_field, #billing_number_field, #billing_address_2_field, #billing_neighborhood_field, #billing_city_field, #billing_state_field').addClass('visivel');
        } else {
            // console.log('3');
            $('#billing_address_1_field, #billing_number_field, #billing_address_2_field, #billing_neighborhood_field, #billing_city_field, #billing_state_field').removeClass('visivel');
        }

    };

    var led_muda_foco_para_numero = function () {
        $('#billing_postcode').each(function () {
            var cep_input = $(this);
            var cep_row = cep_input.closest('.form-row');
            var numero_row = cep_input.closest('form').find('#billing_number_field');
            var numero_input = numero_row.find('input');
            var rua_input = $('#billing_address_1');
            cep_input.blur(function (e) {
                if (cep_input.val() !== '' && numero_row.is(':visible')) {
                    if (rua_input.val() !== '') {
                        numero_input.focus();
                    } else {
                        rua_input.focus();
                    }
                } else if (cep_row.hasClass('woocommerce-invalid') || cep_row.hasClass('woocommerce-invalid-required-field')) {
                    e.preventDefault();
                    cep_input.focus();
                }
            });
        });
    };

    var led_addressAutoComplete = function (field) {
        // Checks with *_postcode field exist.
        if ($('#' + field + '_postcode').length) {

            // Valid CEP.
            var cep = $('#' + field + '_postcode').val().replace('.', '').replace('-', ''),
                country = $('#' + field + '_country').val(),
                address_1 = $('#' + field + '_address_1').val();

            // Check country is BR.
            if (cep !== '' && 8 === cep.length && 'BR' === country/* && 0 === address_1.length*/) {

                var correios = $.ajax({
                    type: 'GET',
                    url: '//viacep.com.br/ws/' + cep + '/json/',
                    dataType: 'jsonp',
                    crossDomain: true,
                    contentType: 'application/json'
                });

                // Gets the address.
                correios.done(function (address) {

                    // Address.
                    if (address.logradouro) {
                        // console.log('encontrou logradouro');
                        $('#' + field + '_address_1').val(address.logradouro).change();
                    }

                    // Neighborhood.
                    if (address.bairro) {
                        $('#' + field + '_neighborhood').val(address.bairro).change();
                    }

                    // City.
                    if ('' !== address.localidade) {
                        $('#' + field + '_city').val(address.localidade).change();
                    } else {
                        led_exibe_esconde_campos_checkout(val, true, array($('#' + field + '_city')));
                    }

                    // State.
                    if ('' !== address.uf) {
                        $('#' + field + '_state option:selected').attr('selected', false).change();
                        $('#' + field + '_state option[value="' + address.uf + '"]').attr('selected', 'selected').change();
                        $('#' + field + '_state').val(address.uf).change();
                    } else {
                        led_exibe_esconde_campos_checkout(val, true, array($('#' + field + '_state')));
                    }

                    // Chosen support.
                    $('#' + field + '_state').trigger('liszt:updated').trigger('chosen:updated');

                    $('#billing_number').focus();

                    if ($('.cep-notice').length) {
                        $('.cep-notice').remove();
                    }

                }).fail(function (jqXHR, textStatus, errorThrown) {

                    if (!$('.cep-notice').length) {

                        var msg = $('<div class="cep-notice"><div class="woocommerce-messages alert-color"><div class="message-wrapper"><ul class="woocommerce-error"><li><div class="message-container container"><span class="message-icon icon-close"><strong>CEP</strong> não encontrado. Digite outro CEP ou preencha manualmente o restante das informações do endereço.</span></div></li></ul></div></div></div>');

                        $('#billing_postcode').focus();
                        $('#billing_postcode').closest('form.checkout.woocommerce-checkout').prepend(msg);

                    }

                });
            }
        }
    };

    var led_addressAutoCompleteOnChange = function (field) {
        // $( document.body ).on( 'blur', '#' + field + '_postcode', function() {
        led_addressAutoComplete(field);
        // });
    };

    var led_init_cep = function () {

        led_addressAutoComplete('billing');

        cep_input = $('#billing_postcode');

        led_exibe_esconde_campos_checkout(cep_input.val());

        cep_input.keyup(function () {
            var val = $(this).val();
            led_exibe_esconde_campos_checkout(val);
            if (val.length === 9) {
                led_addressAutoComplete('billing');
                $('body').trigger('update_checkout');
            }
        });

        $('.address-field').find('input').each(function () {
            var input = $(this);
            input.keydown(function (e) {
                if (input.attr('id') !== 'billing_postcode' && !input.closest('.address-field').hasClass('visivel')) {
                    e.preventDefault();
                }
            });
        });

    };

    var led_masks_init = function () {
        // $( '.rg-mask' ).find( 'input' ).mask( '00.000.000-0' );
        $('.cep-mask').find('input').mask('00000-000');
        $('.cep-mask-input').mask('00000-000');
        $('#billing_postcode').mask('00000-000');
        $('.fone-mask-input').mask('(00) 90000-0000');
    };

    var rec_add_remove_aluno = function () {

        $('#add_aluno').click(function (e) {
            e.preventDefault();
            $('.nome-do-aluno:visible').next('.nome-do-aluno').show();
        }); // $(#add_aluno).click
    };

    var rec_remove_remove_aluno = function () {

        $('#remove_aluno').click(function (e) {
            e.preventDefault();
            $('.nome-do-aluno:visible:last').hide();
        }); // $(#add_aluno).click
    };

    var led_disable_enabled_filho = function () {
        $('.checkbox-aluno .input-checkbox').change(function (e) {
            var checkbox = this;
            var $checkbox = $(this);
            var label = $checkbox.closest('label');
            label.find('.optional').remove();
            var nome = label.text();
            nome = $.trim(nome);
            var input_nome = $('.nome-do-aluno[value="' + nome + '"]');
            var input_serie = $('.serie-do-aluno[data-aluno="' + nome + '"]');
            if (checkbox.checked) {
                // console.log('nome: ' + nome);
                input_nome.attr('disabled', false);
                input_serie.attr('disabled', false);
            } else {
                input_nome.attr('disabled', true);
                input_serie.attr('disabled', true);
            }

        }); // $(selector).click
    };

    const led_parcelamento_init = () => {
        const verificaCheckoutFormExiste = setInterval(() => {
            const checkout_woocommerce_checkout = document.querySelector('form.checkout.woocommerce-checkout');

            if (typeof (checkout_woocommerce_checkout) !== 'undefined' && checkout_woocommerce_checkout !== null) {
                clearInterval(verificaCheckoutFormExiste);
                led_get_parcelamento_text(checkout_woocommerce_checkout);
            }
        }, 1000);
    };

    led_get_parcelamento_text = (checkout_woocommerce_checkout) => {

        const default_lkn_cc_installments = document.getElementById('lkn_cc_installments');
        if (typeof (default_lkn_cc_installments) === 'undefined' || default_lkn_cc_installments === null) {
            return;
        }

        const led_parcelamento_text_input = document.createElement('input');
        led_parcelamento_text_input.setAttribute('type', 'text');
        led_parcelamento_text_input.id = 'led_parcelamento_text';
        led_parcelamento_text_input.name = 'led_parcelamento_text';
        checkout_woocommerce_checkout.appendChild(led_parcelamento_text_input);

        setInterval(() => {
            const lkn_cc_installments_text = lkn_cc_installments.options[lkn_cc_installments.selectedIndex].text;
            led_parcelamento_text_input.value = lkn_cc_installments_text;
        }, 100);
    };

    $(document).ready(function () {
        led_masks_init();
        led_init_cep();
        led_disable_enabled_filho();
        led_parcelamento_init();
    }); // $(document).ready

});