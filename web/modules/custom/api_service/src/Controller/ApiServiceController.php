<?php

namespace Drupal\api_service\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for api_service routes.
 */
class ApiServiceController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    $role = 'student';
    $students = $this->entityTypeManager()->getStorage('user')->loadByProperties([
      'status' => 1,
      'roles' => [$role],
    ]);
    foreach ($students as $roles) {
      $build[] = [
        'uid' => $roles->id(),
        'data' => [
          'uuid' => $roles->uuid(),
          'name' => $roles->get('name')->value,
        ],
      ];
    }
    return new JsonResponse($build);
  }

  /**
   * Builds a JSON response containing user data based on a stream title.
   *
   * @param string $stream
   *   The title of the stream to filter users by.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing user data filtered by the provided stream.
   */
  public function streamBuild($stream) {
    $role = 'student';
    $query = $this->entityTypeManager()->getStorage('node')
      ->getQuery()
      ->condition('type', 'stream')
      ->condition('title', $stream)
      ->accessCheck(FALSE)
      ->execute();
    $node_id = '';
    if (!empty($query)) {
      $node_id = reset($query);
    }
    $query = $this->entityTypeManager()->getStorage('user')->getQuery()
      ->condition('roles', $role)
      ->condition('field_stream', $node_id)
      ->accessCheck(FALSE)
      ->execute();
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($query);
    foreach ($users as $roles) {
      $build[] = [
        'uid' => $roles->id(),
        'data' => [
          'uuid' => $roles->uuid(),
          'name' => $roles->get('name')->value,
          'stream' => $stream,
        ],
      ];
    }
    return new JsonResponse($build);
  }

  /**
   * Builds a JSON response containing user data based on a joining year.
   *
   * @param int $year
   *   The joining year to filter users by.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing user data filtered by the provided joining year.
   */
  public function yearBuild($year) {
    $role = 'student';
    $user_query = $this->entityTypeManager()->getStorage('user')->getQuery()
      ->condition('roles', $role)
      ->condition('field_joining_year', $year)
      ->accessCheck(FALSE)
      ->execute();
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($user_query);
    foreach ($users as $roles) {
      $build[] = [
        'uid' => $roles->id(),
        'data' => [
          'uuid' => $roles->uuid(),
          'name' => $roles->get('name')->value,
          'joining_year' => $year,
        ],
      ];
    }
    return new JsonResponse($build);
  }

}
