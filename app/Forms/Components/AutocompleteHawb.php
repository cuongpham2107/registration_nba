<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class AutocompleteHawb extends Field
{
    protected string $view = 'forms.components.autocomplete-hawb';

    protected ?string $apiEndpoint = null;

    public function apiEndpoint(string $endpoint): static
    {
        $this->apiEndpoint = $endpoint;

        return $this;
    }

    public function getApiEndpoint(): ?string
    {
        return $this->apiEndpoint;
    }

    public function getExtraInputAttributes(): array
    {
        return $this->getExtraAttributes();
    }
}
