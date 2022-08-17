<div class="group form-horizontal">
    <div class="control-group">
        <label class="control-label" for="bm_recurring_purchases">{__("2lm_bm_recurring_purchases")}</label>
        <div class="controls">
            <input type="checkbox" name="bm_recurring_purchases" id="bm_recurring_purchases" value="Y"{if $search.bm_recurring_purchases} checked="checked"{/if} />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="bm_refunds">{__("2lm_bm_refunds")}<br /><span class='muted'>(BlueMedia)</span></label>
        <div class="controls">
            <input type="checkbox" name="bm_refunds" id="bm_refunds" value="Y"{if $search.bm_refunds} checked="checked"{/if} />
        </div>
    </div>
</div>
