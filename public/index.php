<?php
require_once __DIR__ . "/../app/core/Router.php";
require_once __DIR__ . "/../app/controller/LoginController.php";

$router = new Router();

$router->add("", function () {
  require __DIR__ . '/../app/view/pages/Login.php';
});

$router->add("account_sample", function () {
  require __DIR__ . '/../app/view/pages/AccPage.php';
});

$router->add("register", function () {
  require __DIR__ . '/../app/view/pages/Signup.php';
});

$router->add("home", function () {
  require __DIR__ . '/../app/view/pages/customer/HomePage.php';
});

$router->add("movies", function () {
  require __DIR__ . '/../app/view/pages/customer/MoviesPage.php';
});

$router->add("cinemas", function () {
  require __DIR__ . '/../app/view/pages/customer/CinemasPage.php';
});

$router->add("buy", function () {
  require __DIR__ . '/../app/view/pages/customer/BuyPage.php';
});

$router->add("schedule", function () {
  require __DIR__ . '/../app/view/pages/customer/SchedulePage.php';
});

$router->add("checkout", function () {
  require __DIR__ . '/../app/view/pages/customer/CheckoutPage.php';
});

$router->add("account", function () {
  require __DIR__ . '/../app/view/pages/AccPage.html';
});

$router->add("admin", function () {
  require __DIR__ . '/../app/view/pages/admin/AdminPage.html';
});

$router->add("admin/user", function () {
  require __DIR__ . '/../app/view/pages/admin/UsersPage.html';
});

$router->add("admin/movies", function () {
  require __DIR__ . '/../app/view/pages/admin/MoviesPageA.html';
});

$router->handleRequest();
