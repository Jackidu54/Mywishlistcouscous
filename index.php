<?php
require_once 'vendor/autoload.php';
use \Illuminate\Database\Capsule\Manager as DB;
use mywishlist\controleur\ControleurListe;
use mywishlist\controleur\ControleurItem;
use mywishlist\controleur\ControleurUrl;
use mywishlist\controleur\ControleurUser;
use mywishlist\controleur\Authentication;


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
session_start();

//Actions sur les listes


$app->get('/liste/display', function () {
    $app = Slim\Slim::getInstance();
    if(isset($_SESSION['profile'])){
        $control=new ControleurListe();
        $control->afficherListes();
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('affiche_listes');

$app->get('/liste/display/all', function() {
    if(isset($_SESSION['profile']) && Authentication::checkAccessRights(Authentication::$ACCESS_ADMIN)){
        $control=new ControleurListe();
        $control->afficherAdminListes();
    }else{
        $app = Slim\Slim::getInstance();
        $app->redirect('/liste/display');
    }
})->name('listes_all');


$app->get('/liste/display/:id', function ($id) {
    if(isset($_SESSION['profile'])){    
        $control=new ControleurListe();
        $control->afficherListe($id);
    }
})->name('affiche_1_liste');

$app->post('/liste/delete/:id', function($id) {
    if(isset($_SESSION['profile'])){
        $control=new ControleurListe();
        $control->supprimerListe($id);
        $url = ControleurUrl::urlName('affiche_listes');
        header('Location: '.$url);
        exit();
    }
})->name('supprimer_liste');

$app->post('/liste/create/valide', function () {
    $app = \Slim\Slim::getInstance();
    $control=new ControleurListe();
    if(isset($_SESSION['profile'])){
    if($app->request->post('titre')!=null && $app->request->post('description')!=null && isset($_SESSION['profile'])){
        $user_id = $_SESSION['profile']['id'];
        $titre = filter_var($app->request->post('titre'), FILTER_SANITIZE_STRING); 
        $description = filter_var($app->request->post('description'), FILTER_SANITIZE_STRING);
        $control->creerListe($user_id, $titre, $description);
    }
    $app->redirect('/liste/display');
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('validation_liste');

$app->post('/liste/modify/valide/:id', function ($id) {
    if(isset($_SESSION['profile'])){
	$app = \Slim\Slim::getInstance();
    $control=new ControleurListe();
    $user_id = 1; //temporaire
    $titre = filter_var($app->request->post('titre'), FILTER_SANITIZE_STRING); 
    $description = filter_var($app->request->post('description'),FILTER_SANITIZE_STRING); 
    if(isset($user_id) && isset($titre) && isset($description)){
        $control->modifierListe($id, $titre,$description);
    }
    $url = ControleurUrl::urlName('affiche_listes');
    header('Location: '.$url);
    exit();
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('valide_liste');

$app->post('/liste/modify/:id', function ($id) {
    if(isset($_SESSION['profile'])){
    $control=new ControleurListe();
    $control->afficherModificationListe($id);
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('modifie_liste');


$app->get('/liste/create', function () {
    if(isset($_SESSION['profile'])){
    $control=new ControleurListe();
    $control->afficheCreationListe();
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('creation_liste');

$app->get('/liste/users/:id', function($id) {
    if(isset($_SESSION['profile'])){
    $control=new ControleurListe();
    $control->afficherContributeurs($id);
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('contributeurs');

$app->post('/liste/user/delete/:no/:id', function($id_liste, $id_user) {
    if(isset($_SESSION['profile'])){
    $control=new ControleurListe();
    $control->supprimerGuest($id_liste, $id_user);
    \Slim\Slim::getInstance()->redirect('/liste/users/'.$id_liste);
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('supprimer_guest');

$app->post('/liste/user/add/:no', function($id_liste) {
    if(isset($_SESSION['profile'])){
    $control=new ControleurListe();
    $control->ajouterGuest($id_liste);
    \Slim\Slim::getInstance()->redirect('/liste/users/'.$id_liste);
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('ajouter_guest');

$app->post('/liste/message/:id', function ($id) {
    if(isset($_SESSION['profile'])){
    $app = \Slim\Slim::getInstance();
    $control=new ControleurListe();
    $message = $app->request->post('message');
    $control->ajouterMessage($id, $message);
    $url = ControleurUrl::urlId('affiche_1_liste', $id);
    header("Location: ".$url);
    exit();
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('creer_message');

$app->post('/liste/partage/:id', function ($id) {
    if(isset($_SESSION['profile'])){
    $control=new ControleurListe();
    $control->changerUrlPartage($id);
    $url = ControleurUrl::urlId('affiche_1_liste', $id);
    header("Location: ".$url);
    exit();
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('partager_liste');

//action sur les listes partagees

$app->get('/partage/:id', function ($id) {
    if(isset($_SESSION['partage'])){
        $control=new ControleurListe();
        $control->afficherListePartagee($_SESSION['partage']);
    }else{
        \Slim\Slim::getInstance()->redirect("/partage/connection/$id");
    }
})->name('afficher_liste_partagee');

$app->get('/partage/connection/:id', function ($id) {
    $control=new ControleurUser();
    $control->afficherPanelPartage($id);
})->name('connection_partage');

$app->post('/partage/inscription/:id', function ($id) {
    $_SESSION['profile'] = null;
    $control=new ControleurUser();
    $app = \Slim\Slim::getInstance();
    $mail=$app->request->post('mail');
    $control->InscrirePartage($id,$mail);
    $url = ControleurUrl::urlId('afficher_liste_partagee', $id);
    header("Location: ".$url);
    exit();
})->name('creer_partage');

//Actions sur les items


$app->post('/item/ajouter/:id', function($id) {
    if(isset($_SESSION['profile'])){
    $control=new ControleurItem();
    $control->createurItem($id);
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('createur_item');

$app->get('/item/display/:id', function ($id) {
    if(isset($_SESSION['profile'])){
    $control=new ControleurItem();
    $control->afficherItem($id);
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('affiche_1_item');


$app->post('/item/creer/:id', function ($id) {
    if(isset($_SESSION['profile'])){
    $app = \Slim\Slim::getInstance();
    $control=new ControleurItem();
    $titre = filter_var($app->request->post('nom'), FILTER_SANITIZE_STRING);
    $description = filter_var($app->request->post('descr'),FILTER_SANITIZE_STRING);
    $url = filter_var($app->request->post('url'),FILTER_SANITIZE_STRING);
    $tarif = filter_var($app->request->post('tarif'),FILTER_SANITIZE_STRING);
    if(isset($titre) && isset($description)){
        $control->ajouterItem($id,$titre,$description, $url, $tarif);
    }
    $app->redirect('/liste/display/'.$id);
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('ajoute_item_valide');

$app->post('/item/delete/:id', function ($id) {
    if(isset($_SESSION['profile'])){
    $app = \Slim\Slim::getInstance();
    $control = new ControleurItem();
    $control->supprimerItem($id);
    $app->redirect('/liste/display');
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('delete_item');

$app->get('/user/pannel/:id', function($id) {
    if(isset($_SESSION['profile'])){
    $app = \SLim\Slim::getInstance();
    $cu = new ControleurUser();
    $cu->afficherPannel($id);
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('pannel');

$app->post('/user/pannel/change/:id', function($id) {
    $app = \Slim\Slim::getInstance();
    if(isset($_SESSION['profile']) && $app->request->post('newRole')!=null){
        $cu = new ControleurUser();
        $cu->changerDroit($id);
        $app->redirect('/user/pannel/0');
    }else{
        $app->redirect('/user/connection');
    }
})->name('changer_role');

//Action sur l'utilisateur

$app->post('/user/changePass', function() {
    if(isset($_SESSION['profile'])){
    $app = \Slim\Slim::getInstance();
    if($app->request->post('pass')!=null && null!=$app->request->post('newPass') && $app->request->post('passVerif')!=null){
        $cu = new ControleurUser();
        $cu->changePass($app->request->post('pseudo'), $app->request->post('pass'),$app->request->post('newPass'), $app->request->post('passVerif'));
    }else {
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
    }else{
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('changePass');

$app->get('/user/connection', function() {
    $_SESSION['profile'] = null;
    $control=new ControleurUser();
    $control->afficherFormConnect();
})->name('connection');

$app->get('/user/inscription', function() {
    $_SESSION['profile'] = null;
    $control=new ControleurUser();
    $control->afficherFormInscript();
})->name('inscription');

$app->post('/user/create', function() {
    $rep = Authentication::createUser();
        
    
    if($rep){
        $url = ControleurUrl::urlName('affiche_listes');
        header("Location: ".$url);
    }else {
        $url = ControleurUrl::urlName('inscription');
        header("Location: ".$url);
    }
    exit();
})->name('creer_user');

$app->post('/user/connect', function() {
    $app = \Slim\Slim::getInstance();
    
        Authentication::authenticate($app->request->post('pseudo'), $app->request->post('pass'), Authentication::$OPTION_LOADPROFILE, NULL);
    
    if(isset($_SESSION['profile'])){
        $app->redirect('/liste/display');
    }else {
        \Slim\Slim::getInstance()->redirect('/user/connection');
    }
})->name('connect_user');

$app->post('/user/delete/:id', function($id){
    $cu = new ControleurUser();
    $cu->supprimerUser($id);
    \Slim\Slim::getInstance()->redirect('/user/pannel/0');
})->name('supprimer_user');



//actions non finies

$app->get('/item/reserve/:id', function ($id) {
    echo "yolo";
})->name('reserve_item');

$app->get('/item/cancel/:id', function ($id) {
    echo "tu annules $num";
})->name('annule_item');


$app->get('/', function () {
    $url = ControleurUrl::urlName('connection');
    header('Location: '.$url);
    exit();
})->name('route_defaut');


$app->run();