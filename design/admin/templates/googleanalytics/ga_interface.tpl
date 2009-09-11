{*
 @todo add support for passing GET params to both controller and action pages
 @todo add i18n of error message
 @todo auto vertical resizing of action frame (via js)
*}
{*
<!-- Auto login script -->
<script type="text/javascript">
    var popup = window.open('{'googleanalytics/autologin'|ezurl(no)}','autologin','width=10,height=10,menubar=no,status=no,location=no,toolbar=no,scrollbars=no');
    popup.focus();
</script>
*}
<div class="context-block">
{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h1 class="context-title">{'Google Analytics'|i18n('extension/googleanalytics')}</h1>
{* DESIGN: Mainline *}<div class="header-mainline"></div>
{* DESIGN: Header END *}</div></div></div></div></div></div>
{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

<iframe width="100%" height="600px" src="{ezini('AnalyticsSettings','GoogleAdminInterfaceURL','googleanalytics.ini')}{ezini('AnalyticsSettings','GoogleAdminInterfaceID','googleanalytics.ini')}" marginwidth="0" marginheight="0" frameborder="0" style="background-color:#ffffff;" scrolling="auto" name="googleanalytics">
    Browser does not support Iframes. Debugger disabled.
</iframe>


{* DESIGN: Content END *}</div></div></div>
<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
Footer
</div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
