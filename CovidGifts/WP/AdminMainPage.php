<?php

namespace CovidGifts\WP;

use CovidGifts\WP\AbstractAdminPage;
use CovidGifts\WP\ModelListTable;

class AdminMainPage extends AbstractAdminPage {

    public static $action = 'covid-gifts-main';
    public static $title  = 'Gift Certificates';
    public static $slug   = 'covid-gifts-main';

    protected $endpoints = [
        'cvdapp_cancel'  => 'markCertAsCancelled',
        'cvdapp_approve' => 'markCertAsApproved',
        'cvdapp_use'     => 'markCertAsUsed',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->addScript('/dist/table.js', $this->getAjaxObject());
        $this->addBootstrap();
    }

    public function handle()
    {
        echo '<h1>Gift Certificates</h1>';

        $table = new ModelListTable();
        $table->prepare_items();
        $count = count( $table->items );
        $this->printTable($table);

        // echo '<div style="padding: 30px"><div id="cvdapp"></div></div>';
    }

    public function markCertAsCancelled()
    {
        // @todo
    }

    public function markCertAsApproved()
    {
        // @todo
    }

    public function markCertAsUsed()
    {
        // @todo
    }

    private function printTable($table)
    {
        echo '<div class="wrap"><h1 class="wp-heading-inline">' . $table->getTitle() . '</h1>';
        echo '<span class="title-count theme-count">'.$count.'</span>';
        // echo '<a class="page-title-action" href="' . \admin_url( 'admin-ajax.php' ) . '?action=' . $this->exportAction . '" target="_blank">Export All</a>';
        // echo '<a class="page-title-action" href="' . \admin_url( 'admin-ajax.php' ) . '?action=' . $this->mailAction . '" target="_blank">Email All</a>';
        echo '<hr class="wp-header-end">';

        echo '<div id="table-cvdapp"><form id="full-list-form" method="get">';

        $table->display();

        echo '</form></div></div>';

        add_action('admin_footer', [$this, 'footerScript']);
    }

    public function footerScript()
    {
        ?>
            <script type="text/javascript" >
            jQuery(document).ready(function($) {
                // $('.ajaxLink').click(function(e) {
                //     var url = $(this).attr('href');
                //     var toDelete = $(this).hasClass('deleteListItem');
                //     var row = $(this).parents('tr');
                //     jQuery.post(url, function (response) {
                //         var message = response.success ? response.data.message : __('Deleted.');
                //         var notice = '<div class="updated notice-success notice is-dismissable"><p>' + message + '</p></div>';
                //         $('#full-list-form').before(notice);
                //         toDelete && row && $(row).remove();
                //     })
                //     .fail(function( error ) {
                //         var message = error.responseJSON && error.responseJSON.data ? error.responseJSON.data : ( error.statusText || __('Error!') );
                //         var notice = '<div class="error notice-error notice is-dismissable"><p>'+ message +'</p></div>';
                //         $('#full-list-form').before(notice);
                //     });
                //     return false;
                // });
            });
            </script> 
        <?php
    }
}
