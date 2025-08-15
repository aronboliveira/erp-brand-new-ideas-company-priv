@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        YieldingConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use Illuminate\Support\Str;
    $result = json_decode($project->copylinksetting);
@endphp
@extends(ExtendingLayoutsConstants::SPJ)
@section(YieldingConstants::SHR_PRJ_PG_TTL)
    {{ __('Projects Details') }}
@endsection
@push(StacksConstants::SHR_PRJ_SCR_PG)
    <script async>
        window.translations = {
            ar:{users_load_unavailable:'تعذّر تحميل أعضاء المشروع',chart_timesheet_unavailable:'تعذّر عرض مخطط الجداول الزمنية',chart_task_unavailable:'تعذّر عرض مخطط المهام',invite_unavailable:'تعذّر دعوة المستخدم',scrollspy_unavailable:'تعذّر تهيئة ScrollSpy',timesheet_table_unavailable:'تعذّر تحميل جدول الجداول الزمنية',timesheet_popup_unavailable:'تعذّر فتح نافذة الجدول الزمني',task_addrow_unavailable:'تعذّر إضافة صف المهمة',time_calc_unavailable:'تعذّر حساب الوقت',images_view_unavailable:'تعذّر عرض الصور',image_remove_unavailable:'تعذّر إزالة الصورة'},
            da:{users_load_unavailable:'Kunne ikke indlæse projektbrugere',chart_timesheet_unavailable:'Kunne ikke vise timesheet-diagram',chart_task_unavailable:'Kunne ikke vise opgavediagram',invite_unavailable:'Kunne ikke invitere bruger',scrollspy_unavailable:'Kunne ikke initialisere ScrollSpy',timesheet_table_unavailable:'Kunne ikke indlæse timesheet-tabel',timesheet_popup_unavailable:'Kunne ikke åbne timesheet-popup',task_addrow_unavailable:'Kunne ikke tilføje opgaverække',time_calc_unavailable:'Kunne ikke beregne tid',images_view_unavailable:'Kan ikke vise billeder',image_remove_unavailable:'Kan ikke fjerne billede'},
            de:{users_load_unavailable:'Projektmitglieder konnten nicht geladen werden',chart_timesheet_unavailable:'Zeiterfassungsdiagramm konnte nicht angezeigt werden',chart_task_unavailable:'Aufgabendagramm konnte nicht angezeigt werden',invite_unavailable:'Benutzer konnte nicht eingeladen werden',scrollspy_unavailable:'ScrollSpy konnte nicht initialisiert werden',timesheet_table_unavailable:'Zeiterfassungstabelle konnte nicht geladen werden',timesheet_popup_unavailable:'Zeiterfassungs-Popup konnte nicht geöffnet werden',task_addrow_unavailable:'Aufgabenzeile konnte nicht hinzugefügt werden',time_calc_unavailable:'Zeit konnte nicht berechnet werden',images_view_unavailable:'Bilder konnten nicht angezeigt werden',image_remove_unavailable:'Bild konnte nicht entfernt werden'},
            en:{users_load_unavailable:'Cannot load project members',chart_timesheet_unavailable:'Cannot render timesheet chart',chart_task_unavailable:'Cannot render task chart',invite_unavailable:'Cannot invite user',scrollspy_unavailable:'Cannot init ScrollSpy',timesheet_table_unavailable:'Cannot load timesheet table',timesheet_popup_unavailable:'Cannot open timesheet popup',task_addrow_unavailable:'Cannot add task row',time_calc_unavailable:'Cannot compute time',images_view_unavailable:'Cannot view images',image_remove_unavailable:'Cannot remove image'},
            es:{users_load_unavailable:'No se pueden cargar los miembros del proyecto',chart_timesheet_unavailable:'No se puede renderizar el gráfico de partes',chart_task_unavailable:'No se puede renderizar el gráfico de tareas',invite_unavailable:'No se puede invitar al usuario',scrollspy_unavailable:'No se puede inicializar ScrollSpy',timesheet_table_unavailable:'No se puede cargar la tabla de partes',timesheet_popup_unavailable:'No se puede abrir el popup de parte',task_addrow_unavailable:'No se puede añadir fila de tarea',time_calc_unavailable:'No se puede calcular el tiempo',images_view_unavailable:'No se pueden ver imágenes',image_remove_unavailable:'No se puede eliminar la imagen'},
            fr:{users_load_unavailable:'Impossible de charger les membres du projet',chart_timesheet_unavailable:'Impossible d’afficher le graphique des feuilles de temps',chart_task_unavailable:'Impossible d’afficher le graphique des tâches',invite_unavailable:'Impossible d’inviter l’utilisateur',scrollspy_unavailable:'Impossible d’initialiser ScrollSpy',timesheet_table_unavailable:'Impossible de charger le tableau des feuilles de temps',timesheet_popup_unavailable:'Impossible d’ouvrir la fenêtre des feuilles de temps',task_addrow_unavailable:'Impossible d’ajouter une ligne de tâche',time_calc_unavailable:'Impossible de calculer le temps',images_view_unavailable:'Impossible d’afficher les images',image_remove_unavailable:'Impossible de supprimer l’image'},
            he:{users_load_unavailable:'לא ניתן לטעון חברי פרויקט',chart_timesheet_unavailable:'לא ניתן להציג תרשים גיליונות זמנים',chart_task_unavailable:'לא ניתן להציג תרשים משימות',invite_unavailable:'לא ניתן להזמין משתמש',scrollspy_unavailable:'לא ניתן לאתחל ScrollSpy',timesheet_table_unavailable:'לא ניתן לטעון טבלת גיליונות זמנים',timesheet_popup_unavailable:'לא ניתן לפתוח חלון קופץ',task_addrow_unavailable:'לא ניתן להוסיף שורת משימה',time_calc_unavailable:'לא ניתן לחשב זמן',images_view_unavailable:'לא ניתן להציג תמונות',image_remove_unavailable:'לא ניתן להסיר תמונה'},
            it:{users_load_unavailable:'Impossibile caricare i membri del progetto',chart_timesheet_unavailable:'Impossibile mostrare il grafico dei timesheet',chart_task_unavailable:'Impossibile mostrare il grafico delle attività',invite_unavailable:'Impossibile invitare l’utente',scrollspy_unavailable:'Impossibile inizializzare ScrollSpy',timesheet_table_unavailable:'Impossibile caricare la tabella timesheet',timesheet_popup_unavailable:'Impossibile aprire il popup timesheet',task_addrow_unavailable:'Impossibile aggiungere riga attività',time_calc_unavailable:'Impossibile calcolare il tempo',images_view_unavailable:'Impossibile visualizzare immagini',image_remove_unavailable:'Impossibile rimuovere immagine'},
            ja:{users_load_unavailable:'プロジェクトメンバーを読み込めません',chart_timesheet_unavailable:'工数チャートを表示できません',chart_task_unavailable:'タスクチャートを表示できません',invite_unavailable:'ユーザーを招待できません',scrollspy_unavailable:'ScrollSpy を初期化できません',timesheet_table_unavailable:'工数テーブルを読み込めません',timesheet_popup_unavailable:'工数ポップアップを開けません',task_addrow_unavailable:'タスク行を追加できません',time_calc_unavailable:'時間を計算できません',images_view_unavailable:'画像を表示できません',image_remove_unavailable:'画像を削除できません'},
            nl:{users_load_unavailable:'Kan projectleden niet laden',chart_timesheet_unavailable:'Kan timesheetgrafiek niet weergeven',chart_task_unavailable:'Kan taakgrafiek niet weergeven',invite_unavailable:'Kan gebruiker niet uitnodigen',scrollspy_unavailable:'Kan ScrollSpy niet initialiseren',timesheet_table_unavailable:'Kan timesheettabel niet laden',timesheet_popup_unavailable:'Kan timesheet-pop-up niet openen',task_addrow_unavailable:'Kan taakrij niet toevoegen',time_calc_unavailable:'Kan tijd niet berekenen',images_view_unavailable:'Kan afbeeldingen niet bekijken',image_remove_unavailable:'Kan afbeelding niet verwijderen'},
            pl:{users_load_unavailable:'Nie można wczytać członków projektu',chart_timesheet_unavailable:'Nie można wyświetlić wykresu timesheet',chart_task_unavailable:'Nie można wyświetlić wykresu zadań',invite_unavailable:'Nie można zaprosić użytkownika',scrollspy_unavailable:'Nie można zainicjować ScrollSpy',timesheet_table_unavailable:'Nie można wczytać tabeli timesheet',timesheet_popup_unavailable:'Nie można otworzyć okna timesheet',task_addrow_unavailable:'Nie można dodać wiersza zadania',time_calc_unavailable:'Nie można obliczyć czasu',images_view_unavailable:'Nie można wyświetlić obrazów',image_remove_unavailable:'Nie można usunąć obrazu'},
            pt:{users_load_unavailable:'Não foi possível carregar os membros do projeto',chart_timesheet_unavailable:'Não foi possível renderizar o gráfico de horas',chart_task_unavailable:'Não foi possível renderizar o gráfico de tarefas',invite_unavailable:'Não foi possível convidar o usuário',scrollspy_unavailable:'Não foi possível iniciar o ScrollSpy',timesheet_table_unavailable:'Não foi possível carregar a tabela de horas',timesheet_popup_unavailable:'Não foi possível abrir o modal de horas',task_addrow_unavailable:'Não foi possível adicionar linha de tarefa',time_calc_unavailable:'Não foi possível calcular o tempo',images_view_unavailable:'Não foi possível exibir as imagens',image_remove_unavailable:'Não foi possível remover a imagem'},
            'pt-br':{users_load_unavailable:'Não foi possível carregar os membros do projeto',chart_timesheet_unavailable:'Não foi possível renderizar o gráfico de horas',chart_task_unavailable:'Não foi possível renderizar o gráfico de tarefas',invite_unavailable:'Não foi possível convidar o usuário',scrollspy_unavailable:'Não foi possível iniciar o ScrollSpy',timesheet_table_unavailable:'Não foi possível carregar a tabela de horas',timesheet_popup_unavailable:'Não foi possível abrir o modal de horas',task_addrow_unavailable:'Não foi possível adicionar linha de tarefa',time_calc_unavailable:'Não foi possível calcular o tempo',images_view_unavailable:'Não foi possível exibir as imagens',image_remove_unavailable:'Não foi possível remover a imagem'},
            ru:{users_load_unavailable:'Не удалось загрузить участников проекта',chart_timesheet_unavailable:'Не удалось отобразить график табеля',chart_task_unavailable:'Не удалось отобразить график задач',invite_unavailable:'Не удалось пригласить пользователя',scrollspy_unavailable:'Не удалось инициализировать ScrollSpy',timesheet_table_unavailable:'Не удалось загрузить таблицу табеля',timesheet_popup_unavailable:'Не удалось открыть окно табеля',task_addrow_unavailable:'Не удалось добавить строку задачи',time_calc_unavailable:'Не удалось вычислить время',images_view_unavailable:'Не удалось просмотреть изображения',image_remove_unavailable:'Не удалось удалить изображение'},
            tr:{users_load_unavailable:'Proje üyeleri yüklenemedi',chart_timesheet_unavailable:'Zaman çizelgesi grafiği oluşturulamadı',chart_task_unavailable:'Görev grafiği oluşturulamadı',invite_unavailable:'Kullanıcı davet edilemedi',scrollspy_unavailable:'ScrollSpy başlatılamadı',timesheet_table_unavailable:'Zaman çizelgesi tablosu yüklenemedi',timesheet_popup_unavailable:'Zaman çizelgesi açılır penceresi açılamadı',task_addrow_unavailable:'Görev satırı eklenemedi',time_calc_unavailable:'Süre hesaplanamadı',images_view_unavailable:'Görseller görüntülenemiyor',image_remove_unavailable:'Görsel kaldırılamıyor'},
            zh:{users_load_unavailable:'无法加载项目成员',chart_timesheet_unavailable:'无法渲染工时图表',chart_task_unavailable:'无法渲染任务图表',invite_unavailable:'无法邀请用户',scrollspy_unavailable:'无法初始化 ScrollSpy',timesheet_table_unavailable:'无法加载工时表',timesheet_popup_unavailable:'无法打开工时弹窗',task_addrow_unavailable:'无法添加任务行',time_calc_unavailable:'无法计算时间',images_view_unavailable:'无法查看图片',image_remove_unavailable:'无法删除图片'}
        };
    </script>
    <script defer>
        (()=>{
            const ERR_FB='# ERROR';
            const DATA_CLIENT_LOCALIZED='data-client-localized';
            const DATA_GUARD_MSG='data-guard-msg';
            const DATA_LISTENER_ADDED='data-listener-added';
            const DATA_RENDERED='data-chart-rendered';

            const getLocalizedMessage=(el,key)=>{
            let msg=ERR_FB;
            if(el?.getAttribute('data-sv-localized')==='true'||el?.getAttribute(DATA_CLIENT_LOCALIZED)==='true'){
                msg=el.getAttribute(DATA_GUARD_MSG)||ERR_FB;
            }else{
                let lang=(sessionStorage.getItem('erp-np-lang')||document.documentElement.lang||'en').toLowerCase().replace(/_/g,'-');
                lang=lang==='pt-br'?lang:lang.slice(0,2);
                msg=window.translations?.[lang]?.[key]||el?.getAttribute(DATA_GUARD_MSG)||window.translations?.en?.[key]||ERR_FB;
                if(msg!==ERR_FB){ el?.setAttribute(DATA_GUARD_MSG,msg); el?.setAttribute(DATA_CLIENT_LOCALIZED,'true'); }
            }
            return msg;
            };

            const showToast=(text)=>{
            const hasBootstrap=document.querySelector('link[href*="bootstrap"]')&&window.bootstrap?.Toast;
            if(hasBootstrap){
                if(!document.querySelector('#error-toast')){
                const t=document.createElement('div');
                t.id='error-toast';
                t.className='toast align-items-center text-bg-danger border-0';
                t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                t.innerHTML=`<div class="d-flex"><div class="toast-body">${text}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
                document.body.appendChild(t);
                }
                new bootstrap.Toast(document.querySelector('#error-toast')).show();
            }else{ alert(text); }
            };

            const attachGuardOnce=(el,key,ev='pointerup')=>{
            if(!el||el.getAttribute(DATA_LISTENER_ADDED)==='true') return;
            const handler=()=>showToast(getLocalizedMessage(el,key));
            el.addEventListener(ev,handler,{once:true});
            el.setAttribute(DATA_LISTENER_ADDED,'true');
            const mo=new MutationObserver((_,o)=>{ if(!document.body.contains(el)){ el.removeEventListener(ev,handler); o.disconnect(); }});
            mo.observe(document.body,{childList:true,subtree:true});
            };

            const renderChart=(selector, options, key)=>{
            try{
                if(typeof ApexCharts==='undefined'){ console.error('ApexCharts library missing'); attachGuardOnce(document.querySelector(selector)||document.body,key); return; }
                const el=document.querySelector(selector);
                if(!el){ attachGuardOnce(document.body,key); return; }
                if(el.getAttribute(DATA_RENDERED)==='true') return;
                const chart=new ApexCharts(el, options);
                chart.render();
                el.setAttribute(DATA_RENDERED,'true');
            }catch{ attachGuardOnce(document.querySelector(selector)||document.body,key); }
            };

            const routeGuard=(el)=>{
            const url = el?.getAttribute?.('data-url');
            const href = el?.getAttribute?.('action') || el?.getAttribute?.('href');
            return (!url || url==='#') && (!href || href==='#');
            };

            const loadProjectUser=()=>{
            const mainEle=$('#project_users');
            const project_id='{{ $project->id }}';
            try{
                $.ajax({
                url:'{{ route('project.user') }}',
                data:{ project_id },
                beforeSend:()=>{ $('#project_users').html('<tr><th colspan="2" class="h6 text-center pt-5">{{ __("Loading...") }}</th></tr>'); },
                success:(data)=>{ mainEle.html(data.html); $('[id^=fire-modal]').remove(); },
                error:()=>attachGuardOnce(mainEle.get(0),'users_load_unavailable')
                });
            }catch{ attachGuardOnce(mainEle.get(0),'users_load_unavailable'); }
            };

            try{
            if(typeof $==='undefined'){ console.error('jQuery is required'); return; }

            // Initial data
            try{ loadProjectUser(); }catch{ attachGuardOnce(document.body,'users_load_unavailable'); }

            // Charts (safe after DOM parse)
            (function(){
                const options={ chart:{ type:'area', height:60, sparkline:{enabled:true} }, colors:['#ffa21d'], dataLabels:{enabled:false}, stroke:{curve:'smooth',width:2},
                series:[{ name:'Bandwidth', data: {{ json_encode(array_map('intval',$project_data['timesheet_chart']['chart'])) }} }],
                tooltip:{ followCursor:false, fixed:{enabled:false}, x:{show:false}, y:{ title:{ formatter:()=>'' } }, marker:{show:false} }
                };
                renderChart('#timesheet_chart', options, 'chart_timesheet_unavailable');
            })();

            (function(){
                const options={ chart:{ type:'area', height:60, sparkline:{enabled:true} }, colors:['#ffa21d'], dataLabels:{enabled:false}, stroke:{curve:'smooth',width:2},
                series:[{ name:'Bandwidth', data: {{ json_encode($project_data['task_chart']['chart']) }} }],
                tooltip:{ followCursor:false, fixed:{enabled:false}, x:{show:false}, y:{ title:{ formatter:()=>'' } }, marker:{show:false} }
                };
                renderChart('#task_chart', options, 'chart_task_unavailable');
            })();

            // Invite user (stateful → guard on pointerup)
            $(document).on('click','.invite_usr',function(){
                const el=this;
                try{
                const project_id=$('#project_id').val() ?? '{{ $project->id }}';
                const user_id=$(el).attr('data-id') ?? '';
                const url='{{ route(ViewsConstants::PRJ . ".invite.user.member") }}';
                $.ajax({
                    url,
                    method:'POST',
                    dataType:'json',
                    data:{ project_id, user_id, _token:'{{ csrf_token() }}' },
                    success:(data)=>{
                    if(String(data?.code)==='200'){ show_toastr(data.status, data.success, 'success'); setTimeout(()=>location.reload(),5000); loadProjectUser(); }
                    else if(String(data?.code)==='404'){ show_toastr(data.status, data.errors, 'error'); }
                    },
                    error:()=>attachGuardOnce(el,'invite_unavailable')
                });
                }catch{ attachGuardOnce(el,'invite_unavailable'); }
            });

            // Bootstrap ScrollSpy (broad failure allowed to log)
            try{
                if(window.bootstrap?.ScrollSpy){
                new bootstrap.ScrollSpy(document.body,{ target:'#useradd-sidenav', offset:300 });
                }else{ console.error('Bootstrap ScrollSpy not available'); }
            }catch{ attachGuardOnce(document.body,'scrollspy_unavailable','click'); }

            window.check_theme=(color_val)=>{ try{ $('#theme_color').prop('checked',false); $(`input[value="${color_val}"]`).prop('checked',true); }catch{} };

            // Timesheet table filter + pagination arrows (GET heavy → guard on pointerup)
            const ajaxFilterTimesheetTableView=()=>{
                const mainEle=$('#timesheets\\/table-view');
                const notfound=$('.notfound-timesheet');
                const notfound1=$('.notfound-timesheet1');
                const week=parseInt($('#weeknumber').val()||'0',10);
                const project_id='{{ $project->id }}';
                const data={ week, project_id };
                try{
                $.ajax({
                    url:'{{ route('timesheets.filters.table.view') }}',
                    data,
                    success:(res)=>{
                    $('.weekly-dates-div .weekly-dates').text(res.onewWeekDate);
                    $('.weekly-dates-div #selected_dates').val(res.selectedDate);
                    $('#project_tasks').find('option').not(':first').remove();
                    $.each(res.tasks,(i,item)=>{ $('#project_tasks').append($('<option></option>').attr('value',i).text(item)); });
                    if(Number(res.totalrecords)===0){ mainEle.hide(); notfound.css('display','block'); notfound1.hide(); } else { notfound.hide(); mainEle.show(); }
                    mainEle.html(res.html);
                    },
                    error:()=>attachGuardOnce(mainEle.get(0),'timesheet_table_unavailable')
                });
                }catch{ attachGuardOnce(mainEle.get(0),'timesheet_table_unavailable'); }
            };

            $(()=>{ try{ ajaxFilterTimesheetTableView(); }catch{ attachGuardOnce(document.body,'timesheet_table_unavailable'); } });

            $(document).on('click','.weekly-dates-div i',function(){
                try{
                let weeknumber=parseInt($('#weeknumber').val()||'0',10);
                if($(this).hasClass('previous')) weeknumber--;
                else if($(this).hasClass('next')) weeknumber++;
                $('#weeknumber').val(weeknumber);
                ajaxFilterTimesheetTableView();
                }catch{ attachGuardOnce(this,'timesheet_table_unavailable'); }
            });

            // Timesheet modal popup (uses data-url; route verification)
            $(document).on('click','[data-ajax-timesheet-popup="true"]',function(e){
                e.preventDefault();
                const trigger=this;
                try{
                if(routeGuard(trigger)){ attachGuardOnce(trigger,'timesheet_popup_unavailable'); return; }
                const url=$(trigger).data('url');
                const type=$(trigger).data('type');
                const date=$(trigger).data('date');
                const task_id=$(trigger).data('task-id');
                const user_id=$(trigger).data('user-id');
                const p_id=$(trigger).data('project-id');
                const data={ date, task_id };
                if(user_id!==undefined) data.user_id=user_id;
                let title='';
                if(type==='create'){ title='{{ __("Create Timesheet") }}'; data.p_id='{{ $project->id }}'; data.project_id = (data.p_id!=='-1') ? data.p_id : p_id; }
                else if(type==='edit'){ title='{{ __("Edit Timesheet") }}'; }
                $("#commonModal .modal-title").html(`${title} <small>(${moment(date).format("ddd, Do MMM YYYY")})</small>`);
                $.ajax({
                    url,
                    data,
                    dataType:'html',
                    success:(html)=>{ $('#commonModal .body').html(html); $("#commonModal").modal('show'); if(typeof commonLoader==='function') commonLoader(); if(typeof loadConfirm==='function') loadConfirm(); },
                    error:()=>attachGuardOnce(trigger,'timesheet_popup_unavailable')
                });
                }catch{ attachGuardOnce(trigger,'timesheet_popup_unavailable'); }
            });

            // Add task row from select (GET heavy)
            $(document).on('click','#project_tasks',function(){
                const select=this;
                try{
                const mainEle=$('#timesheets\\/table-view');
                const notfound=$('.notfound-timesheet');
                const opt=$(select).children('option:selected');
                const task_id=opt.val();
                const selected_dates=$('#selected_dates').val();
                if(!task_id) return;
                $.ajax({
                    url:'{{ route('timesheets.filters.table.view') }}',
                    data:{ project_id:'{{ $project->id }}', task_id, selected_dates },
                    success:(res)=>{ notfound.hide(); mainEle.show(); $('#timesheets\\/table-view tbody').append(res.html); opt.remove(); },
                    error:()=>attachGuardOnce(select,'task_addrow_unavailable')
                });
                }catch{ attachGuardOnce(select,'task_addrow_unavailable'); }
            });

            // Total time calculator (pure UI)
            $(document).on('change','#time_hour, #time_minute',function(){
                try{
                let hour=$('#time_hour').children('option:selected').val()||'0';
                let minute=$('#time_minute').children('option:selected').val()||'0';
                const total=String($('#totaltasktime').val()||'0:0').split(':');
                if(hour==='00'&&minute==='00'){ $(this).val(''); return; }
                hour=parseInt(hour||'0',10)+parseInt(total[0]||'0',10);
                minute=parseInt(minute||'0',10)+parseInt(total[1]||'0',10);
                if(minute>50){ minute-=60; hour++; }
                const hh=hour<10?`0${hour}`:`${hour}`;
                const mm=minute<10?`0${minute}`:`${minute}`;
                $('.display-total-time span').text(`{{ __('Total Time') }} : ${hh} {{ __('Hours') }} ${mm} {{ __('Minutes') }}`);
                }catch{ attachGuardOnce(this,'time_calc_unavailable','click'); }
            });

            // Swiper gallery helpers (user-triggered; large GET => pointerup)
            const init_slider=()=>{
                if(!$('.product-left').length) return;
                if(typeof Swiper==='undefined'){ console.error('Swiper library missing'); return; }
                const productSlider=new Swiper('.product-slider',{ spaceBetween:0, centeredSlides:false, loop:false, direction:'horizontal', loopedSlides:5, navigation:{ nextEl:'.swiper-button-next', prevEl:'.swiper-button-prev' }, resizeObserver:true });
                const productThumbs=new Swiper('.product-thumbs',{ spaceBetween:0, centeredSlides:true, loop:false, slideToClickedSlide:true, direction:'horizontal', slidesPerView:7, loopedSlides:5 });
                // eslint-disable-next-line no-unused-expressions
                productSlider.controller && (productSlider.controller.control=productThumbs);
                // eslint-disable-next-line no-unused-expressions
                productThumbs.controller && (productThumbs.controller.control=productSlider);
            };

            $(document).on('click','.view-images',function(){
                const el=this;
                try{
                const p_url='{{ route('time_trackers.image.view') }}';
                const data={ id: $(el).attr('data-id') };
                if(typeof postAjax!=='function'){ attachGuardOnce(el,'images_view_unavailable'); return; }
                postAjax(p_url, data, (res)=>{
                    $('.image_sider_div').html(res);
                    $('#exampleModalCenter').modal('show');
                    setTimeout(()=>{ const total=$('.product-left').find('.product-slider').length; if(total>0) init_slider(); },200);
                });
                }catch{ attachGuardOnce(this,'images_view_unavailable'); }
            });

            $(document).on('click','.track-image-remove',function(){
                const rid=$(this).attr('data-pid');
                $('.confirm_yes').addClass('image_remove').attr('image_id', rid);
                $('#cModal').modal('show');
            });

            window.removeImage=(id)=>{
                const el=document.querySelector('.confirm_yes.image_remove')||document.body;
                try{
                const p_url='{{ route('time_trackers.image.remove') }}';
                if(typeof deleteAjax!=='function'){ attachGuardOnce(el,'image_remove_unavailable'); return; }
                deleteAjax(p_url, { id }, (res)=>{
                    if(res.flag){
                    $(`#slide-thum-${id}`).remove();
                    $(`#slide-${id}`).remove();
                    setTimeout(()=>{
                        const total=$('.product-left').find('.swiper-slide').length;
                        if(total>0){ init_slider(); } else { $('.product-left').html('<div class="no-image"><h5 class="text-muted">Images Not Available .</h5></div>'); }
                    },200);
                    }
                    $('#cModal').modal('hide');
                    show_toastr('error', res.msg, 'error');
                });
                }catch{ attachGuardOnce(el,'image_remove_unavailable'); }
            };

            }catch(e){ console.error('Initialization failed', e); }
        })();
    </script>
