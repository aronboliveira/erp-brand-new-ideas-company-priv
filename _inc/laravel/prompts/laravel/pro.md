Following this pattern:

@php
use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC, StacksConstants};
use App\Models\Utility;
use Collective\Html\FormFacade as Form;
use Illuminate\Support\{Facades\Route, Str};

    $lang = Utility::fetchUserLang();
    $formId = 'store-stage-form';
    $stageCreateBaseName  = ViewsConstants::STG;
    $stageCreateKebabName = Str::kebab($stageCreateBaseName);
    $stageCreateResolved  = Route::has($stageCreateBaseName)
        ? $stageCreateBaseName
        : (Route::has($stageCreateKebabName) ? $stageCreateKebabName : null);
    $stageCreateActionUrl = $stageCreateResolved ? route($stageCreateResolved) : '#';
    $stageCreateGuardMsg  = Utility::fetchLinkMessage($lang, ViewsConstants::STG, 'store_stage_route_unavailable') ?? 'Store stage route is unavailable. Please contact technical support or your domain administrator.';

@endphp

{{ Form::open([
    'url'                => $stageCreateActionUrl,
    'method'             => 'post',
    'id'                 => $formId,
    'data-resolved-action' => $stageCreateActionUrl,
    'data-guard-msg'     => $stageCreateGuardMsg,
    'data-sv-localized'  => 'true',
]) }}

