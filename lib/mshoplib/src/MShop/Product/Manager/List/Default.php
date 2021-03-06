<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2013
 * @license LGPLv3, http://www.arcavias.com/en/license
 * @package MShop
 * @subpackage Product
 */


/**
 * Default product list manager for creating and handling product list items.
 * @package MShop
 * @subpackage Product
 */
class MShop_Product_Manager_List_Default
	extends MShop_Common_Manager_List_Abstract
	implements MShop_Product_Manager_List_Interface
{
	private $_searchConfig = array(
		'product.list.id'=> array(
			'code'=>'product.list.id',
			'internalcode'=>'mproli."id"',
			'internaldeps' => array( 'LEFT JOIN "mshop_product_list" AS mproli ON ( mpro."id" = mproli."parentid" )' ),
			'label'=>'Product list ID',
			'type'=> 'integer',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'product.list.siteid'=> array(
			'code'=>'product.list.siteid',
			'internalcode'=>'mproli."siteid"',
			'label'=>'Product list site ID',
			'type'=> 'integer',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'product.list.parentid'=> array(
			'code'=>'product.list.parentid',
			'internalcode'=>'mproli."parentid"',
			'label'=>'Product list parent ID',
			'type'=> 'integer',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'product.list.domain'=> array(
			'code'=>'product.list.domain',
			'internalcode'=>'mproli."domain"',
			'label'=>'Product list domain',
			'type'=> 'string',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_STR,
		),
		'product.list.typeid' => array(
			'code'=>'product.list.typeid',
			'internalcode'=>'mproli."typeid"',
			'label'=>'Product list type ID',
			'type'=> 'integer',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'product.list.refid'=> array(
			'code'=>'product.list.refid',
			'internalcode'=>'mproli."refid"',
			'label'=>'Product list reference ID',
			'type'=> 'string',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_STR,
		),
		'product.list.datestart' => array(
			'code'=>'product.list.datestart',
			'internalcode'=>'mproli."start"',
			'label'=>'Product list start date/time',
			'type'=> 'datetime',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'product.list.dateend' => array(
			'code'=>'product.list.dateend',
			'internalcode'=>'mproli."end"',
			'label'=>'Product list end date/time',
			'type'=> 'datetime',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'product.list.config' => array(
			'code'=>'product.list.config',
			'internalcode'=>'mproli."config"',
			'label'=>'Product list config',
			'type'=> 'string',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'product.list.position' => array(
			'code'=>'product.list.position',
			'internalcode'=>'mproli."pos"',
			'label'=>'Product list position',
			'type'=> 'integer',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
		),
		'product.list.status' => array(
			'code'=>'product.list.status',
			'internalcode'=>'mproli."status"',
			'label'=>'Product list status',
			'type'=> 'integer',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
		),
		'product.list.ctime'=> array(
			'code'=>'product.list.ctime',
			'internalcode'=>'mproli."ctime"',
			'label'=>'Product list create date/time',
			'type'=> 'datetime',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_STR,
		),
		'product.list.mtime'=> array(
			'code'=>'product.list.mtime',
			'internalcode'=>'mproli."mtime"',
			'label'=>'Product list modification date/time',
			'type'=> 'datetime',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_STR,
		),
		'product.list.editor'=> array(
			'code'=>'product.list.editor',
			'internalcode'=>'mproli."editor"',
			'label'=>'Product list editor',
			'type'=> 'string',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_STR,
		),
	);


	/**
	 * Initializes the object.
	 *
	 * @param MShop_Context_Item_Interface $context Context object
	 */
	public function __construct( MShop_Context_Item_Interface $context )
	{
		parent::__construct( $context );
		$this->_setResourceName( 'db-product' );
	}


	/**
	 * Removes old entries from the storage.
	 *
	 * @param array $siteids List of IDs for sites whose entries should be deleted
	 */
	public function cleanup( array $siteids )
	{
		$path = 'classes/product/manager/list/submanagers';
		foreach( $this->_getContext()->getConfig()->get( $path, array( 'type' ) ) as $domain ) {
			$this->getSubManager( $domain )->cleanup( $siteids );
		}

		$this->_cleanup( $siteids, 'mshop/product/manager/list/default/item/delete' );
	}


	/**
	 * Returns the list attributes that can be used for searching.
	 *
	 * @param boolean $withsub Return also attributes of sub-managers if true
	 * @return array List of attribute items implementing MW_Common_Criteria_Attribute_Interface
	 */
	public function getSearchAttributes( $withsub = true )
	{
		/** classes/product/manager/list/submanagers
		 * List of manager names that can be instantiated by the product list manager
		 *
		 * Managers provide a generic interface to the underlying storage.
		 * Each manager has or can have sub-managers caring about particular
		 * aspects. Each of these sub-managers can be instantiated by its
		 * parent manager using the getSubManager() method.
		 *
		 * The search keys from sub-managers can be normally used in the
		 * manager as well. It allows you to search for items of the manager
		 * using the search keys of the sub-managers to further limit the
		 * retrieved list of items.
		 *
		 * @param array List of sub-manager names
		 * @since 2014.03
		 * @category Developer
		 */
		$path = 'classes/product/manager/list/submanagers';

		return $this->_getSearchAttributes( $this->_searchConfig, $path, array( 'type' ), $withsub );
	}


	/**
	 * Returns a new manager for product list extensions.
	 *
	 * @param string $manager Name of the sub manager type in lower case
	 * @param string|null $name Name of the implementation, will be from configuration (or Default) if null
	 * @return MShop_Common_Manager_Interface Manager for different extensions, e.g types, lists etc.
	 */
	public function getSubManager( $manager, $name = null )
	{
		/** classes/product/manager/list/name
		 * Class name of the used product list manager implementation
		 *
		 * Each default product list manager can be replaced by an alternative imlementation.
		 * To use this implementation, you have to set the last part of the class
		 * name as configuration value so the manager factory knows which class it
		 * has to instantiate.
		 *
		 * For example, if the name of the default class is
		 *
		 *  MShop_Product_Manager_List_Default
		 *
		 * and you want to replace it with your own version named
		 *
		 *  MShop_Product_Manager_List_Mylist
		 *
		 * then you have to set the this configuration option:
		 *
		 *  classes/product/manager/list/name = Mylist
		 *
		 * The value is the last part of your own class name and it's case sensitive,
		 * so take care that the configuration value is exactly named like the last
		 * part of the class name.
		 *
		 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
		 * characters are possible! You should always start the last part of the class
		 * name with an upper case character and continue only with lower case characters
		 * or numbers. Avoid chamel case names like "MyList"!
		 *
		 * @param string Last part of the class name
		 * @since 2014.03
		 * @category Developer
		 */

		/** mshop/product/manager/list/decorators/excludes
		 * Excludes decorators added by the "common" option from the product list manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "mshop/common/manager/decorators/default" before they are wrapped
		 * around the product list manager.
		 *
		 *  mshop/product/manager/list/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("MShop_Common_Manager_Decorator_*") added via
		 * "mshop/common/manager/decorators/default" for the product list manager.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/product/manager/list/decorators/global
		 * @see mshop/product/manager/list/decorators/local
		 */

		/** mshop/product/manager/list/decorators/global
		 * Adds a list of globally available decorators only to the product list manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("MShop_Common_Manager_Decorator_*") around the product list manager.
		 *
		 *  mshop/product/manager/list/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "MShop_Common_Manager_Decorator_Decorator1" only to the product controller.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/product/manager/list/decorators/excludes
		 * @see mshop/product/manager/list/decorators/local
		 */

		/** mshop/product/manager/list/decorators/local
		 * Adds a list of local decorators only to the product list manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("MShop_Common_Manager_Decorator_*") around the product list manager.
		 *
		 *  mshop/product/manager/list/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "MShop_Common_Manager_Decorator_Decorator2" only to the product
		 * controller.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/product/manager/list/decorators/excludes
		 * @see mshop/product/manager/list/decorators/global
		 */

		return $this->_getSubManager( 'product', 'list/' . $manager, $name );
	}


	/**
	 * Returns the config path for retrieving the configuration values.
	 *
	 * @return string Configuration path
	 */
	protected function _getConfigPath()
	{
		/** mshop/product/manager/list/default/item/insert
		 * Inserts a new product list record into the database table
		 *
		 * Items with no ID yet (i.e. the ID is NULL) will be created in
		 * the database and the newly created ID retrieved afterwards
		 * using the "newid" SQL statement.
		 *
		 * The SQL statement must be a string suitable for being used as
		 * prepared statement. It must include question marks for binding
		 * the values from the product list item to the statement before they are
		 * sent to the database server. The number of question marks must
		 * be the same as the number of columns listed in the INSERT
		 * statement. The order of the columns must correspond to the
		 * order in the saveItems() method, so the correct values are
		 * bound to the columns.
		 *
		 * The SQL statement should conform to the ANSI standard to be
		 * compatible with most relational database systems. This also
		 * includes using double quotes for table and column names.
		 *
		 * @param string SQL statement for inserting records
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/product/manager/list/default/item/update
		 * @see mshop/product/manager/list/default/item/newid
		 * @see mshop/product/manager/list/default/item/delete
		 * @see mshop/product/manager/list/default/item/search
		 * @see mshop/product/manager/list/default/item/count
		 */

		/** mshop/product/manager/list/default/item/update
		 * Updates an existing product list record in the database
		 *
		 * Items which already have an ID (i.e. the ID is not NULL) will
		 * be updated in the database.
		 *
		 * The SQL statement must be a string suitable for being used as
		 * prepared statement. It must include question marks for binding
		 * the values from the product list item to the statement before they are
		 * sent to the database server. The order of the columns must
		 * correspond to the order in the saveItems() method, so the
		 * correct values are bound to the columns.
		 *
		 * The SQL statement should conform to the ANSI standard to be
		 * compatible with most relational database systems. This also
		 * includes using double quotes for table and column names.
		 *
		 * @param string SQL statement for updating records
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/product/manager/list/default/item/insert
		 * @see mshop/product/manager/list/default/item/newid
		 * @see mshop/product/manager/list/default/item/delete
		 * @see mshop/product/manager/list/default/item/search
		 * @see mshop/product/manager/list/default/item/count
		 */

		/** mshop/product/manager/list/default/item/newid
		 * Retrieves the ID generated by the database when inserting a new record
		 *
		 * As soon as a new record is inserted into the database table,
		 * the database server generates a new and unique identifier for
		 * that record. This ID can be used for retrieving, updating and
		 * deleting that specific record from the table again.
		 *
		 * For MySQL:
		 *  SELECT LAST_INSERT_ID()
		 * For PostgreSQL:
		 *  SELECT currval('seq_mproli_id')
		 * For SQL Server:
		 *  SELECT SCOPE_IDENTITY()
		 * For Oracle:
		 *  SELECT "seq_mproli_id".CURRVAL FROM DUAL
		 *
		 * There's no way to retrive the new ID by a SQL statements that
		 * fits for most database servers as they implement their own
		 * specific way.
		 *
		 * @param string SQL statement for retrieving the last inserted record ID
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/product/manager/list/default/item/insert
		 * @see mshop/product/manager/list/default/item/update
		 * @see mshop/product/manager/list/default/item/delete
		 * @see mshop/product/manager/list/default/item/search
		 * @see mshop/product/manager/list/default/item/count
		 */

		/** mshop/product/manager/list/default/item/delete
		 * Deletes the items matched by the given IDs from the database
		 *
		 * Removes the records specified by the given IDs from the product database.
		 * The records must be from the site that is configured via the
		 * context item.
		 *
		 * The ":cond" placeholder is replaced by the name of the ID column and
		 * the given ID or list of IDs while the site ID is bound to the question
		 * mark.
		 *
		 * The SQL statement should conform to the ANSI standard to be
		 * compatible with most relational database systems. This also
		 * includes using double quotes for table and column names.
		 *
		 * @param string SQL statement for deleting items
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/product/manager/list/default/item/insert
		 * @see mshop/product/manager/list/default/item/update
		 * @see mshop/product/manager/list/default/item/newid
		 * @see mshop/product/manager/list/default/item/search
		 * @see mshop/product/manager/list/default/item/count
		 */

		/** mshop/product/manager/list/default/item/search
		 * Retrieves the records matched by the given criteria in the database
		 *
		 * Fetches the records matched by the given criteria from the product
		 * database. The records must be from one of the sites that are
		 * configured via the context item. If the current site is part of
		 * a tree of sites, the SELECT statement can retrieve all records
		 * from the current site and the complete sub-tree of sites.
		 *
		 * As the records can normally be limited by criteria from sub-managers,
		 * their tables must be joined in the SQL context. This is done by
		 * using the "internaldeps" property from the definition of the ID
		 * column of the sub-managers. These internal dependencies specify
		 * the JOIN between the tables and the used columns for joining. The
		 * ":joins" placeholder is then replaced by the JOIN strings from
		 * the sub-managers.
		 *
		 * To limit the records matched, conditions can be added to the given
		 * criteria object. It can contain comparisons like column names that
		 * must match specific values which can be combined by AND, OR or NOT
		 * operators. The resulting string of SQL conditions replaces the
		 * ":cond" placeholder before the statement is sent to the database
		 * server.
		 *
		 * If the records that are retrieved should be ordered by one or more
		 * columns, the generated string of column / sort direction pairs
		 * replaces the ":order" placeholder. In case no ordering is required,
		 * the complete ORDER BY part including the "\/*-orderby*\/...\/*orderby-*\/"
		 * markers is removed to speed up retrieving the records. Columns of
		 * sub-managers can also be used for ordering the result set but then
		 * no index can be used.
		 *
		 * The number of returned records can be limited and can start at any
		 * number between the begining and the end of the result set. For that
		 * the ":size" and ":start" placeholders are replaced by the
		 * corresponding values from the criteria object. The default values
		 * are 0 for the start and 100 for the size value.
		 *
		 * The SQL statement should conform to the ANSI standard to be
		 * compatible with most relational database systems. This also
		 * includes using double quotes for table and column names.
		 *
		 * @param string SQL statement for searching items
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/product/manager/list/default/item/insert
		 * @see mshop/product/manager/list/default/item/update
		 * @see mshop/product/manager/list/default/item/newid
		 * @see mshop/product/manager/list/default/item/delete
		 * @see mshop/product/manager/list/default/item/count
		 */

		/** mshop/product/manager/list/default/item/count
		 * Counts the number of records matched by the given criteria in the database
		 *
		 * Counts all records matched by the given criteria from the product
		 * database. The records must be from one of the sites that are
		 * configured via the context item. If the current site is part of
		 * a tree of sites, the statement can count all records from the
		 * current site and the complete sub-tree of sites.
		 *
		 * As the records can normally be limited by criteria from sub-managers,
		 * their tables must be joined in the SQL context. This is done by
		 * using the "internaldeps" property from the definition of the ID
		 * column of the sub-managers. These internal dependencies specify
		 * the JOIN between the tables and the used columns for joining. The
		 * ":joins" placeholder is then replaced by the JOIN strings from
		 * the sub-managers.
		 *
		 * To limit the records matched, conditions can be added to the given
		 * criteria object. It can contain comparisons like column names that
		 * must match specific values which can be combined by AND, OR or NOT
		 * operators. The resulting string of SQL conditions replaces the
		 * ":cond" placeholder before the statement is sent to the database
		 * server.
		 *
		 * Both, the strings for ":joins" and for ":cond" are the same as for
		 * the "search" SQL statement.
		 *
		 * Contrary to the "search" statement, it doesn't return any records
		 * but instead the number of records that have been found. As counting
		 * thousands of records can be a long running task, the maximum number
		 * of counted records is limited for performance reasons.
		 *
		 * The SQL statement should conform to the ANSI standard to be
		 * compatible with most relational database systems. This also
		 * includes using double quotes for table and column names.
		 *
		 * @param string SQL statement for counting items
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/product/manager/list/default/item/insert
		 * @see mshop/product/manager/list/default/item/update
		 * @see mshop/product/manager/list/default/item/newid
		 * @see mshop/product/manager/list/default/item/delete
		 * @see mshop/product/manager/list/default/item/search
		 */

		return 'mshop/product/manager/list/default/item/';
	}


	/**
	 * Returns the search configuration for searching items.
	 *
	 * @return array Associative list of search keys and search definitions
	 */
	protected function _getSearchConfig()
	{
		return $this->_searchConfig;
	}
}