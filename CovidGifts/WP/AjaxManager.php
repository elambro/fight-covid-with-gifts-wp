<?php

namespace CovidGifts\WP;

use WP_Error;
use CovidGifts\WP\Enqueues;
use CovidGifts\WP\ShortcodeManager;
use CovidGifts\WP\SubmissionModel;

class AjaxManager {

    public static $save_action   = 'buy_covid_cert';

	/**
	 * [$endpoints description]
	 * @var Array
	 */
	protected $endpoints = [
		'save_water_whiz_form' => 'handlePost',
        'upload_water_whiz_img' => 'handleUpload'
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

    public function handlePost()
    {

        // error_log('1. handling post');

        ( new ShortcodeManager() )->checkNonce();

        $model = (isset($_POST['u_hash']) && $_POST['u_hash'] && isset($_POST['saved_email'])
               ? SubmissionModel::findByHash($_POST['u_hash'], $_POST['saved_email'])
               : null) ?:
               new SubmissionModel();

        // error_log('2. created model');

        $model->assignAttributes($_POST, 'u_');

        $model->validate();
        $model->sanitize();

        // error_log('3. validated and sanitized');

        if ( !$model->save() ) {
            $this->fail($this->errorMessage());
        }

        \wp_send_json_success([
            'message'    => $this->successMessage(),
            'attributes' => $model->getAttributes()
        ]);

        \wp_die();

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