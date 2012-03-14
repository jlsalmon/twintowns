<?php

/**
 * File: lib.php
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      This file contains various functions for global use throughout
 *      the application.
 */

/**
 * Loads and returns a schema-valid configuration file. Will not
 * return an invalid XML file.
 * 
 * @return SimpleXMLElement the schema-valid configuration 
 *         XML tree. 
 */
function loadConfig() {
    $config_xml = 'config.xml';
    $config_schema = 'config.xsd';

    if (file_exists($config_xml)) {
        if (!validate($config_xml, $config_schema)) {
            die('Configuration file validation failed.');
        }
        return simplexml_load_file($config_xml);
    } else {
        die('Configuration file not found.');
    }
}

/**
 * Validates an XML file against the supplied XML Schema. 
 * Requires php-xml to be installed to use the DomDocument 
 * class.
 * 
 * @param string $xml the XML filename to be validated.
 * @param string $xsd the schema filename to validate against.
 * @return boolean true if the validation succeeded, false
 *         otherwise.
 */
function validate($xml, $xsd) {
    $doc = new DomDocument;
    $doc->load($xml);

    if ($doc->schemaValidate($xsd)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Wrapper function for retrieving files behind the UWE proxy.
 * Uses cURL for efficiency (faster than file_get_contents()).
 * 
 * @param string $uri the remote file to retrieve.
 * @return string the raw file requested.
 */
function proxy_retrieve($uri) {
    $proxy = true;

    $ch = curl_init($uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if ($proxy) {
        curl_setopt($ch, CURLOPT_PROXY, 'proxysg.uwe.ac.uk:8080');
    }
    return curl_exec($ch);
}

;
?>
