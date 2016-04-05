<h1>{$entry["name"]}</h1>
<p>{$entry["description"]}</p>

{space15}

<dl>
	{foreach from=$entry["stats"] key=label item=data}
		<dt>
			<h3>{$label}</h3>
		</dt>
		<dd>
			<ul>
				{foreach from=$data item=datum}
					<li>{$datum}</li>
				{/foreach}
			</ul>
		</dd>
	{/foreach}
</dl>

{space15}

<center>
	{button href="STARWARS" caption="Otros t&iacute;tulos"}
</center>