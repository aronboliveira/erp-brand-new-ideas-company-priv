@php 
  use Illuminate\Support\Facades\Auth; 
  $user = Auth::user();
@endphp
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript" src="{{ asset('assets/js/html2pdf.bundle.min.js') }}"></script>
<script async src="{{ asset('assets/js/routes/estimations/lang/pdf.js') }}"></script>
@if(!empty($estimation) && isset($estimation->id))
  <script defer>
      (() => {
        const errFb       = '# ERROR';
        const toastBoxId  = 'toast-box';
        const langKey     = 'erp-np-lang';
        const msgKey      = 'estimate_pdf_unavailable';
      
        const getMsg = () => {
          let lang = (sessionStorage.getItem(langKey) || document.documentElement.lang || 'en')
            .toLowerCase()
            .replace(/_/g, '-');
          lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
          return window.translations?.[lang]?.[msgKey]
            || window.translations.en[msgKey]
            || errFb;
        };
      
        const showToast = message => {
          const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
            .some(l => /bootstrap/i.test(l.href))
            && window.bootstrap?.Toast;
          if (hasBs) {
            let box = document.getElementById(toastBoxId);
            if (!box) {
              box = document.createElement('div');
              box.id = toastBoxId;
              box.setAttribute('aria-live', 'polite');
              box.setAttribute('aria-atomic', 'true');
              document.body.appendChild(box);
            }
            const t = document.createElement('div');
            t.className = 'toast';
            t.innerHTML = `<div class="toast-body">${message}</div>`;
            box.appendChild(t);
            bootstrap.Toast.getOrCreateInstance(t).show();
          } else {
            alert(message);
          }
        };
      
        const closeScript = () => {
          setTimeout(() => {
            try {
              window.open(window.location, '_self').close();
            } catch {
              // ignore
            }
          }, 1000);
        };
      
        window.addEventListener('load', () => {
          try {
            const element = document.getElementById('boxes');
            if (!element) throw new Error();
            const opt = {
              filename: '{{ method_exists($user, "estimateNumberFormat") ? $user->estimateNumberFormat($estimation->estimation_id) : "#ERROR" }}',
              image: { type: 'jpeg', quality: 1 },
              html2canvas: { scale: 4, dpi: 72, letterRendering: true },
              jsPDF: { unit: 'in', format: 'A4' }
            };
            html2pdf()
              .set(opt)
              .from(element)
              .save()
              .then(closeScript)
              .catch(() => { throw new Error(); });
          } catch {
            showToast(getMsg());
          }
        });
      })();
  </script>
@else
  <script data-notice="failed-script"></script>
    