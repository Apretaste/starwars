<h1>{$article["title"]}</h1>
<small><font color="grey">{$article["date"]}</font></small>

{space15}

<div>
	{foreach from=$article["content"] item=p}
		<p>{$p}</p>
	{/foreach}
</div>

{space15}

<center>
	{button href="STARWARS" caption="Otras noticias"}
</center>