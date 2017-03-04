<?php

/**
 * List of fast queries for app
 * @author Lorelie Defensor
 */

 
 namespace App;
 
 class Query {
	
	// LISTINGS QUERY
	public static $JJDA = array(
		'name'=> 'Listing',
		'table' => 'red_listings',
		'table_fetch' => 'listings',
		'table_list' => 'listings',
		
		'params_list' => array('order'=>'order_field', 'fields'=>array('id', 'reference_number')),
		'unique' => array('reference_number')
	);

	//CONTACTS QUERY
	public static $HDA = array(
		'name' => 'Contact',
		'table' =>'red_profiles',

		'unique' => array('lastname', 'firstname')
	);

	//TRANSACTION TYPES
	public static $A7DKh = array(
		'name' => 'Transacation Type',
		'table' => 'red_transaction_types',
		'params_list' => array('order'=>'order_field', 'fields'=>array('id', 'transaction')),
		'unique' => array('transaction')
	);

	//DOCUMENT_CATEGORIES
	public static $hdaj1 = array(
		'name' => 'Category',
		'table' => 'red_doc_categories', 
		'params_list' => array('table', array('order'=>'order_field', 'fields'=>array('id', 'category'))),
		'unique' => array('category') ,
	);
	

	//DOCUMENT TYPES
	public static $UYda = array(
		'name'=> 'Document Type',
		'table' => 'red_documents',
		'table_list' => 'document_types',
		'table_fetch' => 'document_types',

		'params_list' =>  array('order'=>'order_field', 'fields'=>array('id', 'document', 'category')),
		'unique' => array('category_id', 'document')
	);


	//PROPERTY_TYPES
	public static $JADH2 = array(
		'name' => 'Property Type',
		'table' => 'red_property_types',
		'list' => array('fields'=>array('property_type'), 'order'=> 'property_type')
	);
 }
 
 
 
 /** END OF FILE **/