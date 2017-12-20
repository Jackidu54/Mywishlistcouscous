<?php
require_once 'vendor/autoload.php';
use mywishlist\controleur\ControleurListe;
use \Illuminate\Database\Capsule\Manager as DB;
use mywishlist\controleur\ControleurItem;
$db = new DB();
$t=parse_ini_file( 'src/conf/conf.ini' );
$db->addConnection( [
    'driver' => $t['driver'],
    'host' =>  $t['host'],
    'database' =>  $t['database'],
    'username' =>  $t['username'],
    'password' =>  $t['password'],
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => ''
] );
$db->setAsGlobal();
$db->bootEloquent();
$app = new \Slim\Slim();

$app->get('/liste/display', function () {
    $control=new ControleurListe();
    $control->afficherListes();
})->name('affiche_listes');

$app->get('/liste/display/:num', function ($num) {
    $control=new ControleurListe();
    $control->afficherListe($num);
})->name('affiche_1_liste');

$app->post('/liste/delete/:id', function($id) {
    $control=new ControleurListe();
    $control->supprimerListe($id);
    header('Location: /liste/display');
    exit();
})->name('supprimer liste');

$app->post('/liste/create/valide', function () {
    $app = \Slim\Slim::getInstance();
    $control=new ControleurListe();
    if($app->request->post('titre')!=null && $app->request->post('description')!=null){
        //$user = filter_var($app->request->post('user'), FILTER_SANITIZE_STRING);
        $user_id = 1;
        $titre = filter_var($app->request->post('titre'), FILTER_SANITIZE_STRING); 
        $description = filter_var($app->request->post('description'), FILTER_SANITIZE_STRING);
        $control->creerListe($user_id, $titre, $description);
    }
})->name('validation_liste');

$app->post('/liste/modify/valide/:id', function ($id) {
	$app = \Slim\Slim::getInstance();
    $control=new ControleurListe();
    $user_id = 1; //temporaire
    $titre = filter_var($app->request->post('titre'), FILTER_SANITIZE_STRING); 
    $description = filter_var($app->request->post('description'),FILTER_SANITIZE_STRING); 
    if(isset($user_id) && isset($titre) && isset($description)){
        $control->modifierListe($id, $titre,$description);
    }
    header('Location: /liste/display');
    exit();
})->name('valide_liste');

$app->post('/liste/modify/:id', function ($id) {
    $control=new ControleurListe();
    $control->afficherModificationListe($id);
    header('Location: /liste/display');
    exit();
})->name('modifie_liste');

$app->get('/', function () {
    header('Location: /liste/display');
    exit();
})->name('route_defaut');

$app->get('/liste/create', function () {
    $control=new ControleurListe();
    $control->afficheCreationListe();
})->name('creation_liste');

$app->post('/liste/message/:id', function ($id) {
    $app = \Slim\Slim::getInstance();
    $control=new ControleurListe();
    $message = $app->request->post('message');
    $control->ajouterMessage($id, $message);
    header("Location: /liste/display/$id");
    exit();
})->name('cree_message');


$app->post('/item/ajouter/:id', function($id) {
    $control=new ControleurItem();
    $control->createurItem($id);
})->name('createur_item');

$app->get('/item/display/:num', function ($num) {
    $control=new ControleurItem();
    $control->afficherItem($num);
})->name('affiche_1_item');


$app->post('/item/creer/:id', function ($id) {
    $app = \Slim\Slim::getInstance();
    $control=new ControleurItem();
    $titre = filter_var($app->request->post('nom'), FILTER_SANITIZE_STRING);
    $description = filter_var($app->request->post('descr'),FILTER_SANITIZE_STRING);
    $url = filter_var($app->request->post('url'),FILTER_SANITIZE_STRING);
    $tarif = filter_var($app->request->post('tarif'),FILTER_SANITIZE_STRING);
    if(isset($titre) && isset($description)){
        $control->ajouterItem($id,$titre,$description, $url, $tarif);
    }
    header('Location: /liste/display');
    exit();
})->name('ajoute_item_valide');

$app->get('/item/reserve/:num', function ($num) {
    echo "yolo";
})->name('reserve_item');

$app->get('/item/cancel/:num', function ($num) {
    echo "tu annules $num";
})->name('annule_item');



$app->run();