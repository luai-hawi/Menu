<?php

namespace App\Services\Admin;

use RuntimeException;

/**
 * A refused admin action whose message is already translated and safe to show.
 */
class AdminActionException extends RuntimeException
{
}