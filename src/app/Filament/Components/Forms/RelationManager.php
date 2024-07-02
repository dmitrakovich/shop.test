<?php

namespace App\Filament\Components\Forms;

use Closure;
use Filament\Forms\Components\Component;

class RelationManager extends Component
{
    protected string $view = 'filament.forms.relation-manager';

    protected string|Closure $relationManager;

    protected bool|Closure $isLazy = false;

    /**
     * Creates a new instance of the RelationManager class using the service container.
     *
     * @return static The newly created instance of the RelationManager class.
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Sets the relation manager.
     *
     * @param  string|Closure  $relationManager  The relation manager to set.
     * @return static The instance of the relation manager for method chaining.
     */
    public function manager(string|Closure $relationManager): static
    {
        $this->relationManager = $relationManager;

        return $this;
    }

    /**
     * Get the relation manager value by evaluating the relationManager property.
     *
     * @return string The evaluated relation manager value.
     */
    public function getRelationManager(): string
    {
        return $this->evaluate($this->relationManager);
    }

    /**
     * Sets the lazy flag for the relation manager.
     *
     * @param  bool|Closure  $condition  The condition to determine if the relation manager should be lazy. Defaults to true.
     * @return static The instance of the relation manager for method chaining.
     */
    public function lazy(bool|Closure $condition = true): static
    {
        $this->isLazy = $condition;

        return $this;
    }

    /**
     * Determine if the relation manager is lazy.
     */
    public function isLazy(): bool
    {
        return (bool)$this->evaluate($this->isLazy);
    }
}
