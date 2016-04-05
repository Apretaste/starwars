<h1>Noticias de Star Wars</h1>

{space15}

{foreach from=$sections item=s}
	<h2>{str_replace("//", "", $s["title"])|upper}</h3>
		<ul>
			{foreach from=$s["articles"] item=a}
				<li>
					<p>
						<b>{link href=$a["url"] caption=$a["title"]}</b><br/>
						{$a["description"]}{if not empty({$a["description"]})}<br/>{/if}
						<small><font color="grey">Categoría: {$a["category"]}</font></small>
					</p>
					
				</li>
			{/foreach}
		</ul>
	</li>
	{space15}
{/foreach}
