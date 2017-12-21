<?php

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

class StarWars extends Service
{
	public $client = null;
	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 */
	public function _main(Request $request)
	{
		$response = new Response();
		if (empty($request->query))
		{
			$response->setCache("day");
			$starWarsSections = $this->starWarsContentSections();
			$subject = "Noticias de Star Wars";
			$template_name = "home.tpl";
			$template_variables = array("sections" => $starWarsSections);
		}
		elseif (strpos($request->query, "/banco-de-datos/") === false)
		{
			$response->setCache();
			$article = $this->starWarsArticleContent($request->query);
			$subject = "Star Wars: " . $article["title"];
			$template_name = "article.tpl";
			$template_variables = array("article" => $article);
		}
		else
		{
			$response->setCache();
			$entry = $this->starWarsDatabaseContent($request->query);
			$subject = "Star Wars: " . $entry["name"];
			$template_name = "database_entry.tpl";
			$template_variables = array("entry" => $entry);
		}

		$response->setResponseSubject($subject);
		$response->createFromTemplate($template_name, $template_variables);
		return $response;
	}

	protected static $base_url = "http://latino.starwars.com";

	/**
	 * Crawls http://latino.starwars.com and returns article sections.
	 *
	 * @return array[]
	 */
	protected function starWarsContentSections() {
		$crawler = $this->getCrawler();

		// search for result
		$sections = array();
		$current_section = null;

		$crawler
			->filter(".module.bound")
			->each(function ($section) use (&$sections, &$current_section) {
				$title = $section->filter(".module_title");

				if ($title->count()) {
					if ($current_section !== null && $current_section["title"] != "EVENTOS //") {
						$sections[] = $current_section;
					}

					$current_section = array(
						"title" => $title->text(),
						"articles" => array()
					);
				}

				if ($current_section["title"] == "EVENTOS //") {
					return;
				}

				$section
					->filter(".building-block")
					->reduce(function ($article) {
						$category = $article->filter(".category-info .category-name")->text();
						return $category != "Video";
					})
					->each(function ($article) use (&$current_section) {
						$desc_html = $article->filter(".desc-sizer .desc");
						$title_html = $article->filter(".title a");

						$description = "";

						if ($desc_html->count()) {
							$description = $desc_html->text();
						}

						$current_section["articles"][] = array(
							"title" => $title_html->text(),
							"description" => $description,
							"category" => $article->filter(".category-info .category-name")->text(),
							"url" => $this->getArticleUrl($title_html->attr("href"))
						);
					});
			});

		if ($current_section["title"] != "EVENTOS //") {
			$sections[] = $current_section;
		}

		return $sections;
  }


	/**
	 * Returns Apretaste subject for article URL (either WEB service or internal).
	 *
	 * @param string
	 * @return string
	 */
	protected function getArticleUrl ($url) {
		$prefix = self::$base_url;

		if (strpos($url, $prefix) !== false) {
			$url = "STARWARS " . substr($url, strlen($prefix));
		} else {
			$url = "WEB " . $url;
		}

		return urlencode($url);
	}


	protected function starWarsArticleContent ($url) {
		$crawler = $this->getCrawler($url);

		$category_and_date = explode(" // ", $crawler->filter(".article-date")->text());
		$content = array();

		$crawler->filter(".entry-content p")->each(function ($p) use (&$content) {
			$content[] = $p->text();
		});

		return array(
			"title" => $crawler->filter(".entry-title")->text(),
			"content" => $content,
			"category" => $category_and_date[0],
			"date" => $category_and_date[1]
		);
	}


	protected function starWarsDatabaseContent ($url) {
		$crawler = $this->getCrawler($url);

		$featured = $crawler->filter(".featured_single");

		$stats = array();

		$crawler->filter(".stats-container .category")->each(function ($cat) use (&$stats, $labels) {
			$category_values = array();

			$cat->filter("li")->each(function ($item) use (&$category_values) {
				$category_values[] = trim($item->text(), " ,\n\r");
			});

			$heading = $cat->filter(".heading")->text();

			$stats[$heading] = $category_values;
		});

		return array(
			"name" => $featured->filter(".title")->text(),
			"description" => $featured->filter(".desc")->text(),
			"stats" => $stats
		);
	}


	/**
	 * Crawler client
	 *
	 * @return \Goutte\Client
	 */
	public function getClient()
	{
		if (is_null($this->client))
		{
			$this->client = new Client();
			$guzzle = new GuzzleClient(["verify" => false]);
			$this->client->setClient($guzzle);
		}
		return $this->client;
	}

	/**
	 * Get crawler for URL
	 * 
	 * @param string $url
	 *
	 * @return \Symfony\Component\DomCrawler\Crawler
	 */
	protected function getCrawler ($url = "") {
		$url = trim($url);
		if ($url[0] == '/') $url = substr($url, 1);

		$crawler = $this->getClient()->request("GET", self::$base_url . "/$url");

		return $crawler;
	}
}
