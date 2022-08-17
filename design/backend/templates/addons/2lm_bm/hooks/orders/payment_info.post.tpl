{if mb_strtolower($order_info.payment_method.processor) == 'BlueMedia' && in_array($subscr_status, ['activated', 'disactivated'])}
<div id="bluemedia_order_subscription_info">
    {if $subscr_status == 'activated'}
        {include file="common/subheader.tpl" title=__("2lm_bm_label_auto_order_activated")}

        <div id="subscription-deactivate-wrapper">
            <a href="{"2lm_bm.subscription.disactivate&order_id=`$order_info.order_id`"|fn_url}"
               data-ca-confirm-text="Czy na pewno chcesz wyłączyć usługę płatności automatycznych dla tego zamówienia?"
               class="cm-confirm btn">
                {__('2lm_bm_label_auto_order_deactivate')}
            </a>
        </div>

        <div id="subscription-payment-wrapper">
            <a href="{"2lm_bm.subscription.get_payment&order_id=`$order_info.order_id`"|fn_url}"
               data-ca-confirm-text="Czy na pewno chcesz pobrać pieniądze dla tego zamówienia?"
               class="cm-confirm btn btn-primary">
                {__('2lm_bm_label_auto_order_get_payment')}
            </a>
            {*<br />
            <span class="muted">(Szczegóły dotyczące pobierania płatności można zobaczyć w zakładce {__('addons')}</span>*}
        </div>

    {elseif $subscr_status == 'disactivated'}
        {include file="common/subheader.tpl" title=__("2lm_bm_label_auto_order_deactivated")}
    {/if}
<!--bluemedia_order_subscription_info--></div>
{/if}

<div id="bluemedia_refund_order_info">
    {if mb_strtolower($order_info.payment_method.processor) == 'BlueMedia' && $has_access_to_refund && $allow_refund}
        <a href="{"2lm_bm.refund&order_id=`$order_info.order_id`&amount=`$order_info.total`&remote_id=`$order_info.payment_info.remoteID`"|fn_url}"
           data-ca-dialog-title="{__('2lm_bm_refund')}"
           data-ca-confirm-text="{__("2lm_bm_refund_security")}"
           class="cm-dialog-opener btn cm-confirm btn btn-primary" style="padding: 4px 6px;">
            {__('2lm_bm_refund')}
        </a>
    {/if}

    {if $refund_order_info}
        <fieldset class="bm-refunds-list">
            <label class="underline strong">{__('2lm_bm_refunds')}:</label>
            <table class="table table-middle">
                <thead>
                    <tr>
                        <th>{__('time')}</th>
                        <th>{__('amount')}</th>
                        <th>Admin</th>
                    </tr>
                </thead>
                <tbody>
                {foreach from=$refund_order_info item=refund}
                    <tr>
                        <td>{$refund.timestamp}</td>
                        <td>{$refund.amount}</td>
                        <td>
                            {$admin_name = $refund.user_id|fn_get_user_name}
                            {$initials = $admin_name|fn_2lm_bm_get_user_initials}
                            <a href="{"profiles.update&user_id=`$refund.user_id`&user_type=A"|fn_url}" title="{$admin_name}">{if !empty($initials)}{$initials}{else}{$refund.user_id}{/if}</a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
                <tfoot>
                    <tr class="strong">
                        <td>{__('summary')}</td>
                        <td>{$refund_total}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </fieldset>
    {/if}
<!--bluemedia_refund_order_info--></div>
