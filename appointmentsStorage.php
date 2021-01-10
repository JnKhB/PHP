<?php
include_once('storage.php');

class AppointmentsStorage extends Storage {
  public function __construct() {
    parent::__construct(new JsonIO('appointments.json'));
  }
}