<div class="modal-body">
<div class="{{ VC::RW }}">
<div class="{{ VC::FM_G }} {{ VC::C12 }}">
{{ Form::label('name', __('Stage Name'), ['class' => VC::FM_LB]) }}
{{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
</div>
<div class="{{ VC::FM_G }} {{ VC::C12 }}">
{{ Form::label('pipeline_id', __('Pipeline'), ['class' => VC::FM_LB]) }}
{{ Form::select('pipeline_id', $pipelines, null, ['class' => VC::FM_CT_SL . ' select2', 'required' => 'required']) }}
</div>
</div>
</div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>

{{ Form::close() }}

<script defer src="{{ asset('js/routes/stages/store.js') }}"></script>

// in a separate js file:

(() => {
try {
const f = document.getElementById("store-stage-form");
if (!f || f.getAttribute("data-listener-active") === "true") return;
f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") || "#";
    if (
      f.hasAttribute("action") &&
      f.getAttribute("action") === "#" &&
      resolved !== "#"
    ) {
      f.setAttribute("action", resolved);
    }

    f.addEventListener("submit", e => {
      try {
        const action = f.getAttribute("action") || "#";
        if (action !== "#") return;
        e.preventDefault();

        const msg =
          f.getAttribute("data-guard-msg") ||
          "Store stage route is unavailable. Please contact technical support or your domain administrator.";
        const bsLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          document.body.appendChild(container);
        }

        if (bsLink && typeof window.bootstrap !== "undefined") {
          const toast = document.createElement("div");
          toast.className = "toast";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");

          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent = msg;

          toast.appendChild(body);
          container.appendChild(toast);
          bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }

        f.setAttribute("data-failed-route", "true");
      } catch (err) {}
    });

} catch (error) {}
})();

---

And these directives:

<instructions>
	- FOREACH route assigned, create a guard check using Route::has (in a PHP block), that fallbacks to # (or ['#'], if the argumentation demands an array, such as in dynamic routing);
	- For routes with composite names (ex.: chart_of_accounts, or constant such as AB_CD), check for the kebab-case version as well, following this example:
		```
			$markAttendanceRoute = Route::has(ViewsConstants::EMP_ATD.'.index')
					? route(ViewsConstants::EMP_ATD.'.index')
					: (Route::has(Str::kebab(ViewsConstants::EMP_ATD.'.index'))
					? route(Str::kebab(ViewsConstants::EMP_ATD.'.index'))
					: '#');
		```
	- Prepare a script tag with the preparation of a javascript event listener (always checking if the element has data-[event-alias] === true, for preventing multiple multiple additions of the same listener due to rerendering, such as done by React, and setting it as true right after adding it);
	- ALWAYS use getAttribute, hasAttribute and setAttribute (DO NOT use dot chaining for dataset attributes);
	- IF the procedure does not use PHP statements or method calls (that is, only javascript by itself), The script tag should source from asset('assets/js/routes/[/* here the name of the set, such as projectReports, always in camelCase */, [/* CRUD action or method alias */]]'), ELSE (ex.: in the case of Utility:: calls), the script should either a. IF the necessary parameters are safe to be passed to the client, then just save them as local storage, session storage or dataset attributes (according to the complexity) and still create the js asset with the logic to refetch them, ELSE write in the Blade file block directly;
	- IF using a javascript file, DO NOT create a single javascript file for all calls. It should a unique file for each route, with the name of the action, such as create.js, update.js, delete.js, etc., and the file should be placed in the assets/js/routes/[/* here the name of the set, such as projectReports, always in camelCase and plural */] folder;
	- The javascript source files or script tags should have an IIFE with ES6+ syntax, and use defensive programming techniques to prevent any possible crash in the UI, such as nullish coalescence, falsish guard clauses and try/catches;
	- Write the scripts in a separate block within a @push(StacksConstants::ADM_SCRP_PG), so I can place them separately;
	- It is not necessary to test routes that are used for scrolling (e.g.: #top, #bottom, etc.), as they are not used for any CRUD action, but only for scrolling the page to a specific element;
	- IF the route has defaulted to # (both in .href and the equivalent dataset attr, e.g.: const url = l.getAttribute("data-url"); if (url !== "#" || /* el.href !== "#" in case of anchors, el.action !== '#' in case of form elements */) return; ...procedure...), The IIFE should alert the user that the route is unavailable (declaring that the [route-name] is unavailable, e.g.: 'Create leave type route is unavailable. Please contact technical support or your domain administrator.' ), and then query for the bootstrap's UI link existence in the page, and if it exists, mount a bootstrap toast alerting the user the procedure has failed; if the query fails, just use javascript's native alert call... in these cases, regardless of using alert or a bootstrap toast, there should be a call for [element variable].setAttribute('data-failed-route', 'true') so it's easier to identify elements that have failed routes in the page;
	- NEVER use data-guard-url unless it's already found in th element at the same time as  data-url, as it is not a standard attribute and may cause issues with some browsers or libraries. Instead, use data-url for the route URL and data-guard-url only if it is necessary to differentiate between the two URLs for some reason;
	- For the message strings that are mounted for the user (nested between __()) AS A RESPONSE TO LINk/ANCHOR EVENTS ONLY (never for any other contexts already defined as simple strings, such as labels), call Utility::fetchLinkMessage($lang, [ /* here the namespace of the instead declared preferably with views constants IF found, such as ViewsConstants::PRJ_RPT */, /*here your proposed key for the message, following the syntax: [action]_[snake_cased_set in singular, such as project_report]_unavailable */), falling back (with ??) directly into the american english version of the string;
	- Try isolating PHP long procedures, such as conditional assignments, into @php blocks;
	- Always indent content nested in @php and script tags, so they can be folded in editors;
	- Always save repetitive strings (such as dataset values) in variables, so they can be more easily maintainable;
	- Always end lines with semi-colons;
	- DO NOT write try/catch blocks without curly brackets (this is a syntax error);
	- DO NOT omit anything for brevity;
	- DO NOT call use for anything unless explicitly told so, using only comments for the imports as suggestions if you consider that is not already in use;
	- Don't insert comments;
	- Do not include more than one newline after each line end;
	- Remember to indent the script tag in the @push block;
	- Remember to indent the IIFE in the script tag as well;
	- Consider defering or asyncing the scripts if they are not essential for the initial page load;
	- For routes that define the guard message through Utility::fetchLinkMessage, mark them with data-sv-localized="true";
	- If there is NO clear need for keeping the script in the content of a tag (ex.: looping with an id, using secrets or server-locked variables for operating parameters), consider moving it to a separate file;
</instructions>

    <instructions>
    	- Always sort imports alphabetically at all levels;
    	- Use a tab spacing for all lines inside of the @php/@endphp block;
    	- Give a more log errors, with details about the files, the class of the error, etc, but DO NOT add stack tracing
    	- Never use more than one newline to separate lines, EXCEPT for breaking 80+ columns lines;
    	- Don't add comments;
    	- I will be giving you php blocks that you must initialize the variables with their desired types as defaulted variations (with ??=, NOT =), and then start the procedures WITHIN try/catches to give their proper values (always with coalescence);
    	- Include different types of exception catches if you see windows for diverse ones (besides the general \Throwable), including the corresponding import;
    	- Always use {} spreading for the imports, preventing repetition;
    	- The intention is to prevent at all costs that we get a sever crash by a null pointer leaking or an error being thrown without being catched;
    	- DO NOT import Error or Exception, just call them by \Error and \Exception;
    </instructions>

- Refactor applying safeguards with isset(), empty(), nullish coalescence/checks and, for multidepth chains of property (2+), using data_get()

- IF the string is meant to reach the html to be sent to the client view, default the failed values to some sort of variation of **('Could not find [alias for the property]'), **('Failed to get [alias for the property']), \_\_('No [alias for the property] available'), etc

---

Refactor this block:

{{ Collective\Html\FormFacade::model($stage, array('route' => array(ViewsConstants::STG.'.update', $stage->id), 'method' => 'PUT')) }}

<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('name', __('Stage Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-12">
            {{ Collective\Html\FormFacade::label('pipeline_id', __('Pipeline'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('pipeline_id', $pipelines,null, array('class' => 'form-control select2','required'=>'required')) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>

{{Collective\Html\FormFacade::close()}}

- Always import Collective\Html\FormFacade as Form and use the alias instead of the full path;
- For $lang, you don't need to assign with ??=; rather, directly assign it via the same method;
- For variables that are not already expected to be in the blade, you can directly assign with = instead of ??=; use the nullish coalescence assignment only for variables that were already mentioned in the version I sent you;
- Ids should NEVER be casted to ints - they are uuids;
- Note cases where the edit and delete routes are inside a loop, so each anchor for each item will have its own script, therefore they should be pushed to the stack of scripts individually, thus written in the blade itself (since the ids rely on the php loop). The create ones, though, usually do not have this restriction.
