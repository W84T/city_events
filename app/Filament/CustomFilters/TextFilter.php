<?php

namespace App\Filament\CustomFilters;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class TextFilter extends Filter
{
    const CLAUSE_EQUAL = 'equal';
    const CLAUSE_NOT_EQUAL = 'not_equal';
    const CLAUSE_START_WITH = 'start_with';
    const CLAUSE_NOT_START_WITH = 'not_start_with';
    const CLAUSE_END_WITH = 'end_with';
    const CLAUSE_NOT_END_WITH = 'not_end_with';
    const CLAUSE_CONTAIN = 'contain';
    const CLAUSE_NOT_CONTAIN = 'not_contain';
    const CLAUSE_SET = 'set';
    const CLAUSE_NOT_SET = 'not_set';

    protected int $debounce = 500;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->default([
                'clause' => self::CLAUSE_CONTAIN,
                'value'  => null,
            ])
            ->schema(fn() => [
                Fieldset::make($this->getLabel())
                    ->schema([
                        Select::make('clause')
                            ->options($this->clauses())
                            ->hiddenLabel()
                            ->default(self::CLAUSE_CONTAIN)
                            ->live()
                            ->afterStateUpdated(fn($state, Get $get, $set) => $set('value', null)),
                        TextInput::make('value')
                            ->debounce($this->debounce)
                            ->hidden(fn(Get $get) => blank($get('clause')) ||
                                in_array($get('clause'), [self::CLAUSE_SET, self::CLAUSE_NOT_SET]))
                            ->hiddenLabel(),
                    ])
                    ->columns(1),
            ])
            ->query(function (Builder $query, array $data) {
                $clause = $data['clause'] ?? null;
                $value = $data['value'] ?? null;
                $column = $this->getName();

                if (!$clause) {
                    return $query;
                }

                // SET / NOT SET
                if ($clause === self::CLAUSE_SET) {
                    return $query->whereNotNull($column);
                }
                if ($clause === self::CLAUSE_NOT_SET) {
                    return $query->whereNull($column);
                }

                if ($value === null) {
                    return $query;
                }

                $operator = match ($clause) {
                    self::CLAUSE_START_WITH, self::CLAUSE_END_WITH, self::CLAUSE_CONTAIN => 'like',
                    self::CLAUSE_NOT_START_WITH, self::CLAUSE_NOT_END_WITH, self::CLAUSE_NOT_CONTAIN => 'not like',
                    self::CLAUSE_NOT_EQUAL => '!=',
                    default => '=',
                };

                $formattedValue = match ($clause) {
                    self::CLAUSE_START_WITH, self::CLAUSE_NOT_START_WITH => $value . '%',
                    self::CLAUSE_END_WITH, self::CLAUSE_NOT_END_WITH => '%' . $value,
                    self::CLAUSE_CONTAIN, self::CLAUSE_NOT_CONTAIN => '%' . $value . '%',
                    default => $value,
                };

                return $query->where($column, $operator, $formattedValue);
            })
            ->indicateUsing(fn(array $data) => $this->generateIndicators($data));
    }

    public function clauses(): array
    {
        return [
            self::CLAUSE_EQUAL => 'Equals',
            self::CLAUSE_NOT_EQUAL => 'Not equals',
            self::CLAUSE_START_WITH => 'Starts with',
            self::CLAUSE_NOT_START_WITH => 'Does not start with',
            self::CLAUSE_END_WITH => 'Ends with',
            self::CLAUSE_NOT_END_WITH => 'Does not end with',
            self::CLAUSE_CONTAIN => 'Contains',
            self::CLAUSE_NOT_CONTAIN => 'Does not contain',
            self::CLAUSE_SET => 'Is set',
            self::CLAUSE_NOT_SET => 'Is not set',
        ];
    }

    protected function generateIndicators(array $data): array
    {
        $indicators = [];

        if (!isset($data['clause'])) {
            return $indicators;
        }

        if (in_array($data['clause'], [self::CLAUSE_SET, self::CLAUSE_NOT_SET])) {
            $indicators[] = Indicator::make($this->getLabel() . ' ' . $this->clauses()[$data['clause']])
                ->removeField('clause');
        } elseif (!empty($data['value'])) {
            $indicators[] = Indicator::make($this->getLabel() . ' ' . $this->clauses()[$data['clause']] . ' "' . $data['value'] . '"')
                ->removeField('value')
                ->removeField('clause');
        }

        return $indicators;
    }
}
