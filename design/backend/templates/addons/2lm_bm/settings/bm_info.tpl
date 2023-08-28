<div class="bm-info-containter" id="bm_container">
    <div class="title">{__('2lm_bm_info_how_to_start')}<span class="bm-info-close">X</span></div>
    <div class="first-row">
        <div class="logo">
            <img src="{$images_dir}/addons/2lm_bm/autopay_logo.png" height="60" alt="Autopay" />
        </div>
        <div class="column2">
            <span class="thicker"><img src="{$images_dir}/addons/2lm_bm/thick3.png" height="15" alt="" /></span>
            <a href="https://portal.autopay.eu/e2b19483-7643-418d-a66e-6bc1516f569b/?pk_campaign=cscart_panel&pk_source=cscart_panel&pk_medium=cta" target="_blank">{__('2lm_bm_info_commission_of')}</a>
        </div>
        <div class="column3">
            <span class="thicker"><img src="{$images_dir}/addons/2lm_bm/thick3.png" height="15" alt="" /></span>
            {__('2lm_bm_info_prepare_shop_regulations')}<br ><a href="https://developers.autopay.pl/legal-geek?mtm_campaign=cscart_legalgeek&mtm_source=cscart_backoffice&mtm_medium=cta" target="_blank">{__('2lm_bm_info_find_out_more')}</a>
        </div>
    </div>
    <div class="steps">
        <div class="step1">
            <span class="circle">1</span>
            {__('2lm_bm_info_create_account')}
            <a href="https://portal.autopay.eu/e2b19483-7643-418d-a66e-6bc1516f569b/?pk_campaign=cscart_panel&pk_source=cscart_panel&pk_medium=cta" target="_blank">{__('2lm_bm_info_register')}</a>
        </div>
        <div class="step2">
            <span class="circle">2</span>
            {__('2lm_bm_info_enter_your_details')}
        </div>
        <div class="step3">
            <span class="circle">3</span>
            {__('2lm_bm_info_set_up_payments')}
        </div>
    </div>
    <div class="read-more"><a href="https://developers.autopay.pl/online/wdrozenie-krok-po-kroku?mtm_campaign=cscart_developers_aktywacja_platnosci&mtm_source=cscart_backend&mtm_medium=hyperlink" target="_blank">{__('2lm_bm_info_find_out_more')}</a> {__('2lm_bm_info_find_out_more_rest')}</div>
    <div class="read-more">Nasze elementy graficzne do wstawienia w Twoim sklepie możesz pobrać stąd: <a href="https://developers.autopay.pl/online/grafiki" target="_blank">{__('download')}</a></div>
</div>
<script type="text/javascript">
    Tygh.$('.bm-info-close').click(function () {
        Tygh.$('#bm_container .first-row').toggle();
        Tygh.$('#bm_container .steps').toggle();
        Tygh.$('#bm_container .read-more').toggle();
    } );
</script>