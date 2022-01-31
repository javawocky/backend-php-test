<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

const ITEMS_PER_PAGE = 5;

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
        $userService = new UserService($app);
        $user = $userService->fetchByUsernameAndPassword($username, $password);

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

    $todoService = new TodoService($app['db']);

    if ($id){
        $todo = $todoService->fetchByIdAndUser($id, $user['id']);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {

        $todos = $todoService->fetchAllByUser($user['id']);

        $page = $app['request']->get('page') !== null ? $app['request']->get('page') : 1 ;
        $pager = new Pager(ITEMS_PER_PAGE, count($todos));
        $pager->setCurrentPage($page);
        $todoSubset = array_slice($todos, $pager->getStartIndex(), ITEMS_PER_PAGE);

        return $app['twig']->render('todos.html', [
            'todos' => $todoSubset,
            'pager' => $pager
        ]);
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $description = $request->get('description');

    // Do a server side validate in case the user trys bypassing the javascript validation.
    if(empty($description)) {
        return $app->redirect('/todo');
    }

    $todoService = new TodoService($app['db']);

    $todo = new Todo();
    $todo->setUserId($user['id']);
    $todo->setDescription($description);
    $todoService->add($todo);

    $app['session']->getFlashBag()->add('message', 'Todo Added');

    return $app->redirect('/todo');
});

$app->match('/todo/togglecomplete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $todoService = new TodoService($app['db']);

    $page = $app['request']->get('page') !== null ? $app['request']->get('page') : 1 ;

    $todoService->toggleCompleted($id, $user['id']);
    return $app->redirect('/todo?page='.$page);
});

$app->match('/todo/{id}/json', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if(!isset($id) || !is_numeric($id)) {
        return new JsonResponse(['error' => 'Please provide a valid Todo ID'], 400);
    }

    $todoService = new TodoService($app['db']);

    $todoEntity = $todoService->fetchByIdAndUser($id,$user['id']);
    if($todoEntity === false) {
        return new JsonResponse(['error' => 'Todo not found'], 404);
    }

    $todo['id'] = $todoEntity->getId();
    $todo['description'] = $todoEntity->getDescription();
    $todo['completed'] = $todoEntity->isCompleted();

    $response = new JsonResponse();
    $response->setData($todo);
    return $response;
});

$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $todoService = new TodoService($app['db']);

    $todoService->deleteByIdAndUser($id, $user['id']);

    $app['session']->getFlashBag()->add('message', 'Todo '.$id.' has been deleted.');

    return $app->redirect('/todo');
});
