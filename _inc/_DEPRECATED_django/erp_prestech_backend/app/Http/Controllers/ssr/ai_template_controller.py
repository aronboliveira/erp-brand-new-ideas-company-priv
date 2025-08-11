import json
import logging
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import render, redirect
from django.views.decorators.csrf import csrf_exempt
from django.views.decorators.http import require_POST
from django.utils.decorators import method_decorator
from ....Models.shapes.template import Template
from .... import settings
from .._helpers.http import get_redirect_url
from .._helpers.open_ai_client import open_ai_client
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class AiTemplateController(Controller):
  def create(self, request: HttpRequest, module_name: str) -> HttpResponse:
    try:
      templates = Template.objects.filter(module=module_name)
      return render(request, 'template/generate_ai.html', {'templateName': templates})
    except Exception as e:
      logger.error("Failed to execute AiTemplateController.create: %s", e)
      return redirect(get_redirect_url(request))
  
  @method_decorator(csrf_exempt)
  def get_keywords(self, request: HttpRequest, id: int) -> JsonResponse:
    try:
      template = Template.objects.get(pk=id)
      field_data = json.loads(template.field_json)
    except Template.DoesNotExist as e:
      logger.error("Template not found in AiTemplateController.get_keywords (id: %s): %s", id, e)
      return JsonResponse({'success': False, 'message': 'Template not found'}, status=404)
    except Exception as e:
      logger.error("Failed in AiTemplateController.get_keywords: %s", e)
      return JsonResponse({'success': False, 'message': 'An error occurred'}, status=500)
    html = ""
    for value in field_data.get("field", []):
      html += f'<div class="form-group col-md-12"><label class="form-label">{value["label"]}</label>'
      if value["field_type"] == "text_box":
        html += f'<input type="text" class="form-control" name="{value["field_name"]}" value="" placeholder="{value["placeholder"]}" required>'
      elif value["field_type"] == "textarea":
        html += f'<textarea rows=3 class="form-control" name="{value["field_name"]}" placeholder="{value["placeholder"]}" required></textarea>'
      html += '</div>'
    return JsonResponse({'success': True, 'tone': template.is_tone, 'template': html})
  
  @method_decorator(csrf_exempt)
  @method_decorator(require_POST)
  def ai_generate(self, request: HttpRequest) -> JsonResponse:
    post = request.POST.copy()
    reserved_keys = ['_token', 'template_name', 'tone', 'ai_creativity', 'num_of_result', 'result_length']
    input_fields = {k: v for k, v in post.items() if k not in reserved_keys}
    try:
      template = Template.objects.get(id=request.POST['template_name'])
      field_data = json.loads(template.field_json).get("field", [])
    except Template.DoesNotExist as e:
      logger.error("Template not found in AiTemplateController.ai_generate (template_name: %s): %s", request.POST.get('template_name'), e)
      return JsonResponse({'status': 'error', 'message': 'Template not found'})
    except Exception as e:
      logger.error("Failed in AiTemplateController.ai_generate during template retrieval: %s", e)
      return JsonResponse({'status': 'error', 'message': 'An error occurred while retrieving template'})
    prompt = template.prompt
    for field in field_data:
      name = field["field_name"]
      value = input_fields.get(name)
      if not value:
        logger.error("Missing required field: %s", name)
        return JsonResponse({'status': 'error', 'message': f'Missing required field: {name}'})
      prompt = prompt.replace(f"##{name}##", value)
    prompt = prompt.replace("##tone_language##", request.POST.get('tone', 'formal')) if template.is_tone else prompt
    prompt += f"\n\nProvide response in {request.POST.get('language', 'English')} language."
    try:
      key = settings.CHAT_GPT_KEY
      client = open_ai_client(api_key=key)
      response = client.generate_completion(
        prompt=prompt,
        temperature=float(request.POST.get("ai_creativity", 1)),
        max_tokens=int(request.POST.get("result_length", 200)),
        n=int(request.POST.get("num_of_result", 1))
      )
      text = ""
      for idx, choice in enumerate(response.get("choices", []), start=1):
        text += f"{idx}. {choice['text'].strip()}\n\n"
      return JsonResponse({'status': 'success', 'data': text.strip()})
    except Exception as e:
      logger.error("Failed in AiTemplateController.ai_generate: %s", e)
      return JsonResponse({'status': 'error', 'message': str(e)})
  
  def grammar(self, request: HttpRequest, module_name: str) -> HttpResponse:
    try:
      template = Template.objects.filter(module=module_name).first()
      return render(request, 'template/grammar_ai.html', {'templateName': template})
    except Exception as e:
      logger.error("Failed in AiTemplateController.grammar: %s", e)
      return redirect(get_redirect_url(request))
  
  @method_decorator(csrf_exempt)
  @method_decorator(require_POST)
  def grammar_process(self, request: HttpRequest) -> JsonResponse:
    description = request.POST.get('description')
    if not description:
      return JsonResponse({'status': 'error', 'message': 'Missing description'}, status=400)
    prompt = f"Please correct grammar and spelling mistakes in this: {description}"
    try:
      key = settings.CHAT_GPT_KEY
      client = open_ai_client(api_key=key)
      response = client.generate_completion(
        prompt=prompt,
        temperature=1.0,
        max_tokens=len(description),
        n=1
      )
      return JsonResponse({'status': 'success', 'data': response.get("choices", [{}])[0].get("text", "").strip()})
    except Exception as e:
      logger.error("Failed in AiTemplateController.grammar_process: %s", e)
      return JsonResponse({'status': 'error', 'message': str(e)})
