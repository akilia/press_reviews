<?php
/**
 * Déclarations relatives à la base de données
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
 * Déclaration des alias de tables et filtres automatiques de champs
 *
 * @pipeline declarer_tables_interfaces
 * @param array $interfaces
 *     Déclarations d'interface pour le compilateur
 * @return array
 *     Déclarations d'interface pour le compilateur
 */
function press_reviews_declarer_tables_interfaces($interfaces) {

	$interfaces['table_des_tables']['press_reviews'] = 'press_reviews';

	return $interfaces;
}


/**
 * Déclaration des objets éditoriaux
 *
 * @pipeline declarer_tables_objets_sql
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function press_reviews_declarer_tables_objets_sql($tables) {

	$tables['spip_press_reviews'] = array(
		'type' => 'press_review',
		'principale' => 'oui',
		'table_objet_surnoms' => array('pressreview'), // table_objet('press_review') => 'press_reviews' 
		'page' => '',
		'field'=> array(
			'id_press_review'    => 'bigint(21) NOT NULL',
			'objet'          	 => 'varchar(100) NOT NULL DEFAULT ""',
			'id_objet'           => 'bigint(21) NOT NULL DEFAULT 0',
			'id_document'        => 'bigint(21) NOT NULL DEFAULT 0',
			'titre'              => 'text NOT NULL DEFAULT ""',
			'descriptif'         => 'text NOT NULL DEFAULT ""',
			'credit'             => 'text NOT NULL DEFAULT ""',
			'date_en_ligne'      => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'maj'                => 'TIMESTAMP'
		),
		'key' => array(
			'PRIMARY KEY'        => 'id_press_review',
			'KEY id_objet'       => 'id_objet',
			'KEY objet'          => 'objet',
		),
		'titre' => 'titre AS titre, "" AS lang',
		'date' => 'date_en_ligne',
		'champs_editables'  => array('titre', 'descriptif', 'credit', 'objet', 'id_objet'),
		'champs_versionnes' => array(),
		'rechercher_champs' => array("titre" => 5),
		'tables_jointures'  => array(),

	);

	// Ajout du rôle document pour l'objet press_review
	array_set_merge($tables, 'spip_documents', array(
		'roles_objets' => array(
			'press_reviews' => array(
				'choix' => array('logo','document'),
				'defaut' => 'logo'
			)
		)
	));

	return $tables;
}
