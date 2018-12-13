<?php
/**
 * Gestion du formulaire de d'édition de press_review
 *
 * @plugin     Coupure de presse
 * @copyright  2018
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Press_reviews\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/cvtupload');

/**
 * Identifier le formulaire en faisant abstraction des paramètres qui ne représentent pas l'objet edité
 *
 * @param int|string $id_press_review
 *     Identifiant du press_review. 'new' pour un nouveau press_review.
 * @param int $id_objet
 *     Identifiant de l'objet parent (si connu)
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un press_review source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du press_review, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return string
 *     Hash du formulaire
 */
function formulaires_editer_press_review_identifier_dist($id_press_review = 'new', $objet, $id_objet = 0, $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	return serialize(array(intval($id_press_review)));
}

/**
 * Chargement du formulaire d'édition de press_review
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @uses formulaires_editer_objet_charger()
 * @return array
 *     Environnement du formulaire
 */
function formulaires_editer_press_review_charger_dist($id_press_review = 'new', $objet, $id_objet = 0, $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$valeurs = formulaires_editer_objet_charger('press_review', $id_press_review, $id_objet, $lier_trad, $retour, $config_fonc, $row, $hidden);
	if (!$valeurs['id_objet']) {
		$valeurs['id_objet'] = $id_objet;
	}
	if (!$valeurs['objet']) {
		$valeurs['objet'] = $objet;
	}

	// regarder si un document est déjà associé à cette coupure de presse
	$valeurs['id_document'] = 0;
	if (is_numeric($id_press_review)) {
		$id_document = sql_getfetsel('id_document', 'spip_press_reviews', 'id_press_review='.intval($id_press_review));
		if ($id_document != 0) {
			$valeurs['id_document'] = $id_document;
		}
	}

	return $valeurs;
}

/**
* Activer CVT Upload
*/
function formulaires_editer_press_review_fichiers() {
	return array('upload_press_review');
}

/**
 * Vérifications du formulaire d'édition de press_review
 *
 * Vérifier les champs postés et signaler d'éventuelles erreurs
 *
 * @uses formulaires_editer_objet_verifier()
 * @return array
 *     Tableau des erreurs
 */
function formulaires_editer_press_review_verifier_dist($id_press_review = 'new', $objet, $id_objet = 0, $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$erreurs = array();

	// inutile ici : $erreurs = formulaires_editer_objet_verifier('press_review', $id_press_review, array('id_objet'));

	// On commence par récupérer les infos dont ont va avoir besoin
	$id_document = _request('id_document');
	$taille_fichier = $_FILES['upload_press_review']['size'][0];
	$url = _request('url');

	/* ici, pré-traitement de la press review si c'est une url*/
	if (strlen($u = _request('url')) > 0) {
		include_spip('inc/site');
		$auto = analyser_site($u);
		set_request('titre', $auto['nom_site']);
		set_request('descriptif', $auto['descriptif']);
		// rebalancer l'url sinon problème (pas compris exactement pourquoi, mais fonction analyser_site() suspecte ou vertueuse (?))
		set_request('url', $auto['url_site']);
	}

	// Vérifier qu'on a un id_objet
	$id_objet = _request('id_objet');
	if ($id_objet == 0) {
		$erreurs['id_objet'] = 'Aucune référence à un objet ici.';
	}

	// Si il n'y a pas encore eu d'upload, vérifier qu'un document a été demandé en upload
	
	if ($id_document == 0 AND $taille_fichier == 0 AND !$url){
		$erreurs['upload'] = 'Charger au moins un document';
	}


	return $erreurs;
}

/**
 * Traitement du formulaire d'édition de press_review
 *
 * Traiter les champs postés
 *
 * @uses formulaires_editer_objet_traiter()
 * @return array
 *     Retours des traitements
 */
function formulaires_editer_press_review_traiter_dist($id_press_review = 'new', $objet, $id_objet = 0, $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$retours = formulaires_editer_objet_traiter('press_review', $id_press_review, $id_objet, $lier_trad, $retour, $config_fonc, $row, $hidden);

	// on récupère tout de suite l'id de la press review nouvellement créé
	$id_press_review = $retours['id_press_review'];

	/******* Traitement du document associé : fichier ou URL *******/
	$id_document = _request('id_document');
	$file = null;
	$fichier = _request('_fichiers');
	$url = _request('url');
	

	// #1 : Si c'est un fichier uploadé depuis l'ordinateur
	if (is_array($fichier) AND count($fichier)) {
		$file = $fichier['upload_press_review'];
	}

	// #2 : si c'est un fichier distant (http://qqc)
	if ($url) {
		// ici petit hack pour que la fonction joindre_trouver_fichier_envoye() gère un document distant
		include_spip('inc/joindre_document');
		set_request('joindre_distant', true);
		$file = joindre_trouver_fichier_envoye();
	}

	// Traitement du fichier : enregistrement et association
	if ($file) {
		// si il y a déjà un document associé, on le supprime
		if ($id_document != 0) {
			// on passe par la fonction 'dissocier_document' qui fait entièrement le job ! (dissociaton + supp bdd + supp fichier)
			$dissocier_doc = charger_fonction('dissocier_document', 'action');
			$args = $id_press_review.'-press_review-'.$id_document.'-true';
			$diss = $dissocier_doc($args);
		}

		// enregistrement du document
		$ajouter_documents = charger_fonction('ajouter_documents', 'action');
		$ids_doc = $ajouter_documents('new', $file, 'press_review', $id_press_review, 'auto');

		// Attention : comme le dit la documentation, $ids_doc est un array avec la liste des ids des documents insérés
		if ($ids_doc) {
			// mise à jour de spip_press_reviews avec l'id_document créé
			sql_update('spip_press_reviews', array('id_document' => $ids_doc[0]), 'id_press_review='.intval($id_press_review));

			// on force le role à 'document' dans la table de lien
			$res = sql_updateq('spip_documents_liens',
						array('role' => 'document'),
						array(
							"id_document=". intval($ids_doc[0]),
							"objet 		= 'press_review'",
							'id_objet 	='. intval($id_press_review)
						)
			);
		}
	}

	return $retours;
}
