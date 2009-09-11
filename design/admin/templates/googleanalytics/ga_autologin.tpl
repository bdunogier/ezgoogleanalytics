<!-- Thanks to AskApache contribution : http://www.askapache.com/webmaster/login-google-analytics.html -->
<form style="visibility:hidden;" name="googleanalyticslogin" action="https://www.google.com/accounts/ServiceLoginBoxAuth">
<input type="text" name="Email" class="gaia le val" id="Email" size="18" value="{ezini('AnalyticsSettings','Username','googleanalytics.ini')}" />
<input type="password" name="Passwd" class="gaia le val" id="Passwd" size="18" value="{ezini('AnalyticsSettings','Password','googleanalytics.ini')}" />
<input type="checkbox" name="PersistentCookie" value="yes" />
<input type="hidden" name="rmShown" value="1" />
{*<input type="hidden" name="continue" value="{ezini('AnalyticsSettings','GoogleAdminInterfaceURL','googleanalytics.ini')}{ezini('AnalyticsSettings','GoogleAdminInterfaceID','googleanalytics.ini')}" />*}
<input type="hidden" name="service" value="analytics" />
<input type="hidden" name="nui" value="1" />
<input type="hidden" name="hl" value="fr" />
<input type="hidden" name="GA3T" value="oCGYxIWWGUE" />
<input type="submit">
</form>

<script type="text/javascript">
document.googleanalyticslogin.submit();
</script>


