{$bluemedia_payment_ids = fn_2lm_bm_get_bluemedia_payment_ids()}
{if isset($bluemedia_gateways) && (in_array($smarty.request.payment_id, $bluemedia_payment_ids) || (!empty($cart.payment_id) && in_array($cart.payment_id, $bluemedia_payment_ids)))}
<div class="litecheckout__item">
    <div class="clearfix">
        {$is_blik = $cart.payment_id|fn_2lm_bm_is_blik_payment}

        {if $cart.bm_subscription_start && !empty($cart.bm_subscription_order_id)}
            {* Zamowienie cykliczne *}
            <input type="hidden" id="payment_bluemedia_gateway" name="payment_bluemedia_gateway" value="{$smarty.const.BLUEMEDIA_GATEWAY_ID_RECURRING}" />
        {elseif $is_blik}
            {* Platnosc BLIKiem *}
            <input type="hidden" id="payment_bluemedia_gateway" name="payment_bluemedia_gateway" value="{$smarty.const.BLUEMEDIA_GATEWAY_ID_BLIK0}" />
            <div class="clearfix"></div>

            <div class="ty-control-group">
                <label for="blik_code" class="ty-control-group__title cm-required">{__('2lm_bm_enter_blik_code')}:</label>
                <input type="text" maxlength="6" name="payment_blik_code" value="" id="blik_code" />
            </div>
        {else}
            {* Wybór form płatności online BM *}
            <div class="ty-control-group">
                <label for="bm-form-bank-input" class="ty-control-group__title cm-required">{__('2lm_bm_choose_onetime_payment_option')}</label>
            </div>

            <div id="bluemedia-gateways" class="bm-form-banks">

                <div class="bm-form-banks-group">
                    {if $bluemedia_group_by_type == 'N'}

                        {foreach from=$bluemedia_gateways item=gateway name=bmpayment}
                            <div class="bm-form-banks-item" data-id="{$gateway.gatewayID}">
                                <div class="bm-form-banks-item-logo">
                                    <img src="{$gateway.iconURL}" alt="{$gateway.gatewayName}" />
                                </div>
                                <div class="bm-form-banks-item-name">{$gateway.gatewayName}</div>
                            </div>
                        {/foreach}

                    {else}

                        {foreach from=$bluemedia_gateways item=ggroup key=gtype name=bmgatewaygroup}
                            <div class="bm-form-gateway-types clearfix">
                                <div class="bm-form-bank-gateway-group-name">
                                    <strong>{$gtype}</strong>
                                </div>

                                <div class="bm-form-banks-subgroup">
                                    {foreach from=$ggroup item=gateway name=bmpayment}
                                        <div class="bm-form-banks-item" data-id="{$gateway.gatewayID}">
                                            <div class="bm-form-banks-item-logo">
                                                <img src="{$gateway.iconURL}" alt="{$gateway.gatewayName}" />
                                            </div>
                                            <div class="bm-form-banks-item-name">{$gateway.gatewayName}</div>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        {/foreach}

                    {/if}
                </div>

            </div>

            <input type="hidden" id="payment_bluemedia_gateway" name="payment_bluemedia_gateway" value="" />
            <div class="clearfix"></div>

            <script type="text/javascript">
                (function(_, $) {
                    var controller = '{$runtime.controller}', mode = '{$runtime.mode}';

                    function bm_mark_bank_item() {
                        var form_bank = $('#bm-form-banks'), form_bank_input = $('input[name="payment_bluemedia_gateway"]'), banks_item = $('.bm-form-banks-item');
                        banks_item.on('click', function(banks_item) {
                            var obj = $(this),id = obj.data('id');
                            $('.bm-form-banks-group .active').removeClass('active');
                            obj.addClass('active');
                            form_bank_input.val(id);
                        });
                    }
                    function bm_blik_onclick_action() {
                        $('.bm-form-banks-item').on('click', function() {
                            var obj = $(this), id = obj.data('id');
                            var blik_id = {$smarty.const.BLUEMEDIA_GATEWAY_ID_BLIK0};
                            if (id == blik_id) {
                                var active_payment_tab_id = $('#payment_tabs li.active').attr('id');
                                var blik = $('#blik_code');
                                if (blik.length == 0) {
                                    var input_content =
                                        '<div class="ty-control-group">' +
                                        '    <label for="blik_code" class="ty-control-group__title cm-required">{__('2lm_bm_enter_blik_code')}:</label>' +
                                        '    <input type="text" maxlength="6" name="payment_blik_code" value="" id="blik_code" />' +
                                        '</div>';
                                    $('#content_' + active_payment_tab_id + ' .blik-code-wrapper').append(input_content);
                                } else {
                                    $('.blik-code-wrapper').show();
                                }

                            } else {
                                $('.blik-code-wrapper').html('');
                            }
                        });
                    }


                    $(document).ready(function () {
                        bm_mark_bank_item();
                        bm_blik_onclick_action();
                    });

                    $(document).ajaxComplete(function () {
                        bm_mark_bank_item();
                        bm_blik_onclick_action();
                    });

                }(Tygh, Tygh.$));
            </script>

            <div class="blik-code-wrapper"></div>
{*
            <script type="text/javascript">
                (function(_, $) {
                    $(document).ready(function () {
                        var controller = '{$runtime.controller}', mode = '{$runtime.mode}',
                            payment_id = '{$order_info.payment_id}', r_payment_id = '{$smarty.request.payment_id}',
                            gateway_id = '{$order_info.payment_info.gatewayID}', bm_payment_id = '{"1"|fn_2lm_bm_get_bluemedia_payment_ids}';
                        if (controller == 'orders' && mode == 'details' && r_payment_id == '') {
                            $('#payment_' + payment_id).click();
                        }
//            bm_mark_bank_item();
//            bm_blik_onclick_action();
                    });
                    $(document).ajaxComplete(function () {
//            bm_mark_bank_item();
//            bm_blik_onclick_action();
                    });
                }(Tygh, Tygh.$));
            </script>
*}
        {/if}

    </div>
</div>
{/if}
