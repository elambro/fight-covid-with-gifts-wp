<?php

namespace CovidGifts\WP;

use CovidGifts\App\Contracts\Request;
use CovidGifts\App\Requests\IntentFormRequest;
use CovidGifts\App\Requests\PaymentFormRequest;
use CovidGifts\WP\Enqueues;
use CovidGifts\WP\ShortcodeManager;
use WP_Error;

class AjaxManager {

    public static $save_action   = 'buy_covid_cert';
    public static $intent_action = 'intent_covid_cert';

	/**
	 * [$endpoints description]
	 * @var Array
	 */
	protected $endpoints = [
		'buy_covid_cert' => 'chargePurchase',
        'intent_covid_cert' => 'handleIntent'
	];

    public function __construct()
    {
		$this->attach_hooks();
    }

    protected function successMessage() {
        return "Your gift cert request was sent";
    }

    protected function errorMessage() {
        return 'Sorry, there was a problem building your gift certificate. Please let us know.';
    }

    public function handleIntent()
    {
        ShortcodeManager::checkNonce();
        $response = $this->handleRequest( new IntentFormRequest );
        return $this->handleResponse($response);
    }

    public function chargePurchase()
    {
        ShortcodeManager::checkNonce();
        $response = $this->handleRequest( new PaymentFormRequest );
        return $this->handleResponse($response);
    }


    private function handleResponse($data)
    {
        \wp_send_json_success($data);
        \wp_die();
    }

    private function handleRequest(Request $request)
    {
        try {
            return $request->handle();
        } catch (\Exception $e) {
            if ($e instanceof \CovidGifts\App\Contracts\Exception) {
                \wp_send_json_error($e->toArray(), $e->getCode());
            } else {
                throw $e;
            }
        }
    }


    public static function fail( $message ) {

        \wp_send_json_error( $message, 422 );
        \wp_die();
    }

	protected function attach_hooks()
    {
		foreach ( $this->endpoints as $action => $endpoint ) {
			\add_action('wp_ajax_'.$action, [$this, $endpoint]);
            \add_action('wp_ajax_nopriv_'.$action, [$this, $endpoint]);
		}
	}

    public static function export( $csv, $filename )
    {
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: csv" . date("Y-m-d") . ".csv");
        header( "Content-disposition: filename=".$filename.".csv");
        print $csv;
        exit;
    }

}