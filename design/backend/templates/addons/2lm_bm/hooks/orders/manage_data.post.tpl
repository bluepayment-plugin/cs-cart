{if $addons.2lm_bm.order_subscription_enabled == 'Y'}
<td>
    {if !empty($o.bm_recurring_purchases)}
        {__('yes')}: <span class="{if $o.bm_recurring_purchases == 'activated'}bm_rc_activated{elseif $o.bm_recurring_purchases == 'disactivated'}bm_rc_disactivated{else}bm_rc_pending{/if}">
            {$o.bm_recurring_purchases}
        </span>
    {/if}
    {if !empty($o.bm_base_order_id)}
        {__('2lm_bm_base_order')}: <a href="{"orders.details?order_id=`$o.bm_base_order_id`"|fn_url}">{$o.bm_base_order_id}</a>
    {/if}
</td>
{/if}
