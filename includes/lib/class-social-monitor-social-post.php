<?php 

if ( ! defined( 'ABSPATH' ) ) exit;

class Social_Post {
	function __construct($title, $text, $author = null, $service, $photo_url, $video_url, $service_id = null, $original_url = null, $created = null) {
		$this->author = $author;
		$this->title = $title;
		$this->text = $text;
		$this->service = $service;
		$this->photo_url = $photo_url;
		$this->video_url = $video_url;
		$this->service_id = $service_id;
		$this->original_url = $original_url;
		$this->created = $created;
	}
	function add_attachments($attachments){
		
		$this->add_meta('attachments', $attachments);
		
	}
	function save() {
		
		$auto_publish = get_option('sp_auto_publish');
		$social_tags = get_option('sp_auto_categorize');
		
		$this->id = wp_insert_post(array(
			'post_title' => mb_strimwidth( $this->text, 0, 54, '...'),
			'post_type' => 'sm_social_post',
			'post_status' => 'publish',
			'post_date' => date( 'Y-m-d H:i:s', $this->created )
		), true);
		
		/* pause for fraction of a second */
		usleep(62500);
		
		
		wp_set_post_terms( $this->id, $categories, 'category', true );
		
		$this->add_meta('text', $this->remove_emoji( $this->text ) );
		$this->add_meta('author', $this->author);
		$this->add_meta('service', $this->service);
		$this->add_meta('photo_url', $this->photo_url);
		$this->add_meta('video_url', $this->video_url);
		$this->add_meta('service_id', $this->service_id);
		$this->add_meta('original_url', $this->original_url);
		$this->add_meta('created', $this->created);
		$this->add_meta('published', ( $auto_publish ) ? 1 : 0 );
		
		return $this->id;
		
	}
	function add_meta($key, $value, $unique = false) {
		add_post_meta($this->id, $key, $value, $unique);
	}
	function remove_emoji( $text ){

		$clean_text = '';
		
		// Match Emoticons
		$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
		$clean_text = preg_replace($regexEmoticons, '', $text);
		
		// Match Miscellaneous Symbols and Pictographs
		$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
		$clean_text = preg_replace($regexSymbols, '', $clean_text);
		
		// Match Transport And Map Symbols
		$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
		$clean_text = preg_replace($regexTransport, '', $clean_text);
		
		// Match Miscellaneous Symbols
		$regexMisc = '/[\x{2600}-\x{26FF}]/u';
		$clean_text = preg_replace($regexMisc, '', $clean_text);
		
		// Match Dingbats
		$regexDingbats = '/[\x{2700}-\x{27BF}]/u';
		$clean_text = preg_replace($regexDingbats, '', $clean_text);
		
		return $clean_text;
		
	}
}