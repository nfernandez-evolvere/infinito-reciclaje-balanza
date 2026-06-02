<?php

namespace App\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar;

/**
 * Grammar DDL para SQL Server con schema explícito.
 * Usada por Blueprint/migrate para generar CREATE TABLE, ALTER TABLE, etc.
 */
class SqlServerSchemaDDLGrammar extends SqlServerGrammar
{
    protected string $dbSchema = 'dbo';

    public function setSchema(string $schema): static
    {
        $this->dbSchema = $schema;

        return $this;
    }

    public function wrapTable($table, $prefix = null): string
    {
        $tableName = $table instanceof Blueprint ? $table->getTable() : $table;

        $tablePrefix = $prefix ?? $this->connection->getTablePrefix();

        return $this->wrapValue($this->dbSchema)
            .'.'
            .$this->wrapValue($tablePrefix.$tableName);
    }

    public function compileTableExists($schema, $table): string
    {
        $s = $this->quoteString(($schema ?? $this->dbSchema).'.'.$table);

        return "select (case when object_id($s, 'U') is null then 0 else 1 end) as [exists]";
    }

    public function compileColumns($schema, $table): string
    {
        $qs = $this->quoteString($schema ?? $this->dbSchema);

        return sprintf(
            'select col.name, type.name as type_name, '
            .'col.max_length as length, col.precision as precision, col.scale as places, '
            .'col.is_nullable as nullable, def.definition as [default], '
            .'col.is_identity as autoincrement, col.collation_name as collation, '
            .'com.definition as [expression], is_persisted as [persisted], '
            .'cast(prop.value as nvarchar(max)) as comment '
            .'from sys.columns as col '
            .'join sys.types as type on col.user_type_id = type.user_type_id '
            .'join sys.objects as obj on col.object_id = obj.object_id '
            .'join sys.schemas as scm on obj.schema_id = scm.schema_id '
            .'left join sys.default_constraints def on col.default_object_id = def.object_id and col.object_id = def.parent_object_id '
            ."left join sys.extended_properties as prop on obj.object_id = prop.major_id and col.column_id = prop.minor_id and prop.name = 'MS_Description' "
            .'left join sys.computed_columns as com on col.column_id = com.column_id and col.object_id = com.object_id '
            ."where obj.type in ('U', 'V') and obj.name = %s and scm.name = %s "
            .'order by col.column_id',
            $this->quoteString($table),
            $qs
        );
    }

    public function compileIndexes($schema, $table): string
    {
        $qs = $this->quoteString($schema ?? $this->dbSchema);

        return sprintf(
            "select idx.name as name, string_agg(col.name, ',') within group (order by idxcol.key_ordinal) as columns, "
            .'idx.type_desc as [type], idx.is_unique as [unique], idx.is_primary_key as [primary] '
            .'from sys.indexes as idx '
            .'join sys.tables as tbl on idx.object_id = tbl.object_id '
            .'join sys.schemas as scm on tbl.schema_id = scm.schema_id '
            .'join sys.index_columns as idxcol on idx.object_id = idxcol.object_id and idx.index_id = idxcol.index_id '
            .'join sys.columns as col on idxcol.object_id = col.object_id and idxcol.column_id = col.column_id '
            .'where tbl.name = %s and scm.name = %s '
            .'group by idx.name, idx.type_desc, idx.is_unique, idx.is_primary_key',
            $this->quoteString($table),
            $qs
        );
    }

    public function compileForeignKeys($schema, $table): string
    {
        $qs = $this->quoteString($schema ?? $this->dbSchema);

        return sprintf(
            'select fk.name as name, '
            ."string_agg(lc.name, ',') within group (order by fkc.constraint_column_id) as columns, "
            .'fs.name as foreign_schema, ft.name as foreign_table, '
            ."string_agg(fc.name, ',') within group (order by fkc.constraint_column_id) as foreign_columns, "
            .'fk.update_referential_action_desc as on_update, '
            .'fk.delete_referential_action_desc as on_delete '
            .'from sys.foreign_keys as fk '
            .'join sys.foreign_key_columns as fkc on fkc.constraint_object_id = fk.object_id '
            .'join sys.tables as lt on lt.object_id = fk.parent_object_id '
            .'join sys.schemas as ls on lt.schema_id = ls.schema_id '
            .'join sys.columns as lc on fkc.parent_object_id = lc.object_id and fkc.parent_column_id = lc.column_id '
            .'join sys.tables as ft on ft.object_id = fk.referenced_object_id '
            .'join sys.schemas as fs on ft.schema_id = fs.schema_id '
            .'join sys.columns as fc on fkc.referenced_object_id = fc.object_id and fkc.referenced_column_id = fc.column_id '
            .'where lt.name = %s and ls.name = %s '
            .'group by fk.name, fs.name, ft.name, fk.update_referential_action_desc, fk.delete_referential_action_desc',
            $this->quoteString($table),
            $qs
        );
    }
}
