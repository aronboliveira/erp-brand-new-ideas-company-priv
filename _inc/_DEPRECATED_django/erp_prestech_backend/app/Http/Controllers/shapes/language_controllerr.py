import json, os, shutil, logging, inspect
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import redirect, render
from ....Models.companies.vendor import Vendor
from ....Models.configs.settings import Setting
from ....Models.individuals.customer import Customer
from ....Models.individuals.user import User
from ....Models.shapes.language import Language
from ....Models.utils.utility import Utility
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)

class LanguageController(Controller):

    def change_language(self, request: HttpRequest, lang: str) -> HttpResponse:
        C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            self.authorize('update')
            user = request.user
            user.lang = lang
            user.save()
            value = 'on' if lang in ('ar', 'he') else 'off'
            Setting.objects.update_or_create(
                created_by=user.creator_id(),
                name='SITE_RTL',
                defaults={'value': value}
            )
            messages.success(request, 'Language change successfully.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    def manage_language(self, request: HttpRequest, currant_lang: str) -> HttpResponse:
        C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        import ast, subprocess
        from json import JSONDecodeError
        try:
            self.authorize('viewAny')
            if request.user.type != 'super admin':
                raise PermissionDenied('viewAny')
            languages = list(Language.objects.values_list('full_name', 'code'))
            settings_data = Utility.settings()
            disabled_lang = settings_data.get('disable_lang', '').split(',') if settings_data.get('disable_lang') else []
            base_dir = os.path.join('resources', 'lang')
            dir_path = os.path.join(base_dir, currant_lang)
            if not os.path.isdir(dir_path):
                dir_path = os.path.join(base_dir, 'en')
            arr_label = {}
            label_file = f"{dir_path}.json"
            if os.path.exists(label_file):
                with open(label_file) as f:
                    arr_label = json.load(f)
            arr_message = {}
            for fname in os.listdir(dir_path):
                base, ext = os.path.splitext(fname)
                full_path = os.path.join(dir_path, fname)
                try:
                    if ext == '.py':
                        src = open(full_path, 'r').read()
                        tree = ast.parse(src, full_path)
                        for node in tree.body:
                            if isinstance(node, ast.Assign) and hasattr(node, 'value'):
                                arr_message[base] = ast.literal_eval(node.value)
                                break
                    elif ext == '.js':
                        result = subprocess.run(
                            ['node', '-e', f"console.log(JSON.stringify(require('{full_path}')));"],
                            capture_output=True, text=True, check=True
                        )
                        arr_message[base] = json.loads(result.stdout)
                except JSONDecodeError as e:
                    logger.error(f'{REF} raised Decoding error: {e}')
                except Exception as e:
                    logger.error(f'{REF} raised an unexpected error: {e}')
            return render(request, 'lang/index.html', {
                'arr_label':     arr_label,
                'arr_message':   arr_message,
                'currant_lang':  currant_lang,
                'disabled_lang': disabled_lang,
                'languages':     languages,
                'settings':      settings_data,
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @transaction.atomic
    def store_language_data(self, request: HttpRequest, currant_lang: str) -> HttpResponse:
        C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            self.authorize('update')
            if request.user.type != 'super admin':
                raise PermissionDenied('update')
            base = os.path.join('resources', 'lang')
            os.makedirs(base, exist_ok=True)
            json_path = os.path.join(base, f"{currant_lang}.json")
            if 'label' in request.POST:
                open(json_path, 'w').write(json.dumps(request.POST['label']))
            folder = os.path.join(base, currant_lang)
            os.makedirs(folder, exist_ok=True)
            for fname, data in request.POST.get('message', {}).items():
                path = os.path.join(folder, f"{fname}.php")
                content = "<?php return [" + self.build_array(data) + "]"
                open(path, 'w').write(content)
            messages.success(request, 'Language save successfully.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @staticmethod
    def build_array(data: dict) -> str:
        parts = []
        for k, v in data.items():
            if isinstance(v, dict):
                parts.append(f"'{k}'=>[{LanguageController.build_array(v)}],")
            else:
                esc = v.replace("'", "\\'")
                parts.append(f"'{k}'=>'{esc}',")
        return ''.join(parts)

    def create_language(self, request: HttpRequest) -> HttpResponse:
        C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            self.authorize('create')
            return render(request, 'lang/create.html')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @transaction.atomic
    def store_language(self, request: HttpRequest) -> HttpResponse:
        C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            self.authorize('create')
            if request.user.type != 'super admin':
                raise PermissionDenied('create')
            code = request.POST['code'].lower()
            full = request.POST['full_name']
            base = os.path.join('resources', 'lang')
            os.makedirs(base, exist_ok=True)
            shutil.copy(os.path.join(base, 'en.json'), os.path.join(base, f"{code}.json"))
            shutil.copytree(os.path.join(base, 'en'), os.path.join(base, code), dirs_exist_ok=True)
            Language.objects.get_or_create(code=code, defaults={'full_name': full})
            messages.success(request, 'Language successfully created.')
            return redirect('manage.language', code)
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    def destroy_lang(self, request: HttpRequest, lang: str) -> HttpResponse:
        C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            self.authorize('delete')
            default_lang = os.getenv('default_language', 'en')
            base = os.path.join('resources', 'lang')
            shutil.rmtree(os.path.join(base, lang), ignore_errors=True)
            fp = os.path.join(base, f"{lang}.json")
            if os.path.exists(fp):
                os.remove(fp)
            User.objects.filter(lang=lang).update(lang=default_lang)
            Customer.objects.filter(lang=lang).update(lang=default_lang)
            Vendor.objects.filter(lang=lang).update(lang=default_lang)
            Language.objects.filter(code=lang).delete()
            messages.success(request, 'Language Deleted Successfully.')
            return redirect('manage.language', default_lang)
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    def disable_lang(self, request: HttpRequest) -> JsonResponse:
        C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            self.authorize('update')
            if request.user.type != 'super admin':
                raise PermissionDenied('update')
            settings_data = Utility.settings()
            disabled = settings_data.get('disable_lang', '').split(',') if settings_data.get('disable_lang') else []
            lang = request.POST['lang']
            mode = request.POST.get('mode')
            disabled = [l for l in disabled if l != lang] if mode == 'on' else disabled + [lang]
            Setting.objects.update_or_create(
                created_by=request.user.creator_id(),
                name='disable_lang',
                defaults={'value': ','.join(disabled)}
            )
            msg = 'Language Enabled Successfully' if mode == 'on' else 'Language Disabled Successfully'
            return JsonResponse({'status': 'success', 'message': msg})
        except PermissionDenied as e:
            default_permission_denial(request, err=e, ref=REF, logger=logger, auto_redirect=False, json={'status':'error','message':'Permission denied.'})
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            return JsonResponse({'status': 'error', 'message': 'Unable to update setting.'})
