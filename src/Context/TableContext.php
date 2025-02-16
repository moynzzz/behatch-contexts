<?php
declare(strict_types=1);

namespace Behatch\Context;

use Behat\Gherkin\Node\TableNode;

class TableContext extends BaseContext
{
    /**
     * Checks that the specified table's columns match the given schema
     *
     * @Then the columns schema of the :table table should match:
     * @throws \Behat\Mink\Exception\ExpectationException
     */
    public function theColumnsSchemaShouldMatch($table, TableNode $text): void
    {
        $columnsSelector = "$table thead tr th";
        $columns = $this->getSession()->getPage()->findAll('css', $columnsSelector);

        $this->iShouldSeeColumnsInTheTable(\count($text->getHash()), $table);

        foreach ($text->getHash() as $key => $column) {
            $this->assertEquals($column['columns'], $columns[$key]->getText());
        }
    }

    /**
     * Checks that the specified table contains the given number of columns
     *
     * @Then (I )should see :count column(s) in the :table table
     * @throws \Behat\Mink\Exception\ExpectationException
     */
    public function iShouldSeeColumnsInTheTable($count, $table): void
    {
        $columnsSelector = "$table thead tr th";
        $columns = $this->getSession()->getPage()->findAll('css', $columnsSelector);

        $this->assertCount($count, $columns);
    }

    /**
     * Checks that the specified table contains the specified number of rows in its body
     *
     * @Then (I )should see :count rows in the :index :table table
     * @throws \Exception
     */
    public function iShouldSeeRowsInTheNthTable($count, $index, $table): void
    {
        $actual = $this->countElements('tbody tr', $index, $table);
        $this->assertEquals($count, $actual);
    }

    /**
     * Checks that the specified table contains the specified number of rows in its body
     *
     * @Then (I )should see :count row(s) in the :table table
     * @throws \Exception
     */
    public function iShouldSeeRowsInTheTable($count, $table): void
    {
        $this->iShouldSeeRowsInTheNthTable($count, 1, $table);
    }

    /**
     * Checks that the data of the specified row matches the given schema
     *
     * @Then the data in the :index row of the :table table should match:
     * @throws \Exception
     */
    public function theDataOfTheRowShouldMatch($index, $table, TableNode $text): void
    {
        $rowsSelector = "$table tbody tr";
        $rows = $this->getSession()->getPage()->findAll('css', $rowsSelector);

        if (!isset($rows[$index - 1])) {
            throw new \Exception("The row $index was not found in the '$table' table");
        }

        $cells = (array)$rows[$index - 1]->findAll('css', 'td');
        $cells = \array_merge((array)$rows[$index - 1]->findAll('css', 'th'), $cells);

        $hash = \current($text->getHash());

        foreach (\array_keys($hash) as $columnName) {
            // Extract index from column. ex "col2" -> 2
            \preg_match('/^col(?P<index>\d+)$/', $columnName, $matches);
            $index = (int)$matches['index'] - 1;

            $this->assertEquals($hash[$columnName], $cells[$index]->getText());
        }
    }

    /**
     * Checks that the specified cell (column/row) of the table's body contains the specified text
     *
     * @Then the :colIndex column of the :rowIndex row in the :table table should contain :text
     * @throws \Exception
     */
    public function theStColumnOfTheStRowInTheTableShouldContain($colIndex, $rowIndex, $table, string $text): void
    {
        $rowSelector = "$table tbody tr";
        $rows = $this->getSession()->getPage()->findAll('css', $rowSelector);

        if (!isset($rows[$rowIndex - 1])) {
            throw new \Exception("The row $rowIndex was not found in the '$table' table");
        }

        $row = $rows[$rowIndex - 1];
        $cols = $row->findAll('css', 'td');

        if (!isset($cols[$colIndex - 1])) {
            throw new \Exception("The column $colIndex was not found in the row $rowIndex of the '$table' table");
        }

        $actual = $cols[$colIndex - 1]->getText();

        $this->assertContains($text, $actual);
    }
}
