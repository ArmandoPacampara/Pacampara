<?php
require_once __DIR__ . "/../app/core/Router.php";
require_once __DIR__ . "/../app/controller/LoginController.php";

$router = new Router();

$router->add("", function () {
  require __DIR__ . '/../app/view/pages/Login.php';
});

$router->add("register", function () {
  require __DIR__ . '/../app/view/pages/Signup.php';
});

$router->add("home", function () {
  require __DIR__ . '/../app/view/pages/HomePage.php';
});

$router->add("movies", function () {
  require __DIR__ . '/../app/view/pages/MoviesPage.php';
});

$router->add("cinemas", function () {
  require __DIR__ . '/../app/view/pages/CinemasPage.php';
});

$router->add("buy", function () {
  require __DIR__ . '/../app/view/pages/BuyPage.php';
});

$router->add("schedule", function () {
  require __DIR__ . '/../app/view/pages/SchedulePage.php';
});

$router->add("checkout", function () {
  require __DIR__ . '/../app/view/pages/CheckoutPage.php';
});

$router->add("account", function () {
  require __DIR__ . '/../app/view/pages/AccPage.html';
});

$router->add("admin", function () {
  require __DIR__ . '/../app/view/pages/AdminPage.html';
});

$router->add("admin/user", function () {
  require __DIR__ . '/../app/view/pages/UsersPage.html';
});

$router->add("admin/movies", function () {
  require __DIR__ . '/../app/view/pages/MoviesPageA.html';
});

$router->handleRequest();
