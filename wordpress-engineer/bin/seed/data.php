<?php
/**
 * Fixture data shared by the seed steps.
 *
 * Everything here is fictional and generated offline: the seeder never
 * reaches the network.
 *
 * @package AgroNews_Seed
 */

/**
 * Seed used by every random draw, so two runs produce the same site.
 */
const AGRONEWS_SEED_RANDOM_SEED = 20260911;

/**
 * Meta key marking a post created by the seeder, with its index as value.
 */
const AGRONEWS_SEED_UID_META = 'an_seed_uid';

/**
 * The twelve sections of the portal.
 *
 * @return array<string,array<string,string>> Slug => name and description.
 */
function agronews_seed_categories() {
	return array(
		'granos'        => array(
			'name'        => 'Granos',
			'description' => 'Soja, maíz, trigo y el resto de los cultivos extensivos.',
		),
		'ganaderia'     => array(
			'name'        => 'Ganadería',
			'description' => 'Cría, invernada y feedlot.',
		),
		'lecheria'      => array(
			'name'        => 'Lechería',
			'description' => 'Tambos, industria láctea y precio de la leche.',
		),
		'clima'         => array(
			'name'        => 'Clima',
			'description' => 'Pronósticos, sequías y alertas para el campo.',
		),
		'mercados'      => array(
			'name'        => 'Mercados',
			'description' => 'Precios, futuros y exportaciones.',
		),
		'maquinaria'    => array(
			'name'        => 'Maquinaria',
			'description' => 'Fierros, cosechadoras y equipamiento.',
		),
		'tecnologia'    => array(
			'name'        => 'Tecnología',
			'description' => 'Agtech, satélites y agricultura de precisión.',
		),
		'politica'      => array(
			'name'        => 'Política agropecuaria',
			'description' => 'Retenciones, regulación y gremiales del agro.',
		),
		'economia'      => array(
			'name'        => 'Economía',
			'description' => 'Dólar, costos y financiamiento del productor.',
		),
		'agroindustria' => array(
			'name'        => 'Agroindustria',
			'description' => 'Molienda, biocombustibles y valor agregado.',
		),
		'sustentable'   => array(
			'name'        => 'Sustentabilidad',
			'description' => 'Carbono, suelos y buenas prácticas.',
		),
		'regionales'    => array(
			'name'        => 'Economías regionales',
			'description' => 'Yerba, vino, frutas y cultivos regionales.',
		),
	);
}

/**
 * The forty topics used as tags.
 *
 * @return array<string,string> Slug => name.
 */
function agronews_seed_tags() {
	$names = array(
		'Soja',
		'Maíz',
		'Trigo',
		'Girasol',
		'Cebada',
		'Sorgo',
		'Feedlot',
		'Invernada',
		'Novillo',
		'Tambo',
		'Leche',
		'Exportación',
		'Retenciones',
		'Dólar soja',
		'Rosario',
		'Chicago',
		'Cosecha gruesa',
		'Cosecha fina',
		'Siembra directa',
		'Fertilizantes',
		'Agroquímicos',
		'Semillas',
		'Riego',
		'Sequía',
		'Heladas',
		'La Niña',
		'El Niño',
		'Cosechadoras',
		'Tractores',
		'Silobolsa',
		'Agtech',
		'Drones',
		'Satélites',
		'Huella de carbono',
		'Suelos',
		'Biocombustibles',
		'Molienda',
		'Yerba mate',
		'Vitivinicultura',
		'Fruticultura',
	);

	$tags = array();

	foreach ( $names as $name ) {
		$tags[ sanitize_title( $name ) ] = $name;
	}

	return $tags;
}

/**
 * The editorial staff.
 *
 * The admin account is created by bin/setup.sh; the seeder only fills in its
 * profile.
 *
 * @return array<int,array<string,mixed>> User definitions.
 */
