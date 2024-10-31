<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions;

use Perfect\DecorationsForOccasions\Helpers\Auth;
use Perfect\DecorationsForOccasions\Helpers\SQL;
use Perfect\DecorationsForOccasions\Models\Effects;


/**
 * Class Request
 * @package Perfect\DecorationsForOccasions
 */
class Sync {
	const DEFAULT_CACHE_AGE = 604800; //60*60*24*7 - 7 days

	/**
	 * Determines whether sync with service is needed based on the set/default cache age
	 * @return bool
	 */
	public static function isSyncNeeded() {
		$registry = new Registry();

		$allow = $registry->get( 'setup_performed', false ); //avoid trying to sync data when there is no tables created...

		$sync_frequency = $registry->get( 'sync.frequency', self::DEFAULT_CACHE_AGE );
		$last_update    = $registry->get( 'sync.last_update', 0 );
		$now            = time();

		return ( $allow && ( $now - $last_update >= $sync_frequency ) );
	}

	/**
	 * Updates the registry info with update time
	 * @return void
	 */
	public static function saveSyncInfo() {
		$registry = new Registry();
		$registry->set( 'sync.last_update', time() );
		$registry->save();
	}


	/**
	 * Downloads ALL the data from the service, checking if it's needed first
	 *
	 * @param bool $force Force the sync, even if cache is still valid
	 *
	 * @return bool True on success, false otherwise
	 */
	public static function execute( $force = false ) {
		if ( $force || self::isSyncNeeded() ) {
			Logger::record('Starting service sync');
			//Sync all the data
			$request = PFactory::getRequest();
			$request->APICall( 'download', array( 'uid' => Auth::getUID() ) );
			if ( $request->response['response']['code'] === 200 ) {
				//Success! Well, in theory. Something still could've gone and shat itself. Let's assume that nothing did
				Logger::record('200');
				Logger::recordDump($request);
				return self::setData( $request->response['body'] );
			} else {
				Logger::record( 'Service didn\'t return [200 OK]' );
				Logger::recordDump($request);

				return false;
			}
		}
        return false;
	}

	public static function requestSubInfo() {
		Logger::record( 'Requesting subscription info' );
		//Set the flag for API
		$reg = PFactory::getRegistry();
		$reg->set( 'api.expecting_contact', true );
		$reg->save();
		//Now for the request
		$request = PFactory::getRequest();
		$request->APICall( 'requestSubInfo', array( 'uid' => Auth::getUID() ) );
		//If we don't have a subscription, it will not be created! If there is a subscription, Joomla! already answered
		switch ( (int) $request->response['response']['code'] ) {
			case 404:
				//No subscription yet - hence no contact
				$reg->set( 'api.expecting_contact', false );
				$reg->save();
				break;
			case 200:
				//Do nothing :p
				break;
			default:
				Logger::record( 'Invalid response:' );
				Logger::recordDump( $request );
				$reg->set( 'api.expecting_contact', false );
				$reg->save();
				break;
		}
	}

	public static function performAssetSync() {
		if ( ! PFactory::getSub()->isValid() ) {
			Logger::record( 'Aborting asset sync - no sub info or invalid sub' );

			return;
		}
		$secret = Auth::getCustomerSecret();
		//Eh, unqueue the sync now
		$reg = PFactory::getRegistry();
		$reg->set( 'queued_sync', false );
		$reg->save();

		if ( ! $secret ) {
			//No secret, we need to get it!
			$reg = PFactory::getRegistry();
			$reg->set( 'queued_sync', true );
			$reg->save();
			Logger::record( 'Aborting sync - no secret' );
			self::requestSubInfo();

			//We need to wait for Joomla to respond :E
			return;
		}
		//There is a secret! Let's try it :E
		Logger::record( 'Syncing assets...' );
		self::downloadAvailable();
	}

