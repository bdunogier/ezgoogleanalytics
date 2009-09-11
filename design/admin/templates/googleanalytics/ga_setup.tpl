<div class="context-block">
{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h1 class="context-title">{'Google Analytics settings'|i18n('extension/googleanalytics/setup')}</h1>
{* DESIGN: Mainline *}<div class="header-mainline"></div>
{* DESIGN: Header END *}</div></div></div></div></div></div>

<form action={'googleanalytics/setup'|ezurl} method="POST">

{* DESIGN: Content START*}<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-attributes">
    <div class="block">
            <label for="TrackerID">{'Tracker ID'|i18n('extension/googleanalytics/setup')}:</label>
            <input type="text" size="40" name="TrackerID" id="TrackerID" value="{$trackerid|wash}" class="halfbox" />
    </div>
     <div class="block">
            <label for="TrackingCode">{'Tracking Code'|i18n('extension/googleanalytics/setup')}:</label>
            <select name="TrackingCode">
                <option value="ga"{if eq($trackingcode, 'ga' ) } selected{/if}>ga</option>
                <option value="urchin"{if eq($trackingcode, 'urchin' ) } selected{/if}>urchin</option>
            </select>
    </div>
     <div class="block">
            <label for="InterfaceID">{'Interface ID'|i18n('extension/googleanalytics/setup')}:</label>
            <input type="text" size="40" name="InterfaceID" id="InterfaceID" value="{$interfaceid|wash}" class="halfbox" />
    </div>
     <div class="block">
            <label for="Username">{'Username'|i18n('extension/googleanalytics/setup')}:</label>
            <input type="text" size="40" name="Username" id="Username" value="{$username|wash}" class="halfbox" />
    </div>
     <div class="block">
            <label for="Password">{'Password'|i18n('extension/googleanalytics/setup')}:</label>
            <input type="text" size="40" name="Password" id="Password" value="{$password|wash}" class="halfbox" />
    </div>
</div>

{* DESIGN: Content START*}</div></div></div>

{* DESIGN: START Form Footer *}
<div class="controlbar">
{* DESIGN: START Footer Box *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
    <div class="block">
        <input class="button" type="submit" title="Save changes" value="Save" name="SubmitButton"/>
    </div>
{* DESIGN: END Footer Box *}</div></div></div></div></div></div>
</div>
{* DESIGN: END Form Footer *}
</form>
</div>
<div class="break"></div>
