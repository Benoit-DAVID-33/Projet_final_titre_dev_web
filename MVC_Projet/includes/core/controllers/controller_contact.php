<?php
	
	switch ($action){
		case 'list':{
			require_once "includes/core/models/daoContacts.php";
			$lesContacts = getAllContacts();
			require_once "includes/core/views/user_view.phtml";
			break;
		}
		
		case 'view':{
			break;
		}

		case 'edit': {
		    require_once "includes/core/models/daoContacts.php";
		
		    $id = $_GET['id'];
		    $unePersonne = getContactById($id);
		
		    // Vérification de l'accès autorisé
		    if (!isset($_SESSION['login']) && ($_SESSION['isAdmin'] != 1 || $_SESSION['login'] != $unePersonne->getId())) {
		        // Si l'utilisateur n'est pas connecté ou n'est ni l'administrateur ni l'auteur du contact, on le redirige vers l'accueil
		        header('Location: index.php');
		        exit();
		    }
		    if (!empty($_POST)) {
		        // Modification des champs si nécessaire
		        $newPassword = $_POST['chNewPassword'];
		        $oldPassword = $_POST['chOldPassword'];
		        $dateNaissance = date_create($_POST['chDateNaissance']);
		
		        // Récupération de l'avatar actuel depuis la base de données
		        $avatarPath = 'public/avatar/default.png';
		        $userAvatar = $unePersonne->getAvatar();
		        $fullName = $userAvatar; // Initialisation avec le chemin de l'avatar existant
		        if ($userAvatar != null) {
		            $avatarPath = $userAvatar;
		        }
		        // Traitement de la soumission de l'avatar
		        if (isset($_FILES['chAvatar']) && $_FILES['chAvatar']['error'] === UPLOAD_ERR_OK) {
		            // ...
		
		            $tmpFile = $_FILES['chAvatar']['tmp_name'];
		            $destPath = 'public/avatar/';
		            $fileName = basename($_FILES['chAvatar']['name']);
		            $fullName = $destPath . $fileName;
		            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
		            $maxFileSize = 5 * 1024 * 1024; // 5MB
		
		            // Vérification de la taille du fichier
		            if ($_FILES['chAvatar']['size'] <= $maxFileSize) {
		                // Vérification du type MIME
		                if (in_array($_FILES['chAvatar']['type'], $allowedMimeTypes)) {
		                    // Vérification que le fichier a été correctement uploadé
		                    if (move_uploaded_file($tmpFile, $fullName)) {
		                        $avatarPath = $fullName;
		                    } else {
		                        $message = "Erreur lors de l'upload du fichier !";
		                        header('Location: ?page=contact&action=edit&id=' . $id . '&message=' . $message);
		                        exit;
		                    }
				        		} else {
				                    $message = "Type de fichier non pris en charge !";
				                    header('Location: ?page=contact&action=edit&id=' . $id . '&message=' . $message);
				                    exit;
				                }
				        } else {
				        	$message = "Le fichier est trop volumineux !";
				            header('Location: ?page=contact&action=edit&id=' . $id . '&message=' . $message);
				            exit;
				        }
				}
				// Validation de la date de naissance
			    $originalDate = $_POST['chDateNaissance'];
			    $modifiedDate = strip_tags($_POST['chDateNaissance']);
			
			    if ($originalDate !== $modifiedDate) {
			        $message = "Valeur non valide pour la date de naissance !";
			        header('Location: ?page=contact&action=edit&id=' . $id . '&message=' . $message);
			        exit;
			    }
		        // Vérification des champs obligatoires
		        if (isset($_POST['chEmail']) && !empty($_POST['chEmail']) && isset($_POST['chOldPassword']) 
		        && !empty($_POST['chOldPassword']) && isset($_POST['chNewPassword']) && !empty($_POST['chNewPassword'])) {
		            //$email = $_POST['chEmail'];
		            $email = strip_tags($_POST['chEmail']);
		            $oldPassword = $_POST['chOldPassword'];
		            $newPassword = $_POST['chNewPassword'];
		
		            // Fonction de validation de l'adresse email
		            function isValidEmail($email){
		                // Expression régulière pour valider l'adresse email
		                $regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
		
		                // Vérification du format de l'adresse email
		                if (preg_match($regex, $email)) {
		                    return true;
		                } else {
		                    return false;
		                }
		            }
		            if (isValidEmail($email)) {
		                // Vérification si l'adresse email est déjà utilisée
		                $user = getContactByEmail(htmlspecialchars($email));
		                if ($user && $email != $unePersonne->getEmail()) {
		                    $message = "Cette adresse email est déjà utilisée !";
		                } else {
		                    // Vérification si le mot de passe actuel est correct
		                    if (verifyOldPassword($oldPassword, $unePersonne->getPassword())) {
		                        // Le mot de passe actuel est correct, mettre à jour le nouveau mot de passe
		                        $hashedNewPassword = $newPassword;
		                        $unePersonne->setPassword($hashedNewPassword);
		                        $unePersonne->setEmail($email);
		                        $unePersonne->setAvatar($avatarPath);
		                        $unePersonne->setDateNaissance($dateNaissance);
		
									    // Mettre à jour le compte dans la base de données
										if (updateContact($unePersonne)) {
										    $message = ($_SESSION['isAdmin'] != 1 || ($_SESSION['isAdmin'] == 1 && $_SESSION['login'] == $unePersonne->getId())) ? 
										        "Votre compte a bien été modifié !" : "Le compte a bien été modifié !";
										
										    if ($_SESSION['isAdmin'] != 1 || ($_SESSION['isAdmin'] == 1 && $_SESSION['login'] == $unePersonne->getId())) {
										        // Mise à jour de l'e-mail dans la session
										        $_SESSION['login'] = $unePersonne->getEmail();
										
										        $redirectUrl = '?page=contact&action=edit&id=' . $id . '&message=' . urlencode($message);
										    } else {
										        $redirectUrl = '?page=contact&action=edit&id=' . $id . '&message=' . urlencode($message);
										    }
										    header('Location: ' . $redirectUrl);
										    exit();
										} else {
										    $message = "Erreur d'enregistrement !";
										}
		                    } else {
		                        $message = "Ancien mot de passe incorrect !";
		                    }
		                }
		            } else {
		                $message = "Email non valide !";
		            }
		        } else {
		            $message = "Veuillez remplir tous les champs obligatoires ! (*)";
		        }
		    }
		    require_once "includes/core/views/form_contact.phtml";
		    break;
		}

////////////////////********************* mode un password ************************************/////////////////////////////////////////
		
/*case 'edit': {
    require_once "includes/core/models/daoContacts.php";

    $id = $_GET['id'];
    // ie doit récupérer les infos
    $unePersonne = getContactById($id);

    // Vérification de l'accès autorisé
    if (!isset($_SESSION['login']) && ($_SESSION['isAdmin'] != 1 || $_SESSION['login'] != $unePersonne->getId())) {
        // Si l'utilisateur n'est pas connecté et n'est pas l'admin ou n'est pas l'auteur du contact, on le redirige vers l'index
        header('Location: index.php');
        exit();
    }

    if (!empty($_POST)) {

        // Récupération de l'avatar actuel depuis la base de données
        $avatarPath = 'public/avatar/default.png';
        $userAvatar = $unePersonne->getAvatar();
        $fullName = $userAvatar; // Initialisation avec le chemin de l'avatar existant
        if ($userAvatar != null) {
            $avatarPath = $userAvatar;
        }
        // Traitement de la soumission de l'avatar
        if (isset($_FILES['chAvatar']) && $_FILES['chAvatar']['error'] === UPLOAD_ERR_OK) {
            // ...

            $tmpFile = $_FILES['chAvatar']['tmp_name'];
            $destPath = 'public/avatar/';
            $fileName = basename($_FILES['chAvatar']['name']);
            $fullName = $destPath . $fileName;
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB

            // Vérification de la taille du fichier
            if ($_FILES['chAvatar']['size'] <= $maxFileSize) {
                // Vérification du type MIME
                if (in_array($_FILES['chAvatar']['type'], $allowedMimeTypes)) {
                    // Vérification que le fichier a été correctement uploadé
                    if (move_uploaded_file($tmpFile, $fullName)) {
                        $avatarPath = $fullName;
                    } else {
                        $message = "Erreur lors de l'upload du fichier !";
                        header('Location: ?page=contact&action=edit&id=' . $id . '&message=' . $message);
                        exit;
                    }
                } else {
                    $message = "Type de fichier non pris en charge !";
                    header('Location: ?page=contact&action=edit&id=' . $id . '&message=' . $message);
                    exit;
                }
            } else {
                $message = "Le fichier est trop volumineux !";
                header('Location: ?page=contact&action=edit&id=' . $id . '&message=' . $message);
                exit;
            }
        }

        // Tester champ par champ
        if (isset($_POST['chEmail']) && !empty($_POST['chEmail']) && isset($_POST['chPassword']) && !empty($_POST['chPassword'])) {
            $password = $_POST['chPassword'];
            $email = $_POST['chEmail'];

            function isValidEmail($email)
            {
                // Expression régulière pour valider l'adresse email
                $regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

                // Vérification du format de l'adresse email
                if (preg_match($regex, $email)) {
                    return true;
                } else {
                    return false;
                }
            }

            if (isValidEmail($email)) {
                // Vérification si l'adresse email a été modifiée
                $emailModified = ($email != $unePersonne->getEmail());
                if ($emailModified) {
                    $user = getContactByEmail(htmlspecialchars($email));
                    if ($user) {
                        $message = "Cette adresse email est déjà utilisée !";
                    } else {
                        $unePersonne = new Personne(
                            $email,
                            $password,
                            date_create($_POST['chDateNaissance']),
                            $fullName
                        );
                        $unePersonne->setId($id);

                        if (updateContact($unePersonne)) {
                            $message = "Votre compte a bien été modifié !";
                            if ($_SESSION['isAdmin'] != 1 || ($_SESSION['isAdmin'] == 1 && $_SESSION['login'] == $unePersonne->getId())) {
                                // Se déconnecter uniquement si l'utilisateur modifie son propre profil et son adresse e-mail
                                session_unset();
                                session_destroy();
                                header('Location: index.php');
                                exit();
                            } else {
                                header('Location: ?page=contact&action=add&message=' . $message);
                                exit();
                            }
                        } else {
                            $message = "Erreur d'enregistrement !";
                        }

                    }
                } else {
                    $unePersonne = new Personne(
                        $email,
                        $_POST['chPassword'],
                        date_create($_POST['chDateNaissance']),
                        $fullName
                    );
                    $unePersonne->setId($id);

                    if (updateContact($unePersonne)) {
                        $message = "Votre compte a bien été modifié !";
                        if ($_SESSION['isAdmin'] != 1 || ($_SESSION['isAdmin'] == 1 && $_SESSION['login'] == $unePersonne->getId())) {
                            // Se déconnecter uniquement si l'utilisateur modifie son propre profil
                            session_unset();
                            session_destroy();
                            header('Location: index.php');
                            exit();
                        } else {
                            header('Location: ?page=contact&action=add&message=' . $message);
                            exit();
                        }
                    } else {
                        $message = "Erreur d'enregistrement !";
                    }
                }
            } else {
                $message = "Email non valide !";
            }
        } else {
            $message = "Veuillez remplir tous les champs obligatoires ! *";
        }
    }

    require_once "includes/core/views/form_contact.phtml";
    break;
}*/


	/////////////////////////////////////**************************************/////////////////////////////////////////////////////
		
		case 'delete':{
			require_once "includes/core/models/daoContacts.php";
			
			$id = $_GET['id'];
			// Vérification de l'accès autorisé
		    if(!isset($_SESSION['login']) && $_SESSION['isAdmin'] != true) {
			    // Si l'utilisateur n'est pas connecté et n'est pas l'admin  on le redirige vers l'index
			    header('Location: index.php');
		        exit();
		    }
			$id = $_GET['id'] ?? 0;
			if (deleteContact($id)){
					header('Location: ?page=user&action=list');
				}else{
					$message = "Erreur de suppression !";
				}
			break;
		}
		
		
		case 'add':{
			require_once "includes/core/models/daoContacts.php";
			
			if (empty($_POST)){
				// J'arrive sur le formulaire
				$unePersonne = new Personne();
			}else{
				$unePersonne = new Personne();
				
				if (isset($_FILES['chAvatar']) && $_FILES['chAvatar']['error'] === UPLOAD_ERR_OK) {
			        $tmpFile = $_FILES['chAvatar']['tmp_name'];
			        $destPath = 'public/avatar/';
			        $fileName = basename($_FILES['chAvatar']['name']);
			        $fullName = $destPath.$fileName;
			        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
			        $maxFileSize = 5 * 1024 * 1024; // 5MB
			        
			        // Vérification du type MIME
			        if (in_array($_FILES['chAvatar']['type'], $allowedMimeTypes)) {
			            // Vérification de la taille du fichier
			            if ($_FILES['chAvatar']['size'] <= $maxFileSize) {
			                // Vérification que le fichier a été correctement uploadé
			                if (move_uploaded_file($tmpFile, $fullName)) {
			                    $avatarPath = $fullName;
			                } else {
			                    $avatarPath = 'public/avatar/default.png';
			                }
			            } else {
			                $message = "Le fichier est trop volumineux !";
			                header('Location: ?page=contact&action=add&message=' . $message);
			                exit;
			            }
			        } else {
			            $message = "Type de fichier non pris en charge !";
			            header('Location: ?page=contact&action=add&message=' . $message);
			            exit;
			        }
			    } else {
			        $avatarPath = 'public/avatar/default.png';
			    }
			     // Validation de la date de naissance
			    $originalDate = $_POST['chDateNaissance'];
			    $modifiedDate = strip_tags($_POST['chDateNaissance']);
			
			    if ($originalDate !== $modifiedDate) {
			        $message = "Valeur non valide pour la date de naissance !";
			        header('Location: ?page=contact&action=add&message=' . '&message=' . $message);
			        exit;
			    }
				// tester champ par champ 
				if(isset($_POST['chEmail']) && !empty($_POST['chEmail'])
					&& isset($_POST['chPassword']) && !empty($_POST['chPassword']))
					{
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
					//$email = $_POST['chEmail'];
					$email = strip_tags($_POST['chEmail']);
					if (isValidEmail($email)) {
						$unePersonne = new Personne(
						htmlspecialchars($_POST['chEmail']),
						$_POST['chPassword'],
						date_create($_POST['chDateNaissance']),
						$avatarPath
						);
						// tester l'existance d'une adresse mail
						$user = getContactByEmail(htmlspecialchars($_POST['chEmail']));
						if($user)
						{
							$message = "votre compte existe déja !";
						}else
							{
								if (insertContact($unePersonne)){
									$message = "votre compte à bien été crée !";
								header('Location: ?page=contact&action=add&message='.$message);
							}else{
								$message = "Erreur d'enregistrement !";
							}	
						}
					}else{
						$message = "Email non valide !";
					}
				}else{
						$message = "Remplissez le formulaire !";
				}
			}
			require_once "includes/core/views/form_contact.phtml";
			break;
		}
		default:{
		}
	}
	
	