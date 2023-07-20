<?php

    require_once "includes/core/models/Newsletter.php";
    require_once "includes/core/models/daoNewsletter.php";
    
        switch ($action){
            
            case 'add':{
                if (!empty($_POST['email'])) {
                    $email = strip_tags($_POST['email']);

                    // Valider l'adresse email
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $message = "L'adresse email n'est pas valide.";
                        header('Location: ?page=index&message='.$message);
                        break;
                    }
                    // Vérifier si l'email existe déjà dans la base de données
                    $newsletterDAO = new NewsletterDAO();
                    $newsletter = $newsletterDAO->getNewsletterByEmail($email);
                    if ($newsletter) {
                        $message = "Votre email est déjà dans la Newsletter !";
                        header('Location: ?page=index&message='.$message);
                        break;
                    }
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
    				$email = $_POST['email'];
        		    if (isValidEmail($email)){
                        $email = htmlspecialchars($email, ENT_QUOTES);
                
                        // Générer et stocker un jeton CSRF
                        $csrf_token = bin2hex(random_bytes(32));
                        $_SESSION['csrf_token'] = $csrf_token;
                
                        $newsletter = new Newsletter($email);
                
                        // Ajouter l'abonné à la newsletter
                        if ($newsletterDAO->addSubscriber($newsletter)) {
                            $message = "Email ajouté à la Newsletter !";
                            header('Location: ?page=index&message='.$message);
                            exit();
                        } else {
                            $message = "Erreur d'enregistrement !";
                            header('Location: ?page=index&message='.$message);
                            }
                    } else {
                        $message = "Email incorrect !";
                        header('Location: ?page=index&message='.$message);
                    }
                    }else {
                        $message = "Aucun email ajouté à la Newsletter !";
                        header('Location: ?page=index&message='.$message);
                    break;
                    }
                }

            case 'delete':
                
                // Vérification de l'accès autorisé
		        if(!isset($_SESSION['login']) && ($_SESSION['isAdmin'] != 1 )) {
			    // Si l'utilisateur n'est pas connecté et n'est pas l'admin ou n'est pas l'auteur du contact, on le redirige vers l'index
			    header('Location: index.php');
		        exit();
		        }
                if (!empty($_GET['id'])) {
                    $newsletterDAO = new NewsletterDAO();
                    $id = $_GET['id'];
                    
                    $newsletter = new Newsletter('', null, $id);
                    
                    if ($newsletterDAO->removeSubscriber($newsletter)) {
                        header('Location: ?page=user&action=view');
                        exit();
                    } else {
                        $message = "Erreur de suppression !";
                    }
                }
                break;
                
            }
?>