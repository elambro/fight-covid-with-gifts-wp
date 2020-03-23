<?php

namespace CovidGifts\WP;

use CovidGifts\App\Requests\CertificateRequest;
use CovidGifts\WP\AbstractAdminPage;
use CovidGifts\WP\ModelListTable;

class AdminMainPage extends AbstractAdminPage {

    public static $action = 'covid-gifts-main';
    public static $title  = 'Gift Certificates';
    public static $slug   = 'covid-gifts-main';

    protected $endpoints = [
        'cvdapp_cancel' => 'markCertAsCancelled',
        'cvdapp_accept' => 'markCertAsAccepted',
        'cvdapp_use'    => 'markCertAsUsed',
        'cvdapp_unuse'  => 'markCertAsUnused',
        'cvdapp_paid'   => 'markCertAsPaid',
        'cvdapp_unpaid' => 'markCertAsUnpaid',
        'cvdapp_delete' => 'markCertAsDeleted',
    ];

    protected function enqueue()
    {
        $this->addScript('dist/table.js', $this->getAjaxObject());
        $this->addBootstrap();
    }

    public function handle()
    {
        echo '<h1>Gift Certificates</h1>';

        $table = new ModelListTable();
        $table->prepare_items();
        $this->printTable($table);

        // echo '<div style="padding: 30px"><div id="cvdapp"></div></div>';
    }

    private function handleRequest($action)
    {
        $request = new CertificateRequest($action);

        try {
            $response = $request->handle();
        } catch (\Exception $e) {
            if ($e instanceof \CovidGifts\App\Contracts\Exception) {
                \wp_send_json_error($e->toArray(), $e->getCode());
                \wp_die();
            } else {
                throw $e;
            }
        }

        return $this->respond($response);
    }

    public function markCertAsCancelled()
    {
        return $this->handleRequest('cancel');
    }

    public function markCertAsAccepted()
    {
        return $this->handleRequest('accept');
    }

    public function markCertAsUsed()
    {
        return $this->handleRequest('use');
    }

    public function markCertAsDeleted()
    {
        return $this->handleRequest('delete');
    }

    public function markCertAsPaid()
    {
        return $this->handleRequest('paid');
    }

    public function markCertAsUnpaid()
    {
        return $this->handleRequest('unpaid');
    }

    public function markCertAsUnused()
    {
        return $this->handleRequest('unused');
    }

    private function printTable($table)
    {
        $count = count( $table->items );
        echo '<div class="wrap"><h1 class="wp-heading-inline">' . $table->getTitle() . '</h1>';
        echo '<span class="title-count theme-count">'.$count.'</span>';
        // echo '<a class="page-title-action" href="' . \admin_url( 'admin-ajax.php' ) . '?action=' . $this->exportAction . '" target="_blank">Export All</a>';
        // echo '<a class="page-title-action" href="' . \admin_url( 'admin-ajax.php' ) . '?action=' . $this->mailAction . '" target="_blank">Email All</a>';
        echo '<hr class="wp-header-end">';

        echo '<div id="cvdapp"><form id="full-list-form" method="get"><cvdapp-table>';

        $table->display();

        echo '</cvdapp-table></form></div></div>';

        // add_action('admin_footer', [$this, 'footerScript']);
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
