<?php
$config = array(
	'LogFile'        => "/var/log/SimpleSAMLGPoA.log",
        // CAMBIAR por siglas de la institución
	'AuthServerId'   => "SIGLAS_SimpleSAMLGPoA",
	'TTL'            => 3600,
	// CAMBIAR por ruta a la clave propia
	'PrivateKey'     => "file:////usr/local/PAPI/GPoA-SimpleSAMLGPoA/GPoA-SimpleSAMLGPoA_privkey.pem",
        // CAMBIAR si cambia la longitud de la clave
	'KeyLen'         => 1024,
        // Nombre de este conector (que actua como SP) para el IdP simpleSAMLphp
	'SSP-authsource' => "simplesamlgpoa-sp",
	'Debug'          => true,

	// Per PoA config. overrides and attribute mapping from SSP to PAPI assertion
	'PoA' => array(
		// NOTA: el primer PoA es este propio conector, que se configuraria aquí, con fuente el simpleSAMLphp
		'/pruebas/SimpleSAMLGPoA' => array(
			'config' => array(
				'SSP-authsource' => "simplesamlgpoa-sp",
			),
			'attr_mapping' => array(
				'ePTI' => "FabricaAtributos.php->gen_ePTI",
				'ePA'  => "'student'",
				'ePE'  => "eduPersonEntitlement+'urn:mace:dir:entitlement:common-lib-terms'",
				// CAMBIAR por el sHO de la organizacion:
				'sHO'  => "'univ.es'",
				'uid'  => "uid",
				'cn'   => "cn",
				'mail' => "mail+irisMailMainAddress",
				'sPUC' => "FabricaAtributos.php->gen_sPUC",
			),
		),
		// NOTA: Entorno de pruebas de SIR
		// PAPIPOAURL: http://sir.rediris.es/sirtestgpoa/index.php
		'sirtestgpoa' => array(
			'attr_mapping' => array(
				'ePTI' => "FabricaAtributos.php->gen_ePTI",
				'ePA'  => "'student'",
				'ePE'  => "eduPersonEntitlement+'urn:mace:dir:entitlement:common-lib-terms'",
				// CAMBIAR por el sHO de la organizacion:
				'sHO'  => "'univ.es'",
				'uid'  => "uid",
				'cn'   => "cn",
				'mail' => "mail+irisMailMainAddress",
				'sPUC' => "FabricaAtributos.php->gen_sPUC",
			),
		),
		// NOTA: Entorno de producción de SIR
		// PAPIPOAURL: http://www.rediris.es/SIRGPoA/papiPoA
		'SIRGPoA' => array(
			'attr_mapping' => array(
				'ePTI' => "FabricaAtributos.php->gen_ePTI",
				'ePA'  => "'student'",
				'ePE'  => "eduPersonEntitlement+'urn:mace:dir:entitlement:common-lib-terms'",
				// CAMBIAR por el sHO de la organizacion:
				'sHO'  => "'univ.es'",
				'uid'  => "uid",
				'cn'   => "cn",
				'mail' => "mail+irisMailMainAddress",
				'sPUC' => "FabricaAtributos.php->gen_sPUC",
			),
		),
	),
);
?>
