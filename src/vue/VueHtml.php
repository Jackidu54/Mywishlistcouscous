<?php

namespace mywishlist\vue;

use mywishlist\controleur\ControleurUrl;
use mywishlist\controleur\Authentication;

class VueHtml{

	public static $ARTICLE = 1;

	private $contenu;
	private $select;

	function __construct($contenu, $select){
		$this->contenu = $contenu;
		$this->select = $select;
	}

	function render(){


		if($this->select == VueHtml::$ARTICLE){
				$html = "";
				$urlConnect = ControleurUrl::urlName('connection');
				$urlPannel = ControleurUrl::urlId('pannel', 0);
				$urlafficherListes=ControleurUrl::urlName('affiche_listes');
				$urlCreerListe=ControleurUrl::urlName('creation_liste');
				$user = "";
				if(isset($_SESSION['profile']['pseudo']) && isset($_SESSION['profile']['jeton'])){
					$user = $_SESSION['profile']['pseudo'];
			        if($_SESSION['profile']['jeton']==4){
			            $role = 'super admin';
			        }else if($_SESSION['profile']['jeton']==2){
			            $role = 'moderateur';
			        }else if($_SESSION['profile']['jeton']==3){
			            $role = 'Administrateur';
			        }else if($_SESSION['profile']['jeton']==1){
			        	$role = 'Utilisateur';
			        }
		    	}else $role = 'visiteur '.$_SESSION['email'];
		        $html = <<<html
		<!DOCTYPE html>
		<html lang="fr">
		<head>
		  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		  <title>MyWishlist</title>
		  <link rel="stylesheet" href="/www/jacque14u/ProjetPhp/web/css/style.css">


		</head>
		<body>

		<div class="container">

		<header>
		   <h1>Liste de Cadeaux</h1>
		</header>
html;
		if($_SESSION['profile']['droit']>0){
		$html = $html . <<<html
		<nav>
		  <ul>
            <li><a href="$urlConnect"><input type="button" value="Se d�connecter"></a></li>
		    <li><a href="$urlafficherListes"><input type="button" value="Affiche mes listes"></a></li>
		    <li><a href="$urlCreerListe"><input type="button" value="Cr�er une liste"></a></li>
		    <li><a href="$urlPannel"><input type="button" value="Param&#232;tres"></a></li>
            
html;
		}
		    if(Authentication::checkAccessRights(Authentication::$ACCESS_SUP_ADMIN)){
		        $url = ControleurUrl::urlName('listes_all');
		        $html = $html . <<<html
		    <li>Options sp�ciales : </li>
		    <li><a href="$url"><input type="button" value="Afficher toutes les listes"></a></li>
html;
		    }
		    $html = $html . <<<html
		  </ul>
		</nav>

		<article>
		  $this->contenu
		</article>

		<footer>
		<div id="gauche">
		Bienvenue $role $user
		</div>
		<div id="droite">
		&#169; Copyright 2018 Jacque_Cordier_Brugneaux_Oberhausser_Gomas
		</div>

		</footer>

		</div>
		</body>
		</html>
html;
		}
		return $html;
	}

}