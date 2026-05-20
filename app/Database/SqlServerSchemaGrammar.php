<?php

namespace App\Database;

use Illuminate\Database\Query\Grammars\SqlServerGrammar;

/**
 * Grammar personalizada para SQL Server con soporte de schema explícito.
 *
 * Laravel wrappea el prefix + tabla como una sola string entre corchetes,
 * generando [schema.prefix_tabla] en vez de [schema].[prefix_tabla].
 * Esta clase resuelve eso construyendo el wrap de cada segmento por separado.
 */
class SqlServerSchemaGrammar extends SqlServerGrammar
{
    protected string $dbSchema = 'dbo';

    public function setSchema(string $schema): static
    {
        $this->dbSchema = $schema;
        return $this;
    }

    public function wrapTable($table, $prefix = null): string
    {
        if ($this->isExpression($table)) {
            return $this->getValue($table);
        }

        $tablePrefix = $prefix ?? $this->connection->getTablePrefix();

        return $this->wrapValue($this->dbSchema)
            . '.'
            . $this->wrapValue($tablePrefix . $table);
    }
}
