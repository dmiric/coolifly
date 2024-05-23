<?php

namespace Drupal\eca;

/**
 * Provides helper functions to enable and reset extended error handling.
 */
trait ErrorHandlerTrait {

  /**
   * The error level before enabling extended mode.
   *
   * @var int
   */
  protected int $originalErrorHandling = 0;

  /**
   * Helper function to enable extended error handling.
   */
  protected function enableExtendedErrorHandling(string $context): void {
    $buildMessage = static function (string $message) use ($context): string {
      return 'ECA ran into error from third party in the context of "' . $context . '": ' . $message;
    };

    // Set the error handler.
    set_error_handler(function (int $level, string $message, string $file, int $line) use ($buildMessage): bool {
      if ($level === E_USER_DEPRECATED || $level === E_DEPRECATED) {
        return FALSE;
      }
      throw new \ErrorException($buildMessage($message), 0, $level, $file, $line);
    });

    // Handle fatal errors.
    register_shutdown_function(function () use ($buildMessage) {
      $error = error_get_last();
      if ($error === NULL) {
        return;
      }
      $exception = new \ErrorException($buildMessage($error['message']), 0, $error['type'], $error['file'], $error['line']);
      echo 'Error: ', $exception->getMessage(), "\n";
    });

    // Turn off reporting.
    $this->originalErrorHandling = error_reporting(0);
  }

  /**
   * Helper function to reset extended error handling.
   */
  protected function resetExtendedErrorHandling(): void {
    error_reporting($this->originalErrorHandling);
    restore_error_handler();
  }

}
