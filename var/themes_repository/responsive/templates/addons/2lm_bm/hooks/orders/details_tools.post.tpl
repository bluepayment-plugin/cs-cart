{if $addons.2lm_bm.order_subscription_enabled == 'Y'}
    {if !$bm_order_subscription_not_active}
        {include file="buttons/button.tpl" but_role="text"
            but_text=$bm_order_subscription_label
            but_href=$bm_order_subscription_url
            but_meta="{$open_in_new_window} ty-btn__text"
            but_icon="{$bm_order_subscription_icon} orders-print__icon"
        }
    {else}
        <span class="ty-btn ty-btn__text text-button">{$bm_order_subscription_label}</span>
    {/if}
{/if}
