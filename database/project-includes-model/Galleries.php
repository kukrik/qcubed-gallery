<?php

use QCubed\Query\QQ;

require(QCUBED_PROJECT_MODEL_GEN_DIR . '/GalleriesGen.php');

	/**
	 * The Galleries class defined here contains any
	 * customized code for the Galleries class in the
	 * Object Relational Model.  It represents the "galleries" table
	 * in the database, and extends from the code generated abstract GalleriesGen
	 * class, which contains all the basic CRUD-type functionality as well as
	 * basic methods to handle relationships and index-based loading.
	 *
	 * @package My QCubed Application
	 * @subpackage Model
	 *
	 */
	class Galleries extends GalleriesGen {
		/**
		 * Default "to string" handler
		 * Allows pages to _p()/echo()/print() this object, and to define the default
		 * way this object would be outputted.
		 *
		 * @return string a nicely formatted string representation of this object
		 */
		public function __toString() {
			return 'Galleries Object ' . $this->PrimaryKey();
		}

        /**
         * Count Gallerieses
         * by ListId Index(es)
         * @param integer $intListId
         * @return int
         */
        public static function countByListGalleryId($intListId, $intStatus)
        {
            // Call Galleries::QueryCount to perform the CountByListId query

            return Galleries::QueryCount(
                QQ::AndCondition(
                    QQ::notEqual(QQN::Galleries()->Status, $intStatus),
                    QQ::Equal(QQN::Galleries()->ListId, $intListId)
                )
            );
        }


		// Override or Create New Load/Count methods
		// (For obvious reasons, these methods are commented out...
		// but feel free to use these as a starting point)
/*
		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return an array of Galleries objects
			return Galleries::QueryArray(
				QQ::AndCondition(
					QQ::Equal(QQN::Galleries()->Param1, $strParam1),
					QQ::GreaterThan(QQN::Galleries()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a single Galleries object
			return Galleries::QuerySingle(
				QQ::AndCondition(
					QQ::Equal(QQN::Galleries()->Param1, $strParam1),
					QQ::GreaterThan(QQN::Galleries()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function CountBySample($strParam1, $intParam2, $objOptionalClauses = null) {
			// This will return a count of Galleries objects
			return Galleries::QueryCount(
				QQ::AndCondition(
					QQ::Equal(QQN::Galleries()->Param1, $strParam1),
					QQ::Equal(QQN::Galleries()->Param2, $intParam2)
				),
				$objOptionalClauses
			);
		}

		public static function LoadArrayBySample($strParam1, $intParam2, $objOptionalClauses) {
			// Performing the load manually (instead of using QCubed Query)

			// Get the Database Object for this Class
			$objDatabase = Galleries::GetDatabase();

			// Properly Escape All Input Parameters using Database->SqlVariable()
			$strParam1 = $objDatabase->SqlVariable($strParam1);
			$intParam2 = $objDatabase->SqlVariable($intParam2);

			// Setup the SQL Query
			$strQuery = sprintf('
				SELECT
					`galleries`.*
				FROM
					`galleries` AS `galleries`
				WHERE
					param_1 = %s AND
					param_2 < %s',
				$strParam1, $intParam2);

			// Perform the Query and Instantiate the Result
			$objDbResult = $objDatabase->Query($strQuery);
			return Galleries::InstantiateDbResult($objDbResult);
		}
*/



		// Override or Create New Properties and Variables
		// For performance reasons, these variables and __set and __get override methods
		// are commented out.  But if you wish to implement or override any
		// of the data generated properties, please feel free to uncomment them.
/*
		protected $strSomeNewProperty;

		public function __get($strName) {
			switch ($strName) {
				case 'SomeNewProperty': return $this->strSomeNewProperty;

				default:
					try {
						return parent::__get($strName);
					} catch (Caller $objExc) {
						$objExc->incrementOffset();
						throw $objExc;
					}
			}
		}

		public function __set($strName, $mixValue) {
			switch ($strName) {
				case 'SomeNewProperty':
					try {
						return ($this->strSomeNewProperty = \QCubed\Type::Cast($mixValue, \QCubed\Type::String));
					} catch (QInvalidCastException $objExc) {
						$objExc->incrementOffset();
						throw $objExc;
					}

				default:
					try {
						return (parent::__set($strName, $mixValue));
					} catch (Caller $objExc) {
						$objExc->incrementOffset();
						throw $objExc;
					}
			}
		}
*/


		
/*
		public function Initialize()
		{
			parent::Initialize();
			// You additional initializations here
		}
*/
	}
