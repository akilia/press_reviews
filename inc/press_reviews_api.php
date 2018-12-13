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
 * Retourne la listes des objets (nom au pluriel) cochés dans la configuration.
 * 
 * @return array
 */
function press_reviews_liste_objets() {
	$tables = lire_config('press_reviews/objets');
	$objets = array();
	foreach ($tables as $table) {
		if ($table) {
			$objets[] = objet_type($table);
		}
	}
	return $objets;
}