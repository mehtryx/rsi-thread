<?php 
/*
Module Name: RSI_THREADS
Module URI: http://github.com/mehtryx/rsi-threads
Description: 
Author: mehtryx
Version: 0.1.0
Author URI: http://github.com/mehtryx
License: MIT
*/

/**
 * RSI THREAD Class, contains functions to process thread content
 */
class RSI_THREADS {
	
	/**
	 * Looks for the discussionID in the html results, will check multiple pages up to limit
	 *
	 * @param discussionID to look for
	 * @param limit, the number of pages to search total, minimum is 1
	 * @return false if failed, otherwise the resulting position
	 */
	function search_discussions( $discussionID, $limit = 5 ) {
		$base_url = "https://forums.robertsspaceindustries.com/categories/guilds-squadrons";
		$url = $base_url; // for first pass
		
		$counter = 0; // tracks number of discussions total
		$thread_position = 0; // The position of our thread
		$thread_id = 'id="Discussion_' . $discussionID . '"';
		libxml_use_internal_errors(true); // suppress xml validation errors, we dont want them
		$dom = new DomDocument; // Stores html being searched
		
		// safties added, this could be made into a module that is used with query params, we want it to stay sane on the recursion
		if ( $limit < 1 )
			$limit = 1;
		if ( $limit > 20 )
			$limit = 5;
		
		for ( $request = 1; $request < $limit; $request++ ) {
			$raw_html = $this->retrieve_html( $url );		
			// url failed to return content, return false and do not continue as this is a critical error.
			if ( $raw_html === false )
				return false;
		
			$dom->loadHTML( $raw_html );
			foreach($dom->getElementsByTagName('li') as $node) {
				$current_node = $dom->saveHTML( $node );
				if ( strpos( $current_node, 'id="Discussion' ) ) {
					$counter++;
					if ( strpos( $current_node, $thread_id ) )
						$thread_position = $counter;
				}
			}
			if ( $thread_position )
				return $thread_position;
			
			// if we are here, then we didn't find it, set url to next page
			$url = $base_url . '/p' . ( $request + 1 ); // on first iteration this will add /p2 to the requested url
		}
	
		// if we actually hit here...we've done all our loops and found nothing.
		return false;
	}


	/**
	 * Retrieves the specified url
	 *
	 * @param url is a fully constructed address including protocol i.e. https://forums.robertsspaceindustries.com/categories/guilds-squadrons
	 * @return false if unsuccessful, response html otherwise
	 */
	function retrieve_html( $url ) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
}
