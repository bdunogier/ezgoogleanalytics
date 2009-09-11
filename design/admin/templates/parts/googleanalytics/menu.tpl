{* Left-col menu for ws debugger
 *
 * @author O. PORTIER
 * @version $Id$
 * @copyright
 *}

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h4>{'Google Analytics'|i18n('extension/googleanalytics')}</h4>
{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">

<ul>
    <li> <a href={'googleanalytics/admin'|ezurl} onclick="var popup = window.open('{'googleanalytics/autologin'|ezurl(no)}','autologin','width=10,height=10,menubar=no,status=no,location=no,toolbar=no,scrollbars=no'); popup.focus(); sleep(5000); popup.close(); return true;">Auto-login</a></li>
    <li> <a href={'googleanalytics/admin'|ezurl}>Dashboard</a></li>
    <li> <a href={'googleanalytics/setup'|ezurl}>Settings</a></li>
</ul>

{* DESIGN: Content END *}</div></div></div></div></div></div>