@endpush
@section(YieldingConstants::SHR_PRJ_ACT_BTN)
    <a href="#" class="pt-3">
        <select name="language" id="language" class="btn btn-primary my-2"
                onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
            @foreach (\App\Models\Utility::languages() as $language)
                <option @if ($lang == $language) selected @endif
                value="{{ route(ViewsConstants::PRJ.'.link',[\Illuminate\Support\Facades\Crypt::encrypt($project->id), $language]) }}">{{ Str::upper($language) }}</option>
            @endforeach
        </select>
    </a>
@endsection
@php
    $logo = \App\Models\Utility::getFile('tasks/');
    $logo_path = \App\Models\Utility::getFile('/');
@endphp
@section(YieldingConstants::SHR_PRJ_CTT)
    <div class="row">
        <div class="col-xl-3">
        @php
            $sections = [
                ['key' => 'basic_details',    'id' => 'basic',           'label' => __('Basic details')],
                ['key' => 'member',           'id' => 'members',         'label' => __('Members')],
                ['key' => 'task',             'id' => 'task',            'label' => __('Task')],
                ['key' => 'milestone',        'id' => 'milestone',       'label' => __('Milestones')],
                ['key' => 'attachment',       'id' => 'attachment',      'label' => __('Files')],
                ['key' => 'bug_report',       'id' => 'bug_report',      'label' => __('Bug Report')],
                ['key' => 'timesheet',        'id' => 'timesheet',       'label' => __('Timesheet')],
                ['key' => 'tracker_details',  'id' => 'tracker_details', 'label' => __('Tracker details')],
                ['key' => 'expense',          'id' => 'expense',         'label' => __('Expense')],
                ['key' => 'activity',         'id' => 'activity',        'label' => __('Activity Log')],
            ];
        @endphp
        <div class="{{ VC::CD_STK }}" style="top:30px">
            <div class="{{ VC::LG_FLSH }}" id="lead-sidenav">
                @foreach($sections as $section)
                    @if(isset($result->{$section['key']}) && $result->{$section['key']} === 'on')
                        <a href="#{{ $section['id'] }}"
                           class="{{ VC::LGI_ACT_NBD }}">
                            {{ $section['label'] }}
                            <div class="float-end">
                                <i class="{{ VC::TI_CHV_RT }}"></i>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
        </div>
        <div class="col-xl-9">
            @if ( isset($result->basic_details) && $result->basic_details == 'on')
                <div id="basic" class="">
                    <div class="row">
                        <div class="col-lg-4 col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="theme-avatar bg-warning">
                                            <i class="ti ti-list"></i>
                                        </div>
                                        <div class="col text-end">
                                            <h6 class="text-muted mb-1">{{ __('Total Task') }}</h6>
                                            <span class="h6 font-weight-bold mb-0 ">{{$project_data['task']['total'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="theme-avatar bg-danger">
                                            <i class="ti ti-check"></i>
                                        </div>
                                        <div class="col text-end">
                                            <h6 class="text-muted mb-1">{{ __('Done Task') }}</h6>
                                            <span class="h6 font-weight-bold mb-0 ">{{ $project_data['task']['done'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="theme-avatar bg-success">
                                            <i class="ti ti-list"></i>
                                        </div>
                                        <div class="col text-end">
                                            <h6 class="text-muted mb-1">{{ __('Total Milestone') }}</h6>
                                            <span class="h6 font-weight-bold mb-0 ">{{ count($project->milestones)}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="{{ VC::CLM4 }}">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            <img {{ $project->img_image }} alt="" class="img-user wid-45 rounded-circle">
                                        </div>
                                        <div class="d-block  align-items-center justify-content-between w-100">
                                            <div class="mb-3 mb-sm-0">
                                                <h5 class="mb-1"> {{$project->project_name}}</h5>
                                                <p class="mb-0 text-sm">
                                                <div class="progress-wrapper">
                                                    <span class="progress-percentage"><small class="font-weight-bold">{{__('Completed:')}} : </small>{{ $project->projectProgressCopy($usr->id)['percentage'] }}</span>
                                                    <div class="progress progress-xs mt-2">
                                                        <div class="progress-bar bg-info" role="progressbar" aria-valuenow="{{ $project->projectProgressCopy($usr->id)['percentage'] }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $project->projectProgressCopy($usr->id)['percentage'] }};"></div>
                                                    </div>
                                                </div>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                        <div class="row">
                                            <div class="col-sm-10">
                                                <h4 class="mt-3 mb-1"></h4>
                                                <p> {{$project->description }}</p>
                                            </div>
                                        </div>
                                        <div class="card bg-primary mb-0">
                                            <div class="card-body">
                                                <div class="d-block d-sm-flex align-items-center justify-content-between">
                                                    <div class="row align-items-center">
                                                        <span class="text-white text-sm">{{__('Start Date')}}</span>
                                                        <h5 class="text-white text-nowrap">{{ Utility::getDateFormated($project->start_date) }}</h5>
                                                    </div>
                                                    <div class="row align-items-center">
                                                        <span class="text-white text-sm">{{__('End Date')}}</span>
                                                        <h5 class="text-white text-nowrap">{{ Utility::getDateFormated($project->end_date) }}</h5>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <span class="text-white text-sm">{{__('Client')}}</span>
                                                    <h5 class="text-white text-nowrap">{{ (!empty($project->client)?$project->client->name: '-') }}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CLM4 }}">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avatar bg-primary">
                                                <i class="ti ti-clipboard-list"></i>
                                            </div>
                                            <div class="ms-3">
                                                <p class="text-muted mb-0">{{__('Last 7 days task done')}}</p>
                                                <h4 class="mb-0">{{ $project_data['task_chart']['total'] }}</h4>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted">{{__('Day Left')}}</span>
                                            </div>
                                            <span>{{ $project_data['day_left']['day'] }}</span>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" style="width: {{ $project_data['day_left']['percentage'] }}%"></div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center">

                                                <span class="text-muted">{{__('Open Task')}}</span>
                                            </div>
                                            <span>{{ $project_data['open_task']['tasks'] }}</span>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" style="width: {{ $project_data['open_task']['percentage'] }}%"></div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted">{{__('Completed Milestone')}}</span>
                                            </div>
                                            <span>{{ $project_data['milestone']['total'] }}</span>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" style="width: {{ $project_data['milestone']['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CLM4 }}">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="theme-avatar bg-primary">
                                                <i class="ti ti-clipboard-list"></i>
                                            </div>
                                            <div class="ms-3">
                                                <p class="text-muted mb-0">{{__('Last 7 days hours spent')}}</p>
                                                <h4 class="mb-0">{{ $project_data['timesheet_chart']['total'] }}</h4>

                                            </div>
                                        </div>

                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted">{{__('Total project time spent')}}</span>
                                            </div>
                                            <span>{{ $project_data['time_spent']['total'] }}</span>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" style="width: {{ $project_data['time_spent']['percentage'] }}%"></div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center">

                                                <span class="text-muted">{{__('Allocated hours on task')}}</span>
                                            </div>
                                            <span>{{ $project_data['task_allocated_hrs']['hrs'] }}</span>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" style="width: {{ $project_data['task_allocated_hrs']['percentage'] }}%"></div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted">{{__('User Assigned')}}</span>
                                            </div>
                                            <span>{{ $project_data['user_assigned']['total'] }}</span>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" style="width: {{ $project_data['user_assigned']['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                </div>
            @endif

            @if ( isset($result->member) && $result->member == 'on')
                    <div id="members" class="col-md-12">
                        <div class="card ">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Members') }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped data-table">
                                        <thead>
                                        <tr>
                                            <th> {{__('Avatar')}}</th>
                                            <th> {{__('Name')}}</th>
                                            <th> {{__('Type')}}</th>
                                            <th> {{__('Email')}}</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        @if ($project->users || $project->users != '' || $project->users != null)
                                            @foreach ($project->users as $user)
                                                <tr>
                                                    <td class="">
                                                        <img @if($user->avatar)  src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif   class = "avatar rounded-circle" style"height:36px;width:36px;">
                                                    </td>
                                                    <td>{{ $user?->name }}</td>
                                                    <td>{{ $user?->type }}</td>
                                                    <td>{{ $user?->email }}</td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ( isset($result->task) && $result->task == 'on')
                    <div id="task" class="">
                        <div class="card" style="background-color:transparent !important">
                            <div class="card-header" style="padding: 25px 35px !important; background-color:#ffffff !important">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="row">
                                        <h5 class="mb-0">{{ __('Task') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th scope="col">{{__('Task')}}</th>
                                            <th scope="col">{{__('Project')}}</th>
                                            <th scope="col">{{__('Stage')}}</th>
                                            <th scope="col">{{__('Assigned To')}}</th>
                                            <th scope="col">{{__('Priority')}}</th>
                                            <th scope="col">{{__('End Date')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody class="list">
                                        @if(!empty(count($tasks)) > 0)
                                            @foreach($tasks as $task)
                                                <tr>
                                                    <td>{{ $task->name }}</td>
                                                    <td>{{ $task->project->project_name }}</td>
                                                    <td>{{ $task->stage->name }}</td>
                                                    <td>
                                                                <div class="avatar-group">
                                                                    @if($task->users()->count() > 0)
                                                                        @if($users = $task->users())
                                                                            @foreach($users as $key => $user)
                                                                                @if($key<3)
                                                                                    <a href="#" class="avatar rounded-circle avatar-sm">
                                                                                        <img data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user?->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif title = "{{ $user?->name }}" class="hweb">
                                                                                    </a>
                                                                                @else
                                                                                    @break
                                                                                @endif
                                                                            @endforeach
                                                                        @endif
                                                                        @if(count($users) > 3)
                                                                            <a href="#" class="avatar rounded-circle avatar-sm">
                                                                                <img  data-original-title="{{(!empty($user)?$user->name:'')}}" @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user?->avatar)}}" @else src = "{{asset('/storage/uploads/avatar/avatar.png')}}" @endif class="hweb">
                                                                            </a>
                                                                        @endif
                                                                    @else
                                                                        {{ __('-') }}
                                                                    @endif
                                                                </div>
                                                            </td>
                                                    <td>
                                                        <span class="status_badge badge p-2 px-3 rounded bg-{{__(\App\Models\ProjectTask::$priority_color[$task->priority])}}">{{ __(\App\Models\ProjectTask::$priority[$task->priority]) }}</span>
                                                    </td>
                                                    <td class="{{ (strtotime($task->end_date) < time()) ? 'text-danger' : '' }}">{{ Utility::getDateFormated($task->end_date) }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <th scope="col" colspan="7"><h6 class="text-center">{{__('No tasks found')}}</h6></th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ( isset($result->milestone) && $result->milestone == 'on')
                    <div id="milestone" class="">
                        <div class="card" style="overflow-x: none;">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Milestones') }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="" class="table  px-2">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('Due Date') }}</th>
                                            <th>{{ __('Task') }}</th>
                                            <th>{{ __('Cost') }}</th>
                                            <th>{{ __('Progress') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(!empty(count($project->milestones)) > 0)
                                            @foreach ($project->milestones as $key => $milestone)
                                            <tr>
                                                <td>{{ $milestone->title }}</td>
                                                <td>
                                                    <span class="badge-xs status_badge badge bg-{{\App\Models\Project::$status_color[$milestone->status]}} p-2 px-3 rounded">
                                                        {{ __(\App\Models\Project::$project_status[$milestone->status]) }}
                                                    </span>
                                                </td>
                                                @if($milestone->start_date)
                                                    <td>{{ $milestone->start_date }}</td>
                                                @else
                                                    <td>-</td>
                                                @endif
                                                @if($milestone->due_date)
                                                    <td>{{ $milestone->due_date }}</td>
                                                @else
                                                    <td>-</td>
                                                @endif
                                                <td>{{ $milestone->tasks->count().' '. __('Tasks') }}</td>
                                                <td>{{$user?->priceFormat($milestone->cost) }}
                                                </td>
                                                <td>
                                                    <div class="progress_wrapper">
                                                        <div class="progress">
                                                            <div class="progress-bar" role="progressbar"
                                                                 style="width: {{ $milestone->progress }}%;"
                                                                 aria-valuenow="55" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                        <div class="progress_labels">
                                                            <div class="total_progress">
                                                                <strong> @if($milestone->progress) {{ $milestone->progress }}% @else 0% @endif</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @else
                                            <tr>
                                                <th scope="col" colspan="7"><h6 class="text-center">{{__('No milestone found')}}</h6></th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ( isset($result->attachment) && $result->attachment == 'on')
                    <div id="attachment" class="">
                        <div class="card" style="overflow-x: none;">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Files') }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    @if($project->projectAttachments()->count() > 0)
                                        @foreach($project->projectAttachments() as $attachment)
                                            <li class="list-group-item px-0">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col mb-3 mb-sm-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="div">
                                                                <h6 class="m-0">{{ $attachment->name }}</h6>
                                                                <small class="text-muted">{{ $attachment->file_size }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto text-sm-end d-flex align-items-center">
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{asset(Storage::url('tasks/'.$attachment->file))}}"  data-bs-toggle="tooltip" title="{{__('Download')}}" class="btn btn-sm" download>
                                                                <i class="ti ti-download text-white"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @else
                                        <div class="py-5">
                                            <h6 class="h6 text-center">{{__('No Attachments Found.')}}</h6>
                                        </div>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @if ( isset($result->bug_report) && $result->bug_report == 'on')
                    <div id="bug_report" >
                        <div class="card" style="background-color:transparent !important">
                            <div class="card-header" style="padding: 25px 35px !important; background-color:#ffffff !important">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="row">
                                        <h5 class="mb-0">{{ __('Bug Report') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table ">
                                        <thead>
                                        <tr>
                                            <th> {{__('Bug Id')}}</th>
                                            <th> {{__('Assign To')}}</th>
                                            <th> {{__('Bug Title')}}</th>
                                            <th> {{__('Start Date')}}</th>
                                            <th> {{__('Due Date')}}</th>
                                            <th> {{__('Status')}}</th>
                                            <th> {{__('Priority')}}</th>
                                            <th> {{__('Created By')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(!empty(count($bugs)) > 0)
                                            @foreach ($bugs as $bug)
                                            <tr>
                                                <td>{{ $user?->bugNumberFormat($bug->bug_id)}}</td>
                                                <td>{{ $user?->bugNumberFormat($bug->bug_id)}}</td>
                                                <td>{{ (!empty($bug->assignTo)?$bug->assignTo->name:'') }}</td>
                                                <td>{{ $bug->title}}</td>
                                                <td>{{ $user?->dateFormat($bug->start_date) }}</td>
                                                <td>{{ $user?->dateFormat($bug->due_date) }}</td>
                                                <td>{{ (!empty($bug->bug_status)?$bug->bug_status->title:'') }}</td>
                                                <td>{{ $bug->priority }}</td>
                                                <td>{{ $bug->createdBy->name }}</td>
                                            </tr>
                                        @endforeach
                                        @else
                                            <tr>
                                                <th scope="col" colspan="7"><h6 class="text-center">{{__('No Bug found')}}</h6></th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ( isset($result->timesheet) && $result->timesheet == 'on')
                    <div id="timesheet" class="">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card notfound-timesheet1">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"> {{ __('Timesheet') }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="" id="timesheets/table-view" style="width:100%;overflow: auto"></div>
                                    </div>
                                </div>

                                <div class="card notfound-timesheet text-center">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"> {{ __('Timesheet') }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="page-error">
                                            <div class="page-inner">
                                                <div class="page-description">
                                                    {{ __("We couldn't find any data") }}
                                                </div>
                                                <div class="page-search">
                                                    <p class="text-muted mt-3">
                                                        {{ __("Sorry we can't find any timesheet records on this week.") }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ( isset($result->tracker_details) && $result->tracker_details == 'on')
                    <div id="tracker_details" class="">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Tracker details') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style ">
                                <div class="table-responsive">
                                    <table class=" table" id="selection-datatable">
                                        <thead>
                                        <tr>
                                            <th> {{ __('Description') }}</th>
                                            <th> {{ __('Project') }}</th>
                                            <th> {{ __('Task') }}</th>
                                            <th> {{ __('Start Time') }}</th>
                                            <th> {{ __('End Time') }}</th>
                                            <th>{{ __('Total Time') }}</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($treckers as $trecker)
                                            @php
                                                $total_name = App\Models\Utility::secondToTime($trecker->total_time);
                                            @endphp
                                            <tr>
                                                <td>{{ __($trecker->name) }}</td>
                                                <td>{{ __($trecker->project_name) }}</td>
                                                <td>{{ __($trecker->project_task) }}</td>
                                                <td>{{ __(date('H:i:s', strtotime($trecker->start_time))) }}</td>
                                                <td>{{ __(date('H:i:s', strtotime($trecker->end_time))) }}</td>
                                                <td>{{ __($total_name) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ( isset($result->expense) && $result->expense == 'on')
                    <div id="expense" >
                        <div class="card" style="background-color:transparent !important">
                            <div class="card-header" style="padding: 25px 35px !important; background-color:#ffffff !important">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="row">
                                        <h5 class="mb-0">{{ __('Expense') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th scope="col">{{__('Attachment')}}</th>
                                            <th scope="col">{{__('Name')}}</th>
                                            <th scope="col">{{__('Task')}}</th>
                                            <th scope="col">{{__('Date')}}</th>
                                            <th scope="col">{{__('Amount')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody class="list">
                                        @if(isset($project->expense) && !empty($project->expense) && count($project->expense) > 0)
                                            @foreach($project->expense as $expense)
                                                <tr>
                                                    <th scope="row">
                                                        @if(!empty($expense->attachment))
                                                            <a href="{{ asset(Storage::url($expense->attachment)) }}" class="btn btn-sm btn-primary btn-icon rounded-pill" data-bs-toggle="tooltip" title="{{__('Download')}}" download>
                                                                <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
                                                            </a>
                                                        @else

                                                        @endif
                                                    </th>
                                                    <td>{{ $expense->name }}</td>
                                                    <td>{{ !empty($expense->task)?$expense->task->name:'-' }}</td>
                                                    <td>{{ (!empty($expense->date)) ? Utility::getDateFormated($expense->date) : '-' }}</td>
                                                    <td>{{ $user?->priceFormat($expense->amount) }}</td>
                                                    <td>{{ $user?->priceFormat($expense->amount) }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <th scope="col" colspan="5"><h6 class="text-center">{{__('No Expense Found.')}}</h6></th>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ( isset($result->activity) && $result->activity == 'on')
                    <div id="activity" class="">
                        <div class="card  activity-scroll">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">{{ __('Activity') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3 vertical-scroll-cards">
                                @if(!empty(count($project->activities)) > 0)
                                    @foreach($project->activities as $activity)
                                    <div class="card p-2 mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="theme-avatar bg-primary">
                                                    <i class="ti {{$activity->logIcon($activity->log_type)}}"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <h6 class="mb-0">{{ __($activity->log_type) }}</h6>
                                                    <p class="text-muted text-sm mb-0">{!! $activity->getRemark() !!}</p>
                                                </div>
                                            </div>
                                            <p class="text-muted text-sm mb-0">{{$activity->created_at->diffForHumans()}}</p>
                                        </div>
                                    </div>
                                @endforeach
                                @else
                                    <tr>
                                        <th scope="col" colspan="7"><h6 class="text-center">{{__('No activities found')}}</h6></th>
                                    </tr>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

        </div>
    </div>
    <div class="{{ VC::MD_FD }}" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg ss_modale " role="document">
            <div class="modal-content image_sider_div">
            </div>
        </div>
    </div>
@endsection

