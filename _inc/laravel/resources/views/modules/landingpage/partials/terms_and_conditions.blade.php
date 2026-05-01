{{-- Terms and Conditions — LGPD-compliant boilerplate --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-4 p-md-5 legal-content">
        <h2 class="mb-4">{{ __('Terms and Conditions') }}</h2>
        <p class="text-muted mb-4"><small>{{ __('Last updated') }}: {{ date('F Y') }}</small></p>

        <article>
            <h4>1. {{ __('Acceptance of Terms') }}</h4>
            <p>{!! __('terms.acceptance', [
                'company' => '<strong>Brand New Ideas Company.net Informática</strong>',
                'cnpj' => '<abbr title="Cadastro Nacional da Pessoa Jurídica">CNPJ</abbr>',
            ]) !!}</p>
            <p>{{ __('By accessing or using this platform, you expressly agree to these Terms and Conditions. If you do not agree, please discontinue use immediately.') }}</p>
        </article>

        <article>
            <h4>2. {{ __('Definitions') }}</h4>
            <dl>
                <dt><dfn>{{ __('Platform') }}</dfn></dt>
                <dd>{{ __('The ERP Brand New Ideas Company web application, including all modules, APIs, and related services.') }}</dd>
                <dt><dfn>{{ __('User') }}</dfn></dt>
                <dd>{{ __('Any individual or legal entity that accesses or uses the Platform.') }}</dd>
                <dt><dfn>{{ __('Personal Data') }}</dfn></dt>
                <dd>{!! __('Information related to an identified or identifiable natural person, as defined in <a href="https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm" target="_blank" rel="noopener noreferrer">Art. 5, I of Law 13.709/2018 (LGPD)</a>.') !!}</dd>
                <dt><dfn>{{ __('Controller') }}</dfn></dt>
                <dd>{!! __('Brand New Ideas Company.net Informática, responsible for decisions regarding the processing of personal data, pursuant to <a href="https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm" target="_blank" rel="noopener noreferrer">Art. 5, VI of the LGPD</a>.') !!}</dd>
            </dl>
        </article>

        <article>
            <h4>3. {{ __('Scope of Services') }}</h4>
            <p>{{ __('The Platform provides enterprise resource planning tools, including but not limited to: financial management, human resources, project tracking, CRM, invoicing, and reporting modules.') }}</p>
            <p><em>{{ __('Service availability is subject to the subscription plan contracted by the User.') }}</em></p>
        </article>

        <article>
            <h4>4. {{ __('User Obligations') }}</h4>
            <ul>
                <li>{{ __('Provide accurate and up-to-date information during registration.') }}</li>
                <li>{{ __('Maintain the confidentiality of login credentials.') }}</li>
                <li>{{ __('Use the Platform in compliance with applicable laws, including the Brazilian Civil Code and the Marco Civil da Internet.') }}</li>
                <li><mark>{{ __('Not attempt to reverse-engineer, decompile, or exploit the Platform beyond its intended use.') }}</mark></li>
                <li>{{ __('Promptly notify the Controller of any unauthorized access or security breach.') }}</li>
            </ul>
        </article>

        <article>
            <h4>5. {{ __('Intellectual Property') }}</h4>
            <p>{!! __('All content, source code, trademarks, and visual assets of the Platform are the exclusive property of <strong>Brand New Ideas Company.net Informática</strong> or its licensors, protected under <a href="https://www.planalto.gov.br/ccivil_03/leis/l9609.htm" target="_blank" rel="noopener noreferrer">Law 9.609/1998</a> (Software Protection) and <a href="https://www.planalto.gov.br/ccivil_03/leis/l9610.htm" target="_blank" rel="noopener noreferrer">Law 9.610/1998</a> (Copyright).') !!}</p>
        </article>

        <article>
            <h4>6. {{ __('Data Protection and LGPD Compliance') }}</h4>
            <p>{!! __('We process personal data in accordance with <a href="https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm" target="_blank" rel="noopener noreferrer">Lei Geral de Proteção de Dados (Law 13.709/2018)</a>. For details on data collection and processing, see our <a href=":privacy_url">Privacy Policy</a>.', ['privacy_url' => Route::has('privacy_policy') ? route('privacy_policy') : '#']) !!}</p>
            <p>{{ __('Legal bases for processing include: consent, contractual performance, legitimate interest, and compliance with legal obligations (Art. 7, LGPD).') }}</p>
        </article>

        <article>
            <h4>7. {{ __('Limitation of Liability') }}</h4>
            <p>{{ __('To the maximum extent permitted by law, the Platform is provided "as is" without warranties of any kind. The Controller shall not be liable for indirect, incidental, or consequential damages arising from the use of the Platform.') }}</p>
            <p><em>{{ __('Scheduled maintenance windows may cause temporary unavailability. Users will be notified in advance when possible.') }}</em></p>
        </article>

        <article>
            <h4>8. {{ __('Termination') }}</h4>
            <p>{{ __('Either party may terminate the service relationship at any time. Upon termination, the User may request the export of their data within 30 days, after which it may be permanently deleted.') }}</p>
        </article>

        <article>
            <h4>9. {{ __('Governing Law and Jurisdiction') }}</h4>
            <p>{!! __('These Terms are governed by the laws of the <strong>Federative Republic of Brazil</strong>. Any disputes shall be submitted to the courts of the City of <strong>Rio de Janeiro, RJ</strong>, to the exclusion of any other jurisdiction.') !!}</p>
        </article>

        <article>
            <h4>10. {{ __('Contact') }}</h4>
            <p>{{ __('For questions regarding these Terms, contact us at:') }}</p>
            <address>
                <strong>Brand New Ideas Company.net Informática</strong><br>
                {{ __('Rua Francisco Manuel, 99A — Benfica, Rio de Janeiro — RJ, Brasil') }}<br>
                <a href="mailto:comercial@brandnewideascompany.com">comercial@brandnewideascompany.com</a><br>
                <a href="tel:+552138607510">+55 (21) 3860-7510</a>
            </address>
        </article>
    </div>
</div>
