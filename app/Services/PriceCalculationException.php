<?php

namespace App\Services;

use Exception;

/**
 * Thrown by PriceCalculator::calculateWithValidation when the selected
 * options violate a group's constraints (single/multiple, min/max, required,
 * inactive option, etc).
 */
class PriceCalculationException extends Exception {}
