<?php
require_once('Objects/BasicObject.php');
require_once('vendor/erusev/parsedown/Parsedown.php');

/**
 * The news class represents a news item on the website.
 */
class News extends BasicObject {
	private $_title;
	private $_date;
	private $_author;
	private $_image;
	private $_content;
  private $_filename;
  private static $_parsedown;

	/**
	 * News object constructor that extracts the data from the JSON scheme
	 * used. The format looks like this:
	 *
   * {
   *    "title": "Article Title",
   *    "content": "Your article content goes here",
   *    "date": "UNIX timestamp",
   *    "author": "Name"
   * }
	 *
	 *
	 */
	public function __construct($data, $filename, $processContent = false) {
    if (!$_parsedown) {
      $_parsedown = new Parsedown();
    }
		$this->_title = $processContent ? $this->processText($data->title) : $data->title;
		$this->_date = $data->date;
		$this->_author = $data->author;
		$this->_content = $processContent ? $this->processText($_parsedown->text($data->content)) : $_parsedown->text($data->content);
		$this->_filename = basename($filename);
	}

	/**
	 * Search and replace specific text from the title and content of a news item.
	 * Used to filter out entities from the RSS/atom feeds that are not in the XML
	 * standard.
	 *
	 * Check:
	 * http://en.wikipedia.org/wiki/List_of_XML_and_HTML_character_entity_references
	 * for a list of valid entities for both XML and HTML
	 */
	function processText($text) {
		return html_entity_decode($text, ENT_COMPAT, 'UTF-8');
	}

	/* Get the title. */
	public function getTitle() {
		return $this->_title;
	}

	/* Get the date. */
	public function getDate() {
		return $this->_date;
	}

	/* Get the author. */
	public function getAuthor() {
		return $this->_author;
	}

	/* Get the content. */
	public function getContent() {
		return $this->_content;
	}

	/* Get the filename. */
	public function getFilename() {
		return $this->_filename;
  }

  /* Get the News link. */
  public function getLink() {
    return URL_BASE . 'news/' . substr( $this->_filename, 0, -5);
  }
}
?>
