<?php namespace CovidGifts\Adapters\WP;

use CovidGifts\App\Contracts\Database as DatabaseInterface;

class Database implements DatabaseInterface {

    protected $db;

    public function __construct($table)
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $this->getPrefix().$table;
    }

    public function create($attributes)
    {
        $result = $this->db->insert( $this->table, $attributes);
        if (!$result || $this->db->last_error !== '') {
            return false;
        }
        return $this->db->insert_id;
    }

    public function update($id, $attributes)
    {
        $result = $this->db->update( $this->table, $attributes, ['id' => $id]);
        return !$result || $this->db->last_error !== '' ? false : true;
    }

    public function delete($id)
    {
        $result = $this->db->delete($this->table, ['id' => $id]);
        return !$result || $this->db->last_error !== '' ? false : true;
    }

    public function exists($key, $val)
    {
        $query = $this->db->prepare("SELECT count(*) FROM {$this->table} where {$key} = %s", [$val]);
        return $this->db->get_var( $query ) > 0;
    }

    public function all($orderby = null, $order = null)
    {
        list($sql, $bindings) = $this->addOrder("SELECT * FROM {$this->table}", [], $orderby, $order);
        return $this->db->get_results($this->db->prepare($sql, $bind), ARRAY_A);
    }

    protected function addOrder($sql, $bindings, $orderby, $order)
    {
        if ($orderby) {
            $sql .= " ORDER BY %s";
            $bindings[] = $orderby;
            if ($order) {
                $sql .= " %s";
                $bindings[] = $order;
            }
        }
        return [$sql, $bindings];
    }

    public function find($id)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE `id` = %d", [$id]);
        $row = $this->db->get_row($query, ARRAY_A);
        return $row;
    }

    public function findBy($key, $val, $orderby = null, $order = null)
    {
        list($sql, $bindings) = $this->addOrder("SELECT * FROM {$this->table} WHERE `{$key}` = %s", [$val], $orderby, $order);
        return $this->db->get_row($this->db->prepare($sql, $bindings), ARRAY_A);
    }

    public function getPrefix()
    {
        return $this->db->prefix;
    }
}