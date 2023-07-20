<?php

require_once "includes/core/models/bdd.php";
require_once "includes/core/models/Newsletter.php";

class NewsletterDAO {

  // Fonction pour récupérer tous les abonnés de la newsletter
   public function getAllSubscribers(): array {
        $conn = getConnexion();
    
        $SQLQuery = "SELECT id, email, date_envoi FROM newsletter";
    
        $SQLStmt = $conn->prepare($SQLQuery);
        $SQLStmt->execute();
        $listeAbonnes = array();
        while ($SQLRow = $SQLStmt->fetch(PDO::FETCH_ASSOC)) {
            $unAbonne = new Newsletter($SQLRow['email'], $SQLRow['date_envoi'], $SQLRow['id']);
            $unAbonne->setDateEnvoi($SQLRow['date_envoi']);
            $listeAbonnes[] = $unAbonne;
        }
        $SQLStmt->closeCursor();
        return $listeAbonnes;
    }

  // Fonction pour ajouter un abonné à la newsletter
    function addSubscriber(Newsletter $newSubscriber): bool {
        $conn = getConnexion();
      
        $SQLQuery = "INSERT INTO newsletter (email, date_envoi) VALUES (:email, NOW())";
        $SQLStmt = $conn->prepare($SQLQuery);
        $SQLStmt->bindValue(':email', $newSubscriber->getEmail(), PDO::PARAM_STR);
        $success = $SQLStmt->execute();
        return $success;
    }


    // Fonction pour supprimer un abonné de la newsletter
    function removeSubscriber($subscriber): bool {
        if (!($subscriber instanceof Newsletter)) {
            throw new InvalidArgumentException("Argument must be of type Newsletter");
        }
        
        $conn = getConnexion();
        
            $SQLQuery = "DELETE FROM newsletter WHERE id = :id";
        
            $SQLStmt = $conn->prepare($SQLQuery);
            $id = $subscriber->getId();
            $SQLStmt->bindParam(':id', $id, PDO::PARAM_INT);
        
            if (!$SQLStmt->execute()) {
                return false;
            } else {
                return true;
            }
        }
        
    function getNewsletterByEmail(string $mail){
		$conn = getConnexion();
		
		$SQLQuery = "SELECT n.id, email, date_envoi
			FROM newsletter n
			WHERE n.email = :mail";
		
		$SQLStmt = $conn->prepare($SQLQuery);
		$SQLStmt->bindValue(':mail', $mail, PDO::PARAM_STR);
		$SQLStmt->execute();

		$unePersonne = $SQLStmt->fetch();
		return $unePersonne;
	}
}