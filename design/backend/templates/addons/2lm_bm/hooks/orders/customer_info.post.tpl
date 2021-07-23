{*{assign var="order_subscriptions" value=$order_info.order_id|fn_2lm_bm_get_order_subscriptions_data}*}
{$order_subscriptions=$order_info.order_id|fn_2lm_bm_get_order_subscriptions_data}
{if !empty($order_subscriptions)}
	{include file="common/subheader.tpl" title=__("2lm_bm_label_auto_order_transaction_list")}

	<div id="content_bluemedia_subscriptions">
		<div class="orders-container">
			<table class="table table-middle table-striped table-condensed">
				<tr>
					<th>{__("2lm_bm_new_order_id")}</th>
					<th>{__("creation_date")}</th>
				</tr>
            {foreach from=$order_subscriptions item="order"}
				<tr>
					<td><a href="{"orders.details?order_id=`$order.order_id`"|fn_url}">{$order.order_id}</a></td>
					<td>{$order.timestamp}</td>
				</tr>
            {/foreach}
			</table>
		</div>
	</div>
{/if}

{*{assign var="base_order_subscriptions" value=$order_info.order_id|fn_2lm_bm_get_base_order_subscriptions_data}*}
{if !empty($base_order_subscriptions)}
    {include file="common/subheader.tpl" title=__("2lm_bm_label_base_order")}

	<div id="content_bluemedia_base_order_subscription">
		<div class="orders-container">
			{__('2lm_bm_base_order')}: <a href="{"orders.details?order_id=`$base_order_subscriptions.base_order_id`"|fn_url}">{$base_order_subscriptions.base_order_id}</a> ({$base_order_subscriptions.timestamp})
		</div>

	</div>
{/if}
