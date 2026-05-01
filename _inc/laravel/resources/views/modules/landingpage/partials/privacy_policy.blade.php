{{-- Privacy Policy — LGPD-compliant boilerplate --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-4 p-md-5 legal-content">
        <h2 class="mb-4">{{ __('Privacy Policy') }}</h2>
        <p class="text-muted mb-4"><small>{{ __('Last updated') }}: {{ date('F Y') }}</small></p>

        <article>
            <h4>1. {{ __('Introduction') }}</h4>
            <p>{!! __('This Privacy Policy describes how <strong>Brand New Ideas Company.net Informática</strong> ("we", "Controller") collects, uses, stores, and shares personal data through the ERP Brand New Ideas Company platform, in compliance with <a href="https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm" target="_blank" rel="noopener noreferrer">Lei Geral de Proteção de Dados — LGPD (Law 13.709/2018)</a> and the <a href="https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2014/lei/l12965.htm" target="_blank" rel="noopener noreferrer">Marco Civil da Internet (Law 12.965/2014)</a>.') !!}</p>
        </article>

        <article>
            <h4>2. {{ __('Data We Collect') }}</h4>
            <p>{{ __('We may collect the following categories of personal data:') }}</p>
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Examples') }}</th>
                        <th>{{ __('Legal Basis (LGPD)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>{{ __('Identification') }}</strong></td>
                        <td>{{ __('Full name, email, phone number, CNPJ/CPF') }}</td>
                        <td><abbr title="Art. 7, V">{{ __('Contractual performance') }}</abbr></td>
                    </tr>
                    <tr>
                        <td><strong>{{ __('Access & Authentication') }}</strong></td>
                        <td>{{ __('IP address, browser, device information, session tokens') }}</td>
                        <td><abbr title="Art. 7, IX">{{ __('Legitimate interest') }}</abbr></td>
                    </tr>
                    <tr>
                        <td><strong>{{ __('Usage') }}</strong></td>
                        <td>{{ __('Actions performed, pages visited, features used') }}</td>
                        <td><abbr title="Art. 7, IX">{{ __('Legitimate interest') }}</abbr></td>
                    </tr>
                    <tr>
                        <td><strong>{{ __('Financial') }}</strong></td>
                        <td>{{ __('Billing information, transaction history, plan details') }}</td>
                        <td><abbr title="Art. 7, V">{{ __('Contractual performance') }}</abbr></td>
                    </tr>
                </tbody>
            </table>
        </article>

        <article>
            <h4>3. {{ __('Purpose of Processing') }}</h4>
            <ul>
                <li>{{ __('Providing and maintaining Platform services.') }}</li>
                <li>{{ __('Authenticating Users and managing access control.') }}</li>
                <li>{{ __('Processing billing and financial transactions.') }}</li>
                <li>{{ __('Sending service notifications and updates.') }}</li>
                <li>{{ __('Improving the Platform through analytics and usage patterns.') }}</li>
                <li>{{ __('Complying with legal and regulatory obligations.') }}</li>
            </ul>
        </article>

        <article>
            <h4>4. {{ __('Data Sharing') }}</h4>
            <p>{{ __('We do not sell personal data. Data may be shared with:') }}</p>
            <ul>
                <li><strong>{{ __('Service providers') }}</strong>: {{ __('Hosting, payment processing, and email delivery services, under contractual data protection agreements.') }}</li>
                <li><strong>{{ __('Legal authorities') }}</strong>: {{ __('When required by law, court order, or to protect the rights and safety of Users and the Controller.') }}</li>
            </ul>
        </article>

        <article>
            <h4>5. {{ __('Data Retention') }}</h4>
            <p>{!! __('Personal data is retained for the duration necessary to fulfill the purposes described herein, or as required by applicable legislation. Upon request for deletion, data will be removed within <mark>30 business days</mark>, except where retention is mandated by law (e.g., fiscal records under <a href="https://www.planalto.gov.br/ccivil_03/leis/l5172compilado.htm" target="_blank" rel="noopener noreferrer">Art. 173 of the National Tax Code</a>).') !!}</p>
        </article>

        <article>
            <h4>6. {{ __('Your Rights under the LGPD') }}</h4>
            <p>{!! __('Pursuant to <a href="https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm" target="_blank" rel="noopener noreferrer">Art. 18 of the LGPD</a>, you have the right to:') !!}</p>
            <ol>
                <li>{{ __('Confirm the existence of data processing.') }}</li>
                <li>{{ __('Access your personal data.') }}</li>
                <li>{{ __('Correct incomplete, inaccurate, or outdated data.') }}</li>
                <li>{{ __('Anonymize, block, or delete unnecessary or excessive data.') }}</li>
                <li>{{ __('Request data portability to another service provider.') }}</li>
                <li>{{ __('Delete personal data processed with your consent.') }}</li>
                <li>{{ __('Obtain information about public and private entities with which data has been shared.') }}</li>
                <li>{{ __('Be informed about the possibility of denying consent and its consequences.') }}</li>
                <li><mark>{{ __('Revoke consent at any time.') }}</mark></li>
            </ol>
            <p><em>{{ __('To exercise any of these rights, contact us using the information in Section 10.') }}</em></p>
        </article>

        <article>
            <h4>7. {{ __('Security Measures') }}</h4>
            <p>{{ __('We implement technical and organizational measures to protect personal data, including:') }}</p>
            <ul>
                <li>{{ __('Encryption of data in transit (TLS/SSL) and at rest.') }}</li>
                <li>{{ __('Access control with role-based permissions.') }}</li>
                <li>{{ __('Regular security audits and vulnerability assessments.') }}</li>
                <li>{{ __('Secure session management and CSRF protection.') }}</li>
            </ul>
        </article>

        <article>
            <h4>8. {{ __('Cookies and Tracking') }}</h4>
            <p>{{ __('The Platform uses essential cookies for authentication and session management. We may also use analytical cookies to improve user experience. You can manage cookie preferences through your browser settings.') }}</p>
        </article>

        <article>
            <h4>9. {{ __('International Data Transfers') }}</h4>
            <p>{!! __('If data is transferred to servers outside Brazil, we ensure adequate protection in accordance with <a href="https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm" target="_blank" rel="noopener noreferrer">Art. 33 of the LGPD</a>, including the use of standard contractual clauses and data processing agreements.') !!}</p>
        </article>

        <article>
            <h4>10. {{ __('Data Protection Officer (DPO)') }}</h4>
            <p>{{ __('For inquiries, requests, or complaints related to this Privacy Policy, contact our DPO:') }}</p>
            <address>
                <strong>Brand New Ideas Company.net Informática</strong><br>
                {{ __('Rua Francisco Manuel, 99A — Benfica, Rio de Janeiro — RJ, Brasil') }}<br>
                <a href="mailto:comercial@brandnewideascompany.com">comercial@brandnewideascompany.com</a><br>
                <a href="tel:+552138607510">+55 (21) 3860-7510</a>
            </address>
        </article>

        <article>
            <h4>11. {{ __('Changes to This Policy') }}</h4>
            <p>{{ __('We may update this Privacy Policy periodically. Significant changes will be communicated through the Platform or via email. Continued use of the Platform after changes constitutes acceptance of the revised policy.') }}</p>
        </article>
    </div>
</div>
