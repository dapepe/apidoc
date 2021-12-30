<?php

class DemoApi {
	private $methods = [
		/*!
		 * @cmd list
		 * @method get
		 * @route /
		 * @description Lists all projects
		 * @query {array} filter A filter array {search, orderby, orderasc}
		 * @return {array} List of projects [{Id, Name}, ...]
		 */
		'list' => ['GET', '/', [
			['filter', 'array']
		]],
		/*!
		 * @cmd details
		 * @method get
		 * @route /:index
		 * @description Shows the details for a single project
		 * @param {int|string} index* The ID or Identifier of the project
		 * @return {array}
		 */
		'details' => ['GET', '/:index'],
		/*!
		 * @cmd add
		 * @method post
		 * @route /
		 * @description Adds a new project
		 * @query {array} data* The project details
		 * @return {int} The project ID
		 */
		'add' => ['POST', '/', [
			['data', 'array']
		]],
		/*!
		 * @cmd update
		 * @method put
		 * @route /:index
		 * @description Updates a project
		 * @param {int|string} index* The ID or Identifier of the project
		 * @query {array} data* The project data
		 * @return {int} The project ID
		 */
		'update' => ['PUT', '/:index', [
			['data', 'array']
		]],
		/*!
		 * @cmd remove
		 * @method delete
		 * @route /
		 * @description Deletes a single project
		 * @param {int|string} index* The ID or Identifier of the project
		 * @return {bool} true if successful
		 */
		'remove' => ['DELETE', '/:index']
	]
}

?>
