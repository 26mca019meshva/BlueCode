<?php
/**
 * SupplyGuard AI - Base Model
 * 
 * Provides database access for all models.
 */

class Model {
    protected Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }
}
