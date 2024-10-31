<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions;

/**
 * Class Request
 * @package Perfect\DecorationsForOccasions
 */
class Request {

	/**
	 * @var string URI to the service API
	 */
	protected $baseHref = 'https://www.perfect-web.co/index.php?option=com_dfo_service&task=api.';

	/**
	 * @var mixed Holds last response
	 */
	public $response;

	/**
	 * @var bool Whether the response is a valid JSON string or not
	 */
	public $is_json = false;
	/**
	 * @var If $is_json is true this holds the decoded JSON data
	 */
	public $json_data;

	/**
	 * Performs a call to the service.
	 *
	 * @param string $task Task to execute on the service side
	 * @param array $arguments Arguments passed to the service. Usually has to contain Auth data
	 */
	public function APICall( $task = null, $arguments = array() ) {
		if ( $task === null ) {
			throw new \InvalidArgumentException( 'You MUST specify the task when making an API call!' );
		}
		$query_params = '';
		if ( is_array( $arguments ) ) {
			$query_params = http_build_query( $arguments ); //GET query
		}
		$this->response = wp_remote_get( $this->baseHref . $task . '&' . $query_params );
		if($this->response instanceof \WP_Error){
			Logger::recordDump($this->response);
			return;
		}
		//Let's see if we can make an object out of the response...
		$this->json_data = json_decode( $this->response['body'] );
		if ( json_last_error() == JSON_ERROR_NONE ) {
			$this->is_json = true;
		}
	}

	/**
	 * Similar to APICall, but saves the the response in a file. Used to download assets.
	 *
	 * @param string $task
	 * @param array $arguments
	 * @param $file_name
	 */
	public function APICallToFile( $task = null, $arguments = array(), $file_name ) {
		if ( $task === null ) {
			throw new \InvalidArgumentException( 'You MUST specify the task when making an API call!' );
		}
		$query_params = '';
		if ( is_array( $arguments ) ) {
			$query_params = http_build_query( $arguments ); //GET query
		}
		//Not using wp_remote to not load the request info into memory!
		$ch = curl_init( $this->baseHref . $task . '&' . $query_params );
		$fp = fopen( $file_name, 'wb' );
		curl_setopt( $ch, CURLOPT_FILE, $fp );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_exec( $ch );
		curl_close( $ch );
		fclose( $fp );
	}
}