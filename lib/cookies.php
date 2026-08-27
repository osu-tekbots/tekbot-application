<?php

/**
 * Restores the visitor's cart session if possible or generates a new one if needed.
 * 
 * The cart is stored in $_SESSION['cart'], but PHP sessions expire frequently. A
 * dedicated 30-day cookie is used to persist the cart longer for students to come pick
 * up.
 */
function refreshCartSession($inventoryDao) {
    if (isset($_SESSION['cart']) && $_SESSION['cart'] !== false) {
        // Session is still active; no action needed
        return;
    }

    if (isset($_COOKIE['cartId'])) {
        // Session expired but cookie still active; restore the session
        $_SESSION['cart'] = $inventoryDao->getCartByID($_COOKIE['cartId']);
        // Update cart's last access date so deletion doesn't happen while in use
        $inventoryDao->refreshCartInDatabase($_SESSION['cart']);
    } else {
        // No evidence of any cart; generate a new one
        generateNewCartSession($inventoryDao);
    }
}

/**
 * Restores the visitor's cart session if possible or generates a new one if needed.
 * 
 * The cart is stored in $_SESSION['cart'], but PHP sessions expire frequently. A
 * dedicated 30-day cookie is used to persist the cart longer for students to come pick
 * up.
 */
function generateNewCartSession($inventoryDao) {
    $_SESSION['cart'] = $inventoryDao->createCartInDatabase();
    setcookie('cartId', $_SESSION['cart']->getIdKey(), time() + (86400 * 30), "/");
}