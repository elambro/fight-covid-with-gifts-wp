<?php

namespace CovidGifts\WP;

use CovidGifts\WP\AjaxManager;
use CovidGifts\WP\CronManager;
use CovidGifts\WP\DisplayShortcodeManager;
use CovidGifts\WP\ModelListTable;
use CovidGifts\WP\ShortcodeManager;
use CovidGifts\WP\SubmissionModel;

class AdminManager {

	protected $listTable;
	protected $hookSuffix;
	protected $exportAction                = 'export_waterwhiz';
	protected $export_filename             = 'water_whizzes';
	protected $mailAction;
	
	protected static $listModelClass       = SubmissionModel::class;
	protected static $listTableClass       = ModelListTable::class;
	public static $delete_list_item_action = 'delete_waterwhiz';
	public static $approve_list_item_action = 'approve_waterwhiz';
	public static $disapprove_list_item_action = 'disapprove_waterwhiz';

	public function __construct() {

		$cron = new CronManager();
		$this->mailAction = $cron->getAction();
	}

	public function init() {
		$this->attach_hooks();
	}

	protected function attach_hooks() {
		\add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		\add_action('init', array($this, 'addMenus'));
		\add_action('wp_ajax_' . $this->exportAction, [$this, 'export' ]);
        \add_action('wp_ajax_'. $this::$delete_list_item_action, [$this, 'deleteListItem']);
        \add_action('wp_ajax_'. $this::$disapprove_list_item_action, [$this, 'disapproveListItem']);
        \add_action('wp_ajax_'. $this::$approve_list_item_action, [$this, 'approveListItem']);
	}

	public function enqueue() {

		// Enqueue any css or js here
	}

	public function addMenus() {

		\add_action('admin_menu', function () {
			$this->hookSuffix = \add_menu_page('CovidGifts', 'CovidGifts', 'manage_options', 'water_whiz_list', array($this, 'page'), 'dashicons-id', 50);
		});

		\add_action( 'load-'. $this->hookSuffix, array( $this, 'screenOptions' ) );
	}

	public function screenOptions() {
		
		// echo "DOING SCREEN OPTIONS";
	
	}

	public function page() {

		$class = $this::$listTableClass;

		$this->listTable = new $class;

		$this->listTable->prepare_items();

		$count = count( $this->listTable->items );

		echo '<div class="wrap"><h1 class="wp-heading-inline">' . $this->listTable->getTitle() . '</h1>';
		echo '<span class="title-count theme-count">'.$count.'</span>';
		echo '<a class="page-title-action" href="' . \admin_url( 'admin-ajax.php' ) . '?action=' . $this->exportAction . '" target="_blank">Export All</a>';
		echo '<a class="page-title-action" href="' . \admin_url( 'admin-ajax.php' ) . '?action=' . $this->mailAction . '" target="_blank">Email All</a>';
		echo '<hr class="wp-header-end">';

        echo '<p>Add a single water whiz submission to a page or post using the shortcode <strong>['.DisplayShortcodeManager::$shortcode.' id="4"][/'.DisplayShortcodeManager::$shortcode.']</strong>.</p>' . 
             '<p>Add all approved submissions by using <strong>['.DisplayShortcodeManager::$shortcode.'][/'.DisplayShortcodeManager::$shortcode.']</strong>.</p>';

        echo '<p>The water whiz form is displayed using the shortcode <strong>['.ShortcodeManager::$shortcode.'][/'.ShortcodeManager::$shortcode.']</strong>.</p>';

		echo '<div id="browards-wp-list-table-' . $this->export_filename . '"><form id="full-list-form" method="get">';

		$this->listTable->display();

		echo '</form></div></div>';

		add_action( 'admin_footer', function () { 
		?>
			<script type="text/javascript" >
			jQuery(document).ready(function($) {
				$('.ajaxLink').click(function(e) {
			    	var url = $(this).attr('href');
			    	var toDelete = $(this).hasClass('deleteListItem');
			    	var row = $(this).parents('tr');
			    	jQuery.post(url, function (response) {
			    		var message = response.success ? response.data.message : __('Deleted.');
			    		var notice = '<div class="updated notice-success notice is-dismissable"><p>' + message + '</p></div>';
			    		$('#full-list-form').before(notice);
			    		toDelete && row && $(row).remove();
			    	})
			    	.fail(function( error ) {
					    var message = error.responseJSON && error.responseJSON.data ? error.responseJSON.data : ( error.statusText || __('Error!') );
			    		var notice = '<div class="error notice-error notice is-dismissable"><p>'+ message +'</p></div>';
			    		$('#full-list-form').before(notice);
					});
			        return false;
			    });
			});
			</script> 
		<?php
		 });
	}


	public function export() 
	{
		$class = static::$listModelClass;
		$csv = $class::buildCsvString();
		$filename = $this->export_filename . '_' . date('Y-m-d');
		return AjaxManager::export( $csv, $filename );

	}

    public function deleteListItem() 
    {
        $id = $_REQUEST['id'];
        $class = static::$listModelClass;
        $model = $id ? $class::find( $id ) : null;
        if ( !$model ) {
            \wp_send_json_error('The item with that id wasn\'t found.', 404 );
            \wp_die();
        }
        
        if ( !$model->delete() ) {
            \wp_send_json_error('The item could not be deleted.', 400 );
            \wp_die();
        }

        \wp_send_json_success([
            'message' => "The item was deleted."
        ]);
        \wp_die();
    }

    public function approveListItem() 
    {
        $id = $_REQUEST['id'];
        $class = static::$listModelClass;
        $model = $id ? $class::find( $id ) : null;
        if ( !$model ) {
            \wp_send_json_error('The item with that id wasn\'t found.', 404 );
            \wp_die();
        }
        
        if ( !$model->approve() ) {
            \wp_send_json_error('The item could not be marked as approved.', 400 );
            \wp_die();
        }

        \wp_send_json_success([
			'message' => "The item was marked as approved.",
			'model'   => $model->getAttributes()
        ]);
        \wp_die();
    }

    public function disapproveListItem() 
    {
        $id = $_REQUEST['id'];
        $class = static::$listModelClass;
        $model = $id ? $class::find( $id ) : null;
        if ( !$model ) {
            \wp_send_json_error('The item with that id wasn\'t found.', 404 );
            \wp_die();
        }
        
        if ( !$model->unapprove() ) {
            \wp_send_json_error('The item could not be marked as unapproved.', 422 );
            \wp_die();
        }

        \wp_send_json_success([
            'message' => "The item was marked as unapproved.",
            'model'   => $model->getAttributes()
        ]);
        \wp_die();
    }
        
}