function agronews_seed_users() {
	return array(
		array(
			'user_login'   => 'admin',
			'role'         => 'administrator',
			'display_name' => 'Redacción AgroNews',
			'first_name'   => 'Redacción',
			'last_name'    => 'AgroNews',
			'description'  => 'Cuenta de la redacción central de AgroNews.',
			'social'       => array(
				'an_x'    => 'https://x.com/agronews',
				'an_site' => 'https://www.agronews.example',
			),
		),
		array(
			'user_login'   => 'editor',
			'role'         => 'editor',
			'display_name' => 'Marta Quiroga',
			'first_name'   => 'Marta',
			'last_name'    => 'Quiroga',
			'description'  => 'Editora general. Cubre mercados y política agropecuaria desde 2009.',
			'social'       => array(
				'an_x'        => 'https://x.com/martaquiroga',
				'an_linkedin' => 'https://www.linkedin.com/in/martaquiroga',
			),
		),
		array(
			'user_login'   => 'jrivas',
			'role'         => 'author',
			'display_name' => 'Julián Rivas',
			'first_name'   => 'Julián',
			'last_name'    => 'Rivas',
			'description'  => 'Periodista agropecuario. Escribe sobre granos, clima y maquinaria.',
			'social'       => array(
				'an_x'         => 'https://x.com/julianrivas',
				'an_instagram' => 'https://www.instagram.com/julianrivas',
				'an_site'      => 'https://julianrivas.example',
			),
		),
		array(
			'user_login'   => 'pcabrera',
			'role'         => 'author',
			'display_name' => 'Paula Cabrera',
			'first_name'   => 'Paula',
			'last_name'    => 'Cabrera',
			'description'  => 'Cubre ganadería, lechería y economías regionales. Ingeniera agrónoma.',
			'social'       => array(
				'an_linkedin'  => 'https://www.linkedin.com/in/paulacabrera',
				'an_instagram' => 'https://www.instagram.com/paulacabrera',
			),
		),
	);
}

/**
 * Market quotes loaded into the plugin settings.
 *
 * @return array<string,float> Quote key => value.
 */
function agronews_seed_quotes() {
	return array(
		'soy'      => 512.35,
		'wheat'    => 238.10,
		'corn'     => 195.50,
		'cattle'   => 2450.00,
		'usd'      => 1040.25,
		'usd_mep'  => 1187.90,
		'usd_blue' => 1215.00,
	);
}

/**
 * Vocabulary the lorem generator draws from.
 *
 * @return string[] Words.
 */
function agronews_seed_vocabulary() {
	return array(
		'campo', 'cosecha', 'siembra', 'rinde', 'hectárea', 'productor', 'lote',
		'silo', 'grano', 'humedad', 'mercado', 'precio', 'exportación', 'puerto',
		'contrato', 'futuro', 'dólar', 'retención', 'insumo', 'fertilizante',
		'semilla', 'suelo', 'napa', 'lluvia', 'pronóstico', 'sequía', 'helada',
		'tambo', 'rodeo', 'novillo', 'ternero', 'feedlot', 'pastura', 'forraje',
		'maquinaria', 'cosechadora', 'tractor', 'pulverizadora', 'tecnología',
		'satélite', 'dron', 'sensor', 'cooperativa', 'acopio', 'flete', 'camión',
		'molienda', 'aceite', 'harina', 'biodiésel', 'carbono', 'sustentable',
	);
}

/**
 * Headline templates, filled with a section name and a number.
 *
 * @return string[] Templates with %1$s (topic) and %2$d (number) placeholders.
 */
function agronews_seed_headlines() {
	return array(
		'%1$s: el rinde promedio subió %2$d por ciento en la última campaña',
		'Los productores de %1$s esperan %2$d mil toneladas para el cierre del mes',
		'%1$s bajo la lupa: %2$d claves para entender la semana',
		'Alerta en %1$s por una caída de %2$d puntos en los precios de referencia',
		'%1$s suma %2$d nuevos contratos de exportación',
		'Informe de %1$s: %2$d por ciento más de superficie sembrada',
		'%1$s cierra la rueda con %2$d operaciones registradas',
		'El clima le puso freno a %1$s: %2$d milímetros en 48 horas',
		'%1$s: la inversión en tecnología creció %2$d por ciento interanual',
		'Qué pasa con %1$s después de %2$d jornadas de subas',
	);
}
