<?php

namespace App\Http\Controllers\ETO;

// Global vars
global $data, $gConfig, $gLanguage, $siteId;

$db = \DB::connection();
$dbPrefix = get_db_prefix();

$user = auth()->user();

$isAdmin = 1;
$userId = (int) $user->id;

$cmsUserId = $userId;

// Default
$siteId = 0;
$data = array();
$data['message'] = array();
$data['success'] = false;

if ( empty($user->id) || $isAdmin == 0 ) { // Add option - Check if super user
		$data['message'][] = 'You are not logged in!';

		if ( config('app.debug') ) {
			$response['!SENT'] = $etoPost;
		}

		$response = $data;
		return $response;
		exit;
}

// Profile
$siteId = config('site.site_id');

if (empty($siteId)) {
		$data['message'][] = 'No profile was found!';
}

// Config
$gConfig = array();

$sql = "SELECT `key`, `value`
				FROM `{$dbPrefix}config`
				WHERE `site_id`='" . $siteId . "'
				ORDER BY `key` ASC";

$resultsConfig = $db->select($sql);

if (!empty($resultsConfig)) {
		foreach($resultsConfig as $key => $value) {
				$gConfig[$value->key] = $value->value;
		}
}
else {
		$data['message'][] = 'No global config was found!';
}

// Language
$gLanguage = array();

$gConfig['language'] = app()->getLocale();

$gLanguage = trans('backend.old');

if (empty($gLanguage)) {
		$data['message'][] = 'No language was found!';
}

class ETOBackendAPI
{
	public $config = null;
	public $configBrowser = null;
	public $lang = null;
	public $message = array(
				'error' => array(),
				'warning' => array(),
				'success' => array()
			);
	public $success = false;
	public $siteId = 0;
	public $siteName = '';
	public $userId = 0;
	public $userName = '';

  function __construct( $siteId )
	{
		$db = \DB::connection();
		$dbPrefix = get_db_prefix();

		$sql = "SELECT `id`, `name`
				FROM `{$dbPrefix}profile`
				WHERE `published`='1'
				AND `id`='". $siteId ."'
				LIMIT 1";
		$query = $db->select($sql);
		if (!empty($query[0])) {
			$query = $query[0];
		}

		if ( !empty($query) ) {
			$this->siteId = (int)$query->id;
			$this->siteName = (string)$query->name;
		}

		$this->config = self::getConfig();
		$this->lang = self::loadLang();
		// $this->userId = (int)session('etoUserId', 0);


		// $sql = "SELECT `id`, `name`
		// 		FROM `{$dbPrefix}user`
		// 		WHERE `site_id`='". $this->siteId ."'
		// 		AND `id`='". $this->userId ."'
		// 		LIMIT 1";
		// $query = $db->select($sql);
		// if (!empty($query[0])) {
		// 	$query = $query[0];
		// }
		//
		// if ( !empty($query) ) {
		// 	$this->userId = (int)$query->id;
		// 	$this->userName = (string)$query->name;
		// }
  }

	public function loadLang()
	{
		$language = array();

		$language = trans('backend');

		return $language;
	}

	public function getLang( $id )
	{
		$showTranslation = 0;
		$translation = '';

		if ( $showTranslation ) {
			$translation .= '*';
		}

		if ( !empty($this->lang[$id]) ) {
			$translation .= $this->lang[$id];
		}
		else {
			$translation .= $id;
		}

		if ( $showTranslation ) {
			$translation .= '*';
		}

		return $translation;
	}

	public function getConfig()
	{
		$db = \DB::connection();
		$dbPrefix = get_db_prefix();

		$config = new \stdClass();
		$configBrowser = new \stdClass();

		$sql = "SELECT `key` AS `param`, `value`, `type`, `browser`
				FROM `{$dbPrefix}config`
				WHERE `site_id`='". $this->siteId ."'
				ORDER BY `key` ASC";
		$query = $db->select($sql);

		if ( !empty($query) ) {
			foreach($query as $k => $v) {
				switch( $v->type ) {
					case 'int':
						$v->value = (int)$v->value;
					break;
					case 'float':
						$v->value = (float)$v->value;
					break;
					case 'string':
						$v->value = (string)$v->value;
					break;
					case 'object':
						$v->value = json_decode($v->value);
					break;
					default:
						$v->value = $v->value;
					break;
				}

				if ( $v->browser == 1 ) {
					$configBrowser->{$v->param} = $v->value;
				}

				$config->{$v->param} = $v->value;
			}
		}

		$this->configBrowser = $configBrowser;

		return $config;
	}
}

$etoAPI = new ETOBackendAPI($siteId);
$task   = (string) $etoPost['task'];
$action = (string) $etoPost['action'];

switch($task)
{
		case 'payment':

				include __DIR__ .'/BackendPayment.php';

		break;
		case 'user': // custommers

				include __DIR__ .'/BackendUser.php';

		break;
		case 'discount':

				include __DIR__ .'/BackendDiscount.php';

		break;
		case 'category':

				include __DIR__ .'/BackendCategory.php';

		break;
		case 'location':

			include __DIR__ .'/BackendLocation.php';

		break;
		case 'vehicle':

			include __DIR__ .'/BackendVehicle.php';

		break;
		case 'fixedprices':

				include __DIR__ .'/BackendFixedPrices.php';

		break;
		case 'excludedroutes':

				include __DIR__ .'/BackendExcludedRoutes.php';

		break;
		case 'meetingpoint':

				include __DIR__ .'/BackendMeetingPoint.php';

		break;
		case 'config':

				include __DIR__ .'/BackendConfig.php';

		break;
		case 'profile':

				include __DIR__ .'/BackendProfile.php';

		break;
		case 'bookings':

				include __DIR__ .'/BackendBookings.php';

		break;
}

$response = $data;
