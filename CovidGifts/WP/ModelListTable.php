<?php
namespace CovidGifts\WP;

use CovidGifts\WP\AdminManager;
use CovidGifts\WP\AjaxManager;
use CovidGifts\WP\SubmissionModel;

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ModelListTable extends \WP_List_Table {

    public $items;

    protected $_column_headers;

    public function __construct( $args = array() )
    {
        $this->_column_headers = [
            $this->get_columns(),
            array(),
            $this->get_sortable_columns(),
            'actions'
        ];
        parent::__construct( $args );
    }

    public function getTitle()
    {
        return 'Water Whizzes';
    }

    public function get_columns() {

        $fake = new SubmissionModel();
        $columns_keys = array_keys( $fake->getAttributes() );
        $columns = array('cb'  => '<input type="checkbox" />');//array('<input type="checkbox" />', false));
        foreach ( $columns_keys as $key ) {
            $columns[$key] = ucwords(str_replace('_', ' ', $key));// array( ucwords(str_replace('_', ' ', $key) ), true );
        }
        $columns['actions'] = 'Actions';

        if (isset($columns['hash'])) {
            unset($columns['hash']);
        }

        return $columns;

    }

    public function column_default( $item, $column_name ) {

        $deleteUrl = \admin_url( 'admin-ajax.php' ) . '?action=' . AdminManager::$delete_list_item_action . "&id={$item->id}";
        $approveUrl = \admin_url( 'admin-ajax.php' ) . '?action=' . AdminManager::$approve_list_item_action . "&id={$item->id}";
        $disapproveUrl = \admin_url( 'admin-ajax.php' ) . '?action=' . AdminManager::$disapprove_list_item_action . "&id={$item->id}";

        switch ( $column_name ) {
            case 'email':
                return '<a href="mailto:'.\esc_attr($item->email).'">'.\esc_html($item->email).'</a>';
            case 'actions':
                $delete = '<a href="'.$deleteUrl.'" class="ajaxLink deleteListItem" style="color:red">Delete</a>';

                if ($item->isApproved()) {
                    $approve = '<a href="'.$disapproveUrl.'" class="ajaxLink unapproveListItem">Unapprove</a>';
                } else {
                    $approve = '<a href="'.$approveUrl.'" class="ajaxLink approveListItem">Approve</a>';
                }
                return $approve . ' | ' . $delete;

            case 'cb':
                return '<input type="checkbox" data-id="'.\esc_attr($item->id).'" />';
            case 'photos':
                $strs = array_map(function ($photo) {
                    if (!$photo || !is_object($photo) || !isset($photo->url)) {
                        return '';
                    }
                    return '<a href="' . $photo->url . '" target="_blank">IMG</a>';
                }, $item->photos ?: []);
                return implode(' | ', $strs);
            case 'story':
                return $this->shortenString($item->story);
            case 'approved_at':
            case 'created_at':
            case 'updated_at':
                return '<span title="' . \esc_attr($item->$column_name) . '">'.$this->niceTime($item->$column_name).'</span>';
            default:
                return \esc_html($item->$column_name);
        }
    }

    protected function niceTime($date)
    {
        if (!$date) {
            return '';
        }
        $timestamp = strtotime($date);
        return \human_time_diff($timestamp) . ' ago';
    }

    protected function shortenString($string)
    {
        if (strlen($string) >= 20) {
            return '<span title="'. \esc_attr($string) . '">'.substr($string, 0, 17). " ...</span>";
        }
        else {
            return $string;
        }
    }

    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        $this->items = SubmissionModel::all();

    }

    public function no_items() {
        return 'No items yet.';
    }
}