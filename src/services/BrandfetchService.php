<?php
/**
 * Brandfetch plugin for Craft CMS 3.x
 *
 * asdfasdf
 *
 * @link      https://milkshake.studio
 * @copyright Copyright (c) 2020 Milkshake Studio
 */

namespace milkshakestudio\brandfetch\services;

use milkshakestudio\brandfetch\Brandfetch;

use Craft;
use craft\base\Component;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

use craft\elements\Asset;
use craft\errors\InvalidSubpathException;

/**
 * BrandfetchService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Milkshake Studio
 * @package   Brandfetch
 * @since     0.0.1
 */
class BrandfetchService extends Component
{
    const BASE_API_URL = 'https://api.brandfetch.io';
    // Public Methods
    // =========================================================================

    
	/**
	 * Call to Brandfetch API to return the Images that we will save.
	 * 
	 * @return mixed 
	 */
    public function callBFApi($url)
    {

		$client = new Client([
			// Base URI is used with relative requests
			'base_uri' => self::BASE_API_URL ,
			// You can set any number of default request options.
			'timeout'  => 10.0,
			'headers' => [
				'x-api-key' => Brandfetch::$plugin->getSettings()->brandfetch_api_key,
				'Content-Type' => 'application/json'
			]
		]);
		$response = $client->request('POST','/v1/logo', 
			[RequestOptions::JSON => ['domain' => $url ]]
		);
		return $response;
    }
    

    /**
	 * Save url of the image to an actual image
	 */
	public function saveLogo($res, $name )
	{	

		$imgUrl = $res['response']['logo']['image'];

		$path = Craft::$app->getPath();
		$dir = $path->getTempAssetUploadsPath() . '/brandfetch/';
		if (!is_dir($dir)) {
			mkdir($dir);
		}

		$assets = Craft::$app->getAssets();
		$settings = Brandfetch::$plugin->getSettings();
		if(!isset($settings->destination)) {
			// $returnData['success'] = false;
			// $returnData['message'] = Craft::t('splashing-images', 'Please set a file destination in settings so images can be saved');
			// return $this->asJson($returnData);
			return  Craft::t('brand-fetch', 'Please set a file destination in settings so images can be saved');
		}
		// get POST ID
		$id = Craft::$app->request->post('id');

		//save image
		$name = $name . rand() . '.png';
		$tempPath = $dir . $name;
		$savedImg = file_put_contents($tempPath , file_get_contents($imgUrl)); 
		
		// 
		$volume = Craft::$app->volumes->getVolumeById($settings->destination);
		$optPath = (string)$settings->folder;

		if ($optPath) {
			try {
				$optPath = Craft::$app->getView()->renderObjectTemplate($optPath, $settings);
			} catch (\Throwable $e) {
				throw new InvalidSubpathException($optPath);
			}
		}


		$assetsService = Craft::$app->getAssets();
		$folderId = $assetsService->ensureFolderByFullPathAndVolume($optPath, $volume);

		$asset = new Asset();
		$asset->tempFilePath = $tempPath;
		$asset->filename = $name;
		$asset->newFolderId = $folderId;
		$asset->volumeId = $volume->id;
		$asset->title = $name;
		$asset->avoidFilenameConflicts = true;
		$asset->setScenario(Asset::SCENARIO_CREATE);

		$result = Craft::$app->elements->saveElement($asset);

		if ($result) {
			// $asset->getEditorHtml();
			$returnData['result'] =  $asset;
			$returnData['thumbnail'] = $assetsService->getThumbUrl($asset, 300, 200, false, false);
			$returnData['message'] = 'Image saved!';
		} else {
			// $returnData['message'] = Craft::t('brandfetch', 'Oops, something went wrong...');
			$returnData['result'] = $result;
			$returnData['message'] = 'Oops, something went wrong...';
		}
		// return $this->asJson($returnData);
		return $returnData;
		// exit;
		
	}

}
