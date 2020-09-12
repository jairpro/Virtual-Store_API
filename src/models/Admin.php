<?php

class Admin extends User {

  const TYPE_DEV = 'D';
  const TYPE_ADMIN = 'A';
  const TYPE_OPERATOR = 'O';
  const TYPE_ALL = '*';

  protected $tableName = 'admins';
}
