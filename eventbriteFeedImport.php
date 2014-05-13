<?php

/*
Plugin Name: EventBrite Feed Importer
Description: A plugin to import the RSS feed of an eventBrite event and create posts from the aquired events.
Author: JPG
Version: 1.0
*/

require 'definitions.php';

if ( ! defined( 'EB_USERID' ) ) define( 'EB_USERID' , '2447513500' );
if ( ! defined( 'FEED_URL' ) ) define( 'FEED_URL' , 'http://www.eventbrite.com/rss/organizer_list_events/' );

register_activation_hook(__FILE__, 'creatScheduledEventForEventbriteImport');
add_action('performDailyTasks', 'createNewPostForEvents');

add_action('wp', 'createNewPostForEvents'); //USING FOR TESTING FUNCTIONALITY ONLY

/**
 * Creates a scheduled event for RSS feed importer functions
 * @return [type] [description]
 */
function creatScheduledEventForEventbriteImport() {

	if( !wp_next_scheduled( 'createNewPostForEvents' ) ) {

   		wp_schedule_event( time(), 'daily', 'createNewPostForEvents' );
	}
}

/**
 * Returns raw data from http request
 * @param  [URL] $feed [URL of feed]
 * @param  [int] $id   [User ID from feed's website]
 * @return [array]       [Data from given URL formatted into array]
 */
function retrieveEventbriteFeedAddress( $feed = FEED_URL, $id = EB_USERID ) {

	$response = wp_remote_get($feed . $id);

	return $response;
}

/**
 * Checks the feed address contains the appropriate content
 * @param  string $contentType [Type of content that is required]
 * @return [boolean]              [Returns true if desired content type, else returns false]
 */
function checkEventbriteFeedAddressIsValid( $contentType = 'application/rss+xml' ) {

	if ( retrieveEventbriteFeedAddress()['headers']['content-type'] == $contentType ) {
		return true;
	}
	else {
		return false;
	}
}

/**
 * Imports RSS feed from given address
 * @param  [URL] $feed_address [URL of feed to import]
 * @return [object]            [Simplexml formatted object of feed]
 */
function importEventbriteRssFeed( $feed = FEED_URL, $id = EB_USERID ) {

	if ( checkEventbriteFeedAddressIsValid() == true ) {

		$event_feed = simplexml_load_file( $feed . $id, 'SimpleXMLElement', LIBXML_NOCDATA);

		return $event_feed;
	}
}

/**
 * Check whether post already exists before creating new one
 * @param  [object] $event [Current object to inspect in RSS feed]
 * @return [boolean]        [Returns true if post already exists, else returns false]
 */
function checkIfPostExists( $event ) {

	if( null == get_page_by_title( strval($event->title), 'array', 'post' ) && $event->title != 'Test Title') {

		return false;

	} else {

		return true;
	}
}

/**
 * Creates new post with desired attributes
 * @param  [object] $event [Current object to inspect in RSS feed]
 * @return [array]        [Details needed for creation of new post]
 */
function createNewPost( $event ) {

	$new_post = array(

		'post_content'   => strval($event->description), // The full text of the post.
		'post_title'     => strval($event->title), // The title of your post.
		'post_status'    => 'publish', // Default 'draft'.
		'post_type'      => 'post', // Default 'post'.
		'post_date'      => date( 'Y-m-d H:i:s', strtotime(strval($event->pubDate))), // The time post was made.
		'post_category'  => array(get_cat_ID('Events')) // Default empty.
	);  
			
	return $new_post;
}

/**
 * Inserts new post
 * @param  [array] $new_post [Details needed for creation of new post]
 * @return [type]           [description]
 */
function insertNewPost( $new_post ) {

	wp_insert_post( $new_post );
}

/**
 * Retrieves list of events and loops through each one
 * @return [type] [description]
 */
function createNewPostForEvents() {

	$events = importEventbriteRssFeed()->channel->item;

	foreach ($events as $event) {

		if ( checkIfPostExists( $event ) == false ) {

			insertNewPost( $event );
			createNewPost( $new_post );

		}
	}
}



