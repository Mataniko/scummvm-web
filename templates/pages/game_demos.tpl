<script type="text/javascript" src="/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="/js/game_demos.js"></script>

{capture "intro"}
  <div class="row">
		<div class="navigation col-1-2">
			<h4 class="subhead">{#gamesDemosHeading#}</h4>
			<ul>
			{foreach from=$demos item=group}
				<li><a href="/demos/#{$group.href}">{$group.name}</a></li>
			{/foreach}
			</ul>
		</div>
		<div class="text col-1-2">
			<p>
				{#gamesDemosContentP1#}
			</p>
			<p>
				{#gamesDemosContentP2#}
			</p>
		</div>
	</div>
{/capture}

{capture "content"}
  {foreach from=$demos item=group}
  <table class="chart color4 gameDemos" id="{$group.href}">
    <caption>{$group.name}</caption>
    <thead>
      <tr class="color4">
        <th>{#gamesDemosH1#}</th>
        <th class="gameTarget">{#gamesDemosH2#}</th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$group.demos item=demo}
      <tr class="{cycle values="color2, color0"}">
        <td>
          <a href="{$demo->getURL()}">{$demo->getName()}</a>
        </td>
        <td class="gameTarget">{$demo->getTarget()}</td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  {/foreach}
{/capture}

{include file="components/box.tpl" head=$content_title intro=$smarty.capture.intro content=$smarty.capture.content}
