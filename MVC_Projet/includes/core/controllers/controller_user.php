<?php
	switch ($action){
		case 'login':{
			require_once "includes/core/models/daoUser.php";
			require_once "includes/core/models/Personne.php";
	
			if (!empty($_POST)){
				$loginSaisi = htmlspecialchars($_POST['userMail']);
				$mdpSaisi = $_POST['password'];
				    
				if (!empty($loginSaisi) && !empty($mdpSaisi)) {
					
				function isValidEmail($email) {
						// Expression régulière pour valider l'adresse email
						$regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
						
						// Vérification du format de l'adresse email
						if (preg_match($regex, $email)) {
						     return true; 
						 } else {
						     return false; 
						 }
					}
					$email = $loginSaisi;
					if (isValidEmail($email)) {
						if (userExists($loginSaisi)){
							if (checkAuth($loginSaisi, $mdpSaisi)){
								//C'est bon, je suis authentifié
								$_SESSION['login'] = $loginSaisi;
								$maPersonne=getUserByLogin($loginSaisi);
								$_SESSION['idUser'] = $maPersonne->getId();
								$_SESSION['isAdmin'] = isAdmin($loginSaisi);
								
								// Analyser l'URL actuelle
								$url = $_SERVER['HTTP_REFERER'];
								$cleanedUrl = preg_replace('/[?&]message=[^&]+/', '', $url);
								
								// Rediriger vers la nouvelle URL nettoyée
								header('Location: ' . $cleanedUrl);
								exit();
								//header('Location: '.$_SERVER['HTTP_REFERER']);
							}else{
								$message = "Mauvaises informations d'identification !";
								header('Location: ?page=index&message='.$message);
							}
						}else{
							$message = "Cet utilisateur n'existe pas !";
							header('Location: ?page=index&message='.$message);
						}
					}else {
		            	$message = "Email Incorrect !";
		                header('Location: ?page=index&message='.$message);
					}
				}else {
					$message = "Aucune information saisie!";
		         header('Location: ?page=index&message='.$message);
				}
			}
			require_once "includes/core/views/view.phtml";
			break;  
		}
	
		case 'logout':{
			if (isset($_SESSION['login'])){
				unset($_SESSION['login']);
				unset($_SESSION['isAdmin']);
				session_destroy();
			}
			header('Location: index.php');
			break;
		}
	
	
		case 'view':{
			require_once "includes/core/models/daoUser.php";
			require_once "includes/core/models/daoContacts.php";
			require_once "includes/core/models/daoNewsletter.php";
			
			if(!isset($_SESSION['login'])){
				header('Location: index.php');
			}else{
				if (isset($_SESSION['login']) && $_SESSION['login'] != '' && $_SESSION['isAdmin'] == 1){
				$lesContacts = getAllContacts();
				
				$newsletterDAO = new NewsletterDAO();
				$lesNewsletters = $newsletterDAO->getAllSubscribers();
				}else{
					$lesContacts = array();
				    $idPersonne = $_SESSION['idUser'];
				    $idFilms = getFavorisByPersonne($idPersonne);
				    $idFilms2 = getSeeByPersonne($idPersonne);
				    $idFilms3 = getFuturByPersonne($idPersonne);
				}
			}
			require_once "includes/core/views/user_view.phtml";
			break;
		}
		
		
			case 'list':{
				require_once "includes/core/models/daoContacts.php";
				require_once "includes/core/models/daoNewsletter.php";
	
				$lesContacts = getAllContacts();
				$newsletterDAO = new NewsletterDAO();
				$lesNewsletters = $newsletterDAO->getAllSubscribers();
				
				require_once "includes/core/views/user_view.phtml";
				break;
			}
			
		default:{
		}
	}
