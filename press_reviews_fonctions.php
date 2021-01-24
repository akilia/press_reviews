<?php
/**
 * Fonctions utiles au plugin Coupure de presse
 *
 * @plugin     Coupure de presse
 * @copyright  2018
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Press_reviews\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/press_reviews_migration');

function press_release_methode_upload() {
	
	// méthodes d'upload disponibles
	$methodes = array();
	$methodes['ordinateur'] = array('label_lien'=>_T('medias:bouton_download_local'),'label_bouton'=>_T('bouton_upload'));
	$methodes['distant'] = array('label_lien'=>_T('medias:bouton_download_sur_le_web'),'label_bouton'=>_T('bouton_choisir'));

	return $methodes;
}

function press_reviews_chercher_fichier_logo($id_document) {
	$id_logo_press_review = sql_getfetsel('id_document', 'spip_documents_liens', "id_objet=" . intval($id_document) . " AND objet='press_review' AND role='logo'");

	return $id_logo_press_review;
}

/* Outils de migration des logos de presse reviews disparus */
/* Necessite une table de sauvegarde */

function press_review_maj_logos_disparus() {
	$res = sql_allfetsel('id_document, id_objet', 'spip_documents_liens_logo', array(
			"objet=".sql_quote('press_review'),
			"role=".sql_quote('logo'))
		);

	foreach ($res as $key => $value) {
		$where = array(
				'id_document='.intval($value['id_document']),
				'id_objet='.intval($value['id_objet']),
				'objet='.sql_quote('press_review')
				);
		if (sql_getfetsel('id_document', 'spip_documents_liens', $where)) {
			sql_updateq('spip_documents_liens', array('role' => 'logo'), $where);
		}
	}
	debug(' migration fini.');
}


function press_review_maj_logos_disparus_2() {
	$res = sql_allfetsel('id_document', 'spip_documents_liens', array(
			"objet=".sql_quote('press_review'),
			"role=".sql_quote('logo'))
		);
	$res = array_unique(array_column($res, 'id_document'));
	debug($res);

	$compteur = 0;

	
	foreach ($res as $value) {
		$where = array("id_document=".intval($value), "objet=".sql_quote('press_review'), "role=".sql_quote('document'));
		if ($id_document = sql_getfetsel('id_document', 'spip_documents_liens', $where)) {
			debug($id_document);
			$compteur++;
			sql_updateq('spip_documents_liens', array('role' => 'logo'), 'id_document='.intval($value));
		}
	}

	debug("compteur = $compteur. Migration fini.");
}
