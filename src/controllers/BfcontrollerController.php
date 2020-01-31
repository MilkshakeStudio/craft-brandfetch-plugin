<?php
/**
 * Brandfetch plugin for Craft CMS 3.x
 *
 * asdfasdf
 *
 * @link      https://milkshake.studio
 * @copyright Copyright (c) 2020 Milkshake Studio
 */

namespace milkshakestudio\brandfetch\controllers;

use milkshakestudio\brandfetch\Brandfetch;
use milkshakestudio\brandfetch\services\BrandfetchService;
use Craft;
use craft\web\Controller;
use yii\web\Response;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\console\Exception;

// use craft\helpers\Console;

/**
 * Bfcontroller Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Milkshake Studio
 * @package   Brandfetch
 * @since     0.0.1
 */
class BfcontrollerController extends Controller
{

	// Protected Properties
	// =========================================================================

	/**
	 * @var    bool|array Allows anonymous access to this controller's actions.
	 *         The actions must be in 'kebab-case'
	 * @access protected
	 */
	protected $allowAnonymous = ['index', 'fetch-logo'];

	// Public Methods
	// =========================================================================

	/**
	 * Handle a request going to our plugin's index action URL,
	 * e.g.: actions/brandfetch/bfcontroller
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		$result = 'Welcome to the BfcontrollerController actionIndex() method';

		return $result;
	}

	/**
	 * Handle a request going to our plugin's actionDoSomething URL,
	 * e.g.: actions/brandfetch/bfcontroller/fetch-logo
	 *
	 * @return mixed
	 */
	public function actionFetchLogo(): Response
	{      
		$responseData = [
			'success' => true,
			'data'=> null,
			'message' => null,
		];
		// GET PARAMS --------------
		$request = Craft::$app->getRequest();
		$url = $request->getBodyParam('url');
		$bfService =  new BrandfetchService();

		try {

			$res = $bfService->callBFApi($url);
			$name = str_replace('.com', '', parse_url($url, PHP_URL_HOST));
	
			$responseData['brandfetch'] = $bfService->saveLogo(json_decode($res->getBody(), true), $name );
			$responseData['statusCode'] = $res->getStatusCode();
			// $responseData['data'] = json_decode($res->getBody(), true); // returns array

		} catch (\Throwable $e) {
			// $this->stderr("Unable to create {$path}: {$e->getMessage()}" . PHP_EOL, Console::FG_RED);
			// $this->stderr("Unable to find anything" . PHP_EOL, Console::FG_RED);
			// return false;
			$responseData['success'] = false;
			// $responseData['exception'] = $this->asErrorJson($e->getMessage());
			$responseData['exception'] = $e->getMessage();

		}
		
		return $this->asJson($responseData);
	}

	
}
