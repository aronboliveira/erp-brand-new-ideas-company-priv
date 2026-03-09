<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Config\Constants\DatabaseConstants;
use App\Helpers\TemplateHelper;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * No Data Available Component
 * 
 * Renders the "no data available" error HTML for PDF/print templates.
 * 
 * Usage in Blade templates:
 *   <x-no-data entity-type="bill" :doc-lang="$docLang" />
 *   
 * Or in PHP:
 *   echo App\Helpers\TemplateHelper::getNoDataHtml('bill', $docLang);
 */
class NoData extends Component
{
    /**
     * The entity type (e.g., 'bill', 'invoice', 'purchase', 'proposal', 'pos').
     */
    public string $entityType;

    /**
     * The document language code.
     */
    public string $docLang;

    /**
     * The translated error message.
     */
    public string $message;

    /**
     * Whether to render as a full HTML document or just a div.
     */
    public bool $fullDocument;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $entityType = 'document',
        ?string $docLang = null,
        bool $fullDocument = true
    ) {
        $this->entityType = $entityType;
        $this->docLang = $docLang ?? TemplateHelper::getDocLang();
        $this->message = TemplateHelper::escape(__("No {$entityType} data available."));
        $this->fullDocument = $fullDocument;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|string
    {
        if ($this->fullDocument) {
            return TemplateHelper::getNoDataHtml($this->entityType, $this->docLang);
        }
        
        return view('components.no-data');
    }
}
