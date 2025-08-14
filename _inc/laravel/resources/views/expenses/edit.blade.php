@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        SettingsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Expense Edit')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route(ViewsConstants::EXP.'.index')}}">{{__('Expense')}}</a></li>
    <li class="breadcrumb-item">{{__('Expense Edit')}}</li>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script defer src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script defer src="{{asset('js/jquery-searchbox.js')}}"></script>
    <script async>
        window.translations = {
          ar: {
            selection_failed:                 'فشل تغيير النوع.',
            employee_fetch_failed:            'فشل جلب بيانات الموظف.',
            customer_fetch_failed:            'فشل جلب بيانات العميل.',
            vendor_fetch_failed:              'فشل جلب بيانات البائع.',
            repeater_initialization_failed:   'فشل تهيئة المكرر.',
            item_fetch_failed:                'فشل جلب بيانات الصنف.',
            calculation_failed:               'فشل حساب الإجماليات.',
            repeater_delete_failed:           'فشل حذف عنصر المكرر.'
          },
          da: {
            selection_failed:                 'Kunne ikke ændre typen.',
            employee_fetch_failed:            'Kunne ikke hente medarbejderdata.',
            customer_fetch_failed:            'Kunne ikke hente kundedata.',
            vendor_fetch_failed:              'Kunne ikke hente leverandørdata.',
            repeater_initialization_failed:   'Kunne ikke initialisere gentager.',
            item_fetch_failed:                'Kunne ikke hente varedata.',
            calculation_failed:               'Kunne ikke beregne totaler.',
            repeater_delete_failed:           'Kunne ikke slette gentagerelement.'
          },
          de: {
            selection_failed:                 'Auswahl konnte nicht geändert werden.',
            employee_fetch_failed:            'Mitarbeiterdaten konnten nicht geladen werden.',
            customer_fetch_failed:            'Kundendaten konnten nicht geladen werden.',
            vendor_fetch_failed:              'Anbieterdaten konnten nicht geladen werden.',
            repeater_initialization_failed:   'Initialisierung des Repeaters fehlgeschlagen.',
            item_fetch_failed:                'Elementdaten konnten nicht abgerufen werden.',
            calculation_failed:               'Berechnung der Summen fehlgeschlagen.',
            repeater_delete_failed:           'Löschen des Repeater-Elements fehlgeschlagen.'
          },
          en: {
            selection_failed:                 'Failed to change type.',
            employee_fetch_failed:            'Failed to load employee details.',
            customer_fetch_failed:            'Failed to load customer details.',
            vendor_fetch_failed:              'Failed to load vendor details.',
            repeater_initialization_failed:   'Failed to initialize repeater.',
            item_fetch_failed:                'Failed to fetch item data.',
            calculation_failed:               'Failed to calculate totals.',
            repeater_delete_failed:           'Failed to delete repeater item.'
          },
          es: {
            selection_failed:                 'Error al cambiar el tipo.',
            employee_fetch_failed:            'Error al cargar datos del empleado.',
            customer_fetch_failed:            'Error al cargar datos del cliente.',
            vendor_fetch_failed:              'Error al cargar datos del proveedor.',
            repeater_initialization_failed:   'No se pudo inicializar el repetidor.',
            item_fetch_failed:                'No se pudieron obtener los datos del artículo.',
            calculation_failed:               'No se pudieron calcular los totales.',
            repeater_delete_failed:           'Error al eliminar elemento del repetidor.'
          },
          fr: {
            selection_failed:                 'Échec du changement de type.',
            employee_fetch_failed:            'Échec du chargement des détails de l’employé.',
            customer_fetch_failed:            'Échec du chargement des détails du client.',
            vendor_fetch_failed:              'Échec du chargement des détails du fournisseur.',
            repeater_initialization_failed:   'Échec de l’initialisation du répéteur.',
            item_fetch_failed:                'Échec de la récupération des données de l’article.',
            calculation_failed:               'Échec du calcul des totaux.',
            repeater_delete_failed:           'Échec de la suppression de l’élément du répéteur.'
          },
          he: {
            selection_failed:                 'נכשל שינוי הסוג.',
            employee_fetch_failed:            'נכשלו טעינת פרטי העובד.',
            customer_fetch_failed:            'נכשלו טעינת פרטי הלקוח.',
            vendor_fetch_failed:              'נכשלו טעינת פרטי הספק.',
            repeater_initialization_failed:   'נכשל אתחול החוזר.',
            item_fetch_failed:                'נכשל אחזור נתוני הפריט.',
            calculation_failed:               'נכשל חישוב הסכומים.',
            repeater_delete_failed:           'נכשל מחיקת פריט החוזר.'
          },
          it: {
            selection_failed:                 'Impossibile cambiare tipo.',
            employee_fetch_failed:            'Impossibile caricare i dettagli del dipendente.',
            customer_fetch_failed:            'Impossibile caricare i dettagli del cliente.',
            vendor_fetch_failed:              'Impossibile caricare i dettagli del fornitore.',
            repeater_initialization_failed:   'Impossibile inizializzare il ripetitore.',
            item_fetch_failed:                'Impossibile recuperare i dati dell\'articolo.',
            calculation_failed:               'Impossibile calcolare i totali.',
            repeater_delete_failed:           'Impossibile eliminare l\'elemento ripetitore.'
          },
          ja: {
            selection_failed:                 'タイプの変更に失敗しました。',
            employee_fetch_failed:            '従業員の詳細の読み込みに失敗しました。',
            customer_fetch_failed:            '顧客の詳細の読み込みに失敗しました。',
            vendor_fetch_failed:              'ベンダーの詳細の読み込みに失敗しました。',
            repeater_initialization_failed:   'リピーターの初期化に失敗しました。',
            item_fetch_failed:                'アイテムデータの取得に失敗しました。',
            calculation_failed:               '合計の計算に失敗しました。',
            repeater_delete_failed:           'リピーター項目の削除に失敗しました。'
          },
          nl: {
            selection_failed:                 'Kon type niet wijzigen.',
            employee_fetch_failed:            'Kon medewerkersgegevens niet laden.',
            customer_fetch_failed:            'Kon klantgegevens niet laden.',
            vendor_fetch_failed:              'Kon leveranciersgegevens niet laden.',
            repeater_initialization_failed:   'Kan herhaler niet initialiseren.',
            item_fetch_failed:                'Kan itemgegevens niet opvragen.',
            calculation_failed:               'Kan totalen niet berekenen.',
            repeater_delete_failed:           'Kan herhaler-item niet verwijderen.'
          },
          pl: {
            selection_failed:                 'Nie udało się zmienić typu.',
            employee_fetch_failed:            'Nie udało się załadować danych pracownika.',
            customer_fetch_failed:            'Nie udało się załadować danych klienta.',
            vendor_fetch_failed:              'Nie udało się załadować danych dostawcy.',
            repeater_initialization_failed:   'Nie udało się zainicjalizować repeatera.',
            item_fetch_failed:                'Nie udało się pobrać danych przedmiotu.',
            calculation_failed:               'Nie udało się obliczyć sum.',
            repeater_delete_failed:           'Nie udało się usunąć elementu repeatera.'
          },
          pt: {
            selection_failed:                 'Falha ao alterar tipo.',
            employee_fetch_failed:            'Falha ao carregar dados do funcionário.',
            customer_fetch_failed:            'Falha ao carregar dados do cliente.',
            vendor_fetch_failed:              'Falha ao carregar dados do fornecedor.',
            repeater_initialization_failed:   'Falha ao inicializar o repetidor.',
            item_fetch_failed:                'Falha ao buscar dados do item.',
            calculation_failed:               'Falha ao calcular totais.',
            repeater_delete_failed:           'Falha ao excluir item do repetidor.'
          },
          'pt-br': {
            selection_failed:                 'Falha ao alterar tipo.',
            employee_fetch_failed:            'Falha ao carregar dados do funcionário.',
            customer_fetch_failed:            'Falha ao carregar dados do cliente.',
            vendor_fetch_failed:              'Falha ao carregar dados do fornecedor.',
            repeater_initialization_failed:   'Falha ao inicializar o repetidor.',
            item_fetch_failed:                'Falha ao buscar dados do item.',
            calculation_failed:               'Falha ao calcular totais.',
            repeater_delete_failed:           'Falha ao excluir item do repetidor.'
          },
          ru: {
            selection_failed:                 'Не удалось изменить тип.',
            employee_fetch_failed:            'Не удалось загрузить данные сотрудника.',
            customer_fetch_failed:            'Не удалось загрузить данные клиента.',
            vendor_fetch_failed:              'Не удалось загрузить данные поставщика.',
            repeater_initialization_failed:   'Не удалось инициализировать повторитель.',
            item_fetch_failed:                'Не удалось получить данные элемента.',
            calculation_failed:               'Не удалось вычислить итоги.',
            repeater_delete_failed:           'Не удалось удалить элемент повторителя.'
          },
          tr: {
            selection_failed:                 'Tür değiştirilemedi.',
            employee_fetch_failed:            'Çalışan bilgileri yüklenemedi.',
            customer_fetch_failed:            'Müşteri bilgileri yüklenemedi.',
            vendor_fetch_failed:              'Tedarikçi bilgileri yüklenemedi.',
            repeater_initialization_failed:   'Tekrarlayıcı başlatılamadı.',
            item_fetch_failed:                'Öğe verileri alınamadı.',
            calculation_failed:               'Toplamlar hesaplanamadı.',
            repeater_delete_failed:           'Tekrarlayıcı öğesi silinemedi.'
          },
          zh: {
            selection_failed:                 '更改类型失败。',
            employee_fetch_failed:            '无法加载员工详情。',
            customer_fetch_failed:            '无法加载客户详情。',
            vendor_fetch_failed:              '无法加载供应商详情。',
            repeater_initialization_failed:   '无法初始化重复项。',
            item_fetch_failed:                '无法获取项目数据。',
            calculation_failed:               '无法计算总计。',
            repeater_delete_failed:           '无法删除重复器项目。'
          }
        };
    </script>    
    <script defer>
        (() => {
          const errFb = "# ERROR";
          const dataClientLocalized = "data-client-localized";
          const dataGuardMsg = "data-guard-msg";
          const langSessionKey = "erp-np-lang";
      
          function getMsg(key, el){
            let msg = errFb;
            if (
              el.getAttribute("data-sv-localized") === "true" ||
              el.getAttribute(dataClientLocalized) === "true"
            ){
              msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
              let lang = (
                window.sessionStorage.getItem(langSessionKey) ||
                document.documentElement.lang ||
                "en"
              ).toLowerCase().replace(/_/g,"-");
              lang = (lang === "pt-br" ? lang : lang.slice(0,2));
              msg =
                window.translations?.[lang]?.[key] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.["en"]?.[key] ||
                errFb;
              if (msg !== errFb){
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
              }
            }
            return msg;
          }
      
          function showError(message){
            try {
              const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                .some(l=>/bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
              if (hasBs){
                let container = document.getElementById("bootstrap-toast-container");
                if (!container){
                  container = document.createElement("div");
                  container.id = "bootstrap-toast-container";
                  container.setAttribute("aria-live","polite");
                  container.setAttribute("aria-atomic","true");
                  document.body.appendChild(container);
                }
                let toast = container.querySelector(".toast") || (()=>{
                  const t = document.createElement("div");
                  t.className = "toast";
                  t.setAttribute("role","alert");
                  t.setAttribute("aria-live","assertive");
                  t.setAttribute("aria-atomic","true");
                  const body = document.createElement("div");
                  body.className = "toast-body";
                  t.appendChild(body);
                  container.appendChild(t);
                  return t;
                })();
                toast.querySelector(".toast-body").textContent = message;
                bootstrap.Toast.getOrCreateInstance(toast).show();
              } else {
                alert(message);
              }
            } catch {
              alert(message);
            }
          }
      
          try {
            const sel = "body";
            if (!document.querySelector(sel + " .repeater")) return;
      
            const $drag = $("body .repeater tbody").sortable({ handle: ".sort-handler" });
      
            const $rep = $(sel + " .repeater").repeater({
              initEmpty: true,
              defaultValues: { status: 1 },
              show() {
                $(this).slideDown();
                const files = $(this).find("input.multi");
                if (files.length){
                  files.MultiFile({
                    max: 3,
                    accept: "png|jpg|jpeg",
                    max_size: {{ SettingsConstants::MAX_U_SIZE_DEF }}
                  });
                }
                JsSearchBox();
                if (document.querySelector(".select2")) $(".select2").select2();
              },
              hide(deleteEl) {
                $(this).slideUp(deleteEl);
                this.remove();
                const subTotal = Array.from(document.querySelectorAll(".amount"))
                  .reduce((sum, el) => sum + parseFloat(el.textContent || "0"), 0);
                document.querySelector(".subTotal")?.textContent = subTotal.toFixed(2);
                document.querySelector(".totalAmount")?.textContent = subTotal.toFixed(2);
              },
              ready(setIdx) {
                $drag.on("drop", setIdx);
              },
              isFirstItemUndeletable: true
            });
      
            const dataVal = document.querySelector(sel + " .repeater").getAttribute("data-value");
            if (dataVal){
              JSON.parse(dataVal).forEach(item => {
                const row = document.querySelector(`#sortable-table .id[value="${item.id}"]`)?.closest("tr");
                if (row){
                  const elem = row.querySelector(".item");
                  elem.value = item.product_id;
                  changeItem($(elem));
                }
              });
            }
      
          } catch (e){
            const msg = getMsg("repeater_initialization_failed", document.body);
            showError(msg);
          }
        })();
    </script>
    <script defer>
        (() => {
          const errFb = "# ERROR";
          const dataClientLocalized = "data-client-localized";
          const dataGuardMsg = "data-guard-msg";
          const langSessionKey = "erp-np-lang";
        
          function getMsg(key, el) {
            let msg = errFb;
            if (
              el.getAttribute("data-sv-localized") === "true" ||
              el.getAttribute(dataClientLocalized) === "true"
            ) {
              msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
              let lang = (
                window.sessionStorage.getItem(langSessionKey) ||
                document.documentElement.lang ||
                "en"
              ).toLowerCase().replace(/_/g, "-");
              lang = lang === "pt-br" ? lang : lang.slice(0, 2);
              msg =
                window.translations?.[lang]?.[key] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[key] ||
                errFb;
              if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
              }
            }
            return msg;
          }
        
          function showError(message) {
            try {
              const hasBs =
                Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(l =>
                  /bootstrap/i.test(l.href)
                ) && window.bootstrap?.Toast;
              if (hasBs) {
                let c = document.getElementById("bootstrap-toast-container");
                if (!c) {
                  c = document.createElement("div");
                  c.id = "bootstrap-toast-container";
                  c.setAttribute("aria-live", "polite");
                  c.setAttribute("aria-atomic", "true");
                  document.body.appendChild(c);
                }
                let t = c.querySelector(".toast") || (() => {
                  const t0 = document.createElement("div");
                  t0.className = "toast";
                  t0.setAttribute("role", "alert");
                  t0.setAttribute("aria-live", "assertive");
                  t0.setAttribute("aria-atomic", "true");
                  const b = document.createElement("div");
                  b.className = "toast-body";
                  t0.appendChild(b);
                  c.appendChild(t0);
                  return t0;
                })();
                t.querySelector(".toast-body").textContent = message;
                bootstrap.Toast.getOrCreateInstance(t).show();
              } else {
                alert(message);
              }
            } catch {
              alert(message);
            }
          }
        
          const billId = '{{ $expense->id }}';
        
          async function changeItem($el) {
            try {
              const prodId = $el.val();
              const url    = $el.data("url");
              if (!url || url === "#") {
                throw new Error("item_fetch_failed");
              }
        
              const resp = await fetch(url, {
                method: "POST",
                headers: {
                  "X-CSRF-TOKEN": $("#token").val(),
                  "Content-Type": "application/json"
                },
                body: JSON.stringify({ product_id: prodId })
              });
              const text = await resp.text();
              const item = JSON.parse(text);
        
              const resp2 = await fetch(`{{ route(ViewsConstants::EXP.'.items') }}?bill_id=${billId}&product_id=${prodId}`, {
                headers: { "X-CSRF-TOKEN": $("#token").val() }
              });
              const billItems = JSON.parse(await resp2.text());
        
              const $row = $el.closest("tr");
              const qtyIn = $row.find(".quantity");
              const priceIn = $row.find(".price");
              const discIn = $row.find(".discount");
              const descIn = $row.find(".pro_description");
        
              if (billItems) {
                qtyIn.val(billItems.quantity);
                priceIn.val(billItems.price);
                discIn.val(billItems.discount);
                descIn.val(billItems.description);
              } else {
                qtyIn.val(1);
                priceIn.val(item.product.purchase_price);
                discIn.val(0);
                descIn.val(item.product.description);
              }
        
              // compute tax badges
              const taxes = [];
              let totalTaxRate = 0;
              item.taxes?.forEach(tax => {
                taxes.push(`<span class="badge bg-primary p-2 px-3 rounded mt-1 mr-1">
                  ${tax.name} (${tax.rate}%)
                </span>`);
                totalTaxRate += parseFloat(tax.rate);
              });
              const taxPrice = ((totalTaxRate / 100) *
                ( (billItems?.price || item.product.purchase_price) *
                  (billItems?.quantity || 1) -
                  parseFloat(discIn.val() || 0)
                )
              ).toFixed(2);
              $row.find(".itemTaxPrice").val(taxPrice);
              $row.find(".itemTaxRate").val(totalTaxRate.toFixed(2));
              $row.find(".taxes").html(taxes.join(""));
              $row.find(".tax").val(item.taxes.map(t=>t.id));
              $row.find(".unit").text(item.unit);
        
              // recalc totals
              const amounts = Array.from(document.querySelectorAll(".amount"))
                .reduce((s, el) => s + parseFloat(el.textContent||"0"), 0);
              const acct = Array.from(document.querySelectorAll(".accountamount"))
                .reduce((s, el) => {
                  const v = parseFloat(el.textContent||"0");
                  return s + (isNaN(v)?0:v);
                },0);
        
              $(".subTotal").text((amounts+acct).toFixed(2));
              $(".totalTax").text(
                Array.from(document.querySelectorAll(".itemTaxPrice"))
                  .reduce((s, el) => s + parseFloat(el.value||"0"),0)
                  .toFixed(2)
              );
              $(".totalAmount").text((amounts+acct).toFixed(2));
              $(".totalAmount").val((amounts+acct).toFixed(2));
        
            } catch (err) {
              const key = err.message || "item_fetch_failed";
              showError(getMsg(key, document.body));
            }
          }
        
          $(document).on("change", ".item", function() {
            changeItem($(this));
          });
        })();
    </script>
    <script defer>
        (() => {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const langSessionKey = "erp-np-lang";
        
        function getMsg(key, el) {
            let msg = errFb;
            if (
            el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalized) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem(langSessionKey) ||
                document.documentElement.lang ||
                "en"
            ).toLowerCase().replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[key] ||
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        }
        
        function showError(message) {
            try {
            const hasBs =
                Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(l =>
                /bootstrap/i.test(l.href)
                ) && window.bootstrap?.Toast;
            if (hasBs) {
                let c = document.getElementById("bootstrap-toast-container");
                if (!c) {
                c = document.createElement("div");
                c.id = "bootstrap-toast-container";
                c.setAttribute("aria-live", "polite");
                c.setAttribute("aria-atomic", "true");
                document.body.appendChild(c);
                }
                let t = c.querySelector(".toast") || (() => {
                const t0 = document.createElement("div");
                t0.className = "toast";
                t0.setAttribute("role", "alert");
                t0.setAttribute("aria-live", "assertive");
                t0.setAttribute("aria-atomic", "true");
                const b = document.createElement("div");
                b.className = "toast-body";
                t0.appendChild(b);
                c.appendChild(t0);
                return t0;
                })();
                t.querySelector(".toast-body").textContent = message;
                bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        }
        
        const sel = "body";
        if (!$(sel).find(".repeater").length) return;
        
        try {
            const $dragAndDrop = $("body .repeater tbody").sortable({
            handle: ".sort-handler"
            });
            const $repeater = $(`${sel} .repeater`).repeater({
            initEmpty: true,
            defaultValues: { status: 1 },
            show() {
                try {
                $(this).slideDown();
                const $multi = $(this).find("input.multi");
                if ($multi.length) {
                    $multi.MultiFile({
                    max: 3,
                    accept: "png|jpg|jpeg",
                    max_size: {{ SettingsConstants::MAX_U_SIZE_DEF }}
                    });
                }
                JsSearchBox();
                if ($(".select2").length) {
                    $(".select2").select2();
                }
                } catch {
                showError(getMsg("repeater_initialization_failed", this));
                }
            },
            hide(deleteElement) {
                try {
                $(this).slideUp(deleteElement);
                $(this).remove();
                const subTotal = Array.from(document.querySelectorAll(".amount"))
                    .reduce((sum, el) => sum + parseFloat(el.textContent || 0), 0);
                $(".subTotal").html(subTotal.toFixed(2));
                $(".totalAmount").html(subTotal.toFixed(2));
                } catch {
                showError(getMsg("calculation_failed", this));
                }
            },
            ready(setIndexes) {
                $dragAndDrop.on("drop", setIndexes);
            },
            isFirstItemUndeletable: true
            });
        
            const dataVal = $(sel).find(".repeater").attr("data-value");
            if (dataVal) {
            const list = JSON.parse(dataVal);
            $repeater.setList(list);
            list.forEach(item => {
                const tr = $(`#sortable-table .id[value="${item.id}"]`).parent();
                tr.find(".item").val(item.product_id);
                changeItem(tr.find(".item"));
            });
            }
        } catch {
            showError(getMsg("repeater_initialization_failed", document.body));
        }
        })();
    </script>
    <script defer>
        (() => {
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const langSessionKey = "erp-np-lang";
        const errFb = "# ERROR";

        function getMsg(key, el) {
            let msg = errFb;
            if (
            el.getAttribute("data-sv-localized") === "true" ||
            el.getAttribute(dataClientLocalized) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem(langSessionKey) ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            msg =
                window.translations?.[lang]?.[key] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[key] ||
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        }

        function showError(message) {
            try {
            const hasBs =
                Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(l =>
                /bootstrap/i.test(l.href)
                ) && window.bootstrap?.Toast;
            if (hasBs) {
                let container = document.getElementById("bootstrap-toast-container");
                if (!container) {
                container = document.createElement("div");
                container.id = "bootstrap-toast-container";
                container.setAttribute("aria-live", "polite");
                container.setAttribute("aria-atomic", "true");
                document.body.appendChild(container);
                }
                let toast = container.querySelector(".toast") || (() => {
                const t = document.createElement("div");
                t.className = "toast";
                t.setAttribute("role", "alert");
                t.setAttribute("aria-live", "assertive");
                t.setAttribute("aria-atomic", "true");
                const body = document.createElement("div");
                body.className = "toast-body";
                t.appendChild(body);
                container.appendChild(t);
                return t;
                })();
                toast.querySelector(".toast-body").textContent = message;
                bootstrap.Toast.getOrCreateInstance(toast).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        }

        const listenerAttr = "data-rep-del-listener";
        document.querySelectorAll("[data-repeater-delete]").forEach(el => {
            if (el.getAttribute(listenerAttr) === "true") return;
            el.setAttribute(listenerAttr, "true");
            el.addEventListener("click", () => {
            try {
                $(".price").change();
                $(".discount").change();
            } catch {
                showError(getMsg("repeater_delete_failed", el));
            }
            });
            const obs = new MutationObserver((mutations, o) => {
            if (!document.body.contains(el)) {
                el.removeEventListener("click", null);
                o.disconnect();
            }
            });
            obs.observe(document.body, { childList: true, subtree: true });
        });
        })();
    </script>
    {{--  start for user select--}}
    <script defer>
        (() => {
          const dataClientLocalized = "data-client-localized";
          const dataGuardMsg = "data-guard-msg";
          const langKey = "erp-np-lang";
          const errFb = "# ERROR";
        
          const getMsg = (key, el) => {
            let msg = errFb;
            if (
              el.getAttribute("data-sv-localized") === "true" ||
              el.getAttribute(dataClientLocalized) === "true"
            ) {
              msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
              let lang = (
                window.sessionStorage.getItem(langKey) ||
                document.documentElement.lang ||
                "en"
              )
                .toLowerCase()
                .replace(/_/g, "-");
              lang = lang === "pt-br" ? lang : lang.slice(0, 2);
              msg =
                window.translations?.[lang]?.[key] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.en?.[key] ||
                errFb;
              if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
              }
            }
            return msg;
          };
        
          const showError = message => {
            try {
              const hasBs =
                Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(l =>
                  /bootstrap/i.test(l.href)
                ) && window.bootstrap?.Toast;
              if (hasBs) {
                let c = document.getElementById("bootstrap-toast-container");
                if (!c) {
                  c = document.createElement("div");
                  c.id = "bootstrap-toast-container";
                  c.setAttribute("aria-live", "polite");
                  c.setAttribute("aria-atomic", "true");
                  document.body.appendChild(c);
                }
                let t = c.querySelector(".toast");
                if (!t) {
                  t = document.createElement("div");
                  t.className = "toast";
                  t.setAttribute("role", "alert");
                  t.setAttribute("aria-live", "assertive");
                  t.setAttribute("aria-atomic", "true");
                  const b = document.createElement("div");
                  b.className = "toast-body";
                  t.appendChild(b);
                  c.appendChild(t);
                }
                t.querySelector(".toast-body").textContent = message;
                bootstrap.Toast.getOrCreateInstance(t).show();
              } else alert(message);
            } catch {
              alert(message);
            }
          };
        
          const delegate = (event, selector, handler, key) => {
            document.addEventListener(event, e => {
              const el = e.target.closest(selector);
              if (!el) return;
              try {
                handler(el, e);
              } catch {
                showError(getMsg(key, el));
              }
              const obs = new MutationObserver((m, o) => {
                if (!document.body.contains(el)) {
                  o.disconnect();
                }
              });
              obs.observe(document.body, { childList: true, subtree: true });
            });
          };
        
          delegate("change", 'input[name="type"]:radio', (el) => {
            const t = el.value;
            if (t === "employee") {
              $(".employee").addClass("d-block").removeClass("d-none");
              $(".customer, .vendor").addClass("d-none").removeClass("d-block");
            } else if (t === "customer") {
              $(".customer").addClass("d-block").removeClass("d-none");
              $(".employee, .vendor").addClass("d-none").removeClass("d-block");
            } else {
              $(".vendor").addClass("d-block").removeClass("d-none");
              $(".employee, .customer").addClass("d-none").removeClass("d-block");
            }
          }, "selection_failed");
        
          delegate("change", "#employee", (el) => {
            $("#employee_detail").addClass("d-block").removeClass("d-none");
            $("#employee-box").addClass("d-none").removeClass("d-block");
            $.ajax({
              url: el.getAttribute("data-url"),
              type: "POST",
              headers: { "X-CSRF-TOKEN": jQuery("#token").val() },
              data: { id: el.value },
              success: data => {
                if (data) $("#employee_detail").innerHTML = data;
                else {
                  $("#employee-box").addClass("d-block").removeClass("d-none");
                  $("#employee_detail").addClass("d-none").removeClass("d-block");
                }
              },
              error: () => showError(getMsg("employee_fetch_failed", el))
            });
          });
        
          delegate("change", "#customer", (el) => {
            $("#customer_detail").addClass("d-block").removeClass("d-none");
            $("#customer-box").addClass("d-none").removeClass("d-block");
            $.ajax({
              url: el.getAttribute("data-url"),
              type: "POST",
              headers: { "X-CSRF-TOKEN": jQuery("#token").val() },
              data: { id: el.value },
              success: data => {
                if (data) $("#customer_detail").innerHTML = data;
                else {
                  $("#customer-box").addClass("d-block").removeClass("d-none");
                  $("#customer_detail").addClass("d-none").removeClass("d-block");
                }
              },
              error: () => showError(getMsg("customer_fetch_failed", el))
            });
          });
        
          delegate("change", "#vendor", (el) => {
            $("#vendor_detail").addClass("d-block").removeClass("d-none");
            $("#vendor-box").addClass("d-none").removeClass("d-block");
            $.ajax({
              url: el.getAttribute("data-url"),
              type: "POST",
              headers: { "X-CSRF-TOKEN": jQuery("#token").val() },
              data: { id: el.value },
              success: data => {
                if (data) $("#vendor_detail").innerHTML = data;
                else {
                  $("#vendor-box").addClass("d-block").removeClass("d-none");
                  $("#vendor_detail").addClass("d-none").removeClass("d-block");
                }
              },
              error: () => showError(getMsg("vendor_fetch_failed", el))
            });
          });
        
          delegate("click", "#remove", (el) => {
            $(".vendor, .customer, .employee")
              .addClass("d-block").removeClass("d-none");
            $("#vendor_detail, #customer_detail, #employee_detail")
              .addClass("d-none").removeClass("d-block");
          });
        
        })();
    </script>
    {{--   end for user select--}}
@endpush
@section('content')
    <div class="row">
        {{ Collective\Html\FormFacade::model($expense, array('route' => array(ViewsConstants::EXP.'.update', $expense->id), 'method' => 'PUT','class'=>'w-100')) }}
            <div class="col-12">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="col">
                                    <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                        <input type="radio" id="employee_radio" value="employee" name="type" class="form-check-input" {{ $expense->user_type == 'employee' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="employee">{{__('Employee')}}</label>
                                    </div>
                                    <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                        <input type="radio" id="customer_radio" value="customer" name="type" class="form-check-input" {{ $expense->user_type == 'customer' ? 'checked' : '' }} >
                                        <label class="form-check-label" for="customer">{{__('Customer')}}</label>
                                    </div>

                                    <div class="{{ ViewClassNamesConstants::FM_CHK_IL_GP }}">
                                        <input type="radio" id="vendor_radio" value="vendor" name="type" class="form-check-input" {{ $expense->user_type == 'vendor' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="vendor">{{__('Vendor')}}</label>
                                    </div>
                                </div>


                                <div class="col employee">
                                    <div class="form-group" id="employee-box">
                                        {{ Collective\Html\FormFacade::label('employee_id', __('Payee'),['class'=>'form-label']) }}
                                        {{ Collective\Html\FormFacade::select('employee_id', $employees,$expense->vendor_id, array('class' => 'form-control select','id'=>'employee','data-url'=>route(ViewsConstants::EXP.'.employee'))) }}
                                    </div>
                                    <div id="employee_detail" class="d-none">
                                    </div>
                                </div>
                                <div class="col customer d-none">
                                    <div class="form-group" id="customer-box">
                                        {{ Collective\Html\FormFacade::label('customer_id', __('Payee'),['class'=>'form-label']) }}
                                        {{ Collective\Html\FormFacade::select('customer_id', $customers,$expense->vendor_id, array('class' => 'form-control select','id'=>'customer','data-url'=>route(ViewsConstants::EXP.'.customer'))) }}
                                    </div>
                                    <div id="customer_detail" class="d-none">
                                    </div>
                                </div>
                                <div class="col vendor d-none">
                                    <div class="form-group" id="vendor-box">
                                        {{ Collective\Html\FormFacade::label('vendor_id', __('Payee'),['class'=>'form-label']) }}
                                        {{ Collective\Html\FormFacade::select('vendor_id', $vendors,$expense->vendor_id, array('class' => 'form-control select','id'=>'vendor','data-url'=>route(ViewsConstants::EXP.'.vendor'))) }}
                                    </div>
                                    <div id="vendor_detail" class="d-none">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('bill_date', __('Payment Date'),['class'=>'form-label']) }}
                                            {{Collective\Html\FormFacade::date('bill_date',null,array('class'=>'form-control','required'=>'required'))}}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('category_id', __('Category'),['class'=>'form-label']) }}
                                            {{ Collective\Html\FormFacade::select('category_id', $category,null, array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('account_id', __('Account'),['class'=>'form-label']) }}
                                            {{ Collective\Html\FormFacade::select('account_id', $bank_Account,null, array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Collective\Html\FormFacade::label('bill_number', __('Expense Number'),['class'=>'form-label']) }}
                                            <input type="text" class="form-control" value="{{$expense_number}}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <h5 class="d-inline-block mb-4">{{__('Product & Services')}}</h5>
                <div class="card repeater" data-value='{!! json_encode($items) !!}'>
                    <div class="item-section py-2">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                                <div class="all-button-box me-2">
                                    <a href="#" data-repeater-create="" class="btn btn-primary" data-bs-toggle="modal" data-target="#add-bank">
                                        <i class="ti ti-plus"></i> {{__('Add item')}}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table mb-0" data-repeater-list="items" id="sortable-table">
                                <thead>
                                    <tr>
                                    <th width="20%">{{__('Items')}}</th>
                                    <th>{{__('Quantity')}}</th>
                                    <th>{{__('Price')}} </th>
                                    <th>{{__('Discount')}}</th>
                                    <th>{{__('Tax')}} (%)</th>
                                    <th class="text-end">{{__('Amount')}}
                                        <br><small class="text-danger font-bold">{{__('after tax & discount')}}</small>
                                    </th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody class="ui-sortable" data-repeater-item>
                                    <tr>
                                        {{ Collective\Html\FormFacade::hidden('id',null, array('class' => 'form-control id')) }}
                                        {{ Collective\Html\FormFacade::hidden('account_id',null, array('class' => 'form-control account_id')) }}
                                        <td width="25%" class="form-group pt-0">
                                            {{ Collective\Html\FormFacade::select('items', $product_services,null, array('class' => 'form-control select item','data-url'=>route(ViewsConstants::EXP.'.product'))) }}
                                        </td>
                                        <td>
                                            <div class="form-group price-input input-group search-form">
                                                {{ Collective\Html\FormFacade::text('quantity',null, array('class' => 'form-control quantity','placeholder'=>__('Qty'))) }}
                                                <span class="unit input-group-text bg-transparent"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group price-input input-group search-form">
                                                {{ Collective\Html\FormFacade::text('price',null, array('class' => 'form-control price','placeholder'=>__('Price'))) }}
                                                <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group price-input input-group search-form">
                                                {{ Collective\Html\FormFacade::text('discount',null, array('class' => 'form-control discount','placeholder'=>__('Discount'))) }}
                                                <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="taxes"></div>
                                                    {{ Collective\Html\FormFacade::hidden('tax','', array('class' => 'form-control tax')) }}
                                                    {{ Collective\Html\FormFacade::hidden('itemTaxPrice','', array('class' => 'form-control itemTaxPrice')) }}
                                                    {{ Collective\Html\FormFacade::hidden('itemTaxRate','', array('class' => 'form-control itemTaxRate')) }}
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-end amount">
                                            0.00
                                        </td>

                                        <td>
                                            @can('delete bill product')
                                                <a href="#" class="{{ ViewClassNamesConstants::TRS_PARA }}" data-repeater-delete></a>
                                            @endcan
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="form-group">
                                            {{ Collective\Html\FormFacade::select('chart_account_id', $chartAccounts,null, array('class' => 'form-control select js-searchBox')) }}
                                        </td>
                                        <td class="form-group">
                                            <div class="input-group ">
                                                {{ Collective\Html\FormFacade::text('amount',null, array('class' => 'form-control accountAmount','placeholder'=>__('Amount'))) }}
                                                <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
                                            </div>
                                        </td>

                                        <td colspan="2" class="form-group">
                                                {{ Collective\Html\FormFacade::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'1','placeholder'=>__('Description')]) }}
                                        </td>
                                        <td></td>
                                        <td class="text-end accountamount">
                                            0.00
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td></td>
                                        <td><strong>{{__('Sub Total')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                        <td class="text-end subTotal">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td></td>
                                        <td><strong>{{__('Discount')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                        <td class="text-end totalDiscount">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td></td>
                                        <td><strong>{{__('Tax')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                        <td class="text-end totalTax">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td class="blue-text"><strong>{{__('Total Amount')}} ({{\Auth::user()->currencySymbol()}})</strong></td>

                                        <td class="blue-text text-end totalAmount">0.00</td>
                                        {{ Collective\Html\FormFacade::hidden('totalAmount',null, array('class' => 'form-control totalAmount')) }}

                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="button" value="{{__('Cancel')}}" onclick="location.href = ' {{ route(ViewsConstants::EXP.'.index') }}';" class="btn btn-light me-3">
                <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
            </div>
        {{ Collective\Html\FormFacade::close() }}
    </div>
@endsection

