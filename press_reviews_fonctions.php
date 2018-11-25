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

function press_release_methode_upload() {
	
	// méthodes d'upload disponibles
	$methodes = array();
	$methodes['ordinateur'] = array('label_lien'=>_T('medias:bouton_download_local'),'label_bouton'=>_T('bouton_upload'));
	$methodes['distant'] = array('label_lien'=>_T('medias:bouton_download_sur_le_web'),'label_bouton'=>_T('bouton_choisir'));

	return $methodes;
}
