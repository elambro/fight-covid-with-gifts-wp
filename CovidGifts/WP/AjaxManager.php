<?php

namespace CovidGifts\WP;

use CovidGifts\Adapters\WP\Config;
use CovidGifts\App\Contracts\Request;
use CovidGifts\App\Requests\IntentFormRequest;
use CovidGifts\App\Requests\PaymentFormRequest;

class AjaxManager {

    public function __construct()
    {
        $this->on(Config::$intent_action, 'handleIntent');
		$this->on(Config::$charge_action, 'handleCharge');
    }

    protected function on($action, $method)
    {
        \add_action('wp_ajax_'.$action, [$this, $method]);
        \add_action('wp_ajax_nopriv_'.$action, [$this, $method]);
    }

    public function handleIntent()
    {
        $response = $this->request( new IntentFormRequest );
        return $this->respond($response);
    }

    public function handleCharge()
    {
        $response = $this->request( new PaymentFormRequest );
        return $this->respond($response);
    }


    private function respond($data)
    {
        \wp_send_json_success($data);
        \wp_die();
    }

    private function request(Request $request)
    {
        try {
            return $request->handle();
        } catch (\Exception $e) {
            if ($e instanceof \CovidGifts\App\Contracts\Exception) {
                \wp_send_json_error($e->toArray(), $e->getCode());
                \wp_die();
            } else {
                throw $e;
            }
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