	private static function downloadAvailable() {
		Logger::record( 'Downloading available assets...' );
		//Since we don't technically *own* feeds on trial, we need to perform some shenanigans
		$is_trial = PFactory::getSub()->isTrial();

		$model     = new Effects();
		$available = $model->getAvailableFiles( ! $is_trial );

		$asset_path = dirname( dirname( __FILE__ ) ) . '/media/feeds/';

		$download_queue = array();

		if ( is_array( $available ) && ! empty( $available ) ) {
			foreach ( $available as $file ) {
				$owned_assets[] = $file->file_path;
				if ( isset( $download_queue[ $file->decoration_id ] ) ) {
					continue; //We already know this decoration set has missing files
				}
				if ( ! file_exists( $asset_path . $file->file_path ) ) {
					$download_queue[ $file->decoration_id ] = true; //This is the decoration set's id
				}
			}
		}

		//Aaaand quickly parse the queue into the proper format...
		$download_queue = array_keys( $download_queue );

		$file_name = $asset_path . time() . '.zip';

		$arguments = array( 'uid' => Auth::getUID(), 'id' => $download_queue, 'secret' => Auth::getCustomerSecret() );

		$request = PFactory::getRequest();
		$request->APICallToFile( 'assets', $arguments, $file_name );

		//File should be downloaded, unpack the data:
		$zip    = new \ZipArchive();
		$handle = $zip->open( $file_name );
		if ( $handle === true ) {
			//Succeeded in opening the zip archive
			$zip->extractTo( $asset_path );
			$zip->close();
		}
		//Even if we failed to extract/open the file, delete the zip archive as it either is not needed any more or corrupted
		if ( file_exists( $file_name ) ) {
			unlink( $file_name );
		}
		//File management
		$files      = array_diff( scandir( $asset_path ), array( '..', '.' ) ); //Remove the dots in file listing
		$undeclared = array_diff( $files, $owned_assets );
		foreach ( $undeclared as $entry ) {
			unlink( $asset_path . $entry );
		}
	}

	/**
	 * Syncs missing event's asset when it is about to be shown, hence the "emergency" in name
	 *
	 * @param $set_id The id of the decoration set in question
	 *
	 * @return boolean
	 */
	public static function emergencySyncShown( $set_id ) {
		if ( ! Auth::getCustomerSecret() ) {
			self::requestSubInfo();
			Logger::record('Emergency sync aborted, no token');
			return false; //No secret :c
		}
		Logger::recordF( 'Emergency sync for decoration set: %d', $set_id );
		$asset_path = dirname( dirname( __FILE__ ) ) . '/media/feeds/';

		$now       = time();
		$file_name = $asset_path . $now . '.zip';

		$arguments = array( 'uid' => Auth::getUID(), 'token' => Auth::getCustomerSecret(), 'id' => $set_id );

		$request = new Request();
		$request->APICallToFile( 'assets', $arguments, $file_name );
		//File should be downloaded, unpack the data:
		$zip    = new \ZipArchive();
		$handle = $zip->open( $file_name );
		if ( $handle === true ) {
			//Succeeded in opening the zip archive
			$zip->extractTo( $asset_path );
			$zip->close();
		}
		//Even if we failed to extract/open the file, delete the zip archive as it either is not needed any more or corrupted
		if ( file_exists( $file_name ) ) {
			//unlink( $file_name );
		}

		return ( $handle === true );
	}

	/**
	 * Used only in Sync::execute. Split for readability.
	 *
	 * @param $sql_dump List of values that will be "glued" to make a full SQL statement, separated by a string constant
	 *
	 * @return bool True on success, false otherwise
	 */
	private static function setData( $sql_dump ) {
		//TODO: Error checking, FFS!
		global $wpdb;
		//Complete table info
		$tables = array(
			'#__dfo_feeds'             => array( 'id', 'name', 'description', 'price', 'owned', 'category' ),
			'#__dfo_events'            => array(
				'id',
				'name',
				'description',
				'date_start',
				'date_end',
				'available_start',
				'available_end',
			),
			'#__dfo_decorations'       => array( 'id', 'name', 'featured', 'upvotes', 'downvotes' ),
			'#__dfo_effects'           => array( 'decoration_id', 'type', 'file_path', 'options' ),
			'#__dfo_feedsevents'       => array( 'feed_id', 'event_id' ),
			'#__dfo_eventsdecorations' => array( 'event_id', 'decoration_id' )
		);
		$sql    = SQL::splitQueries( $sql_dump );
		if ( ! is_array( $sql ) || empty( $sql ) ) {
			//Something went wrong...
			return false;
		}
		foreach ( $tables as $name => $columns ) {
			$wpdb->query( 'TRUNCATE ' . SQL::fixPrefix( $name ) );
			//Insert the data:
			$columns_str = implode( ', ', $columns );
			$query       = sprintf( 'INSERT INTO %s (%s) VALUES', $name, $columns_str );
			$query .= array_shift( $sql ); //values
			$wpdb->query( SQL::fixPrefix( $query ) );
			if ( ! empty( $wpdb->last_error ) ) {
				return false; //Break out of the sync process, since something fucked up. Transactions would be nice here
			}
		}
		//Finally, we're done...
		self::saveSyncInfo();

		return true;
	}
}