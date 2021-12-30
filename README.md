Apidoc
======

> Easily create API documentation with Markdown and API Blueprint


Purpose
-------

When coding HTTP-based APIs, expecially REST-style APIs, it's very important to offer a clean and complete documentation.
Instead of writing the documentation in a separate document, it's far more convenient to do the documentation within
the code itself.

Apidoc consists of two components:
 * A parser to parse the documentation section in your code
 * A converter that generates a markdown document based on the parsed structure


Usage
-----

Let's say this is part of your code, which represents an API class:

```php
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
		'details' => ['GET', '/:index']
}
```

As you can see, we are using a special documentation wrapper (`/*!` and `*/`) arround the Apidoc
section. You are free to alter these wrappers in any way you like (see `\Apidoc\Parser->setWrapper()`)

In order to parse the content of this document, you can go ahead and create an instance of the `\Apidoc\Parser` class:

```php
$parser = new Apidoc\Parser();

$struct = $p->parseFile('sample/sample.src.php');

print_r($struct);
```

The resulting array would look like this:

```
Array
(
    [0] => Array
        (
            [cmd] => list
            [method] => get
            [route] => /
            [description] => Lists all projects
            [query] => Array
                (
                    [0] => Array
                        (
                            [name] => filter
                            [type] => array
                            [description] => A filter array {search, orderby, orderasc}
                            [optional] => 1
                        )

                )

            [return] => Array
                (
                    [type] => array
                    [description] => List of projects [{Id, Name}, ...]
                )

        )

    [1] => Array
        (
            [cmd] => details
            [method] => get
            [route] => /:index
            [description] => Shows the details for a single project
            [param] => Array
                (
                    [0] => Array
                        (
                            [name] => index
                            [type] => int|string
                            [description] => The ID or Identifier of the project
                            [optional] =>
                        )

                )

            [return] => Array
                (
                    [type] => array
                    [description] =>
                )

        )

)
```

Now, it's easy to use this structure for your own documentation.

In case you want to include your documentation right into a markdown file, you can easily
achive this with this customized format:

```
My great API documentation
==========================

Some introduction...

##[BEGIN-APIDOC](mycode.php)

*[POST](/)
You can now add custom content for your route description. It will be displayed
right after the initial description tag.

*[GET](/:index)
Hello world

##[END-APIDOC]
```

As you can see, the format is pretty straight-forward. In order to generate the final
documents, you will only need a few lines of code:

```php
$m = new Apidoc\Markdown();
$m->setSrcPath('sample'); // Specify the path where your source files are located

echo $m->replace(file_get_contents('sample/sample.doc.md'));
```


Contribute
----------

In case you find bugs or have enhancements, create a pull request and let me know.


License
-------

![Zeyon](http://www.zeyon.net/assets/img/frame/headerlogo.png)

Copyright (C) 2008 - 2014 [Zeyon Technologies Inc.](http://www.zeyon.net)

This work is licensed under the GNU Lesser General Public License (LGPL) which should be included with this software. You may also get a copy of the GNU Lesser General Public License from [http://www.gnu.org/licenses/lgpl.txt](http://www.gnu.org/licenses/lgpl.txt).
