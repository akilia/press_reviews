<?php
/**
 * Script de migration des docs avec vignettes vers PR
 *
 * @plugin     Coupure de presse
 * @copyright  2018
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Press_reviews\inc
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Script de migration de docments ayant une vignette vers la table press_reviews
 */
function press_reviews_migration() {

	$mig = sql_allfetsel('id_document, id_vignette, titre, descriptif, credits, date', 'spip_documents', "id_vignette!=''");

	$compteur = 0;
	foreach ($mig as $value) {
		$compteur++;

		$id_doc = $value['id_document'];
		$id_vig = $value['id_vignette'];
		debug($value);
		
		// Récupérer l'id_objet (livre ou auteur de livre)
		$liaison = sql_fetsel('objet, id_objet', 'spip_documents_liens', 'id_document='.intval($id_doc));
		debug($liaison);

		// créér la press_review
		$champs = array( 'objet' => $liaison['objet'],
						 'id_objet' => $liaison['id_objet'],
						 'id_document' => $id_doc,
						 'titre' => $value['titre'],
						 'descriptif' => $value['descriptif'],
						 'credit' => $value['credits'],
						 'date_en_ligne' => $value['date']);

		$id_new_pr = sql_insertq('spip_press_reviews', $champs);
		debug($id_new_pr);

		// mise à jour de la table de liens spip_documents_liens
		$glop = sql_updateq('spip_documents_liens', array('id_objet' => $id_new_pr, 'objet' => 'press_review'), 'id_document='.intval($id_doc));

		debug($glop);

		// s'occuper du logo
		$glip = sql_insertq('spip_documents_liens', array(  'id_document' => $id_vig, 
													'id_objet' => $id_new_pr, 
													'objet' => 'press_review', 
													'role' => 'logo'));

		// suppression du lien vignette dans la table documents
		$glap = sql_updateq('spip_documents', array('id_vignette' => 0), 'id_document='.intval($id_doc));

		// suprimer le mode 'vignette' des nouveaux logos
		$glip = sql_updateq('spip_documents', array('mode' => 'image'), 'id_document='.intval($id_vig));

	}

	debug($compteur, 'FIN');
}
