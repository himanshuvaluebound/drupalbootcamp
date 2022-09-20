<?php

namespace Drupal\email_contact\Controller;

use Drupal\user\Entity\User;

/**
 *
 */
class UserRoleOther {
  public function getUserNameWithRole() {
    $user = User::create();
    $user->setPassword("password");
    $user->enforceIsNew();
    $user->setEmail("kalia@brijesh.com");
    $user->setUsername("brijesh_kalia");
    $user->addRole('administrator');

    $role_name = $user->getEmail() . '_' . $user->getUsername();
    return $role_name;
  }
}