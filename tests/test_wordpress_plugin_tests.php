<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */

if ( ! defined( 'EB_USERID' ) ) define( 'EB_USERID' , '2447513500' );
if ( ! defined( 'FEED_URL' ) ) define( 'FEED_URL' , 'http://www.eventbrite.com/rss/organizer_list_events/' );

class WP_Test_WordPress_Plugin_Tests extends WP_UnitTestCase {

	function testFeedbriteArrayIsAnArray() {

		$result = retrieveEventbriteFeedAddress( FEED_URL, EB_USERID );

		$this->assertInternalType('array', $result);
	}

	function testFeedbriteAddressArrayIsNotEmpty() {

		$this->assertNotEmpty( retrieveEventbriteFeedAddress( FEED_URL, EB_USERID ) );
	}

	function testFeedbriteAddressValidationReturnsTrue() {

		$contentType = 'application/rss+xml';

		$this->assertTrue( checkEventbriteFeedAddressIsValid( $contentType ) );
	}

	function testFeedbriteAddressValidationReturnsFalse() {

		$contentType = 'some/other/content-type';

		$this->assertFalse( checkEventbriteFeedAddressIsValid( $contentType ) );
	}

	function testRssImportReturnsAnObject() {

		$result = importEventbriteRssFeed( FEED_URL, EB_USERID );

		$this->assertInternalType('object', $result);
	}

	function testCheckPostExistsFlaseIsFalse() {

		$event = (object) array(
							'title'		  => 'some title'
						);

		$this->assertFalse( checkIfPostExists( $event ) );
	}

	function testCheckPostExistsTrueIsTrue() {

		$event = (object) array(
							'title'		  => 'Test Title'
						);

		$this->assertTrue( checkIfPostExists( $event ) );
	}

	function testCreateNewPostReturnsArray() {

		$event = (object) array(
							'description' => 'some description',
							'title'		  => 'some title',
							'pubDate'	  => '1st Jan 1970'
						);

		$result = createNewPost( $event );

		$this->assertInternalType('array', $result);
	}
}
