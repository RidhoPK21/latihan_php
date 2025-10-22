<?php
session_start();
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 'index';
}

require_once(__DIR__ . '/../controllers/TodoController.php');

$todoController = new TodoController();
switch ($page) {
    case 'index':
        $todoController->index();
        break;
    case 'create':
        $todoController->create();
        break;
    case 'update':
        $todoController->update();
        break;
    case 'delete':
        $todoController->delete();
        break;
    case 'detail':
        $todoController->detail();
        break;
}