<?php
class Maintenance_model extends MY_Model {
            
  function __construct(){
    parent::__construct();
  }
  
  /*
  
    Methods for Cleaning Innecessary Data!!!
  
  */
  
  function clean_data_from_innexisting_lists(){
    // Clean empty lists
    $sql = "DELETE FROM $this->p_list
            WHERE user_id = 0 AND name IS NULL;";
    $this->db->query($sql);
    // Clean Items
    $sql = "DELETE FROM $this->p_list_items
            WHERE LIST_ID NOT IN (
              SELECT ID
              FROM $this->p_list
            );";
    $this->db->query($sql);
    // Clean Categories
    $sql = "DELETE FROM $this->p_list_category
            WHERE LIST_ID NOT IN (
              SELECT ID
              FROM $this->p_list
            );";
    $this->db->query($sql);
    // Clean Showrooms
    $sql = "DELETE FROM $this->p_list_showrooms
            WHERE LIST_ID NOT IN (
              SELECT ID
              FROM $this->p_list
            );";
    $this->db->query($sql);
  }
  
}