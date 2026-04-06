<?php

declare(strict_types=1);

namespace App\Services\VideoGeneration;

/** Non-retryable error — bad prompt, invalid parameters, content policy violation. */
final class VideoGenerationException extends \RuntimeException {}
