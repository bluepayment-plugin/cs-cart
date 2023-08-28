<style>
    .hidden.ui-dialog-content.ui-widget-content {
        height: 170px !important;
    }
    .object-container {
        padding-top: 30px;
        padding-bottom: 30px;
    }
</style>
<form action="{""|fn_url}" method="post" name="bluemedia_refund_form" id="bluemedia-refund-form" class="form-horizontal">
    {if !$refund_balance_info}
    <div>
        <div class="control-group">
            <input type="hidden" name="refund[order_id]" value="{$refund_order_info.order_id}" />
            <input type="hidden" name="refund[remote_id]" value="{$refund_order_info.remote_id}" />
            <label class="control-label" for="refund_amount">{__('2lm_bm_refund_amount')}:</label>
            <div class="controls">
                <input type="text" name="refund[amount]" id="refund_amount" value="{$refund_order_info.amount}" />
            </div>
        </div>
    </div>
        {$hide_first_button=false}
    {else}
        {__('2lm_bm_cannot_refund_money', ['[BALANCE_VALUE]' => $refund_balance_info.balance])}
        {$hide_first_button=true}
    {/if}

    <div class="buttons-container buttons-bg cm-toggle-button">
        {include file="buttons/save_cancel.tpl" but_text="{__('refund')}" but_name="dispatch[2lm_bm.do_refund]"
            cancel_action="close" but_role="action-link"
            hide_first_button=$hide_first_button
            hide_second_button=false

        }
    </div>
</form>

{assign var="text_bluemedia_amount_alert" value=__("2lm_bm_refund_amount_alert", ["[min]" => 0, "[max]" => $refund_order_info.amount])}
<script>
(function(_, $) {
    $(document).ready(function() {
        function fn_2lm_bm_validation(message) {
            return confirm(message);
        }

        _.tr('2lm_bm_message', '{$text_bluemedia_amount_alert|escape:"javascript"}');

        $.ceEvent('on', 'ce.formpre_{$form_name|default:"bluemedia_refund_form"}', function (frm, elm) {
            var max = {$refund_order_info.amount|escape:javascript nofilter};
            var min = 0;
            var amount = parseFloat($('#refund_amount').val());
            if (amount != 'NaN' && (amount <= max) && (amount > min)) {
                return true;
            }

            $.ceNotification('show', {
                 type: 'E',
                 title: _.tr('warning'),
                 message: _.tr('2lm_bm_message'),
                 message_state: 'I'
             });

            return false;


        });
    });
}(Tygh, Tygh.$));
</script>