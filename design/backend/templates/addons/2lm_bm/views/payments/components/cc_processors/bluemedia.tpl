<div class="control-group">
    <label class="control-label" for="2lm_bm_mode">Tryb:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="2lm_bm_mode">
            <option value="sandbox"{if $processor_params.mode == 'sandbox'} selected="selected"{/if}>Testowy</option>
            <option value="live"{if $processor_params.mode == 'live'} selected="selected"{/if}>Produkcyjny</option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="2lm_bm_description">{__("2lm_bm_description")} <span class="muted">(dopuszczalne znaki alfanumeryczne oraz: . : / - , spacja)</span>:</label>

    <div class="controls">
        <input type="text" name="payment_data[processor_params][bm_description]" id='2lm_bm_description' size="60" value="{$processor_params.bm_description}" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="2lm_bm_gateway_id">ID bramki:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][gateway_id]" id='2lm_bm_gateway_id' size="60" value="{$processor_params.gateway_id}" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="2lm_bm_service_id">ID usługi:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][service_id]" id='2lm_bm_service_id' size="60" value="{$processor_params.service_id}" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="2lm_bm_api_key">Klucz:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][api_key]" id='2lm_bm_api_key' size="60" value="{$processor_params.api_key}" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="2lm_bm_separator">Separator:</label>
    <div class="controls">
        <select name="payment_data[processor_params][separator]" id="2lm_bm_separator">
            <option value="|"{if $processor_params.separator == '|'} selected="selected"{/if}>|</option>
            <option value=""{if $processor_params.separator == ''} selected="selected"{/if}></option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="2lm_bm_algorithm">Algorytm:</label>
    <div class="controls">
        <select name="payment_data[processor_params][algorithm]" id="2lm_bm_algorithm">
            <option value="SHA256"{if $processor_params.algorithm == 'SHA256'} selected="selected"{/if}>SHA256</option>
            <option value="MD5"{if $processor_params.algorithm == 'MD5'} selected="selected"{/if}>MD5</option>
            <option value="SHA-1"{if $processor_params.algorithm == 'SHA-1'} selected="selected"{/if}>SHA-1</option>
            <option value="SHA-2"{if $processor_params.algorithm == 'SHA-2'} selected="selected"{/if}>SHA-2</option>
        </select>
    </div>
</div>

{include file="common/subheader.tpl" title=__("2lm_bm_status_map") target="#2lm_bm_status_map"}

{assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}

<div id="2lm_bm_status_map" class="in collapse">

    <div class="control-group">
        <label class="control-label" for="2lm_bm_payment_start">{__("2lm_bm_status_ok_before_post_from_bm")}:</label>

        <div class="controls cm-required">
            <select name="payment_data[processor_params][statuses][start]" id="2lm_bm_payment_start">
                <option value=""> ---------- </option>
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.start) && $processor_params.statuses.start == $k)}selected="selected"{/if}>{$s}</option>
            {/foreach}
            </select>
        </div>
    </div>


    <div class="control-group">
        <label class="control-label" for="2lm_bm_payment_pending_status">{__("2lm_bm_payment_pending")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][pending]" id="2lm_bm_payment_pending_status">
                <option value=""> ---------- </option>
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}"{if (isset($processor_params.statuses.pending) && $processor_params.statuses.pending == $k)} selected="selected"{/if}>{$s}</option>
            {/foreach}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="2lm_bm_payment_success_status">{__("2lm_bm_payment_success")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][success]" id="2lm_bm_payment_success_status">
                <option value=""> ---------- </option>
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}"{if (isset($processor_params.statuses.success) && $processor_params.statuses.success == $k)} selected="selected"{/if}>{$s}</option>
            {/foreach}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="2lm_bm_payment_failure_status">{__("2lm_bm_payment_failure")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][failure]" id="2lm_bm_payment_failure_status">
                <option value=""> ---------- </option>
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}"{if (isset($processor_params.statuses.failure) && $processor_params.statuses.failure == $k)} selected="selected"{/if}>{$s}</option>
            {/foreach}
            </select>
        </div>
    </div>

</div>

{include file="common/subheader.tpl" title=__("2lm_bm_urls") target="#2lm_bm_urls"}

<div id="2lm_bm_urls" class="in collapse">
    <div class="control-group">
        <label class="control-label">{__("2lm_bm_url_return")}:</label>
        <div class="controls">
        {$config.current_location}/{$config.customer_index}?dispatch=payment_notification.return&payment=bm
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{__("2lm_bm_url_itn")}:</label>
        <div class="controls">
            {$config.current_location}/{$config.customer_index}?dispatch=payment_notification.notify.itn&payment=bm
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{__("2lm_bm_url_rpan")}:</label>
        <div class="controls">
            {$config.current_location}/{$config.customer_index}?dispatch=payment_notification.notify.rpan&payment=bm
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{__("2lm_bm_url_rpdn")}:</label>
        <div class="controls">
            {$config.current_location}/{$config.customer_index}?dispatch=payment_notification.notify.rpdn&payment=bm
        </div>
    </div>
</div>