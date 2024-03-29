<?php
declare(strict_types=1);

// Extras needed by PHPLint.
/*. array[int]int .*/ $INVENTORY_LEVEL = array();
/*. array[int]int .*/ $MAX_QUANTITIES = array();
/*. array[int]int .*/ $PRICES = array();

// Clean up all state set up by tests.
function clear_cart66_testing_state(): void {
  global $INVENTORY_LEVEL, $MAX_QUANTITIES, $PRICES;
  $INVENTORY_LEVEL = array();
  $MAX_QUANTITIES = array();
  $PRICES = array();
}

class Cart66Product {
  public int $max_quantity = 0;
  public int $price = 0;
  function __construct(int $id) {
    global $MAX_QUANTITIES, $PRICES;
    if (isset($MAX_QUANTITIES[$id])) {
      $this->max_quantity = $MAX_QUANTITIES[$id];
    }
    if (isset($PRICES[$id])) {
      $this->price = $PRICES[$id];
    }
  }

  // Mock functions used by SUT.
  public static function checkInventoryLevelForProduct(int $id): int {
    global $INVENTORY_LEVEL;
    return $INVENTORY_LEVEL[$id];
  }

  // Helper functions used by tests.
  public static function setInventoryLevelForProduct(int $id, int $level): void {
    global $INVENTORY_LEVEL;
    $INVENTORY_LEVEL[$id] = $level;
  }
  public static function setMaxQuantity(int $id, int $max): void {
    global $MAX_QUANTITIES;
    $MAX_QUANTITIES[$id] = $max;
  }
  public static function setPrice(int $id, int $price): void {
    global $PRICES;
    $PRICES[$id] = $price;
  }
}
