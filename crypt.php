<?php
// Copyright (c) 2005, RedIRIS. All Rights Reserved.
//
// You may distribute under the terms of the GNU General Public License,
// as specified in the LICENSE file that was shipped with this distribution

// RIJNDAEL Crypt Functions (AES)

// Constant for encrypt and decrypt data with openssl
define('PADDINGSIZE', 11);

function encrypt_AES($input,$key)
{
 
 $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'',MCRYPT_MODE_ECB,'');
 $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
 
 $key = substr($key, 0, mcrypt_enc_get_key_size($td));
 
 if (mcrypt_generic_init($td, $key, $iv) != -1)
 {
  // Encrypt the text
  $crypttext = mcrypt_generic($td, $input);
  mcrypt_generic_deinit($td);
 }      
 
 mcrypt_module_close($td);
   
 // Encode the encrypted text
 $crypttext = base64_encode($crypttext);
 
 return $crypttext;
}

function decrypt_AES($input,$key)
{
 
 // Decode the encrypted text
 $input = base64_decode($input);
  
 $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'',MCRYPT_MODE_ECB,'');
 $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
 
 $key = substr($key, 0, mcrypt_enc_get_key_size($td));
 
 if (mcrypt_generic_init($td, $key, $iv) != -1)
 {
  // Decrypt the text
  $decrypttext = mdecrypt_generic($td, $input);
  mcrypt_generic_deinit($td);
 }      
 
 mcrypt_module_close($td);
 $decrypttext = trim ($decrypttext);
  
 return $decrypttext;
}


// 3DES Crypt Functions (Not used in phpPoA)

function encrypt_3DES($input,$key)
{
 
 $td = mcrypt_module_open('tripledes', '', 'ecb', '');
 $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
 
 $key = substr($key, 0, mcrypt_enc_get_key_size($td));
 
 if (mcrypt_generic_init($td, $key, $iv) != -1)
 {
  // Encrypt the text
  $crypttext = mcrypt_generic($td, $input);
  mcrypt_generic_deinit($td);
 }      
 
 mcrypt_module_close($td);
   
 // Encode the encrypted text
 $crypttext = base64_encode($crypttext);
 
 return $crypttext;
}


function decrypt_3DES($input,$key)
{
 
 // Decode the encrypted text
 $input = base64_decode($input);
  
 $td = mcrypt_module_open('tripledes', '', 'ecb', '');
 $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
 
 $key = substr($key, 0, mcrypt_enc_get_key_size($td));
 
 if (mcrypt_generic_init($td, $key, $iv) != -1)
 {
  // Decrypt the text
  $decrypttext = mdecrypt_generic($td, $input);
  mcrypt_generic_deinit($td);
 }      
 
 mcrypt_module_close($td);
 $decrypttext = trim ($decrypttext);
  
 return $decrypttext;
}



// Openssl Crypt Functions (Not used in phpPoA)

//////////////////////////////////////////////////////////////////////////////////////
// Openssl Crypt Functions 
//////////////////////////////////////////////////////////////////////////////////////
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
// funcion openssl_encrypt (Not used in phpPoA)
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function openssl_encrypt($in, $key, $key_bits = 0)
{		
	// Get the byte size of data string 
	$inputSize = strlen($in);
	
	// Get details of the key
	$res = openssl_get_privatekey($key);
	if ($key_bits==0) {
		$key_details = openssl_pkey_get_details($res);
	}
	else {
		$key_details=array('bits' => $key_bits);
	}
	
	// Get the output block maximun size in Bytes
	$outputBlockSize = 	$key_details['bits']/8;

	// Total number of blocks
	$inputBlockSize = $outputBlockSize - PADDINGSIZE;
	$numBlocks = ceil($inputSize/$inputBlockSize);
	
	// Start to encrypt.
	$blockCount = 0;
	$cryptBuffer = array();

	while ($blockCount < $numBlocks){
		$index = $blockCount * $inputBlockSize;
		$block = substr($in, $index, $inputBlockSize);
		openssl_private_encrypt($block, $crypttext, $key);
		$cryptBuffer[$blockCount] = $crypttext;		
		$blockCount++;
	}
	// Now joint the array with the blocks string encripted
	$cryptData = join("", $cryptBuffer);
	
	$base64CryptData = base64_encode($cryptData);
	
	// Return the encrypted, joined and base64 encode data string.
	return $base64CryptData;	
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
// funcion openssl_decrypt
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
function openssl_decrypt($in, $key, $error_log, $key_bits = 0)
{
	// Decode the base64 input string
	 $in = base64_decode($in);
	
	// Get the byte size of data string 
	$inputSize = strlen($in);

	// Get details of the key
	$res = openssl_get_publickey($key);	
	if ($key_bits==0) {
		$key_details = openssl_pkey_get_details($res);
	}
	else {
		$key_details=array('bits' => $key_bits);
	}
	
	// Get the output block maximun size in Bytes
	$outputBlockSize = 	$key_details['bits']/8;
	
	//$inputBlockSize = $outputBlockSize - PADDINGSIZE;
	$inputBlockSize = $outputBlockSize;
	$numBlocks = ceil($inputSize/$inputBlockSize);

	// Start to decrypt.
	$blockCount = 0;
	$decryptBuffer = array();



	while ($blockCount < $numBlocks){
		$index = $blockCount * $inputBlockSize;
		$block = substr($in, $index, $inputBlockSize);
		// Decrypt the text 
		if (!openssl_public_decrypt($block, $decrypttext, $key)) {
		    // Cannot decrypt!
		    $error_message = date("d-M-Y H:i:s ")."openssl_decrypt() Function: Cannot decrypt response, check GPoA public key.";
		    error_log ($error_message."\n", 3,$error_log);
		    return 0;
		}  
		$decryptBuffer[$blockCount] = $decrypttext;		
		$blockCount++;
	}

	// Now joint the array with the blocks string encripted
	$decryptData = join("",$decryptBuffer);
	
	// Return the base64 dencode, decrypted and joined data string. 	
	
	return $decryptData;
}

?>
