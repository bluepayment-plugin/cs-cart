{$bluemedia_payment_ids = fn_2lm_bm_get_bluemedia_payment_ids()}
{if isset($payment.bluemedia_gateways) && (in_array($smarty.request.payment_id, $bluemedia_payment_ids) || (!empty($cart.payment_id) && in_array($cart.payment_id, $bluemedia_payment_ids)))}
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
            {if !$payment_method.processor_params.gateway_id && $addons['2lm_bm']['allow_select_gateway'] == 'Y'}
            {assign var="gateway_ids" value=fn_2lm_bm_get_gateway_ids()}

            {* Wybór form płatności online BM *}
            <div class="ty-control-group">
                <label for="bm-form-bank-input" class="ty-control-group__title cm-required">{__('2lm_bm_choose_onetime_payment_option')}</label>
            </div>

            <div id="bluemedia-gateways" class="bm-form-banks">

                <div class="bm-form-banks-group">
                    {if $payment.bluemedia_group_by_type == 'N'}
                        {foreach from=$payment.bluemedia_gateways item=gateway name=bmpayment}
                            {if !($gateway.gatewayID|in_array:$gateway_ids)}
                            <div class="bm-form-banks-item{if $payment.bluemedia_gateways|count == 1} active{/if}" data-id="{$gateway.gatewayID}">
                                <div class="bm-form-banks-item-logo">
                                    <img src="{$gateway.iconURL}" alt="{$gateway.gatewayName}" />
                                </div>
                                <div class="bm-form-banks-item-name">{$gateway.gatewayName}</div>
                            </div>
                            {if $payment.bluemedia_gateways|count == 1}
                            <script type="text/javascript">
                                (function(_, $) {
                                    $('input[name="payment_bluemedia_gateway"]').val({$gateway.gatewayID});
                                }(Tygh, Tygh.$));
                            </script>
                            {/if}
                            {/if}
                        {/foreach}

                    {else}

                        {foreach from=$payment.bluemedia_gateways item=ggroup key=gtype name=bmgatewaygroup}
                            {assign var="is_not_empty" value=false}
                            {foreach from=$ggroup item=gateway name=bmpayment}
                                {if !($gateway.gatewayID|in_array:$gateway_ids)}{assign var="is_not_empty" value=true}{break}{/if}
                            {/foreach}
                            {if $is_not_empty}
                            <div class="bm-form-gateway-types clearfix">
                                <div class="bm-form-bank-gateway-group-name">
                                    {if $gtype == 'PBL'}
                                        {assign var="gtype" value=__('2lm_bm_pbl')}
                                    {/if}
                                    <strong>{$gtype}</strong>
                                </div>

                                <div class="bm-form-banks-subgroup">
                                    {foreach from=$ggroup item=gateway name=bmpayment}
                                        {if !($gateway.gatewayID|in_array:$gateway_ids)}
                                        <div class="bm-form-banks-item" data-id="{$gateway.gatewayID}">
                                            <div class="bm-form-banks-item-logo">
                                                <img src="{$gateway.iconURL}" alt="{$gateway.gatewayName}" />
                                            </div>
                                            <div class="bm-form-banks-item-name">{$gateway.gatewayName}</div>
                                        </div>
                                        {/if}
                                    {/foreach}
                                </div>
                            </div>
                            {/if}
                        {/foreach}

                    {/if}
                </div>

            </div>
            <script type="text/javascript">
                (function(_, $) {
                    var controller = '{$runtime.controller}', mode = '{$runtime.mode}';

                    function bm_mark_bank_item() {
                        var form_bank = $('#bm-form-banks'), form_bank_input = $('input[name="payment_bluemedia_gateway"]'), banks_item = $('.bm-form-banks-item');
                        banks_item.off('click.bmi').on('click.bmi', function(banks_item) {
                            var obj = $(this),id = obj.data('id');
                            $('.bm-form-banks-group .active').removeClass('active');
                            obj.addClass('active');
                            form_bank_input.val(id);
                        });
                    }
                    function bm_blik_onclick_action() {
                        $('.bm-form-banks-item').off('click.bma').on('click.bma', function() {
                            var obj = $(this), id = obj.data('id');
                            var blik_id = {$smarty.const.BLUEMEDIA_GATEWAY_ID_BLIK0};
                            if (id == blik_id) {
                                var active_payment_tab_id = $('#payment_tabs li.active').attr('id');
                                if (typeof active_payment_tab_id !== "undefined") {
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
                                    var active_item = $('.litecheckout__payment-method .bm-form-banks-item.active').attr('id');
                                    // litecheckout_step_payment
                                    var blik = $('#blik_code');
                                    if (blik.length == 0) {
                                        var input_content =
                                                '<div class="ty-control-group">' +
                                                '    <label for="blik_code" class="ty-control-group__title cm-required">{__('2lm_bm_enter_blik_code')}:</label>' +
                                                '    <input type="text" maxlength="6" name="payment_blik_code" value="" id="blik_code" />' +
                                                '</div>';
                                        $('.litecheckout__payment-method .blik-code-wrapper').append(input_content);
                                    } else {
                                        $('.blik-code-wrapper').show();
                                    }
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
            {/if}

            <input type="hidden" id="payment_bluemedia_gateway" name="payment_bluemedia_gateway" value="{$payment_method.processor_params.gateway_id}" />
            <div class="clearfix"></div>

            <div class="blik-code-wrapper"></div>
        {/if}

    </div>
</div>
{/if}
