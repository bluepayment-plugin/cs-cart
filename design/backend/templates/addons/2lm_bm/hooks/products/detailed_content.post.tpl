{include file="common/subheader.tpl" title=__("2lm_bm") target="#acc_bluemedia"}

<div id="acc_bluemedia" class="collapse in">
    <div class="control-group">
        <label class="control-label" for="bluemedia_exclude_from_recurring_purchases">{__("2lm_bm_exclude_from_recurring_purchases")}</label>
        <div class="controls">
            <select name="product_data[bluemedia_exclude_from_rp]" id="bluemedia_exclude_from_recurring_purchases">
                <option value="Y" {if $product_data.bluemedia_exclude_from_rp == "Y"}selected="selected"{/if}>{__('yes')}</option>
                <option value="N" {if $product_data.bluemedia_exclude_from_rp == "N" || $product_data.bluemedia_exclude_from_rp == ""}selected="selected"{/if}>{__('no')}</option>
            </select>
        </div>
    </div>
</div>
