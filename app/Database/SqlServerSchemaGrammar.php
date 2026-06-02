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

    /**
     * Formato de fecha en ISO 8601 con separador 'T'.
     *
     * El default de Laravel ('Y-m-d H:i:s.v', con espacio) es ambiguo para SQL Server:
     * lo interpreta según el DATEFORMAT del servidor. En columnas datetime eso provoca
     * intercambio mes↔día (día ≤ 12, se guarda mal) o error de rango (día > 12). El
     * separador 'T' fuerza la interpretación ISO 8601 sin importar el locale del server,
     * así toda fecha que Eloquent serializa se inserta y consulta sin ambigüedad.
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d\TH:i:s.v';
    }

    public function wrapTable($table, $prefix = null): string
    {
        if ($this->isExpression($table)) {
            return $this->getValue($table);
        }

        // Laravel uses 'laravel_source' as a derived-table alias in MERGE (upsert).
        // It is not a real table and must not receive the schema prefix or table prefix.
        if ($table === 'laravel_source') {
            return $this->wrapValue('laravel_source');
        }

        $tablePrefix = $prefix ?? $this->connection->getTablePrefix();

        return $this->wrapValue($this->dbSchema)
            .'.'
            .$this->wrapValue($tablePrefix.$table);
    }
}
