<?php

namespace Limonlabs\Bigcommerce\Http\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait AppliesApiListQuery
{
    /**
     * @param list<string> $sortableFields
     * @param list<string> $selectableFields
     * @param list<string> $includableRelations
     */
    protected function paginateApiList(
        Builder $query,
        Request $request,
        array $sortableFields,
        array $selectableFields,
        array $includableRelations,
        string $defaultSort = 'id-desc',
        int $defaultLimit = 15,
        int $maxLimit = 100,
    ) {
        $this->applyApiListSort($query, $request, $sortableFields, $defaultSort);
        $this->applyApiListIncludes($query, $request, $includableRelations);
        $this->applyApiListFieldSelection($query, $request, $selectableFields);

        $limit = min(
            max($request->integer('limit', $request->integer('per_page', $defaultLimit)), 1),
            $maxLimit
        );
        $page = max($request->integer('page', 1), 1);

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param list<string> $sortableFields
     */
    protected function applyApiListSort(
        Builder $query,
        Request $request,
        array $sortableFields,
        string $defaultSort,
    ): void {
        $sortBy = $request->filled('sortby')
            ? (string) $request->string('sortby')
            : $defaultSort;

        if (! preg_match('/^(.+)-(asc|desc)$/i', $sortBy, $matches)) {
            throw ValidationException::withMessages([
                'sortby' => ['The sortby parameter must be in the format field-asc or field-desc.'],
            ]);
        }

        $field = $this->resolveApiListField($matches[1]);
        $direction = strtolower($matches[2]);

        $resolvedSortableFields = array_map(
            fn (string $sortableField): string => $this->resolveApiListField($sortableField),
            $sortableFields,
        );

        if (! in_array($field, $resolvedSortableFields, true)) {
            throw ValidationException::withMessages([
                'sortby' => ['The selected sort field is invalid.'],
            ]);
        }

        $query->orderBy($field, $direction);
    }

    /**
     * @param list<string> $includableRelations
     */
    protected function applyApiListIncludes(
        Builder $query,
        Request $request,
        array $includableRelations,
    ): void {
        if (! $request->filled('include_objects')) {
            return;
        }

        $relations = $this->parseApiListCsv($request, 'include_objects');

        $invalidRelations = array_values(array_diff($relations, $includableRelations));
        if ($invalidRelations !== []) {
            throw ValidationException::withMessages([
                'include_objects' => ['One or more include_objects values are invalid.'],
            ]);
        }

        if ($relations !== []) {
            $query->with($relations);
        }
    }

    /**
     * @param list<string> $selectableFields
     */
    protected function applyApiListFieldSelection(
        Builder $query,
        Request $request,
        array $selectableFields,
    ): void {
        if (! $request->filled('include_fields')) {
            return;
        }

        $fields = array_map(
            fn (string $field): string => $this->resolveApiListField($field),
            $this->parseApiListCsv($request, 'include_fields'),
        );

        $resolvedSelectableFields = array_map(
            fn (string $selectableField): string => $this->resolveApiListField($selectableField),
            $selectableFields,
        );

        $invalidFields = array_values(array_diff($fields, $resolvedSelectableFields));
        if ($invalidFields !== []) {
            throw ValidationException::withMessages([
                'include_fields' => ['One or more include_fields values are invalid.'],
            ]);
        }

        if (! in_array('id', $fields, true)) {
            $fields[] = 'id';
        }

        $query->select(array_values(array_unique($fields)));
    }

    protected function resolveApiListField(string $field): string
    {
        return $field;
    }

    /**
     * @return list<string>
     */
    protected function parseApiListCsv(Request $request, string $parameter): array
    {
        return array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) $request->string($parameter)),
        )));
    }
}
