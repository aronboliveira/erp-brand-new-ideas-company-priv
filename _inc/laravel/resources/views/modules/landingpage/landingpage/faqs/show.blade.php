{{-- FAQ show stub --}}
<div>{{ is_array($faqs ?? null) ? ($faqs['faq_questions'] ?? '') : '' }}</div>
