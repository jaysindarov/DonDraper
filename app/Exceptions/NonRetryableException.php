<?php

namespace App\Exceptions;

/**
 * Thrown when a generation error is permanent and the job should not be retried.
 * Examples: API rejected the prompt (content policy), empty response, bad credentials.
 */
class NonRetryableException extends \RuntimeException {}
