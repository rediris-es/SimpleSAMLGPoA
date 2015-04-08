#Descripción:#

  Este conector implementa un IdP PAPI compatible con el servicio SIR, el cual actua
a su vez como un SP para un IdP SimpleSAMLphp.

  Se asume que ya existe un IdP SimpleSAMLphp en funcionamiento, el cual se habrá de
configurar como fuente de autenticación/autorización de este conector. Es decir, el
flujo de autenticación, iniciado desde un SP conectado a SIR será:

```
Petición desde SIR -----> Este conector -----> IdP SimpleSAMLphp
Respuesta a SIR <-------- Este conector <----- IdP SimpleSAMLphp
```

#Configuración:#


  Previo a configurar este conector, hay que generar un par de claves en la ruta que 
se desee. La clave pública habrá de ser enviada junto con el nombre del AS PAPI, además
de la URL donde se encuentra el conector a los responsables del servicio SIR.

 Casi toda la configuración del conector tiene lugar en el fichero SimpleSAMLGPoA.cfg.php,
donde hay que introducir los valores oportunos que se señalan con CAMBIAR. El conector
necesita que desde simpleSAMLphp se envíen los atributos que aparecen.

 Aparte de esta configuración, el fichero FabricaAtributos.php necesita que se adapte 
también al dominio de la institución

 El par de claves se puede generar con la siguiente orden:

```
$ openssl genrsa 2048 > clavePrivada.pem
$ openssl rsa -pubout < clavePrivada.pem > clavePublica.pem
```

(no olvidar adaptar en configuración la ruta y el tamaño de clave)

#Validación del funcionamiento:#

Una vez configurado y comunicado al equipo de SIR, se han de ralizar pruebas, para lo
cual puede utilizarse la aplicación de validación:

```http://www.rediris.es/app/sirdemo/demo/sirdemo.php```

#Contacto#

Para dudas sobre este código, contactar con el equipo SIR: sir@rediris.es

