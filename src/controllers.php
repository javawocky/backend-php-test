<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        // Bug fix - SQL injection.
        // Note: It is identified that the password is stored in plain text.  In a production system
        // this would be encrypted.
        $sql = "SELECT * FROM users WHERE username = ? and password = ?";
        $user = $app['db']->fetchAssoc($sql, [$username, $password]);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        // Bug Fix.  Sql injection.  Added user_id so the user can only access their own todos
        $sql = "SELECT * FROM todos WHERE id = ? and user_id = ?";
        $todo = $app['db']->fetchAssoc($sql,[$id, $user['id']]);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        // Bug Fix. Sql injection
        $sql = "SELECT * FROM todos WHERE user_id = ?";
        $todos = $app['db']->fetchAll($sql,[$user['id']]);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
        ]);
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    $description = $request->get('description');

    // Do a server side validate in case the user trys bypassing the javascript validation.
    if(empty($description)) {
        return $app->redirect('/todo');
    }

    // Bug fix - SQL injection.
    $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
    $app['db']->executeUpdate($sql,[$user_id,$description]);

    $app['session']->getFlashBag()->add('message', 'Todo Added');

    return $app->redirect('/todo');
});

$app->match('/todo/togglecomplete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    // Bug fix - SQL injection.
    $sql = "UPDATE todos SET completed = NOT completed WHERE id = ? and user_id = ?";
    $app['db']->executeUpdate($sql,[$id,$user['id']]);

    return $app->redirect('/todo');
});

$app->match('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if(!isset($id) || !is_numeric($id)) {
        return new JsonResponse(['error' => 'Please provide a valid Todo ID'], 400);
    }

    $sql = "SELECT * FROM todos WHERE id = ? and user_id = ?";
    $todo = $app['db']->fetchAssoc($sql,[$id,$user['id']]);

    if($todo === false) {
        return new JsonResponse(['error' => 'Todo not found'], 404);
    }

    $response = new JsonResponse();
    $response->setData($todo);
    return $response;
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Bug fix.  SQL Injectin and check a user is logged in and that the user owns the todo.
    $sql = "DELETE FROM todos WHERE id = ? and user_id = ?";
    $app['db']->executeUpdate($sql,[$id,$user['id']]);

    $app['session']->getFlashBag()->add('message', 'Todo '.$id.' has been deleted.');

    return $app->redirect('/todo');
});
