<?php
/**
 * Utilisations de pipelines par Coupure de presse
 *
 * @plugin     Coupure de presse
 * @copyright  2018
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Press_reviews\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Ajouter les objets sur les vues des parents directs
 *
 * @pipeline affiche_enfants
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
**/
function press_reviews_affiche_enfants($flux) {
	if ($e = trouver_objet_exec($flux['args']['exec']) and $e['edition'] == false) {
		$id_objet = $flux['args']['id_objet'];

		if ($e['type'] == 'livre') {
			$flux['data'] .= recuperer_fond(
				'prive/objets/liste/press_reviews',
				array(
					'titre' => _T('press_review:titre_press_reviews'),
					'id_livre' => $id_objet
				)
			);

			if (autoriser('creerpressreviewdans', 'livres', $id_objet)) {
				include_spip('inc/presentation');
				$flux['data'] .= icone_verticale(
					_T('press_review:icone_creer_press_review'),
					generer_url_ecrire('press_review_edit', "id_livre=$id_objet"),
					'press_review-24.png',
					'new',
					'right'
				) . "<br class='nettoyeur' />";
			}
		}
	}
	return $flux;
}

/**
 * Afficher le nombre d'éléments dans les parents
 *
 * @pipeline boite_infos
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
**/
function press_reviews_boite_infos($flux) {
	if (isset($flux['args']['type']) and isset($flux['args']['id']) and $id = intval($flux['args']['id'])) {
		$texte = '';
		if ($flux['args']['type'] == 'livre' and $nb = sql_countsel('spip_press_reviews', array('id_livre=' . $id))) {
			$texte .= '<div>' . singulier_ou_pluriel($nb, 'press_review:info_1_press_review', 'press_review:info_nb_press_reviews') . "</div>\n";
		}
		if ($texte and $p = strpos($flux['data'], '<!--nb_elements-->')) {
			$flux['data'] = substr_replace($flux['data'], $texte, $p, 0);
		}
	}
	return $flux;
}


/**
 * Compter les enfants d'un objet
 *
 * @pipeline objets_compte_enfants
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
**/
function press_reviews_objet_compte_enfants($flux) {
	if ($flux['args']['objet'] == 'livre' and $id_livre = intval($flux['args']['id_objet'])) {
		$flux['data']['press_reviews'] = sql_countsel('spip_press_reviews', 'id_livre= ' . intval($id_livre));
	}

	return $flux;
}
