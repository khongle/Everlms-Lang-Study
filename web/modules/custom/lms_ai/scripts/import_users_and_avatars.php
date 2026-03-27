<?php

use Drupal\user\Entity\User;
use Drupal\file\Entity\File;

$json_path = \Drupal::root() . '/modules/custom/lms_ai/JSON/ai_users.json';
if (!file_exists($json_path)) {
  echo "Error: JSON file not found at $json_path\n";
  exit;
}

$json_data = file_get_contents($json_path);
$users = json_decode($json_data, TRUE);

// Thư mục chứa hình ảnh avatar do AI sinh ra (đã được copy vào project cho DDEV đọc được)
$avatar_dir = \Drupal::root() . '/modules/custom/lms_ai/scripts/avatars';

foreach ($users as $user_data) {
  $name = $user_data['name'];
  $email = $user_data['mail'];
  
  // Check if user exists
  $existing_users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['name' => $name]);
  $user = reset($existing_users);

  if (!$user) {
    $user = User::create();
    $user->setPassword($user_data['pass']);
    $user->enforceIsNew();
    $user->setEmail($email);
    $user->setUsername($name);
    
    // Set roles
    foreach ($user_data['roles'] as $role) {
      if ($role !== 'authenticated' && $role !== 'administrator') {
        $user->addRole($role);
      }
    }
    
    // Set fields
    if ($user->hasField('field_first_name')) {
      $user->set('field_first_name', $user_data['field_first_name']);
    }
    if ($user->hasField('field_last_name')) {
      $user->set('field_last_name', $user_data['field_last_name']);
    }
    if ($user->hasField('field_job_title')) {
      $user->set('field_job_title', $user_data['field_job_title']);
    }
    if ($user->hasField('field_company')) {
      $user->set('field_company', $user_data['field_company']);
    }
    if ($user->hasField('field_location')) {
      $user->set('field_location', $user_data['field_location']);
    }
    if ($user->hasField('field_bio')) {
      $user->set('field_bio', ['value' => $user_data['field_bio'], 'format' => 'full_html']);
    }
    
    $user->activate();
    $user->save();
    echo "Created user: $name\n";
  } else {
    echo "User $name already exists, updating fields.\n";
    if ($user->hasField('field_first_name')) {
      $user->set('field_first_name', $user_data['field_first_name']);
    }
    if ($user->hasField('field_last_name')) {
      $user->set('field_last_name', $user_data['field_last_name']);
    }
    if ($user->hasField('field_job_title')) {
      $user->set('field_job_title', $user_data['field_job_title']);
    }
    if ($user->hasField('field_company')) {
      $user->set('field_company', $user_data['field_company']);
    }
    if ($user->hasField('field_location')) {
      $user->set('field_location', $user_data['field_location']);
    }
    if ($user->hasField('field_bio')) {
      $user->set('field_bio', ['value' => $user_data['field_bio'], 'format' => 'full_html']);
    }
    $user->save();
  }

  // Find avatar image for this user
  $prefix = 'avatar_' . strtolower(preg_replace('/[^a-zA-Z]/', '', $user_data['field_first_name'] . $user_data['field_last_name']));
  $avatar_files = glob("$avatar_dir/{$prefix}_*.png");
  
  if (!empty($avatar_files)) {
    $avatar_file_path = $avatar_files[0];
    
    $file_system = \Drupal::service('file_system');
    $avatar_pub_dir = 'public://avatars';
    $file_system->prepareDirectory($avatar_pub_dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);
    
    $file_uri = 'public://avatars/' . basename($avatar_file_path);
    $copied_uri = $file_system->copy($avatar_file_path, $file_uri, \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);
    
    if ($copied_uri) {
      // Find existing file entity with this uri
      $existing_files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $copied_uri]);
      $file = reset($existing_files);
      
      if (!$file) {
        $file = File::create([
          'uri' => $copied_uri,
          'uid' => $user->id(),
          'status' => 1,
        ]);
        $file->save();
      }
      
      if ($user->hasField('user_picture')) {
        $user->set('user_picture', [
          'target_id' => $file->id(),
          'alt' => $user_data['field_first_name'] . ' ' . $user_data['field_last_name'] . ' Avatar',
        ]);
        $user->save();
        echo " - Assigned avatar to $name\n";
      }
    } else {
      echo " - Failed to copy avatar file for $name\n";
    }
  } else {
    echo " - No avatar found for $name (looked for prefix {$prefix}_*.png at $avatar_dir)\n";
  }
}

echo "Done importing users.\n";
