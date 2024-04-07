=========================
Rule ``array_line_break``
=========================

No array value should exist on opening or closing line of multi line array.

Examples
--------

Example #1
~~~~~~~~~~

.. code-block:: diff

   --- Original
   +++ New
    <?php
   -$foo = ['A',
   +$foo = [
   +    'A',
        'B',
   -    'C'];
   +    'C'
   +];

Example #2
~~~~~~~~~~

.. code-block:: diff

   --- Original
   +++ New
    <?php
   -$foo = [[
   +$foo = [
   +    [
        'foo' => 'bar'
   -]];
   +]
   +];

Rule sets
---------

The rule is part of the following rule sets:

- `@PER <./../../ruleSets/PER.rst>`_
- `@PER-CS <./../../ruleSets/PER-CS.rst>`_
- `@PER-CS2.0 <./../../ruleSets/PER-CS2.0.rst>`_
- `@PhpCsFixer <./../../ruleSets/PhpCsFixer.rst>`_
- `@Symfony <./../../ruleSets/Symfony.rst>`_

References
----------

- Fixer class: `PhpCsFixer\\Fixer\\Whitespace\\ArrayLineBreakFixer <./../../../src/Fixer/Whitespace/ArrayLineBreakFixer.php>`_
- Test class: `PhpCsFixer\\Tests\\Fixer\\Whitespace\\ArrayLineBreakFixerTest <./../../../tests/Fixer/Whitespace/ArrayLineBreakFixerTest.php>`_

The test class defines officially supported behaviour. Each test case is a part of our backward compatibility promise.
