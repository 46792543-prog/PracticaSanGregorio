<?php

namespace App\Models\Concerns;

/**
 * Permite que un modelo declare $primaryKey como array (PK compuesta,
 * ej. `correlativa` con id_materia_principal + id_materia_requisito),
 * algo que Eloquent no soporta de forma nativa.
 */
trait HasCompositeKey
{
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    public function getKey()
    {
        $key = [];

        foreach ((array) $this->primaryKey as $column) {
            $key[$column] = $this->getAttribute($column);
        }

        return $key;
    }

    protected function setKeysForSaveQuery($query)
    {
        foreach ((array) $this->primaryKey as $column) {
            $query->where($column, '=', $this->original[$column] ?? $this->getAttribute($column));
        }

        return $query;
    }

    protected function setKeysForSelectQuery($query)
    {
        return $this->setKeysForSaveQuery($query);
    }
}
